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

        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = $user->id . '.png';

            // Upload to remote server via SFTP (Port 22)
            // Ensure FILESYSTEM_DISK is set to 'sftp' in .env or use 'sftp' disk explicitly
            Storage::disk('sftp')->putFileAs('', $file, $filename);

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
