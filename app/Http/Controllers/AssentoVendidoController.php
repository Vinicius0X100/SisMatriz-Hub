<?php

namespace App\Http\Controllers;

use App\Models\AssentoVendido;
use App\Models\Excursao;
use App\Models\Onibus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssentoVendidoController extends Controller
{
    public function store(Request $request, Excursao $excursao, Onibus $onibus)
    {
        if ($onibus->excursao_id != $excursao->id) {
            abort(404);
        }

        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'poltrona' => 'required|integer|min:1',
            'passageiro_nome' => 'required|string|max:255',
            'passageiro_rg' => 'nullable|string|max:20',
            'passageiro_telefone' => 'nullable|string|max:20',
            'menor' => 'nullable',
            'responsavel_nome' => 'nullable|required_if:menor,on|string|max:255',
            'responsavel_rg' => 'nullable|required_if:menor,on|string|max:20',
            'responsavel_telefone' => 'nullable|required_if:menor,on|string|max:20',
            'posicao' => 'required|in:janela,corredor',
            'embarque_ida' => 'nullable',
            'embarque_volta' => 'nullable',
        ]);

        // Verifica se o assento já está ocupado
        $exists = AssentoVendido::where('onibus_id', $onibus->id)
            ->where('poltrona', $request->poltrona)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Esta poltrona já foi vendida!');
        }

        AssentoVendido::create([
            'paroquia_id' => $excursao->paroquia_id,
            'onibus_id' => $onibus->id,
            'poltrona' => $request->poltrona,
            'passageiro_nome' => $request->passageiro_nome,
            'passageiro_rg' => $request->passageiro_rg,
            'passageiro_telefone' => $request->passageiro_telefone,
            'menor' => $request->has('menor'),
            'responsavel_nome' => $request->responsavel_nome,
            'responsavel_rg' => $request->responsavel_rg,
            'responsavel_telefone' => $request->responsavel_telefone,
            'embarque_ida' => $request->has('embarque_ida'),
            'embarque_volta' => $request->has('embarque_volta'),
            'posicao' => $request->posicao,
        ]);

        return back()->with('success', 'Assento vendido com sucesso!');
    }

    public function destroy(Excursao $excursao, Onibus $onibus, AssentoVendido $assento)
    {
        if ($onibus->excursao_id != $excursao->id || $assento->onibus_id != $onibus->id) {
            abort(404);
        }

        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $assento->delete();

        return back()->with('success', 'Assento liberado com sucesso!');
    }
}
