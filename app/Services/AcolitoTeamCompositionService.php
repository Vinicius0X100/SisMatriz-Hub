<?php

namespace App\Services;

use App\Models\AcolitoRegraComposicao;
use App\Models\Acolito;
use Illuminate\Support\Collection;

class AcolitoTeamCompositionService
{
    /**
     * Recupera as regras de composição de um acólito específico para uma paróquia.
     */
    public function getRegrasAcolito(int $paroquiaId, int $acolitoId): Collection
    {
        return AcolitoRegraComposicao::where('paroquia_id', $paroquiaId)
            ->where('acolito_id', $acolitoId)
            ->where('ativo', true)
            ->get();
    }

    protected function fetchRegras(int $paroquiaId, array $acolitosIds): Collection
    {
        return AcolitoRegraComposicao::where('paroquia_id', $paroquiaId)
            ->whereIn('acolito_id', $acolitosIds)
            ->where('ativo', true)
            ->get();
    }

    /**
     * Valida se uma equipe proposta viola alguma regra de composição
     * Retorna um array de erros, ou array vazio se for válida.
     */
    public function validarEquipe(int $paroquiaId, array $acolitosIds): array
    {
        $erros = [];
        
        // Pega as regras ativas de todos os acólitos na equipe proposta
        $regras = $this->fetchRegras($paroquiaId, $acolitosIds);

        // Agrupa as regras por tipo
        $regrasPorTipo = $regras->groupBy('tipo_regra');

        // 1. Incompatibilidade (restricao_companhia)
        if ($regrasPorTipo->has('incompatibilidade')) {
            foreach ($regrasPorTipo['incompatibilidade'] as $regra) {
                $incompativeisIds = $regra->configuracao['acolitos_incompativeis'] ?? [];
                $conflitos = array_intersect($incompativeisIds, $acolitosIds);
                if (!empty($conflitos)) {
                    $acolitoPrincipal = Acolito::find($regra->acolito_id);
                    $nomesConflitos = Acolito::whereIn('id', $conflitos)->pluck('name')->toArray();
                    $erros[] = "{$acolitoPrincipal->name} não pode servir na mesma equipe que " . implode(', ', $nomesConflitos) . ".";
                }
            }
        }

        // 2. Companhia Obrigatória
        if ($regrasPorTipo->has('companhia_obrigatoria')) {
            foreach ($regrasPorTipo['companhia_obrigatoria'] as $regra) {
                $obrigatoriosIds = $regra->configuracao['acolitos_obrigatorios'] ?? [];
                // Se o acólito está na equipe, Pelo menos UM (ou TODOS?) dos obrigatórios devem estar.
                // Assumindo que precisa de pelo menos UM dos obrigatórios na equipe para ele servir, ou todos?
                // Vamos assumir "pelo menos um" (Ex: um novato precisa do João ou da Maria).
                // Caso a regra especifique modo 'todos', podemos verificar depois.
                $presentes = array_intersect($obrigatoriosIds, $acolitosIds);
                $modo = $regra->configuracao['modo'] ?? 'qualquer'; // 'qualquer' ou 'todos'

                if (empty($obrigatoriosIds)) continue;

                if ($modo === 'todos') {
                    $faltando = array_diff($obrigatoriosIds, $acolitosIds);
                    if (!empty($faltando)) {
                        $acolitoPrincipal = Acolito::find($regra->acolito_id);
                        $nomesFaltando = Acolito::whereIn('id', $faltando)->pluck('name')->toArray();
                        $erros[] = "{$acolitoPrincipal->name} requer a presença de " . implode(', ', $nomesFaltando) . " para servir.";
                    }
                } else {
                    if (empty($presentes)) {
                        $acolitoPrincipal = Acolito::find($regra->acolito_id);
                        $nomesObrigatorios = Acolito::whereIn('id', $obrigatoriosIds)->pluck('name')->toArray();
                        $erros[] = "{$acolitoPrincipal->name} requer a presença de pelo menos um dos seguintes: " . implode(', ', $nomesObrigatorios) . ".";
                    }
                }
            }
        }

        // 3. Acompanhamento Mínimo (Legacy)
        if ($regrasPorTipo->has('acompanhamento_minimo')) {
            foreach ($regrasPorTipo['acompanhamento_minimo'] as $regra) {
                $minimoAcompanhantes = $regra->configuracao['minimo_acompanhantes'] ?? 1;
                $categoriasPermitidas = $regra->configuracao['categorias'] ?? []; // ex: [0] para Acolito, [1] para Coroinha

                // Pessoas na equipe, excluindo ele mesmo
                $outrosIds = array_diff($acolitosIds, [$regra->acolito_id]);
                
                $queryAcompanhantes = Acolito::whereIn('id', $outrosIds);
                
                if (!empty($categoriasPermitidas)) {
                    $queryAcompanhantes->whereIn('type', $categoriasPermitidas);
                }

                $qtdAcompanhantes = $queryAcompanhantes->count();

                if ($qtdAcompanhantes < $minimoAcompanhantes) {
                    $acolitoPrincipal = Acolito::find($regra->acolito_id);
                    $erros[] = "{$acolitoPrincipal->name} requer no mínimo {$minimoAcompanhantes} acompanhante(s) na equipe (Restrição de Categoria: " . (empty($categoriasPermitidas) ? 'Nenhuma' : implode(',', $categoriasPermitidas)) . ").";
                }
            }
        }

        // 4. Acompanhamento Unificado (Novo)
        if ($regrasPorTipo->has('acompanhamento')) {
            foreach ($regrasPorTipo['acompanhamento'] as $regra) {
                $minimo = $regra->configuracao['minimo_acompanhantes'] ?? 0;
                $categoria = $regra->configuracao['categoria'] ?? 'qualquer'; // 'qualquer', '0', '1'
                $especificos = $regra->configuracao['acompanhantes_especificos'] ?? [];
                $modo = $regra->configuracao['modo'] ?? 'qualquer';

                $acolitoPrincipal = Acolito::find($regra->acolito_id);

                // A. Valida Acompanhantes Específicos (opcional)
                if (!empty($especificos)) {
                    $presentes = array_intersect($especificos, $acolitosIds);
                    if ($modo === 'todos') {
                        $faltando = array_diff($especificos, $acolitosIds);
                        if (!empty($faltando)) {
                            $nomesFaltando = Acolito::whereIn('id', $faltando)->pluck('name')->toArray();
                            $erros[] = "{$acolitoPrincipal->name} requer a presença de " . implode(', ', $nomesFaltando) . " para servir.";
                        }
                    } else { // qualquer
                        if (empty($presentes)) {
                            $nomesObrigatorios = Acolito::whereIn('id', $especificos)->pluck('name')->toArray();
                            $erros[] = "{$acolitoPrincipal->name} requer a presença de pelo menos um dos seguintes: " . implode(', ', $nomesObrigatorios) . ".";
                        }
                    }
                }

                // B. Valida Quantidade Mínima Geral e Categoria
                if ($minimo > 0) {
                    $outrosIds = array_diff($acolitosIds, [$regra->acolito_id]);
                    
                    if (empty($outrosIds)) {
                        $erros[] = "{$acolitoPrincipal->name} requer no mínimo {$minimo} acompanhante(s) na equipe, mas está sozinho.";
                    } else {
                        $query = Acolito::whereIn('id', $outrosIds);
                        if ($categoria !== 'qualquer') {
                            $query->where('type', (int)$categoria);
                        }
                        $qtd = $query->count();
                        
                        if ($qtd < $minimo) {
                            $catNome = $categoria === '0' ? 'Acólito(s)' : ($categoria === '1' ? 'Coroinha(s)' : 'integrante(s)');
                            $erros[] = "{$acolitoPrincipal->name} requer no mínimo {$minimo} acompanhante(s) do tipo {$catNome} na equipe (possui apenas {$qtd}).";
                        }
                    }
                }
            }
        }

        return $erros;
    }

    /**
     * Retorna apenas os IDs dos acólitos cujas regras foram violadas na equipe proposta.
     * Útil para o gerador de escalas saber quem remover/substituir.
     */
    public function membrosInvalidos(int $paroquiaId, array $acolitosIds): array
    {
        $invalidos = [];
        $regras = $this->fetchRegras($paroquiaId, $acolitosIds);
        $regrasPorTipo = $regras->groupBy('tipo_regra');

        if ($regrasPorTipo->has('incompatibilidade')) {
            foreach ($regrasPorTipo['incompatibilidade'] as $regra) {
                $conflitos = array_intersect($regra->configuracao['acolitos_incompativeis'] ?? [], $acolitosIds);
                if (!empty($conflitos)) {
                    $invalidos[] = $regra->acolito_id;
                    // Também podemos considerar os conflitantes como inválidos, 
                    // mas remover o dono da regra já resolve o conflito na maioria dos casos.
                }
            }
        }

        if ($regrasPorTipo->has('companhia_obrigatoria')) {
            foreach ($regrasPorTipo['companhia_obrigatoria'] as $regra) {
                $obrigatoriosIds = $regra->configuracao['acolitos_obrigatorios'] ?? [];
                $presentes = array_intersect($obrigatoriosIds, $acolitosIds);
                $modo = $regra->configuracao['modo'] ?? 'qualquer';

                if (!empty($obrigatoriosIds)) {
                    if ($modo === 'todos' && count(array_diff($obrigatoriosIds, $acolitosIds)) > 0) {
                        $invalidos[] = $regra->acolito_id;
                    } elseif ($modo === 'qualquer' && empty($presentes)) {
                        $invalidos[] = $regra->acolito_id;
                    }
                }
            }
        }

        if ($regrasPorTipo->has('acompanhamento_minimo')) {
            foreach ($regrasPorTipo['acompanhamento_minimo'] as $regra) {
                $minimoAcompanhantes = $regra->configuracao['minimo_acompanhantes'] ?? 1;
                $categoriasPermitidas = $regra->configuracao['categorias'] ?? [];
                $outrosIds = array_diff($acolitosIds, [$regra->acolito_id]);
                $queryAcompanhantes = Acolito::whereIn('id', $outrosIds);
                if (!empty($categoriasPermitidas)) {
                    $queryAcompanhantes->whereIn('type', $categoriasPermitidas);
                }
                if ($queryAcompanhantes->count() < $minimoAcompanhantes) {
                    $invalidos[] = $regra->acolito_id;
                }
            }
        }

        if ($regrasPorTipo->has('acompanhamento')) {
            foreach ($regrasPorTipo['acompanhamento'] as $regra) {
                $minimo = $regra->configuracao['minimo_acompanhantes'] ?? 0;
                $categoria = $regra->configuracao['categoria'] ?? 'qualquer';
                $especificos = $regra->configuracao['acompanhantes_especificos'] ?? [];
                $modo = $regra->configuracao['modo'] ?? 'qualquer';

                $valido = true;

                if (!empty($especificos)) {
                    $presentes = array_intersect($especificos, $acolitosIds);
                    if ($modo === 'todos' && count(array_diff($especificos, $acolitosIds)) > 0) {
                        $valido = false;
                    } elseif ($modo === 'qualquer' && empty($presentes)) {
                        $valido = false;
                    }
                }

                if ($valido && $minimo > 0) {
                    $outrosIds = array_diff($acolitosIds, [$regra->acolito_id]);
                    if (empty($outrosIds)) {
                        $valido = false;
                    } else {
                        $query = Acolito::whereIn('id', $outrosIds);
                        if ($categoria !== 'qualquer') {
                            $query->where('type', (int)$categoria);
                        }
                        if ($query->count() < $minimo) {
                            $valido = false;
                        }
                    }
                }

                if (!$valido) {
                    $invalidos[] = $regra->acolito_id;
                }
            }
        }

        return array_unique($invalidos);
    }

    /**
     * Salva ou atualiza uma regra para um acólito.
     */
    public function salvarRegra(int $paroquiaId, int $acolitoId, string $tipoRegra, array $configuracao, bool $ativo = true): AcolitoRegraComposicao
    {
        return AcolitoRegraComposicao::updateOrCreate(
            [
                'paroquia_id' => $paroquiaId,
                'acolito_id' => $acolitoId,
                'tipo_regra' => $tipoRegra,
            ],
            [
                'configuracao' => $configuracao,
                'ativo' => $ativo
            ]
        );
    }
    
    /**
     * Remove uma regra de um acólito.
     */
    public function removerRegra(int $paroquiaId, int $acolitoId, string $tipoRegra): bool
    {
        return AcolitoRegraComposicao::where('paroquia_id', $paroquiaId)
            ->where('acolito_id', $acolitoId)
            ->where('tipo_regra', $tipoRegra)
            ->delete();
    }
}
