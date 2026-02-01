<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OfertaController extends Controller
{
    public function index(Request $request)
    {
        $query = Oferta::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('entidade');

        // Filtro por Comunidade
        if ($request->filled('ent_id')) {
            $query->where('ent_id', $request->ent_id);
        }

        // Filtro por Tipo de Lançamento (kind)
        if ($request->filled('kind')) {
            $query->where('kind', $request->kind);
        }

        // Filtro por Período
        if ($request->filled('data_inicio')) {
            $query->whereDate('data', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data', '<=', $request->data_fim);
        }

        $ofertas = $query->orderBy('data', 'desc')
            ->orderBy('criado_em', 'desc')
            ->paginate(15)
            ->appends($request->all());

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('ent_name')
            ->get();

        return view('modules.ofertas.index', compact('ofertas', 'entidades'));
    }

    public function create()
    {
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('ent_name')
            ->get();

        return view('modules.ofertas.create', compact('entidades'));
    }

    public function store(Request $request)
    {
        if ($request->has('valor_total')) {
            $request->merge([
                'valor_total' => $this->parseCurrency($request->valor_total)
            ]);
        }

        $request->validate([
            'data' => 'required|date',
            'ent_id' => 'required|exists:entidades,ent_id',
            'kind' => 'required|integer',
            'valor_total' => 'required|numeric|min:0',
            'tipo' => 'nullable|string|max:255', // Celebracao
            'observacoes' => 'nullable|string',
        ]);

        Oferta::create([
            'data' => $request->data,
            'horario' => $request->horario, // Optional
            'valor_total' => $request->valor_total,
            'tipo' => $request->tipo,
            'kind' => $request->kind,
            'observacoes' => $request->observacoes,
            'ent_id' => $request->ent_id,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return redirect()->route('ofertas.index')->with('success', 'Lançamento realizado com sucesso!');
    }

    public function storeBulk(Request $request)
    {
        $data = $request->all();
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                if (isset($item['valor_total'])) {
                    $item['valor_total'] = $this->parseCurrency($item['valor_total']);
                }
            }
            $request->replace($data);
        }

        $request->validate([
            'data' => 'required|date',
            'ent_id' => 'required|exists:entidades,ent_id',
            'items' => 'required|array|min:1',
            'items.*.kind' => 'required|integer',
            'items.*.valor_total' => 'required|numeric|min:0',
            'items.*.tipo' => 'nullable|string|max:255',
            'items.*.observacoes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->items as $item) {
                Oferta::create([
                    'data' => $request->data,
                    'horario' => $request->horario,
                    'ent_id' => $request->ent_id,
                    'paroquia_id' => Auth::user()->paroquia_id,
                    'kind' => $item['kind'],
                    'valor_total' => $item['valor_total'],
                    'tipo' => $item['tipo'] ?? null,
                    'observacoes' => $item['observacoes'] ?? null,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Lançamentos realizados com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao realizar lançamentos: ' . $e->getMessage()], 500);
        }
    }

    public function edit(Oferta $oferta)
    {
        if ($oferta->paroquia_id !== Auth::user()->paroquia_id) {
            abort(403);
        }

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('ent_name')
            ->get();

        return view('modules.ofertas.edit', compact('oferta', 'entidades'));
    }

    public function update(Request $request, Oferta $oferta)
    {
        if ($oferta->paroquia_id !== Auth::user()->paroquia_id) {
            abort(403);
        }

        if ($request->has('valor_total')) {
            $request->merge([
                'valor_total' => $this->parseCurrency($request->valor_total)
            ]);
        }

        $request->validate([
            'data' => 'required|date',
            'ent_id' => 'required|exists:entidades,ent_id',
            'kind' => 'required|integer',
            'valor_total' => 'required|numeric|min:0',
            'tipo' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string',
        ]);

        $oferta->update([
            'data' => $request->data,
            'horario' => $request->horario,
            'valor_total' => $request->valor_total,
            'tipo' => $request->tipo,
            'kind' => $request->kind,
            'observacoes' => $request->observacoes,
            'ent_id' => $request->ent_id,
        ]);

        return redirect()->route('ofertas.index')->with('success', 'Lançamento atualizado com sucesso!');
    }

    public function destroy(Oferta $oferta)
    {
        if ($oferta->paroquia_id !== Auth::user()->paroquia_id) {
            abort(403);
        }

        $oferta->delete();

        return redirect()->route('ofertas.index')->with('success', 'Lançamento excluído com sucesso!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('ofertas.index')->with('error', 'Nenhum registro selecionado.');
        }

        Oferta::whereIn('id', $ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->delete();

        return redirect()->route('ofertas.index')->with('success', count($ids) . ' lançamentos excluídos com sucesso!');
    }

    public function exportPdf(Request $request)
    {
        $query = Oferta::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('entidade');

        // Filtro por Comunidade
        if ($request->filled('ent_id') && $request->ent_id != 'all') {
            $query->where('ent_id', $request->ent_id);
        }

        // Filtro por Tipo de Lançamento (kind)
        if ($request->filled('kind') && $request->kind != 'all') {
            $query->where('kind', $request->kind);
        }

        // Filtro por Período
        if ($request->filled('data_inicio')) {
            $query->whereDate('data', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data', '<=', $request->data_fim);
        }

        $ofertas = $query->orderBy('data', 'asc')->get();

        // Agrupamento por Comunidade
        $groupedData = $ofertas->groupBy(function($item) {
            return $item->entidade->ent_name;
        });

        // Totais por Tipo (Geral)
        $totaisPorTipo = $ofertas->groupBy('kind')->map(function ($group) {
            return $group->sum('valor_total');
        });

        // Tipos legíveis
        $tiposNomes = [
            1 => 'Dízimo',
            2 => 'Oferta',
            3 => 'Moedas',
            4 => 'Doação em Cofre',
            5 => 'Bazares',
            6 => 'Vendas',
        ];

        $pdf = Pdf::loadView('modules.ofertas.pdf', compact('groupedData', 'totaisPorTipo', 'tiposNomes', 'request'));

        // Configuração opcional do papel
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('relatorio_ofertas_' . date('YmdHis') . '.pdf');
    }

    private function parseCurrency($value)
    {
        if (empty($value)) return 0;
        // Remove dots (thousand separators) and replace comma with dot (decimal separator)
        // Example: "1.234,56" -> "1234.56"
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }
}
