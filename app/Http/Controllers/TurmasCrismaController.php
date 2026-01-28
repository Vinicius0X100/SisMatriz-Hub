<?php

namespace App\Http\Controllers;

use App\Models\TurmaCrisma;
use App\Models\CatequistaCrisma;
use App\Models\Crismando;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TurmasCrismaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TurmaCrisma::with('catequista');

        // Filter by search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('turma', 'like', "%{$search}%");
        }

        // Filter by Status
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by Date (Inicio and Termino)
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('inicio', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('termino', '<=', $request->date_to);
        }

        if (Auth::check() && Auth::user()->paroquia_id) {
            $query->where('paroquia_id', Auth::user()->paroquia_id);
        }

        // Sorting
        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['turma', 'tutor', 'inicio', 'termino', 'alunos_qntd', 'status', 'created_at'];
        if (in_array($sortColumn, $allowedSorts)) {
             // Handle relationship sorting if needed
             if ($sortColumn === 'tutor') {
                 $query->join('catequistas_crisma', 'turmas.tutor', '=', 'catequistas_crisma.id')
                       ->select('turmas.*')
                       ->orderBy('catequistas_crisma.nome', $sortDirection);
             } else {
                 $query->orderBy($sortColumn, $sortDirection);
             }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $turmas = $query->paginate(10);

        if ($request->ajax()) {
            return view('modules.turmas-crisma.partials.list', compact('turmas'))->render();
        }

        $stats = [
            'total' => TurmaCrisma::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'active' => TurmaCrisma::where('paroquia_id', Auth::user()->paroquia_id)->whereIn('status', [1, 3])->count(),
            'inactive' => TurmaCrisma::where('paroquia_id', Auth::user()->paroquia_id)->whereIn('status', [2, 4])->count(),
        ];

        return view('modules.turmas-crisma.index', compact('turmas', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $catequistas = CatequistaCrisma::where('paroquia_id', Auth::user()->paroquia_id)
                                           ->where('status', 1)
                                           ->orderBy('nome')
                                           ->get();

        return view('modules.turmas-crisma.create', compact('catequistas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'turma' => 'required|string|max:255',
            'tutor' => 'required|exists:catequistas_crisma,id',
            'inicio' => 'required|date',
            'termino' => 'required|date|after_or_equal:inicio',
            'status' => 'required|in:1,2,3,4',
        ]);

        TurmaCrisma::create([
            'turma' => $request->turma,
            'tutor' => $request->tutor,
            'inicio' => $request->inicio,
            'termino' => $request->termino,
            'alunos_qntd' => 0, // Default value as requested
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return redirect()->route('turmas-crisma.index')->with('success', 'Turma adicionada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $turma = TurmaCrisma::findOrFail($id);
        
        // Ensure security check for paroquia
        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $catequistas = CatequistaCrisma::where('paroquia_id', Auth::user()->paroquia_id)
                                           ->where('status', 1)
                                           ->orderBy('nome')
                                           ->get();

        return view('modules.turmas-crisma.edit', compact('turma', 'catequistas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $turma = TurmaCrisma::findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'turma' => 'required|string|max:255',
            'tutor' => 'required|exists:catequistas_crisma,id',
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

        return redirect()->route('turmas-crisma.index')->with('success', 'Turma atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $turma = TurmaCrisma::findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $turma->delete();

        return redirect()->route('turmas-crisma.index')->with('success', 'Turma removida com sucesso!');
    }

    public function getStudents(string $id)
    {
        $turma = TurmaCrisma::with(['crismandos.register', 'catequista'])->findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $students = $turma->crismandos->map(function($crismando) {
            return [
                'id' => $crismando->cr_id,
                'name' => $crismando->register->name ?? 'Sem Nome',
                'phone' => $crismando->register->phone ?? 'Sem Telefone',
                'batizado' => $crismando->batizado,
                'is_transfered' => $crismando->is_transfered,
                'turma_id' => $crismando->turma_id
            ];
        });

        $availableTurmas = TurmaCrisma::where('paroquia_id', Auth::user()->paroquia_id)
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
            'student_id' => 'required|exists:crismandos,cr_id',
            'new_turma_id' => 'required|exists:turmas,id',
        ]);

        $crismando = Crismando::findOrFail($request->student_id);
        
        // Verify ownership
        if ($crismando->turma && $crismando->turma->paroquia_id != Auth::user()->paroquia_id) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newTurma = TurmaCrisma::findOrFail($request->new_turma_id);

        if ($newTurma->paroquia_id != Auth::user()->paroquia_id) {
             return response()->json(['error' => 'Unauthorized'], 403);
        }

        $crismando->update([
            'turma_id' => $request->new_turma_id,
            'is_transfered' => true,
            'transfer_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Aluno transferido com sucesso!']);
    }
}
