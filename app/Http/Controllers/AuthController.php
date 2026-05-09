<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private function isAllowedRedirect(?string $redirectTo): bool
    {
        if (!$redirectTo) {
            return false;
        }

        $redirectTo = trim($redirectTo);
        if ($redirectTo === '') {
            return false;
        }

        $parts = parse_url($redirectTo);
        if (!is_array($parts) || empty($parts['scheme'])) {
            return false;
        }

        $allowedSchemes = ['sismatriz'];
        return in_array(strtolower((string) $parts['scheme']), $allowedSchemes, true);
    }

    public function showLogin(Request $request)
    {
        $redirectTo = $request->string('redirect_to')->toString();

        if (Auth::check()) {
            if ($this->isAllowedRedirect($redirectTo)) {
                return redirect()->away($redirectTo);
            }
            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'redirect_to' => $this->isAllowedRedirect($redirectTo) ? $redirectTo : null,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'user' => ['required'],
            'password' => ['required'],
        ]);

        $redirectTo = $request->string('redirect_to')->toString();

        // "login ele tem que ser salvo em cookies por 15 dias"
        // Passing true as the second argument enables "Remember Me"
        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();

            if ($this->isAllowedRedirect($redirectTo)) {
                return redirect()->away($redirectTo);
            }

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'user' => 'As credenciais fornecidas estão incorretas.',
        ])->onlyInput('user');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
