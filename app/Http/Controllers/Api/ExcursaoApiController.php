<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssentoVendido;
use App\Models\Excursao;
use App\Models\Onibus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcursaoApiController extends Controller
{
    /**
     * Roles autorizadas a validar bilhetes.
     * 1 = Admin Geral, 111 = Admin Sistema, 17 = Organizador de Eventos/Excursões
     */
    private const ROLES_VALIDACAO = ['1', '111', '17'];

    /**
     * Consulta um bilhete pelo ID do assento.
     * Usado pelo app iOS após escanear o QR Code.
     *
     * GET /api/excursoes/bilhete/{id}
     */
    public function consultarBilhete(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $assento = AssentoVendido::with(['onibus.excursao', 'validadoPor'])->find($id);

        if (!$assento) {
            return response()->json([
                'success' => false,
                'message' => 'Bilhete não encontrado.',
            ], 404);
        }

        // Verifica se o bilhete pertence à paróquia do usuário
        if ($assento->paroquia_id != $user->paroquia_id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado.',
            ], 403);
        }

        $onibus   = $assento->onibus;
        $excursao = $onibus?->excursao;

        return response()->json([
            'success'    => true,
            'bilhete'    => [
                'id'                  => $assento->id,
                'passageiro_nome'     => $assento->passageiro_nome,
                'passageiro_rg'       => $assento->passageiro_rg,
                'passageiro_telefone' => $assento->passageiro_telefone,
                'poltrona'            => $assento->poltrona,
                'posicao'             => $assento->posicao,
                'menor'               => $assento->menor,
                'responsavel_nome'    => $assento->responsavel_nome,
                'embarque_ida'        => $assento->embarque_ida,
                'embarque_volta'      => $assento->embarque_volta,
                'validado'            => $assento->isValidado(),
                'validado_em'         => $assento->validado_em?->format('d/m/Y H:i'),
                'validado_por_nome'   => $assento->validadoPor?->name,
            ],
            'onibus'     => [
                'id'              => $onibus?->id,
                'numero'          => $onibus?->numero,
                'empresa'         => $onibus?->empresa,
                'horario_saida'   => $onibus?->horario_saida?->format('d/m/Y H:i'),
                'horario_retorno' => $onibus?->horario_retorno?->format('d/m/Y H:i'),
                'local_saida'     => $onibus?->local_saida,
            ],
            'excursao'   => [
                'id'      => $excursao?->id,
                'destino' => $excursao?->destino,
                'tipo'    => $excursao?->tipo,
            ],
        ]);
    }

    /**
     * Valida (marca como usado) um bilhete pelo ID do assento.
     * Chamado pelo app iOS após confirmar a leitura do QR Code.
     *
     * POST /api/excursoes/bilhete/{id}/validar
     */
    public function validarBilhete(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verifica se o usuário tem permissão para validar
        if (!$user->hasAnyRole(self::ROLES_VALIDACAO)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para validar bilhetes.',
            ], 403);
        }

        $assento = AssentoVendido::with(['onibus.excursao', 'validadoPor'])->find($id);

        if (!$assento) {
            return response()->json([
                'success' => false,
                'message' => 'Bilhete não encontrado.',
            ], 404);
        }

        // Verifica se o bilhete pertence à paróquia do usuário
        if ($assento->paroquia_id != $user->paroquia_id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado.',
            ], 403);
        }

        // Verifica se o bilhete já foi validado
        if ($assento->isValidado()) {
            return response()->json([
                'success'           => false,
                'already_validated' => true,
                'message'           => 'Este bilhete já foi validado.',
                'validado_em'       => $assento->validado_em?->format('d/m/Y H:i'),
                'validado_por_nome' => $assento->validadoPor?->name,
            ], 409);
        }

        // Realiza a validação
        $assento->update([
            'validado_em'  => now(),
            'validado_por' => $user->id,
        ]);

        $assento->load('validadoPor');

        return response()->json([
            'success'           => true,
            'message'           => 'Bilhete validado com sucesso!',
            'bilhete_id'        => $assento->id,
            'passageiro_nome'   => $assento->passageiro_nome,
            'poltrona'          => $assento->poltrona,
            'validado_em'       => $assento->validado_em?->format('d/m/Y H:i'),
            'validado_por_nome' => $assento->validadoPor?->name,
        ]);
    }

    /**
     * Lista todos os bilhetes validados de um ônibus.
     * Usado pelo app iOS para exibir o painel de validados.
     *
     * GET /api/excursoes/{excursao}/onibus/{onibus}/validados
     */
    public function listarValidados(Request $request, $excursaoId, $onibusId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $excursao = Excursao::find($excursaoId);

        if (!$excursao) {
            return response()->json([
                'success' => false,
                'message' => 'Excursão não encontrada.',
            ], 404);
        }

        if ($excursao->paroquia_id != $user->paroquia_id) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso não autorizado.',
            ], 403);
        }

        $onibus = Onibus::where('id', $onibusId)
            ->where('excursao_id', $excursaoId)
            ->first();

        if (!$onibus) {
            return response()->json([
                'success' => false,
                'message' => 'Ônibus não encontrado.',
            ], 404);
        }

        $validados = AssentoVendido::with('validadoPor')
            ->where('onibus_id', $onibusId)
            ->whereNotNull('validado_em')
            ->orderBy('validado_em', 'desc')
            ->get()
            ->map(function ($assento) {
                return [
                    'id'                => $assento->id,
                    'passageiro_nome'   => $assento->passageiro_nome,
                    'passageiro_rg'     => $assento->passageiro_rg,
                    'poltrona'          => $assento->poltrona,
                    'posicao'           => $assento->posicao,
                    'menor'             => $assento->menor,
                    'validado_em'       => $assento->validado_em?->format('d/m/Y H:i'),
                    'validado_por_nome' => $assento->validadoPor?->name,
                ];
            });

        $total       = AssentoVendido::where('onibus_id', $onibusId)->count();
        $totalValidados = $validados->count();

        return response()->json([
            'success'           => true,
            'excursao'          => [
                'id'      => $excursao->id,
                'destino' => $excursao->destino,
            ],
            'onibus'            => [
                'id'         => $onibus->id,
                'numero'     => $onibus->numero,
                'capacidade' => $onibus->capacidade,
            ],
            'total_bilhetes'    => $total,
            'total_validados'   => $totalValidados,
            'total_pendentes'   => $total - $totalValidados,
            'validados'         => $validados,
        ]);
    }
}
