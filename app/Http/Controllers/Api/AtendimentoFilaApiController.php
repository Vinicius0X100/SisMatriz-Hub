<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AtendimentoFila;
use App\Models\AtendimentoFilaItem;
use App\Models\Register;
use Illuminate\Http\Request;

/**
 * API PÚBLICA — Consulta de posição na fila de atendimento por CPF.
 *
 * Destinada ao site da paróquia. Não requer autenticação.
 *
 * Instruções para IA do site:
 *  - Endpoint: GET /api/atendimento/consultar?cpf={cpf_apenas_numeros}
 *  - Remover pontos, traços e espaços do CPF antes de enviar
 *  - Verificar campo "encontrado" na resposta
 *  - Se "status" == "em_atendimento", exibir mensagem de destaque ao fiel
 */
class AtendimentoFilaApiController extends Controller
{
    /**
     * GET /api/atendimento/consultar?cpf={cpf}
     *
     * Consulta a posição de um fiel na fila ativa de hoje pelo CPF.
     * Somente funciona para agendados (walk-ins não têm CPF vinculado).
     */
    public function consultar(Request $request)
    {
        $cpf = preg_replace('/\D/', '', $request->input('cpf', ''));

        if (strlen($cpf) < 11) {
            return response()->json([
                'encontrado' => false,
                'mensagem'   => 'CPF inválido. Informe apenas os números.',
            ]);
        }

        // Localiza o register pelo CPF
        $register = Register::where('cpf', $cpf)->first();

        if (!$register) {
            return response()->json([
                'encontrado' => false,
                'mensagem'   => 'CPF não encontrado no cadastro da paróquia.',
            ]);
        }

        // Procura em filas ativas ou aguardando abertura de hoje
        $fila = AtendimentoFila::where('paroquia_id', $register->paroquia_id)
            ->whereDate('data', today())
            ->whereIn('status', [AtendimentoFila::STATUS_AGUARDANDO, AtendimentoFila::STATUS_ATIVA])
            ->first();

        if (!$fila) {
            return response()->json([
                'encontrado' => false,
                'mensagem'   => 'Não há fila de atendimento ativa hoje.',
            ]);
        }

        // Localiza o item do fiel na fila
        $item = AtendimentoFilaItem::where('fila_id', $fila->id)
            ->where('register_id', $register->id)
            ->whereNotIn('status', [AtendimentoFilaItem::STATUS_ATENDIDO, AtendimentoFilaItem::STATUS_AUSENTE])
            ->first();

        if (!$item) {
            return response()->json([
                'encontrado' => false,
                'mensagem'   => 'Seu nome não foi encontrado na fila de hoje ou você já foi atendido(a).',
            ]);
        }

        // Calcula posição na fila
        $posicao       = $this->calcularPosicao($fila, $item);
        $emAtendimento = $this->getEmAtendimento($fila);

        return response()->json([
            'encontrado'      => true,
            'nome'            => $item->nome,
            'posicao'         => $posicao,
            'status'          => match($item->status) {
                AtendimentoFilaItem::STATUS_EM_ATENDIMENTO => 'em_atendimento',
                default                                    => 'aguardando',
            },
            'tipo'            => $item->isAgendado() ? 'agendado' : 'walkin',
            'hora_agendada'   => $item->hora_agendada,
            'total_na_frente' => max(0, $posicao - 1),
            'em_atendimento'  => $emAtendimento?->nome,
        ]);
    }

    /**
     * Calcula a posição do item na fila respeitando a regra de prioridade.
     */
    private function calcularPosicao(AtendimentoFila $fila, AtendimentoFilaItem $item): int
    {
        if ($item->status === AtendimentoFilaItem::STATUS_EM_ATENDIMENTO) {
            return 1;
        }

        $posicao = 1;

        // Conta agendados aguardando com hora antes do item (se item for agendado)
        if ($item->isAgendado()) {
            $posicao += AtendimentoFilaItem::where('fila_id', $fila->id)
                ->where('tipo', AtendimentoFilaItem::TIPO_AGENDADO)
                ->where('status', AtendimentoFilaItem::STATUS_AGUARDANDO)
                ->where('hora_agendada', '<', $item->hora_agendada)
                ->count();
        } else {
            // Walk-in: vêm depois de todos os agendados
            $posicao += AtendimentoFilaItem::where('fila_id', $fila->id)
                ->where('tipo', AtendimentoFilaItem::TIPO_AGENDADO)
                ->where('status', AtendimentoFilaItem::STATUS_AGUARDANDO)
                ->count();

            // Mais os walk-ins que chegaram antes
            $posicao += AtendimentoFilaItem::where('fila_id', $fila->id)
                ->where('tipo', AtendimentoFilaItem::TIPO_WALKIN)
                ->where('status', AtendimentoFilaItem::STATUS_AGUARDANDO)
                ->where('created_at', '<', $item->created_at)
                ->count();
        }

        return $posicao;
    }

    /**
     * Retorna quem está sendo atendido no momento.
     */
    private function getEmAtendimento(AtendimentoFila $fila): ?AtendimentoFilaItem
    {
        return AtendimentoFilaItem::where('fila_id', $fila->id)
            ->where('status', AtendimentoFilaItem::STATUS_EM_ATENDIMENTO)
            ->first();
    }
}
