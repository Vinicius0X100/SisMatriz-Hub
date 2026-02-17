<?php

namespace App\Http\Controllers;

use App\Models\FestaEvento;
use App\Models\FestaEventoEntrada;
use App\Models\FestaEventoSaida;
use App\Models\FestaEventoItemEntrada;
use App\Models\FestaEventoItemSaida;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class FestaEventoController extends Controller
{
    protected function ensureFinancialAccess()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $allowedRules = [1, 111, 11, 14];
        if (!in_array((int)$user->rule, $allowedRules, true)) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureFinancialAccess();

        $query = FestaEvento::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('comunidade');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('titulo', 'like', "%{$search}%");
        }

        if ($request->filled('comunidade_id')) {
            $query->where('comunidade_id', $request->input('comunidade_id'));
        }

        $festas = $query->orderBy('data_inicio', 'desc')->paginate(10);

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('ent_name')
            ->get();

        $stats = [
            'total' => FestaEvento::where('paroquia_id', Auth::user()->paroquia_id)->count(),
        ];

        return view('modules.festas-eventos.index', compact('festas', 'entidades', 'stats'));
    }

    public function store(Request $request)
    {
        $this->ensureFinancialAccess();

        $request->validate([
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
            'comunidade_id' => 'required|exists:entidades,ent_id',
            'descricao' => 'nullable|string',
            'meta' => 'nullable|numeric|min:0',
        ]);

        FestaEvento::create([
            'titulo' => $request->titulo,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'comunidade_id' => $request->comunidade_id,
            'descricao' => $request->descricao,
            'meta' => $request->meta,
            'paroquia_id' => Auth::user()->paroquia_id,
            'criado_em' => now(),
        ]);

        return redirect()->route('festas-eventos.index')->with('success', 'Festa/Evento criado com sucesso!');
    }

    public function update(Request $request, FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'titulo' => 'required|string|max:255',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
            'comunidade_id' => 'required|exists:entidades,ent_id',
            'descricao' => 'nullable|string',
            'meta' => 'nullable|numeric|min:0',
        ]);

        $festaEvento->update([
            'titulo' => $request->titulo,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'comunidade_id' => $request->comunidade_id,
            'descricao' => $request->descricao,
            'meta' => $request->meta,
        ]);

        return redirect()->route('festas-eventos.index')->with('success', 'Festa/Evento atualizado com sucesso!');
    }

    public function destroy(FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $festaEvento->delete();

        return redirect()->route('festas-eventos.index')->with('success', 'Festa/Evento excluído com sucesso!');
    }

    public function storeEntrada(Request $request, FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'valor' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        $entrada = FestaEventoEntrada::create([
            'festa_evento_id' => $festaEvento->id,
            'valor' => $request->valor,
            'descricao' => $request->descricao,
            'data' => $request->data,
            'user_id' => Auth::id(),
            'criado_em' => now(),
        ]);

        return Response::json($entrada);
    }

    public function storeSaida(Request $request, FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'valor' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
        ]);

        $saida = FestaEventoSaida::create([
            'festa_evento_id' => $festaEvento->id,
            'valor' => $request->valor,
            'descricao' => $request->descricao,
            'data' => $request->data,
            'user_id' => Auth::id(),
            'criado_em' => now(),
        ]);

        return Response::json($saida);
    }

    public function storeItemEntrada(Request $request, FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'item' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        $item = FestaEventoItemEntrada::create([
            'festa_evento_id' => $festaEvento->id,
            'item' => $request->item,
            'quantidade' => $request->quantidade,
            'observacao' => $request->observacao,
            'data' => $request->data,
            'user_id' => Auth::id(),
            'criado_em' => now(),
        ]);

        return Response::json($item);
    }

    public function storeItemSaida(Request $request, FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'item' => 'required|string|max:255',
            'quantidade' => 'required|integer|min:1',
            'observacao' => 'nullable|string',
        ]);

        $item = FestaEventoItemSaida::create([
            'festa_evento_id' => $festaEvento->id,
            'item' => $request->item,
            'quantidade' => $request->quantidade,
            'observacao' => $request->observacao,
            'data' => $request->data,
            'user_id' => Auth::id(),
            'criado_em' => now(),
        ]);

        return Response::json($item);
    }

    public function show(FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $festaEvento->load(['comunidade', 'entradas', 'saidas', 'itensEntradas', 'itensSaidas']);

        $totalEntradas = $festaEvento->entradas->sum('valor');
        $totalSaidas = $festaEvento->saidas->sum('valor');
        $saldoFinanceiro = $totalEntradas - $totalSaidas;

        return Response::json([
            'festa' => $festaEvento,
            'totais' => [
                'entradas' => $totalEntradas,
                'saidas' => $totalSaidas,
                'saldo_financeiro' => $saldoFinanceiro,
            ],
            'entradas' => $festaEvento->entradas,
            'saidas' => $festaEvento->saidas,
            'itens_entrada' => $festaEvento->itensEntradas,
            'itens_saida' => $festaEvento->itensSaidas,
        ]);
    }

    public function generateReport(FestaEvento $festaEvento)
    {
        $this->ensureFinancialAccess();

        if ($festaEvento->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $festaEvento->load(['comunidade', 'entradas', 'saidas', 'itensEntradas', 'itensSaidas']);

        $entradas = $festaEvento->entradas()->orderBy('data', 'desc')->get();
        $saidas = $festaEvento->saidas()->orderBy('data', 'desc')->get();
        $itensEntrada = $festaEvento->itensEntradas()->orderBy('data', 'desc')->get();
        $itensSaida = $festaEvento->itensSaidas()->orderBy('data', 'desc')->get();

        $totalEntradas = $entradas->sum('valor');
        $totalSaidas = $saidas->sum('valor');
        $saldoFinanceiro = $totalEntradas - $totalSaidas;

        $movimentacoes = $entradas->map(function($e) {
            return [
                'tipo' => 'entrada',
                'data' => $e->data,
                'valor' => $e->valor,
                'descricao' => $e->descricao,
            ];
        })->concat($saidas->map(function($s) {
            return [
                'tipo' => 'saida',
                'data' => $s->data,
                'valor' => $s->valor,
                'descricao' => $s->descricao,
            ];
        }))->concat($itensEntrada->map(function($ie) {
            return [
                'tipo' => 'item_entrada',
                'data' => $ie->data,
                'quantidade' => $ie->quantidade,
                'detalhe' => trim(($ie->item ?? '') . ($ie->observacao ? ' • ' . $ie->observacao : '')),
            ];
        }))->concat($itensSaida->map(function($is) {
            return [
                'tipo' => 'item_saida',
                'data' => $is->data,
                'quantidade' => $is->quantidade,
                'detalhe' => trim(($is->item ?? '') . ($is->observacao ? ' • ' . $is->observacao : '')),
            ];
        }))->sortByDesc('data')->values();

        $paroquiaName = optional(Auth::user()->paroquia)->name ?? 'Paróquia';

        $pdf = Pdf::loadView('modules.festas-eventos.pdf.report', compact(
            'festaEvento',
            'totalEntradas',
            'totalSaidas',
            'saldoFinanceiro',
            'movimentacoes',
            'paroquiaName'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('relatorio_festa_evento_' . \Illuminate\Support\Str::slug($festaEvento->titulo) . '.pdf');
    }

    public function destroyEntrada($id)
    {
        $this->ensureFinancialAccess();

        $entrada = FestaEventoEntrada::findOrFail($id);
        if ($entrada->festa->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $entrada->delete();

        return Response::json(['success' => true]);
    }

    public function destroySaida($id)
    {
        $this->ensureFinancialAccess();

        $saida = FestaEventoSaida::findOrFail($id);
        if ($saida->festa->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $saida->delete();

        return Response::json(['success' => true]);
    }

    public function destroyItemEntrada($id)
    {
        $this->ensureFinancialAccess();

        $item = FestaEventoItemEntrada::findOrFail($id);
        if ($item->festa->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $item->delete();

        return Response::json(['success' => true]);
    }

    public function destroyItemSaida($id)
    {
        $this->ensureFinancialAccess();

        $item = FestaEventoItemSaida::findOrFail($id);
        if ($item->festa->paroquia_id != Auth::user()->paroquia_id) {
            return Response::json(['error' => 'Unauthorized'], 403);
        }

        $item->delete();

        return Response::json(['success' => true]);
    }
}
