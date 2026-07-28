<?php

namespace App\Http\Controllers;

use App\Models\TurmaPreCatequese;
use App\Models\CatequistaPreCatequese;
use App\Models\CatecandoPreCatequese;
use App\Models\FaltaPreCatequese;
use App\Models\InscricaoPreCatequese;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TurmasPreCatequeseController extends Controller
{
    /**
     * Verifica se o usuário é coordenador (role 20).
     */
    private function isCoordenador(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        $roles = explode(',', $user->rule ?? '');
        return in_array('20', $roles) || in_array('1', $roles) || in_array('111', $roles);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TurmaPreCatequese::with('catequista')->withCount('catecandos');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('turma', 'like', "%{$search}%");
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->where('inicio', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->where('termino', '<=', $request->date_to);
        }

        if (Auth::check() && Auth::user()->paroquia_id) {
            $query->where('paroquia_id', Auth::user()->paroquia_id);
        }

        $sortColumn    = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        $allowedSorts  = ['turma', 'tutor', 'inicio', 'termino', 'status', 'created_at'];

        if (in_array($sortColumn, $allowedSorts)) {
            if ($sortColumn === 'tutor') {
                $query->join('catequistas_pre_catequese', 'turmas_pre_catequese.tutor', '=', 'catequistas_pre_catequese.id')
                      ->addSelect('turmas_pre_catequese.*')
                      ->orderBy('catequistas_pre_catequese.nome', $sortDirection);
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $turmas = $query->paginate(10);

        if ($request->ajax()) {
            $isCoordenador = $this->isCoordenador();
            return view('modules.turmas-pre-catequese.partials.list', compact('turmas', 'isCoordenador'))->render();
        }

        $paroquiaId = Auth::user()->paroquia_id;
        $stats = [
            'total'    => TurmaPreCatequese::where('paroquia_id', $paroquiaId)->count(),
            'active'   => TurmaPreCatequese::where('paroquia_id', $paroquiaId)->whereIn('status', [1, 3])->count(),
            'inactive' => TurmaPreCatequese::where('paroquia_id', $paroquiaId)->whereIn('status', [2, 4])->count(),
        ];

        $isCoordenador = $this->isCoordenador();

        return view('modules.turmas-pre-catequese.index', compact('turmas', 'stats', 'isCoordenador'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!$this->isCoordenador()) {
            abort(403, 'Acesso negado.');
        }

        $catequistas = CatequistaPreCatequese::where('paroquia_id', Auth::user()->paroquia_id)
                                             ->where('status', 1)
                                             ->orderBy('nome')
                                             ->get();

        return view('modules.turmas-pre-catequese.create', compact('catequistas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$this->isCoordenador()) {
            abort(403, 'Acesso negado.');
        }

        $request->validate([
            'turma'   => 'required|string|max:100',
            'tutor'   => 'required|exists:catequistas_pre_catequese,id',
            'inicio'  => 'required|string',
            'termino' => 'required|string',
            'status'  => 'required|in:1,2,3,4',
        ]);

        $turma = TurmaPreCatequese::create([
            'turma'       => $request->turma,
            'tutor'       => $request->tutor,
            'inicio'      => $request->inicio,
            'termino'     => $request->termino,
            'status'      => $request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        if ($request->has('students') && is_array($request->students)) {
            foreach ($request->students as $studentData) {
                if (isset($studentData['id'])) {
                    CatecandoPreCatequese::create([
                        'turma_id'     => $turma->id,
                        'register_id'  => $studentData['id'],
                        'inscricao_id' => $studentData['inscricao_id'] ?? null,
                        'batizado'     => isset($studentData['batizado']) ? 1 : 0,
                    ]);
                }
            }
        }

        return redirect()->route('turmas-pre-catequese.index')->with('success', 'Turma criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (!$this->isCoordenador()) {
            abort(403, 'Acesso negado.');
        }

        $turma = TurmaPreCatequese::with('catecandos.register')->findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $catequistas = CatequistaPreCatequese::where('paroquia_id', Auth::user()->paroquia_id)
                                             ->where('status', 1)
                                             ->orderBy('nome')
                                             ->get();

        $isCoordenador = true;

        return view('modules.turmas-pre-catequese.edit', compact('turma', 'catequistas', 'isCoordenador'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (!$this->isCoordenador()) {
            abort(403, 'Acesso negado.');
        }

        $turma = TurmaPreCatequese::findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'turma'   => 'required|string|max:100',
            'tutor'   => 'required|exists:catequistas_pre_catequese,id',
            'inicio'  => 'required|string',
            'termino' => 'required|string',
            'status'  => 'required|in:1,2,3,4',
        ]);

        $turma->update([
            'turma'   => $request->turma,
            'tutor'   => $request->tutor,
            'inicio'  => $request->inicio,
            'termino' => $request->termino,
            'status'  => $request->status,
        ]);

        $submittedStudentIds = [];
        if ($request->has('students') && is_array($request->students)) {
            foreach ($request->students as $studentData) {
                if (isset($studentData['id'])) {
                    $submittedStudentIds[] = $studentData['id'];
                    $catecando = CatecandoPreCatequese::where('turma_id', $turma->id)
                                                      ->where('register_id', $studentData['id'])
                                                      ->first();
                    if (!$catecando) {
                        CatecandoPreCatequese::create([
                            'turma_id'     => $turma->id,
                            'register_id'  => $studentData['id'],
                            'inscricao_id' => $studentData['inscricao_id'] ?? null,
                            'batizado'     => isset($studentData['batizado']) ? 1 : 0,
                        ]);
                    } else {
                        $catecando->update([
                            'batizado' => isset($studentData['batizado']) ? 1 : 0,
                        ]);
                    }
                }
            }
        }

        CatecandoPreCatequese::where('turma_id', $turma->id)
                             ->whereNotIn('register_id', $submittedStudentIds)
                             ->delete();

        return redirect()->route('turmas-pre-catequese.index')->with('success', 'Turma atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$this->isCoordenador()) {
            abort(403, 'Acesso negado.');
        }

        $turma = TurmaPreCatequese::findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $turma->delete();

        return redirect()->route('turmas-pre-catequese.index')->with('success', 'Turma removida com sucesso!');
    }

    /**
     * Get students JSON for the modal.
     */
    public function getStudents(string $id)
    {
        $turma = TurmaPreCatequese::with(['catecandos.register', 'catequista'])->findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $students = $turma->catecandos->map(function ($catecando) {
            return [
                'id'            => $catecando->cr_id,
                'register_id'   => $catecando->register_id,
                'name'          => $catecando->register->name ?? 'Sem Nome',
                'phone'         => $catecando->register->phone ?? 'Sem Telefone',
                'batizado'      => $catecando->batizado,
                'is_transfered' => $catecando->is_transfered,
                'turma_id'      => $catecando->turma_id,
            ];
        });

        $availableTurmas = TurmaPreCatequese::where('paroquia_id', Auth::user()->paroquia_id)
                                            ->where('id', '!=', $id)
                                            ->whereIn('status', [1, 3])
                                            ->get(['id', 'turma']);

        return response()->json([
            'turma'           => $turma,
            'students'        => $students,
            'availableTurmas' => $availableTurmas,
        ]);
    }

    /**
     * Transfer a student to another class.
     */
    public function transferStudent(Request $request)
    {
        if (!$this->isCoordenador()) {
            return response()->json(['error' => 'Acesso negado.'], 403);
        }

        $request->validate([
            'student_cr_id' => 'required|exists:catecandos_pre_catequese,cr_id',
            'new_turma_id'  => 'required|exists:turmas_pre_catequese,id',
        ]);

        $catecando = CatecandoPreCatequese::findOrFail($request->student_cr_id);

        if ($catecando->turma && $catecando->turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newTurma = TurmaPreCatequese::findOrFail($request->new_turma_id);
        if ($newTurma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $catecando->update([
            'turma_id'      => $request->new_turma_id,
            'is_transfered' => true,
            'transfer_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Aluno transferido com sucesso!']);
    }

    /**
     * Export students of a single class.
     */
    public function exportStudents(Request $request, string $id)
    {
        if (!$this->isCoordenador()) {
            abort(403, 'Acesso negado.');
        }

        $turma = TurmaPreCatequese::with(['catecandos.register', 'catequista'])->findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $students = $turma->catecandos->map(function ($student) {
            return [
                'name'  => $student->register->name ?? 'Sem Nome',
                'phone' => $student->register->phone ?? 'Sem Telefone',
            ];
        })->sortBy('name');

        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView('pdf.turma-students', [
                'turma'      => $turma,
                'students'   => $students,
                'typeLabel'  => 'Catecandos(as)',
                'paroquia'   => Auth::user()->paroquia,
            ]);
            return $pdf->download('turma_pre_catequese_' . $id . '_catecandos.pdf');
        }

        if ($request->type === 'excel') {
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=turma_pre_catequese_{$id}_catecandos.csv",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0",
            ];
            $callback = function () use ($students) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, ['Nome', 'Telefone']);
                foreach ($students as $student) {
                    fputcsv($file, [$student['name'], $student['phone']]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        return redirect()->back()->with('error', 'Formato inválido.');
    }

    /**
     * Export multiple classes as ZIP.
     */
    public function exportBulk(Request $request)
    {
        if (!$this->isCoordenador()) {
            abort(403, 'Acesso negado.');
        }

        $ids     = explode(',', $request->ids);
        $type    = $request->type;
        $zipFile = tempnam(sys_get_temp_dir(), 'zip');
        $zip     = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== TRUE) {
            return response()->json(['error' => 'Não foi possível criar o arquivo ZIP'], 500);
        }

        $turmas = TurmaPreCatequese::whereIn('id', $ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['catecandos.register', 'catequista'])
            ->get();

        if ($turmas->isEmpty()) {
            return response()->json(['error' => 'Nenhuma turma encontrada'], 404);
        }

        foreach ($turmas as $turma) {
            $students = $turma->catecandos->map(function ($student) {
                return [
                    'name'  => $student->register->name ?? 'Sem Nome',
                    'phone' => $student->register->phone ?? 'Sem Telefone',
                ];
            })->sortBy('name');

            $filename = 'turma_' . $turma->id . '_' . \Illuminate\Support\Str::slug($turma->turma) . '.' . ($type === 'excel' ? 'csv' : 'pdf');

            if ($type === 'pdf') {
                $pdf = Pdf::loadView('pdf.turma-students', [
                    'turma'     => $turma,
                    'students'  => $students,
                    'typeLabel' => 'Catecandos(as)',
                    'paroquia'  => Auth::user()->paroquia,
                ]);
                $zip->addFromString($filename, $pdf->output());
            } else {
                $csv  = "\xEF\xBB\xBF";
                $csv .= "Nome,Telefone\n";
                foreach ($students as $student) {
                    $csv .= '"' . str_replace('"', '""', $student['name']) . '",';
                    $csv .= '"' . str_replace('"', '""', $student['phone']) . '"' . "\n";
                }
                $zip->addFromString($filename, $csv);
            }
        }

        $zip->close();

        return response()->download($zipFile, 'turmas_pre_catequese_' . date('Y-m-d_H-i') . '.zip')->deleteFileAfterSend(true);
    }

    /**
     * Get attendance for a given date.
     */
    public function getAttendance(Request $request, $id)
    {
        $turma = TurmaPreCatequese::findOrFail($id);
        $date  = $request->input('date', date('Y-m-d'));

        $students = CatecandoPreCatequese::where('turma_id', $id)
            ->with('register')
            ->get()
            ->map(function ($student) use ($id, $date) {
                $falta = FaltaPreCatequese::where('turma_id', $id)
                    ->where('aluno_id', $student->register_id)
                    ->where('data_aula', $date)
                    ->first();

                return [
                    'id'      => $student->register->id,
                    'name'    => $student->register->name,
                    'status'  => $falta ? (int)$falta->status : 0,
                    'title'   => $falta ? $falta->title : '',
                    'justify' => $falta ? $falta->justify : '',
                ];
            })
            ->sortBy('name')
            ->values();

        return response()->json([
            'turma'    => $turma->turma,
            'students' => $students,
        ]);
    }

    /**
     * Save individual attendance.
     */
    public function saveAttendance(Request $request)
    {
        $request->validate([
            'turma_id'  => 'required|exists:turmas_pre_catequese,id',
            'aluno_id'  => 'required|exists:registers,id',
            'data_aula' => 'required|date',
            'title'     => 'required|string',
            'status'    => 'required|boolean',
        ]);

        $falta = FaltaPreCatequese::updateOrCreate(
            [
                'turma_id'  => $request->turma_id,
                'aluno_id'  => $request->aluno_id,
                'data_aula' => $request->data_aula,
            ],
            [
                'title'  => $request->title,
                'status' => $request->status,
            ]
        );

        return response()->json(['success' => true, 'data' => $falta]);
    }

    /**
     * Save bulk attendance (full class).
     */
    public function saveBulkAttendance(Request $request)
    {
        $request->validate([
            'turma_id'              => 'required|exists:turmas_pre_catequese,id',
            'data_aula'             => 'required|date',
            'title'                 => 'required|string',
            'students'              => 'required|array',
            'students.*.aluno_id'   => 'required|exists:registers,id',
            'students.*.status'     => 'required|boolean',
        ]);

        foreach ($request->students as $studentData) {
            FaltaPreCatequese::updateOrCreate(
                [
                    'turma_id'  => $request->turma_id,
                    'aluno_id'  => $studentData['aluno_id'],
                    'data_aula' => $request->data_aula,
                ],
                [
                    'title'  => $request->title,
                    'status' => $studentData['status'],
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    /**
     * Attendance analysis view.
     */
    public function attendanceAnalysis(Request $request, $id)
    {
        $turma = TurmaPreCatequese::with(['catequista'])->findOrFail($id);

        if (Auth::user()->paroquia_id && $turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $query = $turma->catecandos()->with('register');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('register', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $students = $query->get()->map(function ($catecando) use ($turma) {
            $presencas = FaltaPreCatequese::where('turma_id', $turma->id)
                                          ->where('aluno_id', $catecando->register_id)
                                          ->where('status', 1)
                                          ->count();

            $faltas = FaltaPreCatequese::where('turma_id', $turma->id)
                                       ->where('aluno_id', $catecando->register_id)
                                       ->where('status', 0)
                                       ->count();

            $presencasList = FaltaPreCatequese::where('turma_id', $turma->id)
                                              ->where('aluno_id', $catecando->register_id)
                                              ->where('status', 1)
                                              ->orderBy('data_aula', 'desc')
                                              ->get(['data_aula', 'title']);

            $faltasList = FaltaPreCatequese::where('turma_id', $turma->id)
                                           ->where('aluno_id', $catecando->register_id)
                                           ->where('status', 0)
                                           ->orderBy('data_aula', 'desc')
                                           ->get(['data_aula', 'title']);

            return [
                'id'            => $catecando->register_id,
                'name'          => $catecando->register->name ?? 'Sem Nome',
                'presencas'     => $presencas,
                'faltas'        => $faltas,
                'presencas_list' => $presencasList,
                'faltas_list'   => $faltasList,
            ];
        });

        if ($request->has('filter_attendance') && $request->filter_attendance != '') {
            if ($request->filter_attendance == 'has_faults') {
                $students = $students->filter(fn($s) => $s['faltas'] > 0);
            } elseif ($request->filter_attendance == 'has_presences') {
                $students = $students->filter(fn($s) => $s['presencas'] > 0);
            }
        }

        $isCoordenador = $this->isCoordenador();

        return view('modules.turmas-pre-catequese.attendance-analysis', compact('turma', 'students', 'isCoordenador'));
    }

    /**
     * Attendance history for a specific student.
     */
    public function attendanceHistory(Request $request, $id, $student_id)
    {
        $turma   = TurmaPreCatequese::findOrFail($id);
        $student = Register::findOrFail($student_id);

        if (Auth::user()->paroquia_id && $turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $query = FaltaPreCatequese::where('turma_id', $id)->where('aluno_id', $student_id);

        if ($request->filled('status')) {
            if ($request->status == 'present') {
                $query->where('status', 1);
            } elseif ($request->status == 'absent') {
                $query->where('status', 0);
            }
        }

        if ($request->filled('start_date')) {
            $query->whereDate('data_aula', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('data_aula', '<=', $request->end_date);
        }

        $history = $query->orderBy('data_aula', 'desc')->get();

        $isCoordenador = $this->isCoordenador();

        return view('modules.turmas-pre-catequese.attendance-history', compact('turma', 'student', 'history', 'isCoordenador'));
    }

    /**
     * Store justification for an absence.
     */
    public function storeJustification(Request $request)
    {
        $request->validate([
            'falta_id' => 'required|exists:faltas_pre_catequese,id',
            'justify'  => 'required|string|max:255',
        ]);

        $falta = FaltaPreCatequese::findOrFail($request->falta_id);

        $turma = TurmaPreCatequese::findOrFail($falta->turma_id);
        if (Auth::user()->paroquia_id && $turma->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $falta->update(['justify' => $request->justify]);

        return back()->with('success', 'Justificativa salva com sucesso!');
    }

    /**
     * Search registers and inscricoes for adding students.
     */
    public function searchRegisters(Request $request)
    {
        $search     = $request->search ?? '';
        $paroquiaId = Auth::user()->paroquia_id;

        // Search in inscricoes_pre_catequese first
        $inscricoes = InscricaoPreCatequese::where('paroquia_id', $paroquiaId)
            ->where('nome', 'like', "%{$search}%")
            ->whereNotNull('nome')
            ->take(10)
            ->get(['id as inscricao_id', 'nome as name', 'telefone1 as phone', 'batismo']);

        // Search in registers
        $registers = Register::where('paroquia_id', $paroquiaId)
            ->where('name', 'like', "%{$search}%")
            ->take(10)
            ->get(['id', 'name', 'phone']);

        // Merge results, marking source
        $results = [];

        foreach ($inscricoes as $i) {
            // Try to find matching register
            $reg = Register::where('paroquia_id', $paroquiaId)
                           ->where('name', 'like', $i->name)
                           ->first();

            $results[] = [
                'id'          => $reg?->id ?? null,
                'inscricao_id' => $i->inscricao_id,
                'name'        => $i->name,
                'phone'       => $i->phone,
                'batismo'     => $i->batismo ? 1 : 0,
                'source'      => 'inscricao',
            ];
        }

        foreach ($registers as $r) {
            // Avoid duplicates already added from inscricoes
            $alreadyAdded = collect($results)->firstWhere('id', $r->id);
            if (!$alreadyAdded) {
                $results[] = [
                    'id'          => $r->id,
                    'inscricao_id' => null,
                    'name'        => $r->name,
                    'phone'       => $r->phone,
                    'batismo'     => 0, // Register não tem coluna de batismo fácil, padrão 0
                    'source'      => 'register',
                ];
            }
        }

        return response()->json(array_slice($results, 0, 15));
    }
}
