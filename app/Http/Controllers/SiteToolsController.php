<?php

namespace App\Http\Controllers;

use App\Models\ParoquiaAjuste;
use App\Models\ParoquiaImagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteToolsController extends Controller
{
    private function ensureCanManageSiteTools(): void
    {
        $user = Auth::user();

        if (!$user || !$user->hasAnyRole(['1', '111', '9', '10'])) {
            abort(403, 'Acesso não autorizado.');
        }
    }

    public function index()
    {
        $this->ensureCanManageSiteTools();
        return view('site-tools.index');
    }

    public function gallery(Request $request)
    {
        $this->ensureCanManageSiteTools();
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
        $this->ensureCanManageSiteTools();
        $request->validate([
            'items' => 'required|array',
            'items.*.file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,heic,heif,svg|max:409600', // 400MB max, expanded types
            'items.*.tipo' => 'required|in:1,2',
            'items.*.titulo' => 'nullable|string|max:255',
            'items.*.descricao' => 'nullable|string',
        ]);

        $data = $request->all();

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

    public function paroquiaAjustes()
    {
        $this->ensureCanManageSiteTools();

        $paroquiaId = Auth::user()->paroquia_id;
        $ajustes = ParoquiaAjuste::where('paroquia_id', $paroquiaId)->first();

        $diasSemana = [
            ['key' => 'domingo', 'label' => 'Domingo'],
            ['key' => 'segunda', 'label' => 'Segunda-feira'],
            ['key' => 'terca', 'label' => 'Terça-feira'],
            ['key' => 'quarta', 'label' => 'Quarta-feira'],
            ['key' => 'quinta', 'label' => 'Quinta-feira'],
            ['key' => 'sexta', 'label' => 'Sexta-feira'],
            ['key' => 'sabado', 'label' => 'Sábado'],
        ];

        $defaultSecretaria = array_map(function ($d) {
            return [
                'day' => $d['key'],
                'label' => $d['label'],
                'closed' => false,
                'slots' => [
                    ['start' => '', 'end' => ''],
                    ['start' => '', 'end' => ''],
                ],
            ];
        }, $diasSemana);

        $defaultPorDiaSlots = array_map(function ($d) {
            return [
                'day' => $d['key'],
                'label' => $d['label'],
                'enabled' => false,
                'slots' => [
                    ['start' => '', 'end' => ''],
                ],
            ];
        }, $diasSemana);

        return view('site-tools.paroquia-ajustes', [
            'ajustes' => $ajustes,
            'secretariaHorarios' => $ajustes?->secretaria_horarios ?: $defaultSecretaria,
            'confissoesHorarios' => $ajustes?->confissoes_horarios ?: $defaultPorDiaSlots,
            'adoracaoEnabled' => (bool) ($ajustes?->adoracao_enabled ?? false),
            'adoracaoHorarios' => $ajustes?->adoracao_horarios ?: $defaultPorDiaSlots,
        ]);
    }

    public function saveParoquiaAjustes(Request $request)
    {
        $this->ensureCanManageSiteTools();

        $validated = $request->validate([
            'secretaria_horarios' => 'required|string',
            'confissoes_horarios' => 'required|string',
            'adoracao_enabled' => 'nullable|boolean',
            'adoracao_horarios' => 'nullable|string',
        ]);

        $secretaria = json_decode($validated['secretaria_horarios'], true);
        $confissoes = json_decode($validated['confissoes_horarios'], true);
        $adoracaoEnabled = $request->boolean('adoracao_enabled');
        $adoracao = null;

        if (!is_array($secretaria) || !is_array($confissoes)) {
            return back()
                ->withErrors(['form' => 'Formato inválido de horários. Recarregue a página e tente novamente.'])
                ->withInput();
        }

        if ($adoracaoEnabled) {
            $adoracao = json_decode($validated['adoracao_horarios'] ?? 'null', true);

            if (!is_array($adoracao)) {
                return back()
                    ->withErrors(['form' => 'Formato inválido de horários de adoração. Recarregue a página e tente novamente.'])
                    ->withInput();
            }
        }

        ParoquiaAjuste::updateOrCreate(
            ['paroquia_id' => Auth::user()->paroquia_id],
            [
                'secretaria_horarios' => $secretaria,
                'confissoes_horarios' => $confissoes,
                'adoracao_enabled' => $adoracaoEnabled,
                'adoracao_horarios' => $adoracaoEnabled ? $adoracao : null,
            ]
        );

        return redirect()
            ->route('site-tools.paroquia-ajustes')
            ->with('success', 'Ajustes da paróquia salvos com sucesso!');
    }
}
