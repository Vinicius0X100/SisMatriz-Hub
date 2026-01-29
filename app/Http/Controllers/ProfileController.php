<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists('uploads/avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('uploads/avatars/' . $user->avatar);
            }

            $file = $request->file('avatar');
            // Use ID + timestamp to avoid caching issues and conflicts
            $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store in storage/app/public/uploads/avatars
            Storage::disk('public')->putFileAs('uploads/avatars', $file, $filename);
            
            $user->avatar = $filename;
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Perfil atualizado com sucesso!');
    }
}
