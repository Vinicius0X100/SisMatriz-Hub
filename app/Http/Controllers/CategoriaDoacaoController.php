<?php

namespace App\Http\Controllers;

use App\Models\CategoriaDoacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriaDoacaoController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id);

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $records = $query->orderBy('name', 'asc')->paginate(10);

        return view('modules.categorias_doacao.index', compact('records'));
    }

    public function create()
    {
        return view('modules.categorias_doacao.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $validated['paroquia_id'] = Auth::user()->paroquia_id;

            CategoriaDoacao::create($validated);

            return redirect()->route('categorias_doacao.index')
                ->with('success', 'Categoria criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar categoria: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $record = CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);

        return view('modules.categorias_doacao.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        try {
            $record = CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id)
                ->findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $record->update($validated);

            return redirect()->route('categorias_doacao.index')
                ->with('success', 'Categoria atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar categoria: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $record = CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);

        $record->delete();

        return redirect()->route('categorias_doacao.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids)) {
            return redirect()->route('categorias_doacao.index')
                ->with('error', 'Nenhum registro selecionado.');
        }

        CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->route('categorias_doacao.index')
            ->with('success', 'Registros selecionados excluídos com sucesso!');
    }
}
