<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VinWatched;
use App\Models\VicentinosRecord;
use App\Services\AcaoSocialContextService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class VicentinoApiController extends Controller
{
    protected AcaoSocialContextService $contextService;

    public function __construct(AcaoSocialContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * Retorna a lista de Apurações / Atendimentos
     * Compatibilidade total com a rota móvel: /api/vicentinos/apuracoes
     * E nova rota canônica: /api/acoes-sociais/atendimentos
     */
    public function getApuracoes(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $equipe = $this->contextService->resolveForApi($user, $request);

        if (!$equipe) {
            return response()->json(new LengthAwarePaginator([], 0, 15));
        }

        $query = VinWatched::where('paroquia_id', $user->paroquia_id)
            ->where('equipe_id', $equipe->id)
            ->with(['entidade', 'sender'])
            ->orderBy('w_id', 'desc');

        // Filtro por nome
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Filtro por mês
        if ($request->filled('month')) {
            $query->where('month_entire', $request->input('month'));
        }

        // Filtro por status (0 = Não Assistido, 1 = Assistido)
        if ($request->filled('kind')) {
            $query->where('kind', $request->input('kind'));
        }

        // Filtro por entidade
        if ($request->filled('ent_id')) {
            $query->where('ent_id', $request->input('ent_id'));
        }

        $records = $query->paginate(15);
        
        return response()->json($records);
    }

    /**
     * Retorna a lista de Fichas / Famílias Acompanhadas
     * Compatibilidade total com a rota móvel: /api/vicentinos/fichas
     * E nova rota canônica: /api/acoes-sociais/familias
     */
    public function getFichas(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $equipe = $this->contextService->resolveForApi($user, $request);

        if (!$equipe) {
            return response()->json(new LengthAwarePaginator([], 0, 15));
        }

        $query = VicentinosRecord::where('paroquia_id', $user->paroquia_id)
            ->where('equipe_id', $equipe->id)
            ->orderBy('id', 'desc');

        // Filtro de busca genérica por nome
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('responsavel_nome', 'like', "%{$search}%");
        }

        $records = $query->paginate(15);
        
        return response()->json($records);
    }
}
