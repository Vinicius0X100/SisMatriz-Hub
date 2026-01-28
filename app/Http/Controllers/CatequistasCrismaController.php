<?php

namespace App\Http\Controllers;

use App\Models\CatequistaCrisma;
use App\Models\Entidade;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CatequistasCrismaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CatequistaCrisma::with(['entidade', 'register']);

        // Filter by search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%")
                  ->orWhereHas('entidade', function($q) use ($search) {
                      $q->where('ent_name', 'like', "%{$search}%");
                  });
        }

        // Filter by Paroquia (if applicable, usually good practice)
        if (Auth::check() && Auth::user()->paroquia_id) {
            $query->where('paroquia_id', Auth::user()->paroquia_id);
        }

        $catequistas = $query->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('modules.catequistas-crisma.partials.list', compact('catequistas'))->render();
        }

        return view('modules.catequistas-crisma.index', compact('catequistas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('ent_name')
                             ->get();
        // Fetch all registers for the search/select
        // Optimized: select only needed columns
        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('name')
                             ->select('id', 'name')
                             ->get();

        return view('modules.catequistas-crisma.create', compact('entidades', 'registers'));
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

        CatequistaCrisma::create([
            'register_id' => $request->register_id,
            'nome' => $register->name, // Denormalized as per screenshot/requirement
            'ent_id' => $request->ent_id,
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
            'created_at' => now(),
        ]);

        return redirect()->route('catequistas-crisma.index')->with('success', 'Catequista adicionado com sucesso!');
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
        $catequista = CatequistaCrisma::findOrFail($id);
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('ent_name')
                             ->get();
        // Registers list might not be needed if we don't allow changing the person, 
        // but typically edit allows changing fields. 
        // However, changing the person implies changing the name too.
        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('name')
                             ->select('id', 'name')
                             ->get();

        return view('modules.catequistas-crisma.edit', compact('catequista', 'entidades', 'registers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $catequista = CatequistaCrisma::findOrFail($id);

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
            // paroquia_id usually doesn't change
            // created_at doesn't change
        ]);

        return redirect()->route('catequistas-crisma.index')->with('success', 'Catequista atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $catequista = CatequistaCrisma::findOrFail($id);
        $catequista->delete();

        return redirect()->route('catequistas-crisma.index')->with('success', 'Catequista removido com sucesso!');
    }
}
