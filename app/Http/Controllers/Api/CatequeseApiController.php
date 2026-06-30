<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Register;

// Models Eucaristia
use App\Models\TurmaEucaristia;
use App\Models\Catecando;
use App\Models\FaltaCatequese;

// Models Crisma
use App\Models\TurmaCrisma;
use App\Models\Crismando;
use App\Models\FaltaCrisma;

// Models Adultos
use App\Models\TurmaAdultos;
use App\Models\CatecandoAdultos;
use App\Models\FaltaAdultos;

class CatequeseApiController extends Controller
{
    /**
     * Resolve os models baseados no tipo de turma solicitado pelo App
     */
    private function resolveModels($tipo)
    {
        switch ($tipo) {
            case 'eucaristia':
                return [
                    'turma' => TurmaEucaristia::class,
                    'aluno' => Catecando::class,
                    'falta' => FaltaCatequese::class,
                ];
            case 'crisma':
                return [
                    'turma' => TurmaCrisma::class,
                    'aluno' => Crismando::class,
                    'falta' => FaltaCrisma::class,
                ];
            case 'adultos':
                return [
                    'turma' => TurmaAdultos::class,
                    'aluno' => CatecandoAdultos::class,
                    'falta' => FaltaAdultos::class,
                ];
            default:
                abort(404, 'Tipo inválido.');
        }
    }

    /**
     * Retorna a lista de turmas ativas do tipo especificado.
     */
    public function getTurmas(Request $request, $tipo)
    {
        $models = $this->resolveModels($tipo);
        $turmaClass = $models['turma'];

        $query = $turmaClass::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('status', [1, 3]) // Apenas turmas ativas
            ->orderBy('id', 'desc');

        $turmas = $query->get(['id', 'turma', 'status', 'inicio', 'termino']);

        return response()->json($turmas);
    }

    /**
     * Retorna a lista de alunos da turma e suas respectivas presenças/faltas para uma data.
     */
    public function getAttendance(Request $request, $tipo, $id)
    {
        $models = $this->resolveModels($tipo);
        $turmaClass = $models['turma'];
        $alunoClass = $models['aluno'];
        $faltaClass = $models['falta'];

        $turma = $turmaClass::findOrFail($id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $date = $request->input('date', date('Y-m-d'));
        
        $students = $alunoClass::where('turma_id', $id)
            ->with('register')
            ->get()
            ->map(function ($student) use ($id, $date, $faltaClass) {
                $falta = $faltaClass::where('turma_id', $id)
                    ->where('aluno_id', $student->register_id)
                    ->where('data_aula', $date)
                    ->first();
                
                return [
                    'id' => $student->register->id ?? null,
                    'name' => $student->register->name ?? 'Sem Nome',
                    'status' => $falta ? $falta->status : 0,
                    'title' => $falta ? $falta->title : '',
                ];
            })
            // Remover caso o aluno tenha tido o cadastro excluido
            ->filter(function($s) { return !is_null($s['id']); })
            ->sortBy('name')
            ->values();

        return response()->json([
            'turma_id' => $turma->id,
            'turma' => $turma->turma,
            'date' => $date,
            'students' => $students
        ]);
    }

    /**
     * Salva a presença ou falta de um aluno individualmente
     */
    public function saveAttendance(Request $request, $tipo)
    {
        $models = $this->resolveModels($tipo);
        $faltaClass = $models['falta'];
        $turmaClass = $models['turma'];

        $request->validate([
            'turma_id' => 'required|integer',
            'aluno_id' => 'required|integer',
            'data_aula' => 'required|date',
            'title' => 'required|string',
            'status' => 'required|boolean',
        ]);

        $turma = $turmaClass::findOrFail($request->turma_id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $falta = $faltaClass::updateOrCreate(
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

    /**
     * Salva as presenças ou faltas da turma em massa
     */
    public function saveBulkAttendance(Request $request, $tipo)
    {
        $models = $this->resolveModels($tipo);
        $faltaClass = $models['falta'];
        $turmaClass = $models['turma'];

        $request->validate([
            'turma_id' => 'required|integer',
            'data_aula' => 'required|date',
            'title' => 'required|string',
            'students' => 'required|array',
            'students.*.aluno_id' => 'required|integer',
            'students.*.status' => 'required|boolean',
        ]);

        $turma = $turmaClass::findOrFail($request->turma_id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        foreach ($request->students as $studentData) {
            $faltaClass::updateOrCreate(
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

    /**
     * Retorna o histórico completo de presenças e faltas de um aluno em uma turma.
     * Suporta filtros opcionais: ?status=present|absent, ?start_date=YYYY-MM-DD, ?end_date=YYYY-MM-DD
     */
    public function getAttendanceHistory(Request $request, $tipo, $turma_id, $student_id)
    {
        $models = $this->resolveModels($tipo);
        $turmaClass = $models['turma'];
        $faltaClass = $models['falta'];

        $turma = $turmaClass::findOrFail($turma_id);

        if ($turma->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = Register::findOrFail($student_id);

        $query = $faltaClass::where('turma_id', $turma_id)
                            ->where('aluno_id', $student_id);

        // Filtro por status: ?status=present ou ?status=absent
        if ($request->filled('status')) {
            if ($request->status === 'present') {
                $query->where('status', 1);
            } elseif ($request->status === 'absent') {
                $query->where('status', 0);
            }
        }

        // Filtro por intervalo de datas
        if ($request->filled('start_date')) {
            $query->whereDate('data_aula', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('data_aula', '<=', $request->end_date);
        }

        $history = $query->orderBy('data_aula', 'desc')
                         ->get(['id', 'data_aula', 'title', 'status']);

        $presencas = $history->where('status', 1)->count();
        $faltas    = $history->where('status', 0)->count();

        return response()->json([
            'turma_id'  => $turma->id,
            'turma'     => $turma->turma,
            'student_id'=> $student->id,
            'student'   => $student->name,
            'presencas' => $presencas,
            'faltas'    => $faltas,
            'history'   => $history->values(),
        ]);
    }
}
