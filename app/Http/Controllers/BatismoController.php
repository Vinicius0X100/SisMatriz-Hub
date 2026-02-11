<?php

namespace App\Http\Controllers;

use App\Models\Batismo;
use App\Models\Register;
use App\Models\Catecando;
use App\Models\Crismando;
use App\Models\CatecandoAdultos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BatismoController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $paroquiaId = $user->paroquia_id;

        $query = Batismo::with('register')
            ->where('paroquia_id', $paroquiaId);

        // Filtro por Nome (via relacionamento)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('register', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Filtro por Status
        if ($request->has('status') && $request->status != '') {
            if ($request->status == '1') {
                $query->where('is_batizado', true);
            } elseif ($request->status == '0') {
                $query->where('is_batizado', false);
            }
        }

        $batismos = $query->paginate(15);

        if ($request->ajax()) {
            return view('modules.batismos.partials.list', compact('batismos'))->render();
        }

        // Stats para os cards
        $stats = [
            'total' => Batismo::where('paroquia_id', $paroquiaId)->count(),
            'batizados' => Batismo::where('paroquia_id', $paroquiaId)->where('is_batizado', true)->count(),
            'nao_batizados' => Batismo::where('paroquia_id', $paroquiaId)->where('is_batizado', false)->count(),
        ];

        return view('modules.batismos.index', compact('batismos', 'stats'));
    }

    public function create()
    {
        // Retorna view para buscar pessoa e criar registro
        return view('modules.batismos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'register_id' => 'required|exists:registers,id',
        ]);

        // Verificar se já existe
        $existing = Batismo::where('register_id', $request->register_id)->first();
        if ($existing) {
            return redirect()->route('batismos.edit', $existing->id)->with('info', 'Esta pessoa já possui um registro de batismo. Você foi redirecionado para a edição.');
        }

        $batismo = Batismo::create([
            'register_id' => $request->register_id,
            'paroquia_id' => Auth::user()->paroquia_id,
            'is_batizado' => false, // Default
        ]);

        return redirect()->route('batismos.edit', $batismo->id)->with('success', 'Registro de batismo iniciado. Preencha os detalhes.');
    }

    public function edit($id)
    {
        $batismo = Batismo::with('register')->findOrFail($id);
        
        if (Auth::user()->paroquia_id && $batismo->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        return view('modules.batismos.edit', compact('batismo'));
    }

    public function update(Request $request, $id)
    {
        $batismo = Batismo::findOrFail($id);
        
        if (Auth::user()->paroquia_id && $batismo->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $validated = $request->validate([
            'is_batizado' => 'required|boolean',
            'data_batismo' => 'nullable|date',
            'local_batismo' => 'nullable|string|max:255',
            'celebrante' => 'nullable|string|max:255',
            'padrinho_nome' => 'nullable|string|max:255',
            'madrinha_nome' => 'nullable|string|max:255',
            'livro' => 'nullable|string|max:50',
            'folha' => 'nullable|string|max:50',
            'registro' => 'nullable|string|max:50',
            'obs' => 'nullable|string',
        ]);

        $batismo->update($validated);

        // Sincronização com Turmas
        // Se mudou o status de batizado, atualiza nas tabelas de turma onde essa pessoa estiver
        $this->syncBatismoStatus($batismo->register_id, $validated['is_batizado']);

        return redirect()->route('batismos.index')->with('success', 'Registro de batismo atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $batismo = Batismo::findOrFail($id);
        
        if (Auth::user()->paroquia_id && $batismo->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        // Before deleting, maybe we should check if it affects turmas? 
        // For now, we just delete the baptism record. The Turma record (Catecando) has its own 'batizado' boolean which might become out of sync if we just delete this.
        // However, if we delete the baptism record, it effectively means we don't have a record of it. 
        // Should we set 'batizado' to false in turmas?
        // Let's assume if you delete the baptism record, you are removing the *confirmation* of baptism.
        // So we should sync 'false' to turmas.
        
        $this->syncBatismoStatus($batismo->register_id, false);

        $batismo->delete();

        return redirect()->route('batismos.index')->with('success', 'Registro de batismo removido com sucesso!');
    }

    /**
     * Sincroniza o status de batismo com as tabelas de turmas (Catequese, Crisma, Adultos)
     */
    protected function syncBatismoStatus($registerId, $isBatizado)
    {
        // Catequese Eucaristia
        Catecando::where('register_id', $registerId)->update(['batizado' => $isBatizado]);

        // Crisma
        Crismando::where('register_id', $registerId)->update(['batizado' => $isBatizado]);

        // Catequese Adultos
        CatecandoAdultos::where('register_id', $registerId)->update(['batizado' => $isBatizado]);
    }
}
