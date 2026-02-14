<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCampaignAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->hasAnyRole(['1', '111', '11'])) {
             abort(403, 'Acesso não autorizado. Apenas administradores e financeiro (tesoureiros) podem acessar este módulo.');
        }

        return $next($request);
    }
}
