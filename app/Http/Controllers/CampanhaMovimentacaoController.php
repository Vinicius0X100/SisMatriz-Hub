<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\CampanhaEntrada;
use App\Models\CampanhaSaida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampanhaMovimentacaoController extends Controller
{
    public function storeEntrada(Request $request, Campanha $campanha)
    {
        if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'valor' => 'required|numeric|min:0',
            'forma' => 'nullable|string',
            'observacoes' => 'nullable|string',
        ]);

        $entrada = CampanhaEntrada::create([
            'campanha_id' => $campanha->id,
            'data' => $request->data,
            'valor' => $request->valor,
            'forma' => $request->forma,
            'observacoes' => $request->observacoes,
        ]);

        return response()->json($entrada);
    }

    public function storeSaida(Request $request, Campanha $campanha)
    {
        if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'valor' => 'required|numeric|min:0',
            'categoria' => 'nullable|string',
            'descricao' => 'nullable|string',
        ]);

        $saida = CampanhaSaida::create([
            'campanha_id' => $campanha->id,
            'data' => $request->data,
            'valor' => $request->valor,
            'categoria' => $request->categoria,
            'descricao' => $request->descricao,
        ]);

        return response()->json($saida);
    }
    
    public function destroyEntrada(CampanhaEntrada $entrada)
    {
        if ($entrada->campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $entrada->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function destroySaida(CampanhaSaida $saida)
    {
        if ($saida->campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $saida->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
