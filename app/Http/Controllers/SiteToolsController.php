<?php

namespace App\Http\Controllers;

use App\Models\ParoquiaImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteToolsController extends Controller
{
    public function index()
    {
        return view('site-tools.index');
    }

    public function gallery(Request $request)
    {
        $query = ParoquiaImagem::where('paroquia_id', Auth::user()->paroquia_id);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        // Filter by Type
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filter by Period
        if ($request->filled('period')) {
            $query->where('created_at', 'like', "{$request->period}%");
        }

        $imagens = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // Get available periods for filter
        $periods = ParoquiaImagem::where('paroquia_id', Auth::user()->paroquia_id)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, DATE_FORMAT(created_at, '%M %Y') as label")
            ->distinct()
            ->orderBy('period', 'desc')
            ->get();

        return view('site-tools.gallery', compact('imagens', 'periods'));
    }

    public function uploadGallery(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'items.*.tipo' => 'required|in:1,2',
            'items.*.titulo' => 'nullable|string|max:255',
            'items.*.descricao' => 'nullable|string',
        ]);

        $uploadedCount = 0;

        $createdImages = [];

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // Ensure file exists and is valid
                if (!isset($item['file']) || !$item['file']->isValid()) {
                    continue;
                }

                $file = $item['file'];

                // Validação extra de mime type para garantir que não é vídeo
                if (str_starts_with($file->getMimeType(), 'video/')) {
                    continue;
                }

                $filename = 'paroquia-' . Auth::user()->paroquia_id . '-' . time() . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Salvar em storage/app/public/uploads/paroquias
                $file->storeAs('uploads/paroquias', $filename, 'public');

                $imagem = ParoquiaImagem::create([
                    'paroquia_id' => Auth::user()->paroquia_id,
                    'imagem' => $filename,
                    'titulo' => $item['titulo'] ?? 'Sem Título', // Default title if null
                    'descricao' => $item['descricao'] ?? null,
                    'tipo' => $item['tipo'],
                ]);

                // Adicionar URL completa para uso no frontend
                $imageData = $imagem->toArray();
                $imageData['url'] = asset('storage/uploads/paroquias/' . $filename);
                $createdImages[] = $imageData;

                $uploadedCount++;
            }
        }

        if ($uploadedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhuma imagem válida foi enviada.'
            ], 422);
        }

        // Não usar flash session para resposta JSON pura, mas manter se houver reload fallback
        // session()->flash('success', "{$uploadedCount} imagens enviadas com sucesso!");

        return response()->json([
            'success' => true, 
            'message' => "{$uploadedCount} imagens enviadas com sucesso!",
            'count' => $uploadedCount,
            'images' => $createdImages
        ]);
    }
}
