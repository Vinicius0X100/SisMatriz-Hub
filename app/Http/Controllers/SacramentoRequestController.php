<?php

namespace App\Http\Controllers;

use App\Models\SacramentoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SacramentoRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = SacramentoRequest::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nome_completo', 'like', "%{$search}%")
                  ->orWhere('telefone', 'like', "%{$search}%")
                  ->orWhere('sacramento', 'like', "%{$search}%");
            });
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->input('data_inicio'));
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->input('data_fim'));
        }

        $records = $query->paginate(15);

        return view('modules.sacramento_requests.index', compact('records'));
    }

    public function destroy($id)
    {
        $record = SacramentoRequest::where('id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $record->delete();

        return redirect()->route('solicitacoes-segunda-via.index')->with('success', 'Solicitação removida com sucesso!');
    }

    public function updateStatus(Request $request, $id)
    {
        $record = SacramentoRequest::where('id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $record->status = $request->input('status');
        $record->save();

        return response()->json(['success' => true, 'message' => 'Status atualizado com sucesso!']);
    }

    public function bulkAction(Request $request)
    {
        $ids = explode(',', $request->input('ids'));
        $action = $request->input('action');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Nenhum item selecionado.');
        }

        if ($action === 'delete') {
            SacramentoRequest::whereIn('id', $ids)
                ->where('paroquia_id', Auth::user()->paroquia_id)
                ->delete();
            return redirect()->back()->with('success', 'Itens removidos com sucesso!');
        }

        if ($action === 'print') {
            $records = SacramentoRequest::whereIn('id', $ids)
                ->where('paroquia_id', Auth::user()->paroquia_id)
                ->orderBy('created_at', 'desc')
                ->get();

            $columns = $request->input('columns', ['data_solicitacao', 'solicitante', 'telefone', 'sacramento', 'status']); // Default columns

            $pdf = PDF::loadView('modules.sacramento_requests.pdf', compact('records', 'columns'));
            return $pdf->download('relatorio_solicitacoes_segunda_via.pdf');
        }

        return redirect()->back();
    }

    public function printSheet($id)
    {
        $record = SacramentoRequest::where('id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $pdf = PDF::loadView('modules.sacramento_requests.pdf_sheet', compact('record'));
        return $pdf->download('ficha_solicitacao_' . $record->id . '.pdf');
    }
}
