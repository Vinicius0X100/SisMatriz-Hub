<?php

namespace App\Http\Controllers;

use App\Models\Acolito;
use App\Models\Entidade;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcolitoController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = Acolito::where('paroquia_id', Auth::user()->paroquia_id)
                ->with(['register', 'entidade'])
                ->orderBy('id', 'desc');

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('register', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('ent_id')) {
                $query->where('ent_id', $request->input('ent_id'));
            }

            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            $acolitos = $query->paginate(10);

            // Transformar para JSON
            $acolitos->getCollection()->transform(function ($acolito) {
                return [
                    'id' => $acolito->id,
                    'name' => $acolito->register->name ?? $acolito->name,
                    'ent_name' => $acolito->entidade->ent_name ?? 'N/A',
                    'type' => $acolito->type,
                    'age' => $acolito->register->age ?? $acolito->age,
                    'graduation_year' => $acolito->graduation_year,
                    'status' => $acolito->status,
                ];
            });

            return response()->json($acolitos);
        }

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();

        $stats = [
            'total' => Acolito::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'active' => Acolito::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 0)->count(), // 0 = Ativo
            'inactive' => Acolito::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 1)->count(), // 1 = Inativo
        ];

        return view('modules.acolitos.index', compact('stats', 'entidades'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:acolitos,id'
        ]);

        Acolito::whereIn('id', $request->ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->delete();

        return response()->json(['message' => 'Registros excluídos com sucesso.']);
    }

    public function create()
    {
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        return view('modules.acolitos.create', compact('entidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'register_id' => 'required|exists:registers,id',
            'ent_id' => 'required|exists:entidades,ent_id',
            'type' => 'required|in:0,1',
            'graduation_year' => 'required|numeric',
            'status' => 'required|in:0,1',
        ]);

        $register = Register::find($request->register_id);

        Acolito::create([
            'name' => $register->name,
            'ent_id' => $request->ent_id,
            'type' => $request->type,
            'register_id' => $request->register_id,
            'age' => $register->age,
            'graduation_year' => $request->graduation_year,
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return redirect()->route('acolitos.index')->with('success', 'Acólito/Coroinha cadastrado com sucesso!');
    }

    public function edit($id)
    {
        $acolito = Acolito::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        return view('modules.acolitos.edit', compact('acolito', 'entidades'));
    }

    public function update(Request $request, $id)
    {
        $acolito = Acolito::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();

        $request->validate([
            'register_id' => 'required|exists:registers,id',
            'ent_id' => 'required|exists:entidades,ent_id',
            'type' => 'required|in:0,1',
            'graduation_year' => 'required|numeric',
            'status' => 'required|in:0,1',
        ]);

        $register = Register::find($request->register_id);

        $acolito->update([
            'name' => $register->name,
            'ent_id' => $request->ent_id,
            'type' => $request->type,
            'register_id' => $request->register_id,
            'age' => $register->age,
            'graduation_year' => $request->graduation_year,
            'status' => $request->status,
        ]);

        return redirect()->route('acolitos.index')->with('success', 'Cadastro atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $acolito = Acolito::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();
        $acolito->delete();

        return redirect()->route('acolitos.index')->with('success', 'Removido com sucesso!');
    }

    public function searchRegisters(Request $request)
    {
        $search = $request->get('q');
        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('name', 'like', "%{$search}%")
            ->limit(20)
            ->get(['id', 'name', 'age']);

        return response()->json($registers);
    }
}
