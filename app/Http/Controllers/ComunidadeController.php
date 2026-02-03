<?php

namespace App\Http\Controllers;

use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComunidadeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Entidade::where('paroquia_id', Auth::user()->paroquia_id);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('ent_name', 'like', "%{$search}%");
        }

        $comunidades = $query->orderBy('ent_name')->paginate(10);

        return view('modules.comunidades.index', compact('comunidades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modules.comunidades.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ent_name' => 'required|string|max:255',
            'cep' => 'required|string|max:10',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|max:2',
        ]);

        // Format address: Rua X, 123 - Bairro - Cidade/UF - CEP: 00000-000
        $address = "{$request->rua}, {$request->numero} - {$request->bairro} - {$request->cidade}/{$request->estado} - CEP: {$request->cep}";

        Entidade::create([
            'ent_name' => $request->ent_name,
            'address' => $address,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return redirect()->route('comunidades.index')->with('success', 'Comunidade criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $comunidade = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        // Parse address to pre-fill form
        // Expected format: Rua X, 123 - Bairro - Cidade/UF - CEP: 00000-000
        // We will try a regex or simple split. Regex is safer.
        $parsedAddress = [
            'rua' => '',
            'numero' => '',
            'bairro' => '',
            'cidade' => '',
            'estado' => '',
            'cep' => '',
        ];

        if ($comunidade->address) {
            if (preg_match('/^(.*), (.*) - (.*) - (.*)\/(.*) - CEP: (.*)$/', $comunidade->address, $matches)) {
                $parsedAddress = [
                    'rua' => $matches[1],
                    'numero' => $matches[2],
                    'bairro' => $matches[3],
                    'cidade' => $matches[4],
                    'estado' => $matches[5],
                    'cep' => $matches[6],
                ];
            } else {
                // Fallback if format doesn't match perfectly, maybe put everything in 'rua' or handle gracefully
                // For now, let's leave empty or try to guess.
                // Or just pass the full address string to 'rua' and let user fix it if it's legacy data.
                $parsedAddress['rua'] = $comunidade->address;
            }
        }

        return view('modules.comunidades.edit', compact('comunidade', 'parsedAddress'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $comunidade = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        $request->validate([
            'ent_name' => 'required|string|max:255',
            'cep' => 'required|string|max:10',
            'rua' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'bairro' => 'required|string|max:100',
            'cidade' => 'required|string|max:100',
            'estado' => 'required|string|max:2',
        ]);

        $address = "{$request->rua}, {$request->numero} - {$request->bairro} - {$request->cidade}/{$request->estado} - CEP: {$request->cep}";

        $comunidade->update([
            'ent_name' => $request->ent_name,
            'address' => $address,
        ]);

        return redirect()->route('comunidades.index')->with('success', 'Comunidade atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $comunidade = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $comunidade->delete();

        return redirect()->route('comunidades.index')->with('success', 'Comunidade excluída com sucesso!');
    }
}
