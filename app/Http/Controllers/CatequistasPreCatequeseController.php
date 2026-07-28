<?php

namespace App\Http\Controllers;

use App\Models\CatequistaPreCatequese;
use App\Models\Entidade;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CatequistasPreCatequeseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CatequistaPreCatequese::with(['entidade', 'register']);

        // Filter by search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhereHas('entidade', function($q) use ($search) {
                      $q->where('ent_name', 'like', "%{$search}%");
                  });
        }

        if (Auth::check() && Auth::user()->paroquia_id) {
            $query->where('paroquia_id', Auth::user()->paroquia_id);
        }

        $catequistas = $query->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('modules.catequistas-pre-catequese.partials.list', compact('catequistas'))->render();
        }

        return view('modules.catequistas-pre-catequese.index', compact('catequistas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('ent_name')
                             ->get();
        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('name')
                             ->select('id', 'name')
                             ->get();

        return view('modules.catequistas-pre-catequese.create', compact('entidades', 'registers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'register_id' => [
                'required',
                'exists:registers,id',
                Rule::unique('catequistas_pre_catequese', 'register_id')->where(function ($query) {
                    return $query->where('paroquia_id', Auth::user()->paroquia_id);
                }),
            ],
            'ent_id' => 'required|exists:entidades,ent_id',
            'status' => 'required|in:0,1',
        ], [
            'register_id.unique' => 'Esta pessoa já está cadastrada como Catequista de Pré-Catequese.',
        ]);

        $register = Register::findOrFail($request->register_id);

        CatequistaPreCatequese::create([
            'register_id' => $request->register_id,
            'nome' => $register->name,
            'ent_id' => $request->ent_id,
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
            'created_at' => now(),
        ]);

        return redirect()->route('catequistas-pre-catequese.index')->with('success', 'Catequista adicionado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $catequista = CatequistaPreCatequese::findOrFail($id);
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('ent_name')
                             ->get();
        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('name')
                             ->select('id', 'name')
                             ->get();

        return view('modules.catequistas-pre-catequese.edit', compact('catequista', 'entidades', 'registers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $catequista = CatequistaPreCatequese::findOrFail($id);

        $request->validate([
            'register_id' => 'required|exists:registers,id',
            'ent_id' => 'required|exists:entidades,ent_id',
            'status' => 'required|in:0,1',
        ]);

        $register = Register::findOrFail($request->register_id);

        $catequista->update([
            'register_id' => $request->register_id,
            'nome' => $register->name,
            'ent_id' => $request->ent_id,
            'status' => $request->status,
        ]);

        return redirect()->route('catequistas-pre-catequese.index')->with('success', 'Catequista atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $catequista = CatequistaPreCatequese::findOrFail($id);
        $catequista->delete();

        return redirect()->route('catequistas-pre-catequese.index')->with('success', 'Catequista removido com sucesso!');
    }
}
