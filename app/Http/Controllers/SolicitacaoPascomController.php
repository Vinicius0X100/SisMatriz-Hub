<?php

namespace App\Http\Controllers;

use App\Models\SolicitacaoPascom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitacaoPascomController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitacaoPascom::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('pastoral', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->input('data_fim'));
        }

        $records = $query->paginate(15);

        return view('modules.pascom.index', compact('records'));
    }

    public function destroy($id)
    {
        $record = SolicitacaoPascom::where('id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $record->delete();

        return redirect()->route('solicitacoes-pascom.index')->with('success', 'Solicitação removida com sucesso!');
    }
}
