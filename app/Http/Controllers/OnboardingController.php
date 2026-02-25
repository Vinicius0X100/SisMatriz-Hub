<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class OnboardingController extends Controller
{
    public function showPasswordForm()
    {
        return view('setup.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->is_pass_change = 1;
        $user->save();

        return redirect()->route('setup.welcome');
    }

    public function showWelcome()
    {
        $user = Auth::user();
        $user->load('paroquia');
        return view('setup.welcome', compact('user'));
    }

    public function updateWelcome(Request $request)
    {
        set_time_limit(300); // Increase execution time to 5 minutes for FTP upload

        $request->validate(
            [
                'avatar' => 'required|mimes:jpeg,png,jpg,heic,heif|max:5120',
            ],
            [
                'avatar.required' => 'Envie uma foto para continuar.',
                'avatar.mimes' => 'A foto deve ser do tipo JPEG, PNG ou HEIC.',
                'avatar.max' => 'A foto deve ter no máximo 5 MB.',
            ]
        );

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
            $originalExtension = $extension;
            $targetExtension = 'png';

            if (in_array($extension, ['heic', 'heif'])) {
                if (!class_exists(\Imagick::class)) {
                    $targetExtension = $extension;
                }
            }

            $filename = $user->id . '.' . $targetExtension;

            if (in_array($originalExtension, ['heic', 'heif']) && class_exists(\Imagick::class)) {
                $image = new \Imagick();
                $image->readImageBlob($file->get());
                $image->setImageFormat($targetExtension);
                Storage::disk('public')->put('uploads/avatars/' . $filename, $image->getImageBlob());
                $image->clear();
                $image->destroy();
            } else {
                Storage::disk('public')->putFileAs('uploads/avatars', $file, $filename);
            }

            $user->avatar = $filename;
            $user->accepted_photo = 1;
            $user->save();
        }

        return redirect()->route('dashboard');
    }

    public function skipWelcome()
    {
        $user = Auth::user();
        $user->accepted_photo = 1; // Mark as completed even if skipped
        $user->save();

        return redirect()->route('dashboard');
    }
}
