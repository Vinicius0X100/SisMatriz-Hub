<?php

namespace App\Http\Controllers;

use App\Models\TurmaCrisma;
use App\Models\CatequistaCrisma;
use App\Models\Crismando;
use App\Models\FaltaCrisma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Barryvdh\DomPDF\Facade\Pdf;

class TurmasCrismaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TurmaCrisma::with('catequista')->withCount('crismandos');

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
                 // Sort by related model name would require join, for simplicity we might skip or do join
                 // Let's do a join for correct sorting if requested
                  $query->join('catequistas_crisma', 'turmas.tutor', '=', 'catequistas_crisma.id')
                        ->addSelect('turmas.*')
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

        $turma = TurmaCrisma::create([
            'turma' => $request->turma,
            'tutor' => $request->tutor,
            'inicio' => $request->inicio,
            'termino' => $request->termino,
            'alunos_qntd' => 0, // Will be updated
            'status' => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        // Handle Students
        if ($request->has('students') && is_array($request->students)) {
            foreach ($request->students as $studentData) {
                if (isset($studentData['id'])) {
                    Crismando::create([
                        'turma_id' => $turma->id,
                        'register_id' => $studentData['id'],
                        'batizado' => isset($studentData['batizado']) ? (bool)$studentData['batizado'] : false,
                    ]);
                }
            }
            // Update count
            $turma->alunos_qntd = $turma->crismandos()->count();
            $turma->save();
        }

        return redirect()->route('turmas-crisma.index')->with('success', 'Turma adicionada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $turma = TurmaCrisma::with('crismandos.register')->findOrFail($id);
        
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

        // Sync Students
        $submittedStudentIds = [];
        if ($request->has('students') && is_array($request->students)) {
            foreach ($request->students as $studentData) {
                if (isset($studentData['id'])) {
                    $submittedStudentIds[] = $studentData['id'];
                    $crismando = Crismando::where('turma_id', $turma->id)
                                          ->where('register_id', $studentData['id'])
                                          ->first();
                    
                    $isBatizado = isset($studentData['batizado']) ? (bool)$studentData['batizado'] : false;

                    if ($crismando) {
                        // Update existing
                        $crismando->batizado = $isBatizado;
                        $crismando->save();
                    } else {
                        // Create new
                        Crismando::create([
                            'turma_id' => $turma->id,
                            'register_id' => $studentData['id'],
                            'batizado' => $isBatizado,
                        ]);
                    }
                }
            }
        }

        // Remove students not in the submitted list
        Crismando::where('turma_id', $turma->id)
                 ->whereNotIn('register_id', $submittedStudentIds)
                 ->delete();

        // Update count
        $turma->alunos_qntd = $turma->crismandos()->count();
        $turma->save();

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
            'student_id' => 'required|exists:crismandos,register_id',
            'new_turma_id' => 'required|exists:turmas,id',
        ]);

        $student = Crismando::where('register_id', $request->student_id)->first();
        
        if (!$student) {
            return response()->json(['error' => 'Aluno não encontrado'], 404);
        }

        $student->turma_id = $request->new_turma_id;
        $student->is_transfered = true;
        $student->transfer_date = now();
        $student->save();

        return response()->json(['success' => true]);
    }

    public function exportStudents(Request $request, string $id)
    {
        $turma = TurmaCrisma::with(['crismandos.register', 'catequista'])->findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $students = $turma->crismandos->map(function($student) {
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
                'typeLabel' => 'Crismandos(as)',
                'paroquia' => Auth::user()->paroquia
            ]);
            return $pdf->download('turma_'.$id.'_crismandos.pdf');
        }

        if ($request->type === 'excel') {
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=turma_".$id."_crismandos.csv",
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

        $turmas = TurmaCrisma::whereIn('id', $ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['crismandos.register', 'catequista'])
            ->get();

        if ($turmas->isEmpty()) {
             return response()->json(['error' => 'Nenhuma turma encontrada'], 404);
        }

        foreach ($turmas as $turma) {
            $students = $turma->crismandos->map(function($student) {
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
                    'typeLabel' => 'Crismandos(as)',
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

    public function getAttendance(Request $request, $id)
    {
        $turma = TurmaCrisma::findOrFail($id);
        $date = $request->input('date', date('Y-m-d'));
        
        $students = Crismando::where('turma_id', $id)
            ->with('register')
            ->get()
            ->map(function ($student) use ($id, $date) {
                $falta = FaltaCrisma::where('turma_id', $id)
                    ->where('aluno_id', $student->register_id)
                    ->where('data_aula', $date)
                    ->first();
                
                return [
                    'id' => $student->register->id,
                    'name' => $student->register->name,
                    'status' => $falta ? $falta->status : 0,
                    'title' => $falta ? $falta->title : '',
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json([
            'turma' => $turma->turma,
            'students' => $students
        ]);
    }

    public function saveAttendance(Request $request)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'aluno_id' => 'required|exists:registers,id',
            'data_aula' => 'required|date',
            'title' => 'required|string',
            'status' => 'required|boolean',
        ]);

        $falta = FaltaCrisma::updateOrCreate(
            [
                'turma_id' => $request->turma_id,
                'aluno_id' => $request->aluno_id,
                'data_aula' => $request->data_aula,
            ],
            [
                'title' => $request->title,
                'status' => $request->status,
            ]
        );

        return response()->json(['success' => true, 'data' => $falta]);
    }

    public function saveBulkAttendance(Request $request)
    {
        $request->validate([
            'turma_id' => 'required|exists:turmas,id',
            'data_aula' => 'required|date',
            'title' => 'required|string',
            'students' => 'required|array',
            'students.*.aluno_id' => 'required|exists:registers,id',
            'students.*.status' => 'required|boolean',
        ]);

        foreach ($request->students as $studentData) {
            FaltaCrisma::updateOrCreate(
                [
                    'turma_id' => $request->turma_id,
                    'aluno_id' => $studentData['aluno_id'],
                    'data_aula' => $request->data_aula,
                ],
                [
                    'title' => $request->title,
                    'status' => $studentData['status'],
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function attendanceAnalysis(Request $request, $id)
    {
        $turma = TurmaCrisma::with(['catequista'])->findOrFail($id);
        
        if (Auth::user()->paroquia_id && $turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $query = $turma->crismandos()->with('register');

        // Filter by Name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('register', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filter by Batizado
        if ($request->has('filter_batizado') && $request->filter_batizado !== null && $request->filter_batizado !== '') {
            if ($request->filter_batizado == '1') {
                $query->where('batizado', true);
            } elseif ($request->filter_batizado == '0') {
                $query->where('batizado', false);
            }
        }

        $students = $query->get()->map(function($crismando) use ($turma) {
            $presencas = FaltaCrisma::where('turma_id', $turma->id)
                                    ->where('aluno_id', $crismando->register_id)
                                    ->where('status', 1)
                                    ->count();
            
            $faltas = FaltaCrisma::where('turma_id', $turma->id)
                                 ->where('aluno_id', $crismando->register_id)
                                 ->where('status', 0)
                                 ->count();
            
            $presencasList = FaltaCrisma::where('turma_id', $turma->id)
                                        ->where('aluno_id', $crismando->register_id)
                                        ->where('status', 1)
                                        ->orderBy('data_aula', 'desc')
                                        ->get(['data_aula', 'title']);
                                        
            $faltasList = FaltaCrisma::where('turma_id', $turma->id)
                                     ->where('aluno_id', $crismando->register_id)
                                     ->where('status', 0)
                                     ->orderBy('data_aula', 'desc')
                                     ->get(['data_aula', 'title']);

            return [
                'id' => $crismando->register_id,
                'name' => $crismando->register->name ?? 'Sem Nome',
                'batizado' => $crismando->batizado,
                'presencas' => $presencas,
                'faltas' => $faltas,
                'presencas_list' => $presencasList,
                'faltas_list' => $faltasList,
            ];
        });

        // Filter by Attendance (Post-processing)
        if ($request->has('filter_attendance') && $request->filter_attendance != '') {
            if ($request->filter_attendance == 'has_faults') {
                $students = $students->filter(function($s) { return $s['faltas'] > 0; });
            } elseif ($request->filter_attendance == 'has_presences') {
                $students = $students->filter(function($s) { return $s['presencas'] > 0; });
            }
        }

        return view('modules.turmas-crisma.attendance-analysis', compact('turma', 'students'));
    }

    public function attendanceHistory(Request $request, $id, $student_id)
    {
        $turma = TurmaCrisma::findOrFail($id);
        $student = \App\Models\Register::findOrFail($student_id);
        
        if (Auth::user()->paroquia_id && $turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $query = FaltaCrisma::with('justificativa')
                              ->where('turma_id', $id)
                              ->where('aluno_id', $student_id);

        // Filter by Status (Presence/Absence)
        if ($request->filled('status')) {
            if ($request->status == 'present') {
                $query->where('status', 1);
            } elseif ($request->status == 'absent') {
                $query->where('status', 0);
            }
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('data_aula', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('data_aula', '<=', $request->end_date);
        }

        $history = $query->orderBy('data_aula', 'desc')->get();

        return view('modules.turmas-crisma.attendance-history', compact('turma', 'student', 'history'));
    }

    public function storeJustification(Request $request)
    {
        $request->validate([
            'falta_id' => 'required|exists:faltas_crisma,id',
            'justify' => 'required|string|max:255',
        ]);

        $falta = FaltaCrisma::findOrFail($request->falta_id);
        
        // Check permission
        $turma = TurmaCrisma::findOrFail($falta->turma_id);
        if (Auth::user()->paroquia_id && $turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        \App\Models\FaltaJustify::updateOrCreate(
            ['faltas_id' => $request->falta_id],
            ['justify' => $request->justify]
        );

        return back()->with('success', 'Justificativa salva com sucesso!');
    }
}
