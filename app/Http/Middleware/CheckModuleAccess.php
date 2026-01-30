<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CheckModuleAccess
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
             return $next($request);
        }

        // Super Admin Bypass
        if ($user->hasAnyRole(['1', '111'])) {
            return $next($request);
        }

        $currentPath = '/' . trim($request->path(), '/');
        $modules = config('modules');

        foreach ($modules as $module) {
            if (isset($module['url']) && !empty($module['url'])) {
                // Normalize module URL
                $moduleUrl = '/' . trim($module['url'], '/');

                // Check if current path matches module URL (exact or sub-path)
                if ($currentPath === $moduleUrl || Str::startsWith($currentPath, $moduleUrl . '/')) {
                    
                    $allowedRoles = $module['allowed_roles'] ?? [];
                    
                    // Wildcard access
                    if (in_array('*', $allowedRoles)) {
                        return $next($request);
                    }

                    // Check user roles
                    if (!$user->hasAnyRole($allowedRoles)) {
                        abort(404);
                    }
                    
                    // If matched and allowed, proceed
                    return $next($request);
                }
            }
        }

        return $next($request);
    }
}
