<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccessControlController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = User::where('paroquia_id', Auth::user()->paroquia_id);

        // Filters
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('user', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && !empty($request->role)) {
            $query->whereRaw("FIND_IN_SET(?, rule)", [$request->role]);
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Sorting
        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['id', 'name', 'user', 'email', 'status'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id';
        }
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $users = $query->orderBy($sort, $direction)->paginate(10);

        // Stats
        $baseQuery = User::where('paroquia_id', Auth::user()->paroquia_id);
        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('status', 0)->count(),
            'inactive' => (clone $baseQuery)->where('status', 1)->count(),
        ];

        $roles = User::ROLE_LABELS;

        return view('modules.access-control.index', compact('users', 'stats', 'roles'));
    }

    public function create()
    {
        if (!Auth::user()->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $roles = User::ROLE_LABELS;
        return view('modules.access-control.create', compact('roles'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'user' => 'required|string|max:100|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'roles' => 'nullable|array',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->user = $request->user;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->paroquia_id = Auth::user()->paroquia_id;
        $user->status = 0; // Ativo
        $user->rule = $request->roles ? implode(',', $request->roles) : null;
        $user->is_visible = 1;
        $user->hide_name = 0;
        $user->is_pass_change = 0;
        $user->login_attempts = 0;
        $user->save();

        return redirect()->route('access-control.index')->with('success', 'Usuário criado com sucesso!');
    }

    public function edit($id)
    {
        if (!Auth::user()->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $user = User::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        if ($user->id === Auth::id() && $user->hasRole('1')) {
             return redirect()->route('access-control.index')->with('error', 'Você não pode editar seu próprio usuário.');
        }

        $roles = User::ROLE_LABELS;
        return view('modules.access-control.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $user = User::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        if ($user->id === Auth::id() && $user->hasRole('1')) {
             return redirect()->route('access-control.index')->with('error', 'Você não pode editar seu próprio usuário.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'user' => ['required', 'string', 'max:100', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'roles' => 'nullable|array',
            'status' => 'required|in:0,1',
        ]);

        $user->name = $request->name;
        $user->user = $request->user;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->rule = $request->roles ? implode(',', $request->roles) : null;
        $user->status = $request->status;
        $user->save();

        return redirect()->route('access-control.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy($id)
    {
        if (!Auth::user()->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $user = User::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        try {
            $user->delete();
            return redirect()->route('access-control.index')->with('success', 'Usuário excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Não foi possível excluir o usuário. Ele pode ter registros associados.');
        }
    }
}
