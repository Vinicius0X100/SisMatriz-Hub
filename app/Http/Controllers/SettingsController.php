<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    public function updatePrivacy(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'is_visible' => 'nullable|boolean',
            'hide_name' => 'nullable|boolean',
        ]);

        // Checkboxes return '1' or 'on' if checked, null otherwise. 
        // We need to handle explicit true/false logic.
        // Actually, better to send explicit boolean or handle presence.
        // If it's a form submit, unchecked checkboxes are not sent.
        // Let's assume the frontend sends explicit values or we handle the toggle.

        $user->is_visible = $request->has('is_visible');
        $user->hide_name = $request->has('hide_name');
        
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Configurações de privacidade atualizadas com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'A senha atual está incorreta.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('settings.index')->with('success', 'Senha alterada com sucesso!');
    }
}
