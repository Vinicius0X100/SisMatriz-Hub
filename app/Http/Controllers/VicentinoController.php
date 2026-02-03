<?php

namespace App\Http\Controllers;

use App\Models\VinWatched;
use App\Models\Register;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VicentinoController extends Controller
{
    // Listagem
    public function index(Request $request)
    {
        $query = VinWatched::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('entidade')
            ->orderBy('w_id', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('month')) {
            $query->where('month_entire', $request->input('month'));
        }

        if ($request->filled('kind')) {
            $query->where('kind', $request->input('kind'));
        }

        if ($request->filled('ent_id')) {
            $query->where('ent_id', $request->input('ent_id'));
        }

        $records = $query->paginate(15);
        
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        
        $stats = [
            'total' => VinWatched::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'assistidos' => VinWatched::where('paroquia_id', Auth::user()->paroquia_id)->where('kind', 1)->count(),
            'nao_assistidos' => VinWatched::where('paroquia_id', Auth::user()->paroquia_id)->where('kind', 0)->count(),
        ];

        return view('modules.vicentinos.index', compact('records', 'entidades', 'stats'));
    }

    // Formulário de Criação
    public function create()
    {
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        return view('modules.vicentinos.create', compact('entidades'));
    }

    // Salvar
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'kind' => 'required|in:0,1',
            'month_entire' => 'required|integer|min:1|max:12',
            'address' => 'nullable|string',
            'address_number' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $data = $validated;
        $data['sendby'] = Auth::user()->name;
        $data['paroquia_id'] = Auth::user()->paroquia_id;
        $data['created_at'] = now();

        VinWatched::create($data);

        return redirect()->route('vicentinos.index')->with('success', 'Apuração registrada com sucesso!');
    }

    // Formulário de Edição
    public function edit($id)
    {
        $record = VinWatched::where('w_id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();
            
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        
        return view('modules.vicentinos.edit', compact('record', 'entidades'));
    }

    // Atualizar
    public function update(Request $request, $id)
    {
        $record = VinWatched::where('w_id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'kind' => 'required|in:0,1',
            'month_entire' => 'required|integer|min:1|max:12',
            'address' => 'nullable|string',
            'address_number' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $record->update($validated);

        return redirect()->route('vicentinos.index')->with('success', 'Apuração atualizada com sucesso!');
    }

    // Excluir
    public function destroy($id)
    {
        $record = VinWatched::where('w_id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $record->delete();

        return redirect()->route('vicentinos.index')->with('success', 'Registro excluído com sucesso.');
    }

    // Exclusão em massa
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:vin_watcheds,w_id'
        ]);

        $count = VinWatched::whereIn('w_id', $request->ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->delete();

        return response()->json(['message' => "$count registros excluídos com sucesso."]);
    }

    // Busca de Registros (AJAX)
    public function searchRegisters(Request $request)
    {
        $search = $request->input('q');
        if (strlen($search) < 3) {
            return response()->json([]);
        }

        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'address', 'address_number']);

        return response()->json($registers);
    }
}
