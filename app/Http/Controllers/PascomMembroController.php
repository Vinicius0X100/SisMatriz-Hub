<?php

namespace App\Http\Controllers;

use App\Models\PascomMembro;
use App\Models\Entidade;
use App\Models\Register;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PascomMembroController extends Controller
{
    private function ensureAccess(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['1', '111', '9', '10'])) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureAccess();

        if ($request->ajax() || $request->wantsJson()) {
            $query = PascomMembro::with(['entidade', 'register'])
                ->where('paroquia_id', Auth::user()->paroquia_id);

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('ent_id')) {
                $query->where('ent_id', $request->get('ent_id'));
            }

            if ($request->filled('type') && $request->get('type') !== '') {
                $query->where('type', (int)$request->get('type'));
            }

            if ($request->filled('status') && $request->get('status') !== '') {
                $query->where('status', (int)$request->get('status'));
            }

            $sortBy = $request->get('sort_by', 'id');
            $sortDir = $request->get('sort_dir', 'desc');
            $allowedSorts = ['id', 'name', 'age', 'year_member', 'status'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'id';
            }
            $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';

            $records = $query->orderBy($sortBy, $sortDir)->paginate(10);

            $records->getCollection()->transform(function ($item) {
                $registerExists = $item->register !== null;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'entidade' => $item->entidade?->ent_name,
                    'type' => (int)$item->type,
                    'age' => $item->age,
                    'year_member' => $item->year_member,
                    'status' => (int)$item->status,
                    'register_exists' => $registerExists,
                    'register_id' => $item->register_id,
                ];
            });

            return response()->json($records);
        }

        $paroquiaId = Auth::user()->paroquia_id;
        $entidades = Entidade::where('paroquia_id', $paroquiaId)
            ->orderBy('ent_name')->get();

        $total = PascomMembro::where('paroquia_id', $paroquiaId)->count();
        $avgAge = PascomMembro::where('paroquia_id', $paroquiaId)->whereNotNull('age')->avg('age');
        $active = PascomMembro::where('paroquia_id', $paroquiaId)->where('status', 0)->count();
        $inactive = PascomMembro::where('paroquia_id', $paroquiaId)->where('status', 1)->count();

        $stats = [
            'total' => $total,
            'avg_age' => $avgAge ? round($avgAge, 1) : 0,
            'active' => $active,
            'inactive' => $inactive,
        ];

        return view('modules.pascom-membros.index', compact('entidades', 'stats'));
    }

    public function create()
    {
        $this->ensureAccess();
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('ent_name')->get();
        return view('modules.pascom-membros.create', compact('entidades'));
    }

    public function store(Request $request)
    {
        $this->ensureAccess();
        $request->validate([
            'register_id' => 'required|exists:registers,id',
            'name' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'type' => 'required|in:0,1,2,3,4,5',
            'age' => 'nullable|integer|min:0|max:120',
            'year_member' => 'nullable|integer|min:1900|max:2100',
            'status' => 'required|in:0,1',
        ]);

        PascomMembro::create([
            'register_id' => $request->register_id,
            'name' => $request->name,
            'ent_id' => $request->ent_id,
            'type' => (int)$request->type,
            'age' => $request->age,
            'year_member' => $request->year_member ?? date('Y'),
            'status' => (int)$request->status,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return redirect()->route('pascom-membros.index')->with('success', 'Membro adicionado com sucesso!');
    }

    public function edit($id)
    {
        $this->ensureAccess();
        $record = PascomMembro::with('register')->where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('ent_name')->get();
        $registerExists = $record->register !== null;
        $registerName = $registerExists ? $record->register->name : $record->name;
        return view('modules.pascom-membros.edit', compact('record', 'entidades', 'registerExists', 'registerName'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureAccess();
        $record = PascomMembro::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        $registerExists = Register::where('id', $record->register_id)->exists();
        $registerRule = $registerExists ? 'required|exists:registers,id' : 'required|in:' . $record->register_id;

        $request->validate([
            'register_id' => $registerRule,
            'name' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'type' => 'required|in:0,1,2,3,4,5',
            'age' => 'nullable|integer|min:0|max:120',
            'year_member' => 'nullable|integer|min:1900|max:2100',
            'status' => 'required|in:0,1',
        ]);

        $record->update([
            'register_id' => $request->register_id,
            'name' => $request->name,
            'ent_id' => $request->ent_id,
            'type' => (int)$request->type,
            'age' => $request->age,
            'year_member' => $request->year_member ?? date('Y'),
            'status' => (int)$request->status,
        ]);

        return redirect()->route('pascom-membros.index')->with('success', 'Membro atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $this->ensureAccess();
        $record = PascomMembro::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $record->delete();
        return redirect()->route('pascom-membros.index')->with('success', 'Membro removido com sucesso!');
    }

    public function bulkDelete(Request $request)
    {
        $this->ensureAccess();
        $ids = $request->input('selected_ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        } elseif (is_array($ids)) {
            $ids = array_filter(array_map('intval', $ids));
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Nenhum item selecionado.'], 422);
            }
            return redirect()->back()->with('error', 'Nenhum item selecionado.');
        }

        $deleted = PascomMembro::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $ids)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'deleted' => $deleted]);
        }
        return redirect()->route('pascom-membros.index')->with('success', 'Itens excluídos com sucesso!');
    }

    public function generatePdf(Request $request)
    {
        $this->ensureAccess();
        $user = Auth::user();

        $query = PascomMembro::with(['entidade', 'register'])
            ->where('paroquia_id', $user->paroquia_id);

        if ($request->has('selected_ids') && !empty($request->selected_ids)) {
            $ids = is_array($request->selected_ids) ? $request->selected_ids : explode(',', (string) $request->selected_ids);
            $ids = array_filter(array_map('intval', $ids));
            $query->whereIn('id', $ids);
        }

        $order = (string) $request->input('order', 'name_asc');
        $allowedOrders = [
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'year_member_desc' => ['year_member', 'desc'],
            'year_member_asc' => ['year_member', 'asc'],
            'age_desc' => ['age', 'desc'],
            'age_asc' => ['age', 'asc'],
            'status_asc' => ['status', 'asc'],
            'status_desc' => ['status', 'desc'],
        ];
        [$orderBy, $orderDir] = $allowedOrders[$order] ?? $allowedOrders['name_asc'];
        $query->orderBy($orderBy, $orderDir)->orderBy('id', 'asc');

        $records = $query->get();
        $columns = $request->get('columns', ['name', 'type', 'entidade', 'age', 'year_member', 'status']);
        if (!is_array($columns)) {
            $columns = [];
        }
        $allowedColumns = ['name', 'type', 'entidade', 'age', 'year_member', 'status', 'register_status'];
        $columns = array_values(array_intersect($allowedColumns, $columns));
        $paroquia = $user->paroquia;

        $pdf = PDF::loadView('modules.pascom-membros.pdf', compact('records', 'columns', 'paroquia'));
        return $pdf->download('relatorio_pascom_membros_' . date('YmdHis') . '.pdf');
    }

    public function searchRegisters(Request $request)
    {
        $this->ensureAccess();
        $term = $request->get('q');
        if (!$term || mb_strlen($term) < 2) {
            return response()->json([]);
        }
        $results = Register::where('paroquia_id', Auth::user()->paroquia_id)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('cpf', 'like', "%{$term}%");
            })
            ->select(['id', 'name', 'born_date'])
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(function ($r) {
                $age = null;
                if ($r->born_date) {
                    try {
                        $age = \Carbon\Carbon::parse($r->born_date)->age;
                    } catch (\Exception $e) {
                        $age = null;
                    }
                }
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'age' => $age,
                ];
            });
        return response()->json($results);
    }
}
