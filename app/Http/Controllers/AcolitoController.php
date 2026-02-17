<?php

namespace App\Http\Controllers;

use App\Models\Acolito;
use App\Models\AcolitoFalta;
use App\Models\AcolitoFaltaJustify;
use App\Models\Entidade;
use App\Models\Escala;
use App\Models\EscalaDataHora;
use App\Models\EscaladoData;
use App\Models\Register;
use App\Models\User;
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
                    'user_id' => $acolito->user_id, // Added user_id for frontend logic
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
        if ($request->boolean('select_all')) {
            $query = Acolito::where('paroquia_id', Auth::user()->paroquia_id);

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

            $count = $query->delete();
            return response()->json(['message' => "$count registros excluídos com sucesso."]);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:acolitos,id'
        ]);

        $count = Acolito::whereIn('id', $request->ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->delete();

        return response()->json(['message' => "$count registros excluídos com sucesso."]);
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
            'user_id' => 'nullable|exists:users,id',
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
            'user_id' => $request->user_id,
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
            'user_id' => 'nullable|exists:users,id',
        ]);

        $register = Register::find($request->register_id);

        $data = [
            'name' => $register->name,
            'ent_id' => $request->ent_id,
            'type' => $request->type,
            'register_id' => $request->register_id,
            'age' => $register->age,
            'graduation_year' => $request->graduation_year,
            'status' => $request->status,
        ];

        // Update user_id if provided (even if 0/null to unlink if logic dictates, but typically we only link here)
        // If user_id is passed, update it. If not passed, keep existing? 
        // User request implies we only add link. But if editing, we might want to preserve or update.
        // Let's assume if it's in request, we update it.
        if ($request->has('user_id')) {
            $data['user_id'] = $request->user_id;
        }

        $acolito->update($data);

        return redirect()->route('acolitos.index')->with('success', 'Cadastro atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $acolito = Acolito::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();
        $acolito->delete();

        return redirect()->route('acolitos.index')->with('success', 'Removido com sucesso!');
    }

    public function checkUser(Request $request)
    {
        $name = $request->input('name');
        
        $user = User::where('name', $name)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->first();

        if ($user) {
            return response()->json([
                'found' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user' => $user->user,
                    'rule' => $user->role_label // Use label instead of raw ID
                ]
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function linkUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Verifica se o usuário alvo pertence à mesma paróquia
        $targetUser = User::where('id', $request->user_id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->first();

        if (!$targetUser) {
            return response()->json(['success' => false, 'message' => 'Usuário inválido ou de outra paróquia.'], 403);
        }

        $acolito = Acolito::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();
        
        $acolito->update([
            'user_id' => $request->user_id
        ]);

        return response()->json(['success' => true, 'message' => 'Usuário vinculado com sucesso!']);
    }

    public function checkBulkUserMatches(Request $request)
    {
        try {
            $query = Acolito::where('paroquia_id', Auth::user()->paroquia_id)
                ->with('register')
                ->doesntHave('user');

            if ($request->boolean('select_all')) {
                if ($request->filled('search')) {
                    $search = $request->input('search');
                    $query->whereHas('register', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('ent_id')) $query->where('ent_id', $request->input('ent_id'));
                if ($request->filled('type')) $query->where('type', $request->input('type'));
                if ($request->filled('status')) $query->where('status', $request->input('status'));
            } else {
                $request->validate(['ids' => 'required|array']);
                $query->whereIn('id', $request->ids);
            }

            $acolitos = $query->get();
            $matches = [];

            foreach ($acolitos as $acolito) {
                // Se o acolito não tem nome definido no próprio registro, tenta pegar do relacionamento
                $acolitoName = $acolito->name ?? $acolito->register?->name;
                
                if (empty($acolitoName)) continue;

                $user = User::where('paroquia_id', Auth::user()->paroquia_id)
                    ->where('name', $acolitoName)
                    ->first();

                if ($user) {
                    $name = trim($acolitoName);
                    // Confidence check: needs to have space (indicating > 1 word) and length > 3
                    $isReliable = strpos($name, ' ') !== false && strlen($name) > 3;
                    
                    $matches[] = [
                        'acolito_id' => $acolito->id,
                        'acolito_name' => $name,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'user_role' => $user->role_label,
                        'confidence' => $isReliable ? 'high' : 'low'
                    ];
                }
            }

            return response()->json(['matches' => $matches]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function bulkLinkUsers(Request $request)
    {
        try {
            $request->validate([
                'links' => 'required|array',
                'links.*.acolito_id' => 'required|exists:acolitos,id',
                'links.*.user_id' => 'required|exists:users,id',
            ]);

            $count = 0;
            // Pre-fetch valid user IDs for this paroquia to avoid N+1 queries
            $validUserIds = User::where('paroquia_id', Auth::user()->paroquia_id)
                ->whereIn('id', array_column($request->links, 'user_id'))
                ->pluck('id')
                ->toArray();

            foreach ($request->links as $link) {
                // Skip if user_id is not in our valid list (belongs to another paroquia or doesn't exist)
                if (!in_array($link['user_id'], $validUserIds)) {
                    continue;
                }

                $acolito = Acolito::where('id', $link['acolito_id'])
                    ->where('paroquia_id', Auth::user()->paroquia_id)
                    ->first();
                
                // Only update if not currently linked to a valid user
                if ($acolito && !$acolito->user()->exists()) {
                    $acolito->update(['user_id' => $link['user_id']]);
                    $count++;
                }
            }

            return response()->json(['message' => "$count usuários vinculados com sucesso!"]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao vincular usuários: ' . $e->getMessage()], 400);
        }
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
    
    public function chamada(Request $request)
    {
        if (Auth::user()->rule == 8) {
            abort(403);
        }

        $escalas = Escala::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $selectedEscalaId = $request->input('escala_id');
        $selectedDId = $request->input('d_id');

        $datas = collect();
        $celebration = null;
        $escalados = collect();
        $existingByAcolito = collect();
        $dateStr = null;

        if ($selectedEscalaId) {
            $datas = EscalaDataHora::where('es_id', $selectedEscalaId)
                ->with('entidade')
                ->orderBy('data')
                ->orderBy('hora')
                ->get();
        }

        if ($selectedDId) {
            $celebration = EscalaDataHora::where('d_id', $selectedDId)
                ->whereHas('escala', function ($q) {
                    $q->where('paroquia_id', Auth::user()->paroquia_id);
                })
                ->with(['entidade', 'escala'])
                ->firstOrFail();

            $escalados = EscaladoData::where('d_id', $selectedDId)
                ->with(['acolito', 'funcao'])
                ->get();

            $monthsMap = [
                'janeiro' => 1,'fevereiro' => 2,'março' => 3,'marco' => 3,'abril' => 4,'maio' => 5,'junho' => 6,
                'julho' => 7,'agosto' => 8,'setembro' => 9,'outubro' => 10,'novembro' => 11,'dezembro' => 12
            ];
            $monthName = mb_strtolower($celebration->escala->month, 'UTF-8');
            $monthNum = $monthsMap[$monthName] ?? date('n');
            $dateStr = sprintf('%04d-%02d-%02d', (int)$celebration->escala->year, (int)$monthNum, (int)$celebration->data);

            $existingByAcolito = AcolitoFalta::with('justificativa')
                ->where('paroquia_id', Auth::user()->paroquia_id)
                ->where('d_id', $selectedDId)
                ->whereDate('data_aula', $dateStr)
                ->get()
                ->keyBy('acolito_id');
        }

        return view('modules.acolitos.chamada', compact(
            'escalas',
            'datas',
            'celebration',
            'escalados',
            'selectedEscalaId',
            'selectedDId',
            'existingByAcolito',
            'dateStr'
        ));
    }
    
    public function attendanceHistory(Request $request, $id)
    {
        if (Auth::user()->rule == 8) {
            abort(403);
        }

        $acolito = Acolito::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();
        
        $query = AcolitoFalta::with(['justificativa', 'escalaDataHora'])
                              ->where('acolito_id', $id)
                              ->where('paroquia_id', Auth::user()->paroquia_id);
        
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
        
        return view('modules.acolitos.attendance-history', compact('acolito', 'history'));
    }
    
    public function storeAttendance(Request $request)
    {
        if (Auth::user()->rule == 8) {
            abort(403);
        }

        if ($request->has('registros')) {
            $request->validate([
                'd_id' => 'nullable|integer',
                'data' => 'required|date',
                'registros' => 'required|array',
                'registros.*.acolito_id' => 'required|exists:acolitos,id',
                'registros.*.status' => 'required|in:present,absent',
                'registros.*.justify_type' => 'nullable|in:sem,com',
                'registros.*.motivo' => 'nullable|string|max:255',
            ]);

            foreach ($request->registros as $registro) {
                $acolito = Acolito::where('id', $registro['acolito_id'])
                    ->where('paroquia_id', Auth::user()->paroquia_id)
                    ->first();

                if (!$acolito) {
                    continue;
                }

                $record = AcolitoFalta::updateOrCreate(
                    [
                        'acolito_id' => $acolito->id,
                        'paroquia_id' => Auth::user()->paroquia_id,
                        'data_aula' => $request->data,
                        'd_id' => $request->input('d_id'),
                    ],
                    [
                        'title' => $registro['status'] === 'present' ? 'Presença' : 'Falta',
                        'status' => $registro['status'] === 'present' ? 1 : 0,
                        'grave' => $registro['status'] === 'absent' && ($registro['justify_type'] ?? 'sem') === 'sem',
                    ]
                );

                if ($registro['status'] === 'absent' && ($registro['justify_type'] ?? null) === 'com' && !empty($registro['motivo'])) {
                    AcolitoFaltaJustify::updateOrCreate(
                        ['faltas_id' => $record->id],
                        ['motivo' => $registro['motivo']]
                    );
                } else {
                    AcolitoFaltaJustify::where('faltas_id', $record->id)->delete();
                }
            }

            return back()->with('success', 'Chamada salva com sucesso!');
        }

        $request->validate([
            'acolito_id' => 'required|exists:acolitos,id',
            'data' => 'required|date',
            'status' => 'required|in:present,absent',
            'justify_type' => 'nullable|in:sem,com',
            'motivo' => 'nullable|string|max:255',
        ]);

        $acolito = Acolito::where('id', $request->acolito_id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $record = AcolitoFalta::create([
            'acolito_id' => $acolito->id,
            'paroquia_id' => Auth::user()->paroquia_id,
            'title' => $request->status === 'present' ? 'Presença' : 'Falta',
            'data_aula' => $request->data,
            'status' => $request->status === 'present' ? 1 : 0,
            'grave' => $request->status === 'absent' && $request->justify_type === 'sem',
            'd_id' => $request->input('d_id'),
        ]);

        if ($request->status === 'absent' && $request->justify_type === 'com' && $request->filled('motivo')) {
            AcolitoFaltaJustify::updateOrCreate(
                ['faltas_id' => $record->id],
                ['motivo' => $request->motivo]
            );
        }

        return back()->with('success', 'Registro salvo com sucesso!');
    }
    
    public function storeJustification(Request $request)
    {
        if (Auth::user()->rule == 8) {
            abort(403);
        }

        $request->validate([
            'falta_id' => 'required|exists:faltas_acolitos,id',
            'motivo' => 'required|string|max:255',
        ]);
        
        $falta = AcolitoFalta::findOrFail($request->falta_id);
        $acolito = Acolito::findOrFail($falta->acolito_id);
        
        if (Auth::user()->paroquia_id && $acolito->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }
        
        AcolitoFaltaJustify::updateOrCreate(
            ['faltas_id' => $request->falta_id],
            ['motivo' => $request->motivo]
        );
        
        return back()->with('success', 'Justificativa salva com sucesso!');
    }
}
