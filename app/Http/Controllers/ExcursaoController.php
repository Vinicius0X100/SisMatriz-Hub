<?php

namespace App\Http\Controllers;

use App\Models\Excursao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcursaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = Excursao::query();
        
        if (!in_array($user->rule, [1, 111])) {
            $query->where('paroquia_id', $user->paroquia_id);
        }
        
        $excursoes = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('modules.excursoes.index', compact('excursoes'));
    }

    public function create()
    {
        return view('modules.excursoes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'destino' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        Excursao::create([
            'paroquia_id' => Auth::user()->paroquia_id,
            'destino' => $request->destino,
            'tipo' => $request->tipo,
            'descricao' => $request->descricao,
            'status' => true,
        ]);

        return redirect()->route('excursoes.index')->with('success', 'Excursão criada com sucesso!');
    }

    public function show(Excursao $excursao)
    {
        if ($excursao->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $excursao->load('onibus');

        return view('modules.excursoes.show', compact('excursao'));
    }

    public function edit(Excursao $excursao)
    {
        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }
        return view('modules.excursoes.edit', compact('excursao'));
    }

    public function update(Request $request, Excursao $excursao)
    {
        $user = Auth::user();
        if (!in_array($user->rule, [1, 111]) && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'destino' => 'required|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'descricao' => 'nullable|string',
        ]);

        $excursao->update($request->only(['destino', 'tipo', 'descricao']));

        return redirect()->route('excursoes.index')->with('success', 'Excursão atualizada com sucesso!');
    }

    public function destroy(Excursao $excursao)
    {
        if ($excursao->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $excursao->delete();

        return redirect()->route('excursoes.index')->with('success', 'Excursão excluída com sucesso!');
    }
}
