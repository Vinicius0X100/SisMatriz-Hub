<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
Log::info('UPDATE CHEGOU', [
    'user' => auth()->id(),
    'hasFile' => $request->hasFile('avatar'),
]);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        Log::info('PROFILE UPDATE REQUEST', [
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'accept' => $request->header('Accept'),
            'ajax' => $request->ajax(),
            'expectsJson' => $request->expectsJson(),
            'hasFile_avatar' => $request->hasFile('avatar'),
            'all' => $request->except(['avatar']),
            'files' => $request->allFiles(),
        ]);

        try {

            $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Atualiza nome
            if ($request->filled('name')) {
                $user->name = $request->name;
            }

            // Atualiza email
            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            // Upload avatar
            if ($request->hasFile('avatar')) {

                $file = $request->file('avatar');

                Log::info('AVATAR RECEBIDO', [
                    'original_name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);

                // Sempre salva como PNG
                $filename = $user->id . '.png';

                // Remove antigo
                if (
                    $user->avatar &&
                    Storage::disk('public')->exists('uploads/avatars/' . $filename)
                ) {
                    Storage::disk('public')->delete('uploads/avatars/' . $filename);
                }

                // Converte imagem para PNG
                $image = imagecreatefromstring(
                    file_get_contents($file->getRealPath())
                );

                if ($image === false) {

                    Log::error('ERRO AO CONVERTER IMAGEM');

                    return response()->json([
                        'success' => false,
                        'message' => 'Erro ao processar imagem'
                    ], 422);
                }

                ob_start();
                imagepng($image, null, 9);
                $pngData = ob_get_clean();

                Storage::disk('public')->put(
                    'uploads/avatars/' . $filename,
                    $pngData
                );

                imagedestroy($image);

                $user->avatar = $filename;

                Log::info('AVATAR SALVO', [
                    'path' => 'uploads/avatars/' . $filename
                ]);
            }

            // ✅ CORRIGIDO: accepted_photo fora do bloco do avatar
            // Marca como aceito se acabou de enviar OU se já tinha avatar salvo
            if ($request->hasFile('avatar') || $user->avatar) {
                $user->accepted_photo = 1;
            }

            $user->save();

            // ✅ CORRIGIDO: removido o redirect() morto que nunca era alcançado
            return response()->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!',
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar
                    ? asset('storage/uploads/avatars/' . $user->avatar)
                    : null,
                'accepted_photo' => $user->accepted_photo,
            ]);

        } catch (\Throwable $e) {

            Log::error('ERRO PROFILE UPDATE', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno no servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}