<?php

namespace App\Http\Controllers;

use App\Models\TurmaEucaristia;
use App\Models\CatequistaEucaristia;
use App\Models\Catecando;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Barryvdh\DomPDF\Facade\Pdf;

class TurmasEucaristiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TurmaEucaristia::with('catequista')->withCount('catecandos');

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
        
        $allowedSorts = ['turma', 'tutor', 'inicio', 'termino', 'status', 'created_at'];
        if (in_array($sortColumn, $allowedSorts)) {
             // Handle relationship sorting if needed, but for now simple column sort
             if ($sortColumn === 'tutor') {
                 // Sort by related model name would require join, for simplicity we might skip or do join
                 // Let's do a join for correct sorting if requested
                 $query->join('catequistas_eucaristia', 'turmas_catequese.tutor', '=', 'catequistas_eucaristia.id')
                       ->addSelect('turmas_catequese.*')
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

        $turma = TurmaEucaristia::create([
            'turma' => $request->turma,
            'tutor' => $request->tutor,
            'inicio' => $request->inicio,
            'termino' => $request->termino,
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        // Handle Students
        if ($request->has('students') && is_array($request->students)) {
            foreach ($request->students as $studentData) {
                if (isset($studentData['id'])) {
                    Catecando::create([
                        'turma_id' => $turma->id,
                        'register_id' => $studentData['id'],
                        'batizado' => isset($studentData['batizado']) ? (bool)$studentData['batizado'] : false,
                    ]);
                }
            }
        }

        return redirect()->route('catequese-eucaristia.index')->with('success', 'Turma adicionada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $turma = TurmaEucaristia::with('catecandos.register')->findOrFail($id);
        
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

        // Sync Students
        $submittedStudentIds = [];
        if ($request->has('students') && is_array($request->students)) {
            foreach ($request->students as $studentData) {
                if (isset($studentData['id'])) {
                    $submittedStudentIds[] = $studentData['id'];
                    $catecando = Catecando::where('turma_id', $turma->id)
                                          ->where('register_id', $studentData['id'])
                                          ->first();
                    
                    $isBatizado = isset($studentData['batizado']) ? (bool)$studentData['batizado'] : false;

                    if ($catecando) {
                        // Update existing
                        $catecando->batizado = $isBatizado;
                        $catecando->save();
                    } else {
                        // Create new
                        Catecando::create([
                            'turma_id' => $turma->id,
                            'register_id' => $studentData['id'],
                            'batizado' => $isBatizado,
                        ]);
                    }
                }
            }
        }

        // Remove students not in the submitted list
        Catecando::where('turma_id', $turma->id)
                 ->whereNotIn('register_id', $submittedStudentIds)
                 ->delete();

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

    public function exportStudents(Request $request, string $id)
    {
        $turma = TurmaEucaristia::with(['catecandos.register', 'catequista'])->findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $students = $turma->catecandos->map(function($student) {
            return [
                'name' => $student->register->name ?? 'Sem Nome',
                'phone' => $student->register->phone ?? 'Sem Telefone',
                'batizado' => $student->batizado
            ];
        })->sortBy('name');

        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView('pdf.turma-students', [
                'turma' => $turma,
                'students' => $students,
                'typeLabel' => 'Catecandos(as)',
                'paroquia' => Auth::user()->paroquia
            ]);
            return $pdf->download('turma_'.$id.'_catecandos.pdf');
        }

        if ($request->type === 'excel') {
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=turma_".$id."_catecandos.csv",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use ($students) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Excel
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['Nome', 'Telefone', 'Batizado']); // Headers

                foreach ($students as $student) {
                    fputcsv($file, [
                        $student['name'],
                        $student['phone'],
                        $student['batizado'] ? 'Sim' : 'Não'
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return redirect()->back()->with('error', 'Formato inválido.');
    }

    public function exportBulk(Request $request)
    {
        $ids = explode(',', $request->ids);
        $type = $request->type;
        
        $zipFile = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['error' => 'Não foi possível criar o arquivo ZIP'], 500);
        }

        $turmas = TurmaEucaristia::whereIn('id', $ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['catecandos.register', 'catequista'])
            ->get();

        if ($turmas->isEmpty()) {
             return response()->json(['error' => 'Nenhuma turma encontrada'], 404);
        }

        foreach ($turmas as $turma) {
            $students = $turma->catecandos->map(function($student) {
                return [
                    'name' => $student->register->name ?? 'Sem Nome',
                    'phone' => $student->register->phone ?? 'Sem Telefone',
                    'batizado' => $student->batizado
                ];
            })->sortBy('name');

            $filename = 'turma_' . $turma->id . '_' . \Illuminate\Support\Str::slug($turma->turma) . '.' . ($type === 'excel' ? 'csv' : 'pdf');

            if ($type === 'pdf') {
                $pdf = Pdf::loadView('pdf.turma-students', [
                    'turma' => $turma,
                    'students' => $students,
                    'typeLabel' => 'Catecandos(as)',
                    'paroquia' => Auth::user()->paroquia
                ]);
                $zip->addFromString($filename, $pdf->output());
            } else {
                // Excel/CSV
                $csv = "\xEF\xBB\xBF"; // BOM
                $csv .= "Nome,Telefone,Batizado\n";
                foreach ($students as $student) {
                    $csv .= '"' . str_replace('"', '""', $student['name']) . '",';
                    $csv .= '"' . str_replace('"', '""', $student['phone']) . '",';
                    $csv .= '"' . ($student['batizado'] ? 'Sim' : 'Não') . '"' . "\n";
                }
                $zip->addFromString($filename, $csv);
            }
        }

        $zip->close();

        return response()->download($zipFile, 'turmas_export_'.date('Y-m-d_H-i').'.zip')->deleteFileAfterSend(true);
    }
}
