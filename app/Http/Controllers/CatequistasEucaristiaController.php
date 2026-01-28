<?php

namespace App\Http\Controllers;

use App\Models\CatequistaEucaristia;
use App\Models\Entidade;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatequistasEucaristiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CatequistaEucaristia::with(['entidade', 'register']);

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
            return view('modules.catequistas-eucaristia.partials.list', compact('catequistas'))->render();
        }

        return view('modules.catequistas-eucaristia.index', compact('catequistas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $entidades = Entidade::orderBy('ent_name')->get();
        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
                             ->where('status', 1)
                             ->orderBy('name')
                             ->select('id', 'name')
                             ->get();

        return view('modules.catequistas-eucaristia.create', compact('entidades', 'registers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'register_id' => 'required|exists:registers,id',
            'ent_id' => 'required|exists:entidades,ent_id',
            'status' => 'required|in:0,1',
        ]);

        $register = Register::findOrFail($request->register_id);

        CatequistaEucaristia::create([
            'register_id' => $request->register_id,
            'nome' => $register->name,
            'ent_id' => $request->ent_id,
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
            'created_at' => now(),
        ]);

        return redirect()->route('catequistas-eucaristia.index')->with('success', 'Catequista adicionado com sucesso!');
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
        $catequista = CatequistaEucaristia::findOrFail($id);
        $entidades = Entidade::orderBy('ent_name')->get();
        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('name')
                             ->select('id', 'name')
                             ->get();

        return view('modules.catequistas-eucaristia.edit', compact('catequista', 'entidades', 'registers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $catequista = CatequistaEucaristia::findOrFail($id);

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

        return redirect()->route('catequistas-eucaristia.index')->with('success', 'Catequista atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $catequista = CatequistaEucaristia::findOrFail($id);
        $catequista->delete();

        return redirect()->route('catequistas-eucaristia.index')->with('success', 'Catequista removido com sucesso!');
    }
}
