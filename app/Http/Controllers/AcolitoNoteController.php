<?php

namespace App\Http\Controllers;

use App\Models\Acolito;
use App\Models\AcolitoNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Register;

class AcolitoNoteController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $query = AcolitoNote::query()
                ->whereHas('acolito', function ($q) {
                    $q->where('paroquia_id', Auth::user()->paroquia_id);
                })
                ->with('acolito');

            // Filter logic for notes
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            if ($dateFrom) {
                $query->whereRaw("STR_TO_DATE(send_at, '%d/%m/%Y %H:%i') >= ?", [implode('-', array_reverse(explode('/', $dateFrom))) . ' 00:00:00']);
            }
            if ($dateTo) {
                $query->whereRaw("STR_TO_DATE(send_at, '%d/%m/%Y %H:%i') <= ?", [implode('-', array_reverse(explode('/', $dateTo))) . ' 23:59:59']);
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('acolito', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }
            
            // Order logic
            $sortBy = $request->input('sort_by', 'send_at');
            $sortOrder = strtolower($request->input('sort_order', 'desc'));
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            switch ($sortBy) {
                case 'name':
                    // Join to sort by acolito name
                    $query->select('acolitos_notes.*')
                          ->join('acolitos', 'acolitos_notes.ac_id', '=', 'acolitos.id')
                          ->orderBy('acolitos.name', $sortOrder);
                    break;
                case 'note':
                    $query->orderBy('note', $sortOrder);
                    break;
                case 'send_at':
                default:
                    $query->orderByRaw("STR_TO_DATE(send_at, '%d/%m/%Y %H:%i') $sortOrder");
                    break;
            }

            $notes = $query->paginate(10);

            // Transform to simpler format for frontend
            $notes->getCollection()->transform(function ($note) {
                return [
                    'id' => $note->id,
                    'name' => $note->acolito ? $note->acolito->name : 'N/A',
                    'note' => $note->note,
                    'send_at' => $note->send_at,
                    'send_by' => $note->send_by,
                ];
            });

            return response()->json($notes);
        }

        return view('modules.acolitos.notes.index');
    }

    public function bulkDestroy(Request $request)
    {
        // Se selecionar tudo, apaga notas de todos os acólitos filtrados
        if ($request->boolean('select_all')) {
            $query = AcolitoNote::query()->whereHas('acolito', function ($q) {
                $q->where('paroquia_id', Auth::user()->paroquia_id);
            });

            // Apply filters to find which notes to delete
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->whereHas('register', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Date filters
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            
            if ($dateFrom) {
                $query->whereRaw("STR_TO_DATE(send_at, '%d/%m/%Y %H:%i') >= ?", [implode('-', array_reverse(explode('/', $dateFrom))) . ' 00:00:00']);
            }
            if ($dateTo) {
                $query->whereRaw("STR_TO_DATE(send_at, '%d/%m/%Y %H:%i') <= ?", [implode('-', array_reverse(explode('/', $dateTo))) . ' 23:59:59']);
            }

            $count = $query->delete();
            return response()->json(['message' => "$count notas excluídas com sucesso."]);
        }

        $request->validate([
            'ids' => 'required|array',
        ]);

        // Delete all notes for the selected IDs (which are note IDs now, not register_ids, because we list notes directly)
        // Wait, the previous logic assumed ids were register_ids. Now we list NOTES directly.
        // So the checkboxes in index.blade.php have value="${item.id}" where item is a NOTE.
        // So $request->ids contains AcolitoNote IDs.
        
        $query = AcolitoNote::whereIn('id', $request->ids)
            ->whereHas('acolito', function ($q) {
                $q->where('paroquia_id', Auth::user()->paroquia_id);
            });
            
        $count = $query->delete();

        return response()->json(['message' => "Notas excluídas com sucesso."]);
    }
}
