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
use App\Services\AcolitoTeamCompositionService;

class EscalaGeneratorService
{
    protected $teamCompositionService;

    public function __construct(AcolitoTeamCompositionService $teamCompositionService)
    {
        $this->teamCompositionService = $teamCompositionService;
    }
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
        // status = 0 = Ativo no sistema
        $acolitosAtivos = Acolito::where('ent_id', $ent_id)
                                 ->where('paroquia_id', $paroquia_id)
                                 ->where('status', 0)
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
        $currentMonthHours = [];
        foreach ($acolitosAtivos as $a) {
            $currentMonthCounts[$a->id] = 0;
            $currentMonthHours[$a->id] = [];
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

        // Mapear ocupações já existentes no banco para evitar conflito de data/hora
        $occupiedSlots = [];
        $existingSchedules = EscalaDataHora::join('escalas', 'escalas.es_id', '=', 'escalas_datas_horas.es_id')
            ->join('escalados_datas', 'escalados_datas.d_id', '=', 'escalas_datas_horas.d_id')
            ->where('escalas.paroquia_id', $paroquia_id)
            ->where('escalas.month', $escala->month)
            ->where('escalas.year', $year)
            ->select('escalas_datas_horas.data', 'escalas_datas_horas.hora', 'escalados_datas.acolito_id')
            ->get();

        foreach ($existingSchedules as $sched) {
            $key = $sched->data . '_' . substr($sched->hora, 0, 5);
            $occupiedSlots[$key][] = $sched->acolito_id;
        }

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
                    $timeKey = $day . '_' . substr($horario->horario, 0, 5);
                    $rejectedIds = [];
                    $maxAttempts = 3;

                    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                        $currAcolitos = count(array_filter($selecionados, fn($s) => $s['type'] == 0));
                        $currCoroinhas = count(array_filter($selecionados, fn($s) => $s['type'] == 1));

                        $neededAcolitos = $qtdAcolitos - $currAcolitos;
                        $neededCoroinhas = $qtdCoroinhas - $currCoroinhas;

                        if ($neededAcolitos > 0) {
                            $this->selectMembers(
                                $acolitosList, $neededAcolitos, $maxServes, $currentMonthCounts, 
                                $escaladoCounts, $selecionados, null, $funcoes, false,
                                $currentMonthHours, $horario->horario, $occupiedSlots, $timeKey, $rejectedIds
                            );
                        }

                        if ($neededCoroinhas > 0) {
                            $this->selectMembers(
                                $coroinhasList, $neededCoroinhas, $maxServes, $currentMonthCounts, 
                                $escaladoCounts, $selecionados, $regraDoDia->coroinha_funcao_id, $funcoes, true,
                                $currentMonthHours, $horario->horario, $occupiedSlots, $timeKey, $rejectedIds
                            );
                        }

                        // Validate composition
                        $acolitosIds = array_column($selecionados, 'id');
                        $invalidos = $this->teamCompositionService->membrosInvalidos($paroquia_id, $acolitosIds);

                        if (empty($invalidos)) {
                            break; // All rules satisfied!
                        }

                        // Remove invalid members and rollback their counters
                        foreach ($selecionados as $k => $sel) {
                            if (in_array($sel['id'], $invalidos)) {
                                if (isset($occupiedSlots[$timeKey])) {
                                    $pos = array_search($sel['id'], $occupiedSlots[$timeKey]);
                                    if ($pos !== false) unset($occupiedSlots[$timeKey][$pos]);
                                }
                                $currentMonthCounts[$sel['id']]--;
                                $escaladoCounts[$sel['id']]--;
                                
                                $rejectedIds[] = $sel['id'];
                                unset($selecionados[$k]);
                            }
                        }
                        $selecionados = array_values($selecionados);
                    }

                    // Last pass validation just to generate warnings for the UI (if any remain)
                    $acolitosIds = array_column($selecionados, 'id');
                    $errosComposicao = $this->teamCompositionService->validarEquipe($paroquia_id, $acolitosIds);

                    // Preparar payload para o Draft
                    $payload = [
                        'data' => $day,
                        'dia' => $dayOfWeek,
                        'hora' => $horario->horario,
                        'celebration' => 'Missa',
                        'ent_id' => $ent_id,
                        'status' => 'draft',
                        'acolitos' => $selecionados,
                        'avisos' => $errosComposicao // Adiciona avisos para o preview
                    ];

                    $celebrationsToCreate[] = $payload;
                }
            }
        }

        // Return the array of payloads to be previewed/saved later
        return $celebrationsToCreate;
    }

    private function selectMembers($availableMembers, $qtdNeeded, $maxServes, &$currentMonthCounts, &$escaladoCounts, &$selecionados, $defaultFuncaoId, $todasFuncoes, $isCoroinha = false, &$currentMonthHours = null, $currentHora = null, &$occupiedSlots = [], $timeKey = null, $rejectedIds = [])
    {
        if ($qtdNeeded <= 0 || $availableMembers->isEmpty()) return;

        // Remover acólitos que já estão ocupados neste exato dia e horário, ou que foram rejeitados
        $availableMembers = $availableMembers->reject(function($member) use ($occupiedSlots, $timeKey, $rejectedIds) {
            if (in_array($member->id, $rejectedIds)) return true;
            if ($timeKey && isset($occupiedSlots[$timeKey]) && in_array($member->id, $occupiedSlots[$timeKey])) {
                return true;
            }
            return false;
        });

        if ($availableMembers->isEmpty()) return;

        // 1ª tentativa: filtrar apenas quem ainda não atingiu o teto mensal
        $eligible = $availableMembers->filter(function($member) use ($maxServes, $currentMonthCounts) {
            if ($maxServes > 0 && $currentMonthCounts[$member->id] >= $maxServes) {
                return false;
            }
            return true;
        });

        // Se não sobrou ninguém dentro do teto (todos já serviram o máximo),
        // reinicia o ciclo com todos os disponíveis para não deixar missa vazia.
        if ($eligible->isEmpty()) {
            $eligible = $availableMembers;
        }

        // Ordenar por menos vezes escalado no mês atual (desempate aleatório)
        $eligible = $eligible->shuffle()->sortBy(function($member) use ($currentMonthCounts, $escaladoCounts, $currentMonthHours, $currentHora) {
            // Peso principal: contagem no mês atual; secundário: histórico
            $score = ($currentMonthCounts[$member->id] * 10) + $escaladoCounts[$member->id];
            
            // Penalidade imensa se já serviu neste exato horário neste mês (evita repetir o mesmo horário todo domingo)
            if ($currentMonthHours !== null && $currentHora !== null) {
                if (in_array($currentHora, $currentMonthHours[$member->id])) {
                    $score += 10000;
                }
            }
            
            return $score;
        })->take($qtdNeeded);

        foreach ($eligible as $member) {
            // Decidir função
            $funcao_id = null;
            if ($isCoroinha && $defaultFuncaoId) {
                $funcao_id = $defaultFuncaoId;
            } else {
                // Acolito: pegar função aleatória
                if ($todasFuncoes->isNotEmpty()) {
                    $funcao_id = $todasFuncoes->random()->f_id;
                }
            }

            $selecionados[] = [
                'id'       => $member->id,
                'name'     => $member->name,
                'type'     => $member->type, // 0 = acolito, 1 = coroinha
                'funcao_id' => $funcao_id
            ];

            // Incrementar contadores e registrar ocupação
            $currentMonthCounts[$member->id]++;
            $escaladoCounts[$member->id]++;
            if ($currentMonthHours !== null && $currentHora !== null) {
                $currentMonthHours[$member->id][] = $currentHora;
            }
            if ($timeKey !== null) {
                $occupiedSlots[$timeKey][] = $member->id;
            }
        }
    }
}
