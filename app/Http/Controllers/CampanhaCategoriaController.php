<?php

namespace App\Http\Controllers;

use App\Models\CampanhaCategoria;
use App\Models\Campanha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampanhaCategoriaController extends Controller
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

    public function index()
    {
        $categorias = CampanhaCategoria::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('nome')
            ->get();
        return response()->json($categorias);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $categoria = CampanhaCategoria::create([
            'nome' => $request->nome,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return response()->json($categoria);
    }

    public function update(Request $request, CampanhaCategoria $categoria)
    {
        if ($categoria->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $categoria->update(['nome' => $request->nome]);

        return response()->json($categoria);
    }

    public function destroy(CampanhaCategoria $categoria)
    {
        if ($categoria->paroquia_id != Auth::user()->paroquia_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categoria->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function stats()
    {
        $paroquiaId = Auth::user()->paroquia_id;

        // Categorias com contagem de campanhas
        $categorias = CampanhaCategoria::where('paroquia_id', $paroquiaId)
            ->withCount('campanhas')
            ->get();

        $total = $categorias->count();
        $unused = $categorias->where('campanhas_count', 0)->count();
        $mostUsed = $categorias->sortByDesc('campanhas_count')->first();

        // Top 5 most used for bar chart
        $topCategories = $categorias->sortByDesc('campanhas_count')->take(5)->values();

        // Data for charts
        $barChartData = $topCategories->map(function($c) {
            return ['name' => $c->nome, 'count' => $c->campanhas_count];
        });
        
        // Distribution (Pie chart) - simple version
        $pieChartData = $topCategories->map(function($c) {
             return ['name' => $c->nome, 'value' => $c->campanhas_count];
        });

        return response()->json([
            'total' => $total,
            'unused' => $unused,
            'most_used' => $mostUsed ? $mostUsed->nome : '-',
            'bar_chart' => $barChartData,
            'pie_chart' => $pieChartData,
            'categorias' => $categorias, // Return list with counts if needed
        ]);
    }
}
