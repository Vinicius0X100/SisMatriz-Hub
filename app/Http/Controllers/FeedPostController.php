<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeedPostRequest;
use App\Models\FeedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FeedPostController extends Controller
{
    public function create()
    {
        return view('modules.avisos.create');
    }

    public function index()
    {
        $posts = FeedPost::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderByDesc('send_at')
            ->orderByDesc('id')
            ->paginate(25);

        return view('modules.avisos.index', compact('posts'));
    }

    public function store(StoreFeedPostRequest $request)
    {
        $data = [
            'title' => $request->input('title'),
            'legend' => $request->input('legend'),
            'level_importance' => (int) $request->input('level_importance'),
            'send_at' => now(),
            'device' => 1,
            'views' => 0,
            'paroquia_id' => Auth::user()->paroquia_id,
        ];

        if ($request->hasFile('anexo')) {
            $path = $request->file('anexo')->store('uploads/feed', 'public');
            $data['anexo'] = $path;
        }

        FeedPost::create($data);

        try {
            $baseUrl = rtrim(config('services.notification_api.url', config('app.url')), '/');
            $notificationUrl = $baseUrl . '/api/send_notification.php';

            $notificationData = [
                'title' => $data['title'],
                'body' => $data['legend'],
                'paroquia_id' => $data['paroquia_id'],
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $notificationUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($notificationData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            if ($response === false) {
                Log::error('Erro ao enviar notificação via cURL: ' . curl_error($ch));
            } else {
                Log::info('Resposta da API de notificação: ' . $response);
            }
            curl_close($ch);
        } catch (\Throwable $e) {
            Log::error('Erro geral ao chamar API de notificação: ' . $e->getMessage());
        }

        return redirect()->route('avisos.index')->with('success', 'Aviso publicado com sucesso.');
    }

    public function edit(FeedPost $aviso)
    {
        if ($aviso->paroquia_id !== Auth::user()->paroquia_id) {
            abort(403);
        }
        return view('modules.avisos.edit', compact('aviso'));
    }

    public function update(StoreFeedPostRequest $request, FeedPost $aviso)
    {
        if ($aviso->paroquia_id !== Auth::user()->paroquia_id) {
            abort(403);
        }

        $aviso->title = $request->input('title');
        $aviso->legend = $request->input('legend');
        $aviso->level_importance = (int) $request->input('level_importance');

        if ($request->hasFile('anexo')) {
            if ($aviso->anexo && Storage::disk('public')->exists($aviso->anexo)) {
                Storage::disk('public')->delete($aviso->anexo);
            }

            $path = $request->file('anexo')->store('uploads/feed', 'public');
            $aviso->anexo = $path;
        }

        $aviso->save();

        return redirect()->route('avisos.index')->with('success', 'Aviso atualizado com sucesso.');
    }

    public function destroy(FeedPost $aviso)
    {
        if ($aviso->paroquia_id !== Auth::user()->paroquia_id) {
            abort(403);
        }

        if ($aviso->anexo && Storage::disk('public')->exists($aviso->anexo)) {
            Storage::disk('public')->delete($aviso->anexo);
        }

        $aviso->delete();

        return redirect()->route('avisos.index')->with('success', 'Aviso removido com sucesso.');
    }
}
