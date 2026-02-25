<?php

namespace App\Http\Controllers;

use App\Models\Excursao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExcursaoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Excursao::query();
        
        if ($user->paroquia_id) {
            $query->where('paroquia_id', $user->paroquia_id);
        }

        // Stats calculation
        $statsQuery = clone $query;
        $stats = [
            'total' => $statsQuery->count(),
            'active' => (clone $statsQuery)->where('finalizada', false)->count(),
            'finished' => (clone $statsQuery)->where('finalizada', true)->count(),
        ];

        // Search by destination
        if ($request->has('search') && !empty($request->search)) {
            $query->where('destino', 'like', "%{$request->search}%");
        }

        // Filter by type
        if ($request->has('tipo') && !empty($request->tipo)) {
            $query->where('tipo', $request->tipo);
        }

        // Filter by status (finalizada)
        if ($request->has('status') && $request->status !== null) {
            $query->where('finalizada', $request->status == '1');
        }
        
        $excursoes = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Get unique types for filter
        $typesQuery = Excursao::select('tipo')->distinct()->whereNotNull('tipo');
        if ($user->paroquia_id) {
            $typesQuery->where('paroquia_id', $user->paroquia_id);
        }
        $types = $typesQuery->pluck('tipo');

        return view('modules.excursoes.index', compact('excursoes', 'stats', 'types'));
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
        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $excursao->load('onibus');

        return view('modules.excursoes.show', compact('excursao'));
    }

    public function edit(Excursao $excursao)
    {
        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }
        return view('modules.excursoes.edit', compact('excursao'));
    }

    public function update(Request $request, Excursao $excursao)
    {
        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
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
        $user = Auth::user();
        if ($user->paroquia_id && $excursao->paroquia_id != $user->paroquia_id) {
            abort(403);
        }

        $excursao->delete();

        return redirect()->route('excursoes.index')->with('success', 'Excursão excluída com sucesso!');
    }
}
