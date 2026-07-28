<?php

namespace App\Services;

use App\Models\Escala;
use App\Models\Entidade;
use App\Models\Acolito;
use App\Models\AcolitoFuncao;
use App\Models\CelebrationSchedule;
use App\Models\EscalaAcolitoRegra;
use App\Models\EscalaDraft;
use App\Models\EscalaDataHora;
use App\Models\EscaladoData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class EscalaGeneratorService
{
    /**
     * Gera a escala automática criando drafts
     */
    public function generate(Escala $escala, $ent_id, $paroquia_id, $user_id)
    {
        // Months mapping
        $months = [
            'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'abril' => 4,
            'maio' => 5, 'junho' => 6, 'julho' => 7, 'agosto' => 8,
            'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12
        ];
        
        $normalizedMonth = mb_strtolower(trim($escala->month), 'UTF-8');
        $monthNum = $months[$normalizedMonth] ?? date('n');
        $year = $escala->year;

        // Regras da comunidade
        $regras = EscalaAcolitoRegra::where('ent_id', $ent_id)
                                    ->where('paroquia_id', $paroquia_id)
                                    ->get()
                                    ->keyBy('dia_semana');

        // Horários de missa da comunidade
        $horarios = CelebrationSchedule::where('ent_id', $ent_id)
                                       ->where('paroquia_id', $paroquia_id)
                                       ->get();

        // Acólitos e Coroinhas ativos da comunidade
        // status = 1 (active)
        $acolitosAtivos = Acolito::where('ent_id', $ent_id)
                                 ->where('paroquia_id', $paroquia_id)
                                 ->where('status', 1)
                                 ->get();

        $acolitosList = $acolitosAtivos->where('type', 0)->values();
        $coroinhasList = $acolitosAtivos->where('type', 1)->values();
        
        $funcoes = AcolitoFuncao::where('paroquia_id', $paroquia_id)->get();

        // Puxar histórico do mês passado para balanceamento
        $lastMonthNum = $monthNum - 1;
        $lastYear = $year;
        if ($lastMonthNum == 0) {
            $lastMonthNum = 12;
            $lastYear = $year - 1;
        }
        
        // Find last month's scales for this community to get historical counts
        $historico = [];
        $lastEscalas = Escala::where('year', $lastYear)
                             ->where('paroquia_id', $paroquia_id)
                             // where month equals name of lastMonthNum
                             ->get();
        
        // Simpler approach: count EscaladoData for these members in the last 60 days
        $startDate = Carbon::createFromDate($year, $monthNum, 1)->subDays(60);
        $escaladoCounts = EscaladoData::join('escalas', 'escalados_datas.escala_id', '=', 'escalas.es_id')
                                      ->whereIn('escalados_datas.acolito_id', $acolitosAtivos->pluck('id'))
                                      ->where('escalas.year', '>=', $startDate->year)
                                      ->selectRaw('acolito_id, count(*) as total')
                                      ->groupBy('acolito_id')
                                      ->pluck('total', 'acolito_id')
                                      ->toArray();

        // Tracker para max frequencia no mes atual
        $currentMonthCounts = [];
        foreach ($acolitosAtivos as $a) {
            $currentMonthCounts[$a->id] = 0;
            if (!isset($escaladoCounts[$a->id])) {
                $escaladoCounts[$a->id] = 0;
            }
        }

        // Mapping ISO day of week to string used in horarios_celebracoes table
        $diasSemanaNomes = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        // Descobrir as datas do mês atual
        $daysInMonth = Carbon::createFromDate($year, $monthNum, 1)->daysInMonth;
        
        $celebrationsToCreate = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $monthNum, $day);
            $dayOfWeek = $date->dayOfWeekIso; // 1 (Monday) to 7 (Sunday)
            $nomeDia = $diasSemanaNomes[$dayOfWeek];

            // Tem regra e tem horário?
            $horariosDoDia = $horarios->where('dia_semana', $nomeDia);
            $regraDoDia = $regras->get($dayOfWeek);

            if ($horariosDoDia->isNotEmpty() && $regraDoDia) {
                foreach ($horariosDoDia as $horario) {
                    // Selecionar os acólitos
                    $qtdAcolitos = rand($regraDoDia->min_acolitos, $regraDoDia->max_acolitos);
                    $qtdCoroinhas = rand($regraDoDia->min_coroinhas, $regraDoDia->max_coroinhas);
                    $maxServes = $regraDoDia->max_serves_per_month;

                    $selecionados = [];

                    // Selecionar Acolitos (type 0)
                    $this->selectMembers(
                        $acolitosList, $qtdAcolitos, $maxServes, $currentMonthCounts, 
                        $escaladoCounts, $selecionados, null, $funcoes
                    );

                    // Selecionar Coroinhas (type 1)
                    $this->selectMembers(
                        $coroinhasList, $qtdCoroinhas, $maxServes, $currentMonthCounts, 
                        $escaladoCounts, $selecionados, $regraDoDia->coroinha_funcao_id, $funcoes, true
                    );

                    // Preparar payload para o Draft
                    $payload = [
                        'data' => $day,
                        'dia' => $dayOfWeek,
                        'hora' => $horario->horario,
                        'celebration' => 'Missa',
                        'ent_id' => $ent_id,
                        'status' => 'draft',
                        'acolitos' => $selecionados
                    ];

                    $celebrationsToCreate[] = $payload;
                }
            }
        }

        // Return the array of payloads to be previewed/saved later
        return $celebrationsToCreate;
    }

    private function selectMembers($availableMembers, $qtdNeeded, $maxServes, &$currentMonthCounts, &$escaladoCounts, &$selecionados, $defaultFuncaoId, $todasFuncoes, $isCoroinha = false)
    {
        if ($qtdNeeded <= 0) return;

        // Filtrar membros que já atingiram o maximo global (se maxServes > 0)
        $eligible = $availableMembers->filter(function($member) use ($maxServes, $currentMonthCounts) {
            if ($maxServes > 0 && $currentMonthCounts[$member->id] >= $maxServes) {
                return false;
            }
            return true;
        });

        // Ordenar por menos vezes escalado historicamente e no mes atual (pesos)
        // Embaralhar primeiro para desempatar aleatoriamente
        $eligible = $eligible->shuffle()->sortBy(function($member) use ($currentMonthCounts, $escaladoCounts) {
            // Peso: 70% mes atual, 30% historico
            return ($currentMonthCounts[$member->id] * 10) + $escaladoCounts[$member->id];
        })->take($qtdNeeded);

        foreach ($eligible as $member) {
            // Decidir função
            $funcao_id = null;
            if ($isCoroinha && $defaultFuncaoId) {
                $funcao_id = $defaultFuncaoId;
            } else {
                // Acolito: pegar função aleatória ou null
                if ($todasFuncoes->isNotEmpty()) {
                    // Random function just to fill
                    $funcao_id = $todasFuncoes->random()->f_id;
                }
            }

            $selecionados[] = [
                'id' => $member->id,
                'funcao_id' => $funcao_id
            ];

            // Incrementar contadores
            $currentMonthCounts[$member->id]++;
            $escaladoCounts[$member->id]++;
        }
    }
}
