<?php

namespace App\Http\Controllers;

use App\Models\AcolitoFuncao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcolitoFuncaoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = AcolitoFuncao::where('paroquia_id', Auth::user()->paroquia_id);

            // Ordenação dinâmica
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            // Lista branca de colunas permitidas
            $allowedSorts = ['title', 'created_at', 'f_id'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }

            // Aplica a ordenação principal
            $query->orderBy($sortBy, $sortOrder);

            // Fallback para f_id caso a ordenação principal não seja f_id (desempate)
            if ($sortBy !== 'f_id') {
                $query->orderBy('f_id', 'desc');
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where('title', 'like', "%{$search}%");
            }

            try {
                $funcoes = $query->paginate(10);
            } catch (\Exception $e) {
                // Fallback seguro em caso de erro na ordenação (ex: coluna created_at não existe)
                $query = AcolitoFuncao::where('paroquia_id', Auth::user()->paroquia_id);
                
                if ($request->filled('search')) {
                    $search = $request->input('search');
                    $query->where('title', 'like', "%{$search}%");
                }
                
                $query->orderBy('f_id', 'desc');
                $funcoes = $query->paginate(10);
            }

            return response()->json($funcoes);
        }

        return view('modules.acolitos.funcoes.index');
    }

    public function create()
    {
        return view('modules.acolitos.funcoes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        AcolitoFuncao::create([
            'title' => $request->title,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return redirect()->route('acolitos.funcoes.index')
            ->with('success', 'Função criada com sucesso!');
    }

    public function edit($id)
    {
        $funcao = AcolitoFuncao::where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);

        return view('modules.acolitos.funcoes.edit', compact('funcao'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $funcao = AcolitoFuncao::where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);

        $funcao->update([
            'title' => $request->title,
        ]);

        return redirect()->route('acolitos.funcoes.index')
            ->with('success', 'Função atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $funcao = AcolitoFuncao::where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);

        $funcao->delete();

        return redirect()->route('acolitos.funcoes.index')
            ->with('success', 'Função excluída com sucesso!');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:acolitos_funcoes,f_id'
        ]);

        AcolitoFuncao::whereIn('f_id', $request->ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->delete();

        return response()->json(['message' => 'Funções excluídas com sucesso.']);
    }
}
