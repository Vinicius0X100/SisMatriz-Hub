<?php

namespace App\Http\Controllers;

use App\Models\Excursao;
use App\Models\Onibus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnibusController extends Controller
{
    public function create(Excursao $excursao)
    {
        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }
        return view('modules.excursoes.onibus.create', compact('excursao'));
    }

    public function store(Request $request, Excursao $excursao)
    {
        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'numero' => 'required|string|max:10',
            'capacidade' => 'required|integer|min:1',
            'responsavel' => 'nullable|string|max:255',
            'telefone_responsavel' => 'nullable|string|max:20',
            'horario_saida' => 'nullable|date',
            'horario_retorno' => 'nullable|date',
            'local_saida' => 'nullable|string|max:255',
        ]);

        $excursao->onibus()->create([
            'paroquia_id' => $excursao->paroquia_id,
            'numero' => $request->numero,
            'capacidade' => $request->capacidade,
            'responsavel' => $request->responsavel,
            'telefone_responsavel' => $request->telefone_responsavel,
            'horario_saida' => $request->horario_saida,
            'horario_retorno' => $request->horario_retorno,
            'local_saida' => $request->local_saida,
            'ativo' => true,
        ]);

        return redirect()->route('excursoes.show', $excursao)->with('success', 'Ônibus adicionado com sucesso!');
    }

    public function show(Excursao $excursao, Onibus $onibus)
    {
        // Verifica se o ônibus pertence à excursão
        if ($onibus->excursao_id != $excursao->id) {
            abort(404);
        }

        // Verifica permissão (Admin ou mesma paróquia)
        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }
        
        $onibus->load('assentosVendidos');
        
        // Prepare seats grid
        $seats = [];
        for ($i = 1; $i <= $onibus->capacidade; $i++) {
            $seats[$i] = $onibus->assentosVendidos->where('poltrona', $i)->first();
        }

        return view('modules.excursoes.onibus.show', compact('excursao', 'onibus', 'seats'));
    }

    public function edit(Excursao $excursao, Onibus $onibus)
    {
        if ($excursao->paroquia_id != Auth::user()->paroquia_id || $onibus->excursao_id != $excursao->id) {
            abort(403);
        }
        return view('modules.excursoes.onibus.edit', compact('excursao', 'onibus'));
    }

    public function update(Request $request, Excursao $excursao, Onibus $onibus)
    {
        if ($excursao->paroquia_id != Auth::user()->paroquia_id || $onibus->excursao_id != $excursao->id) {
            abort(403);
        }

        $request->validate([
            'numero' => 'required|string|max:10',
            'capacidade' => 'required|integer|min:1',
            'responsavel' => 'nullable|string|max:255',
            'telefone_responsavel' => 'nullable|string|max:20',
            'horario_saida' => 'nullable|date',
            'horario_retorno' => 'nullable|date',
            'local_saida' => 'nullable|string|max:255',
        ]);

        $onibus->update($request->only([
            'numero',
            'capacidade',
            'responsavel',
            'telefone_responsavel',
            'horario_saida',
            'horario_retorno',
            'local_saida',
        ]));

        return redirect()->route('excursoes.show', $excursao)->with('success', 'Ônibus atualizado com sucesso!');
    }

    public function destroy(Excursao $excursao, Onibus $onibus)
    {
        if ($excursao->paroquia_id != Auth::user()->paroquia_id || $onibus->excursao_id != $excursao->id) {
            abort(403);
        }

        $onibus->delete();

        return redirect()->route('excursoes.show', $excursao)->with('success', 'Ônibus removido com sucesso!');
    }
}
