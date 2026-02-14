<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Permitir logout sempre
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // Permitir checagem de lembretes (JSON) mesmo durante onboarding
        if ($request->routeIs('lembretes.check')) {
            return $next($request);
        }

        // 1. Verificar Troca de Senha
        if ($user->is_pass_change == 0) {
            if ($request->routeIs('setup.password') || $request->routeIs('setup.password.update')) {
                return $next($request);
            }
            return redirect()->route('setup.password');
        }

        // 2. Verificar Boas Vindas / Foto
        if ($user->accepted_photo == 0) {
            if ($request->routeIs('setup.welcome') || $request->routeIs('setup.welcome.update') || $request->routeIs('setup.welcome.skip')) {
                return $next($request);
            }
            return redirect()->route('setup.welcome');
        }

        // 3. Se tudo estiver ok, mas tentar acessar páginas de setup, redirecionar para dashboard
        if ($request->routeIs('setup.*')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
