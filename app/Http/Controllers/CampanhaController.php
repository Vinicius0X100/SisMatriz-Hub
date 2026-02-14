<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\CampanhaCategoria;
use App\Models\CampanhaEntrada;
use App\Models\CampanhaSaida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CampanhaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !Auth::user()->hasAnyRole(['1', '111', '11'])) {
                abort(403, 'Acesso não autorizado. Apenas administradores e financeiro (tesoureiros) podem acessar este módulo.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Campanha::query();

        if (Auth::user()->paroquia_id) {
            $query->where('paroquia_id', Auth::user()->paroquia_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        if ($request->has('categoria_id') && !empty($request->categoria_id)) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $campanhas = $query->with('categoria')->orderBy('created_at', 'desc')->paginate(10);
        $categorias = CampanhaCategoria::where('paroquia_id', Auth::user()->paroquia_id)->orderBy('nome')->get();

        $stats = [
            'total' => Campanha::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'active' => Campanha::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 'ativa')->count(),
            'inactive' => Campanha::where('paroquia_id', Auth::user()->paroquia_id)->where('status', '!=', 'ativa')->count(),
        ];

        return view('modules.campanhas.index', compact('campanhas', 'categorias', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:campanha_categorias,id',
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date',
        ]);

        Campanha::create([
            'nome' => $request->nome,
            'categoria_id' => $request->categoria_id,
            'descricao' => $request->descricao,
            'data_inicio' => $request->data_inicio,
            'data_fim' => $request->data_fim,
            'paroquia_id' => Auth::user()->paroquia_id,
            'status' => 'ativa',
        ]);

        return redirect()->route('campanhas.index')->with('success', 'Campanha criada com sucesso!');
    }

    public function update(Request $request, Campanha $campanha)
    {
        if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:campanha_categorias,id',
        ]);

        $campanha->update($request->all());

        return redirect()->route('campanhas.index')->with('success', 'Campanha atualizada com sucesso!');
    }

    public function destroy(Campanha $campanha)
    {
        if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $campanha->delete();

        return redirect()->route('campanhas.index')->with('success', 'Campanha excluída com sucesso!');
    }

    public function generateReport(Campanha $campanha)
    {
        if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $entradas = $campanha->entradas()->orderBy('data', 'desc')->get();
        $saidas = $campanha->saidas()->orderBy('data', 'desc')->get();

        $totalEntradas = $entradas->sum('valor');
        $totalSaidas = $saidas->sum('valor');
        $saldo = $totalEntradas - $totalSaidas;

        $movimentacoes = $entradas->map(function($e) {
            return [
                'tipo' => 'entrada',
                'data' => $e->data,
                'valor' => $e->valor,
                'forma' => $e->forma,
                'observacoes' => $e->observacoes,
            ];
        })->concat($saidas->map(function($s) {
            return [
                'tipo' => 'saida',
                'data' => $s->data,
                'valor' => $s->valor,
                'categoria' => $s->categoria,
                'descricao' => $s->descricao,
            ];
        }))->sortByDesc('data')->values();

        $paroquiaName = optional(Auth::user()->paroquia)->name ?? 'Paróquia';

        $pdf = Pdf::loadView('modules.campanhas.pdf.report', compact('campanha', 'totalEntradas', 'totalSaidas', 'saldo', 'movimentacoes', 'paroquiaName'));
        
        return $pdf->download('relatorio_campanha_' . \Illuminate\Support\Str::slug($campanha->nome) . '.pdf');
    }

    public function storeEntrada(Request $request, Campanha $campanha)
    {
        if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'data' => 'required|date',
                'valor' => 'required|numeric|min:0.01',
                'forma' => 'nullable|string|max:255',
                'observacoes' => 'nullable|string',
            ]);
    
            $entrada = $campanha->entradas()->create($validated);
    
            return response()->json($entrada);
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar entrada: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno ao salvar entrada: ' . $e->getMessage()], 500);
        }
    }

    public function storeSaida(Request $request, Campanha $campanha)
    {
        if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'data' => 'required|date',
                'valor' => 'required|numeric|min:0.01',
                'categoria' => 'nullable|string|max:255',
                'descricao' => 'nullable|string',
            ]);
    
            $saida = $campanha->saidas()->create($validated);
    
            return response()->json($saida);
        } catch (\Exception $e) {
            \Log::error('Erro ao salvar saída: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno ao salvar saída: ' . $e->getMessage()], 500);
        }
    }

    public function destroyEntrada($id)
    {
        $entrada = CampanhaEntrada::findOrFail($id);
        
        if ($entrada->campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $entrada->delete();

        return response()->json(['success' => true]);
    }

    public function destroySaida($id)
    {
        $saida = CampanhaSaida::findOrFail($id);
        
        if ($saida->campanha->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $saida->delete();

        return response()->json(['success' => true]);
    }

    // API methods for dashboard
    public function getDashboardData(Campanha $campanha)
    {
        try {
            if ($campanha->paroquia_id != Auth::user()->paroquia_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Load relationships with safety checks
            $entradas = $campanha->entradas()->orderBy('data', 'desc')->get();
            $saidas = $campanha->saidas()->orderBy('data', 'desc')->get();

            $totalEntradas = $entradas->sum('valor');
            $totalSaidas = $saidas->sum('valor');
            $saldo = $totalEntradas - $totalSaidas;

            // Map collections
            $movimentacoesEntradas = $entradas->map(function($e) {
                return [
                    'id' => $e->id,
                    'tipo' => 'entrada',
                    'data' => $e->data,
                    'valor' => $e->valor,
                    'categoria' => $e->forma ?? 'N/A',
                    'descricao' => $e->observacoes ?? '',
                ];
            });

            $movimentacoesSaidas = $saidas->map(function($s) {
                return [
                    'id' => $s->id,
                    'tipo' => 'saida',
                    'data' => $s->data,
                    'valor' => $s->valor,
                    'categoria' => $s->categoria ?? 'N/A',
                    'descricao' => $s->descricao ?? '',
                ];
            });

            $movimentacoes = $movimentacoesEntradas->concat($movimentacoesSaidas)->sortByDesc('data')->values();

            return response()->json([
                'campanha' => $campanha,
                'stats' => [
                    'total_arrecadado' => $totalEntradas,
                    'total_gasto' => $totalSaidas,
                    'saldo' => $saldo,
                ],
                'entradas' => $entradas,
                'saidas' => $saidas,
                'movimentacoes' => $movimentacoes,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados do dashboard: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
}
