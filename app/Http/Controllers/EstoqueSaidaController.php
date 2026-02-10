<?php

namespace App\Http\Controllers;

use App\Models\EstoqueSaida;
use App\Models\SocialAssistant;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstoqueSaidaController extends Controller
{
    public function index(Request $request)
    {
        $query = EstoqueSaida::where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['item', 'comunidade']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome_item', 'like', "%{$search}%")
                  ->orWhere('retirado_por', 'like', "%{$search}%")
                  ->orWhere('entregue_por', 'like', "%{$search}%");
            });
        }

        if ($request->filled('ent_id')) {
            $query->where('ent_id', $request->ent_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('data_saida', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('data_saida', '<=', $request->end_date);
        }

        $items = $query->orderBy('data_saida', 'desc')->paginate(15);
        
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();

        $stats = [
            'total' => EstoqueSaida::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'this_month' => EstoqueSaida::where('paroquia_id', Auth::user()->paroquia_id)
                ->whereMonth('data_saida', now()->month)
                ->whereYear('data_saida', now()->year)
                ->count(),
            'items_distributed' => EstoqueSaida::where('paroquia_id', Auth::user()->paroquia_id)->sum('qntd_distribuida'),
        ];

        return view('modules.estoque-saida.index', compact('items', 'entidades', 'stats'));
    }

    public function create()
    {
        // Fetch items with stock > 0
        $items = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('qntd_destributed', '>', 0)
            ->orderBy('description')
            ->get();

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();

        return view('modules.estoque-saida.create', compact('items', 'entidades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            's_id' => 'required|exists:social_assistant,s_id',
            'qntd_distribuida' => 'required|integer|min:1',
            'retirado_por' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'data_saida' => 'required|date',
        ]);

        $item = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('s_id', $request->s_id)
            ->firstOrFail();

        if ($item->qntd_destributed < $request->qntd_distribuida) {
            return back()->withErrors(['qntd_distribuida' => 'Quantidade insuficiente em estoque. Disponível: ' . $item->qntd_destributed])->withInput();
        }

        DB::transaction(function () use ($request, $item) {
            // Create Stock Out Record
            EstoqueSaida::create([
                's_id' => $item->s_id,
                'nome_item' => $item->description,
                'qntd_distribuida' => $request->qntd_distribuida,
                'retirado_por' => $request->retirado_por,
                'entregue_por' => Auth::user()->name,
                'data_saida' => $request->data_saida . ' ' . now()->format('H:i:s'),
                'ent_id' => $request->ent_id,
                'ano' => Carbon::parse($request->data_saida)->year,
                'mes' => Carbon::parse($request->data_saida)->locale('pt_BR')->monthName,
                'status' => 1, // Assuming 1 is active/confirmed
                'paroquia_id' => Auth::user()->paroquia_id,
            ]);

            // Update Stock Quantity
            $item->decrement('qntd_destributed', $request->qntd_distribuida);
        });

        return redirect()->route('estoque-saida.index')->with('success', 'Saída de estoque registrada com sucesso!');
    }
}
