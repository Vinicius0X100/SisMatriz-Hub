<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PascomPostagem;
use App\Models\PascomPostagemArquivo;
use App\Models\Entidade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use Illuminate\Support\Facades\Log;

class PascomPostagemController extends Controller
{
    private function ensureAccess(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['1', '111', '9', '10'])) {
            abort(403);
        }
    }

    private function ensureManageAccess(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['1', '111', '9'])) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureAccess();

        $query = PascomPostagem::with(['user', 'comunidade', 'arquivos'])
            ->where('paroquia_id', Auth::user()->paroquia_id);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('celebrante', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%")
                  ->orWhereHas('comunidade', function($q2) use ($search) {
                      $q2->where('ent_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('comunidade') && !empty($request->comunidade)) {
            $query->where('comunidade_id', $request->comunidade);
        }

        if ($request->has('data') && !empty($request->data)) {
            $query->where('data', $request->data);
        }

        $postagens = $query->orderBy('data', 'desc')->orderBy('horario', 'desc')->paginate(10);

        $stats = [
            'total' => PascomPostagem::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'este_mes' => PascomPostagem::where('paroquia_id', Auth::user()->paroquia_id)->whereMonth('data', date('m'))->whereYear('data', date('Y'))->count(),
            'comunidades' => PascomPostagem::where('paroquia_id', Auth::user()->paroquia_id)->distinct('comunidade_id')->count('comunidade_id'),
        ];

        $comunidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->orderBy('ent_name')->get();

        return view('modules.pascom.postagens.index', compact('postagens', 'stats', 'comunidades'));
    }

    public function create()
    {
        $this->ensureAccess();
        $comunidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->orderBy('ent_name')->get();
        return view('modules.pascom.postagens.create', compact('comunidades'));
    }

    public function store(Request $request)
    {
        $this->ensureAccess();
        $request->validate([
            'data' => 'required|date',
            'horario' => 'required',
            'celebrante' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'comunidade_id' => 'required|exists:entidades,ent_id',
            'arquivos' => 'required|array|max:120', // Array de paths temporários ou JSON
        ]);

        $postagem = PascomPostagem::create([
            'data' => $request->data,
            'horario' => $request->horario,
            'celebrante' => $request->celebrante,
            'descricao' => $request->descricao,
            'comunidade_id' => $request->comunidade_id,
            'user_id' => Auth::id(),
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        // Processa os arquivos movendo-os do temp para o final
        if ($request->has('arquivos')) {
            foreach ($request->arquivos as $arquivoTemp) {
                // arquivoTemp é um JSON com os dados passados pelo upload via AJAX
                $data = json_decode($arquivoTemp, true);
                if ($data && isset($data['path']) && Storage::disk('public')->exists($data['path'])) {
                    $newPath = str_replace('temp/', '', $data['path']);
                    Storage::disk('public')->move($data['path'], $newPath);

                    PascomPostagemArquivo::create([
                        'postagem_id' => $postagem->id,
                        'filename' => basename($newPath),
                        'original_name' => $data['original_name'],
                        'type' => $data['type'],
                        'size' => $data['size'],
                    ]);
                }
            }
        }

        return redirect()->route('pascom.postagens.index')->with('success', 'Postagem criada com sucesso!');
    }

    public function edit($id)
    {
        $this->ensureManageAccess();
        $postagem = PascomPostagem::with(['comunidade', 'arquivos'])
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);
        $comunidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->orderBy('ent_name')->get();
        return view('modules.pascom.postagens.edit', compact('postagem', 'comunidades'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureManageAccess();
        $postagem = PascomPostagem::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        $request->validate([
            'data' => 'required|date',
            'horario' => 'required',
            'celebrante' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'comunidade_id' => 'required|exists:entidades,ent_id',
        ]);

        $postagem->update([
            'data' => $request->data,
            'horario' => $request->horario,
            'celebrante' => $request->celebrante,
            'descricao' => $request->descricao,
            'comunidade_id' => $request->comunidade_id,
        ]);

        return redirect()->route('pascom.postagens.index')->with('success', 'Postagem atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $this->ensureManageAccess();
        $postagem = PascomPostagem::with('arquivos')
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);

        foreach ($postagem->arquivos as $arquivo) {
            Storage::disk('public')->delete('uploads/pascom/' . $arquivo->filename);
        }

        $postagem->delete();

        return redirect()->route('pascom.postagens.index')->with('success', 'Postagem excluída com sucesso!');
    }

    public function destroyArquivo($postagemId, $arquivoId)
    {
        $this->ensureManageAccess();
        $postagem = PascomPostagem::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($postagemId);
        if ($postagem->arquivos()->count() <= 1) {
            return response()->json([
                'success' => false,
                'error' => 'A postagem deve ter no mínimo 1 mídia. Não é possível remover todas.',
            ], 422);
        }
        $arquivo = PascomPostagemArquivo::where('postagem_id', $postagem->id)->findOrFail($arquivoId);

        Storage::disk('public')->delete('uploads/pascom/' . $arquivo->filename);
        $arquivo->delete();

        return response()->json(['success' => true]);
    }

    public function upload(Request $request)
    {
        $this->ensureAccess();
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }
        @ini_set('max_execution_time', '300');

        $request->validate([
            'file' => 'required|file|max:512000', // 500MB max para videos
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $size = $file->getSize();

        // Determinar se é imagem ou vídeo
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
        $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm'];

        $type = in_array($extension, $imageExtensions) ? 'image' : (in_array($extension, $videoExtensions) ? 'video' : 'other');

        if ($type === 'other') {
            return response()->json(['error' => 'Formato não suportado'], 422);
        }

        $filename = date('YmdHis') . '_' . Str::random(10);
        $finalExtension = $extension;

        // Caminho temporário
        $tempDir = 'uploads/pascom/temp';

        if (!Storage::disk('public')->exists($tempDir)) {
            Storage::disk('public')->makeDirectory($tempDir);
        }

        $path = $file->storeAs($tempDir, $filename . '.' . $extension, 'public');
        $fullPath = Storage::disk('public')->path($path);

        try {
            if ($type === 'image') {
                if (in_array($extension, ['heic', 'heif'])) {
                    // Tentar converter HEIC usando Imagick
                    if (class_exists('Imagick')) {
                        $imagick = new Imagick();
                        $imagick->readImage($fullPath);
                        $imagick->setImageFormat('jpg');
                        // Reduzir qualidade para otimizar espaço
                        $imagick->setImageCompressionQuality(80);
                        
                        $finalExtension = 'jpg';
                        $newPath = $tempDir . '/' . $filename . '.' . $finalExtension;
                        $newFullPath = Storage::disk('public')->path($newPath);
                        
                        $imagick->writeImage($newFullPath);
                        $imagick->clear();
                        $imagick->destroy();
                        
                        // Remover original HEIC
                        Storage::disk('public')->delete($path);
                        $path = $newPath;
                        $size = filesize($newFullPath);
                    }
                } else {
                    // Otimizar imagem normal com Imagick se disponível
                    if (class_exists('Imagick')) {
                        $imagick = new Imagick();
                        $imagick->readImage($fullPath);
                        
                        // Resize se for muito grande para evitar ocupar muito espaço (ex: max 1920x1080)
                        $width = $imagick->getImageWidth();
                        $height = $imagick->getImageHeight();
                        if ($width > 1920 || $height > 1920) {
                            $imagick->resizeImage(1920, 1920, Imagick::FILTER_LANCZOS, 1, true);
                        }
                        
                        if (in_array($extension, ['jpg', 'jpeg'])) {
                            $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
                            $imagick->setImageCompressionQuality(80);
                        }
                        
                        $imagick->writeImage($fullPath);
                        $imagick->clear();
                        $imagick->destroy();
                        
                        $size = filesize($fullPath);
                    }
                }
            } else if ($type === 'video') {
                // Para vídeos, uma otimização real requereria FFMpeg (ffmpeg/ffprobe instalados no server)
                // Como não temos certeza do ambiente, salvamos o arquivo, mas se FFMpeg estivesse disponível:
                // Poderíamos usar um Job para processar em background
                // Por hora, apenas aceitamos o vídeo como está, mas já validamos o tamanho no request
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar arquivo Pascom: ' . $e->getMessage());
            // Mesmo com erro na otimização, mantemos o arquivo original
        }

        return response()->json([
            'path' => $path,
            'original_name' => $originalName,
            'type' => $type,
            'size' => $size,
            'url' => asset('storage/' . $path)
        ]);
    }
}
