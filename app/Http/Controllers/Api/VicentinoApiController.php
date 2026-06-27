<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VinWatched;
use App\Models\VicentinosRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VicentinoApiController extends Controller
{
    /**
     * Retorna a lista de Apurações (Assistidos - Modelo Antigo)
     * Equivalente à tela web: /vicentinos-apuracoes
     */
    public function getApuracoes(Request $request)
    {
        $query = VinWatched::where('paroquia_id', Auth::user()->paroquia_id)
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
     * Retorna a lista de Fichas (Assistidos - Modelo Novo)
     * Equivalente à tela web: /vicentinos
     */
    public function getFichas(Request $request)
    {
        $query = VicentinosRecord::where('paroquia_id', Auth::user()->paroquia_id)
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
