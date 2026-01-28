<?php

namespace App\Http\Controllers;

use App\Models\TurmaEucaristia;
use App\Models\CatequistaEucaristia;
use App\Models\Catecando;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TurmasEucaristiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TurmaEucaristia::with('catequista');

        // Filter by search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('turma', 'like', "%{$search}%");
        }

        // Filter by Status
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by Date (Inicio)
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('inicio', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('inicio', '<=', $request->date_to);
        }

        if (Auth::check() && Auth::user()->paroquia_id) {
            $query->where('paroquia_id', Auth::user()->paroquia_id);
        }

        // Sorting
        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['turma', 'tutor', 'inicio', 'termino', 'status', 'created_at'];
        if (in_array($sortColumn, $allowedSorts)) {
             // Handle relationship sorting if needed, but for now simple column sort
             if ($sortColumn === 'tutor') {
                 // Sort by related model name would require join, for simplicity we might skip or do join
                 // Let's do a join for correct sorting if requested
                 $query->join('catequistas_eucaristia', 'turmas_catequese.tutor', '=', 'catequistas_eucaristia.id')
                       ->select('turmas_catequese.*')
                       ->orderBy('catequistas_eucaristia.nome', $sortDirection);
             } else {
                 $query->orderBy($sortColumn, $sortDirection);
             }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $turmas = $query->paginate(10);

        if ($request->ajax()) {
            return view('modules.turmas-eucaristia.partials.list', compact('turmas'))->render();
        }

        $stats = [
            'total' => TurmaEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'active' => TurmaEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->whereIn('status', [1, 3])->count(),
            'inactive' => TurmaEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->whereIn('status', [2, 4])->count(),
        ];

        return view('modules.turmas-eucaristia.index', compact('turmas', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $catequistas = CatequistaEucaristia::where('paroquia_id', Auth::user()->paroquia_id)
                                           ->where('status', 1)
                                           ->orderBy('nome')
                                           ->get();

        return view('modules.turmas-eucaristia.create', compact('catequistas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'turma' => 'required|string|max:255',
            'tutor' => 'required|exists:catequistas_eucaristia,id',
            'inicio' => 'required|date',
            'termino' => 'required|date|after_or_equal:inicio',
            'status' => 'required|in:1,2,3,4',
        ]);

        TurmaEucaristia::create([
            'turma' => $request->turma,
            'tutor' => $request->tutor,
            'inicio' => $request->inicio,
            'termino' => $request->termino,
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return redirect()->route('turmas-eucaristia.index')->with('success', 'Turma adicionada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $turma = TurmaEucaristia::findOrFail($id);
        
        // Ensure security check for paroquia
        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $catequistas = CatequistaEucaristia::where('paroquia_id', Auth::user()->paroquia_id)
                                           ->where('status', 1)
                                           ->orderBy('nome')
                                           ->get();

        return view('modules.turmas-eucaristia.edit', compact('turma', 'catequistas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $turma = TurmaEucaristia::findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'turma' => 'required|string|max:255',
            'tutor' => 'required|exists:catequistas_eucaristia,id',
            'inicio' => 'required|date',
            'termino' => 'required|date|after_or_equal:inicio',
            'status' => 'required|in:1,2,3,4',
        ]);

        $turma->update([
            'turma' => $request->turma,
            'tutor' => $request->tutor,
            'inicio' => $request->inicio,
            'termino' => $request->termino,
            'status' => $request->status,
        ]);

        return redirect()->route('turmas-eucaristia.index')->with('success', 'Turma atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $turma = TurmaEucaristia::findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $turma->delete();

        return redirect()->route('turmas-eucaristia.index')->with('success', 'Turma removida com sucesso!');
    }

    public function getStudents(string $id)
    {
        $turma = TurmaEucaristia::with(['catecandos.register', 'catequista'])->findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $students = $turma->catecandos->map(function($catecando) {
            return [
                'id' => $catecando->cr_id,
                'name' => $catecando->register->name ?? 'Sem Nome',
                'phone' => $catecando->register->phone ?? 'Sem Telefone',
                'batizado' => $catecando->batizado,
                'is_transfered' => $catecando->is_transfered,
                'turma_id' => $catecando->turma_id
            ];
        });

        $availableTurmas = TurmaEucaristia::where('paroquia_id', Auth::user()->paroquia_id)
                                          ->where('id', '!=', $id)
                                          ->whereIn('status', [1, 3])
                                          ->get(['id', 'turma']);

        return response()->json([
            'turma' => $turma,
            'students' => $students,
            'availableTurmas' => $availableTurmas
        ]);
    }

    public function transferStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:catecandos,cr_id',
            'new_turma_id' => 'required|exists:turmas_catequese,id',
        ]);

        $catecando = Catecando::findOrFail($request->student_id);
        
        // Verify ownership via current turma
        if ($catecando->turma && $catecando->turma->paroquia_id != Auth::user()->paroquia_id) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newTurma = TurmaEucaristia::findOrFail($request->new_turma_id);

        if ($newTurma->paroquia_id != Auth::user()->paroquia_id) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        $catecando->update([
            'turma_id' => $request->new_turma_id,
            'is_transfered' => true,
            'transfer_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Aluno transferido com sucesso!']);
    }
}
