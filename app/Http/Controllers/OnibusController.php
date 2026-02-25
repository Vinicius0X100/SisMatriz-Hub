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
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }
        return view('modules.excursoes.onibus.create', compact('excursao'));
    }

    public function store(Request $request, Excursao $excursao)
    {
        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
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
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }
        
        $onibus->load('assentosVendidos');
        
        // Prepare seats grid
        $seats = [];
        for ($i = 1; $i <= $onibus->capacidade; $i++) {
            $seats[$i] = $onibus->assentosVendidos->first(function ($assento) use ($i) {
                return (int) $assento->poltrona === $i;
            });
        }

        return view('modules.excursoes.onibus.show', compact('excursao', 'onibus', 'seats'));
    }

    public function edit(Excursao $excursao, Onibus $onibus)
    {
        if ($onibus->excursao_id != $excursao->id) {
            abort(404);
        }

        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        return view('modules.excursoes.onibus.edit', compact('excursao', 'onibus'));
    }

    public function update(Request $request, Excursao $excursao, Onibus $onibus)
    {
        if ($onibus->excursao_id != $excursao->id) {
            abort(404);
        }

        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
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
        if ($onibus->excursao_id != $excursao->id) {
            abort(404);
        }

        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $onibus->delete();

        return redirect()->route('excursoes.show', $excursao)->with('success', 'Ônibus removido com sucesso!');
    }

    public function storeAssento(Request $request, Excursao $excursao, Onibus $onibus)
    {
        \Illuminate\Support\Facades\Log::info('Iniciando venda de assento', ['request' => $request->all(), 'excursao' => $excursao->id, 'onibus' => $onibus->id]);

        if ($onibus->excursao_id != $excursao->id) {
            \Illuminate\Support\Facades\Log::error('Onibus não pertence a excursão');
            abort(404);
        }

        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            \Illuminate\Support\Facades\Log::error('Usuário sem permissão');
            abort(403);
        }

        try {
            $validated = $request->validate([
                'poltrona' => 'required|integer|min:1|max:' . $onibus->capacidade,
                'passageiro_nome' => 'required|string|max:255',
                'passageiro_rg' => 'nullable|string|max:20',
                'passageiro_telefone' => 'nullable|string|max:20',
                'posicao' => 'required|in:janela,corredor',
                'menor' => 'sometimes|boolean',
                'responsavel_nome' => 'nullable|required_if:menor,1|string|max:255',
                'responsavel_rg' => 'nullable|required_if:menor,1|string|max:20',
                'responsavel_telefone' => 'nullable|required_if:menor,1|string|max:20',
            ]);
            \Illuminate\Support\Facades\Log::info('Validação passou', $validated);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Erro de validação', $e->errors());
            throw $e;
        }

        // Verifica se a poltrona já está ocupada
        if ($onibus->assentosVendidos()->where('poltrona', $request->poltrona)->exists()) {
            \Illuminate\Support\Facades\Log::warning('Poltrona já ocupada: ' . $request->poltrona);
            return back()->withErrors(['poltrona' => 'Esta poltrona já está ocupada.']);
        }

        try {
            $assento = $onibus->assentosVendidos()->create([
                'paroquia_id' => $excursao->paroquia_id,
                'passageiro_nome' => $request->passageiro_nome,
                'passageiro_rg' => $request->passageiro_rg,
                'passageiro_telefone' => $request->passageiro_telefone,
                'poltrona' => $request->poltrona,
                'posicao' => $request->posicao,
                'menor' => $request->boolean('menor'),
                'responsavel_nome' => $request->responsavel_nome,
                'responsavel_rg' => $request->responsavel_rg,
                'responsavel_telefone' => $request->responsavel_telefone,
                'embarque_ida' => $request->has('embarque_ida'),
                'embarque_volta' => $request->has('embarque_volta'),
            ]);
            \Illuminate\Support\Facades\Log::info('Assento criado com sucesso', ['id' => $assento->id]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar assento no banco', ['message' => $e->getMessage()]);
            return back()->withErrors(['erro' => 'Erro ao salvar no banco de dados: ' . $e->getMessage()])->withInput();
        }

        return redirect()->route('excursoes.onibus.show', [$excursao, $onibus])->with('success', 'Passagem vendida com sucesso!');
    }

    public function destroyAssento(Excursao $excursao, Onibus $onibus, $assentoId)
    {
        if ($onibus->excursao_id != $excursao->id) {
            abort(404);
        }

        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $assento = $onibus->assentosVendidos()->findOrFail($assentoId);
        $assento->delete();

        return redirect()->route('excursoes.onibus.show', [$excursao, $onibus])->with('success', 'Assento liberado com sucesso!');
    }
}
