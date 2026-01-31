<?php

namespace App\Http\Controllers;

use App\Models\Acolito;
use App\Models\Entidade;
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
                    'rule' => $user->rule
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
}
