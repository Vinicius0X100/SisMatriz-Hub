<?php

namespace App\Http\Middleware;

use App\Services\ActivityLoggerService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogActivityNavigation
{
    /**
     * Rotas e caminhos que são polling, heartbeats ou chamadas técnicas e DEVEM ser ignoradas.
     */
    protected const IGNORED_ROUTES = [
        'dashboard.ping',
        'dashboard.online-users',
        'dashboard.toggle-pin',
        'dashboard.reorder-pins',
        'dashboard.update-pin-style',
        'lembretes.check',
        'chat.messages',
        'chat.users',
        'chat.requests',
        'registers.check-phone',
        'registers.search',
        'pascom-membros.search-registers',
        'processos.users-search',
        'processos.users-by-grupo',
        'activity-logs.interaction',
    ];

    protected const IGNORED_PATHS = [
        'up',
        'api/*',
        'sanctum/*',
        'livewire/*',
        '_debugbar/*',
    ];

    public function __construct(
        protected ActivityLoggerService $logger
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Apenas para usuários autenticados
        if (!Auth::check()) {
            return $response;
        }

        // Apenas requisições GET
        if (!$request->isMethod('GET')) {
            return $response;
        }

        // Apenas respostas de sucesso (HTTP 200). Ignora redirecionamentos (301, 302) e erros (403, 404, 500)
        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        // Ignora requisições AJAX / XMLHttpRequest que não representam navegação de página completa
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        // Checagem de rota ignorada
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, self::IGNORED_ROUTES, true)) {
            return $response;
        }

        // Checagem de paths ignorados
        foreach (self::IGNORED_PATHS as $pattern) {
            if ($request->is($pattern)) {
                return $response;
            }
        }

        $path = '/' . trim($request->path(), '/');

        // Deduplicação ultra-eficiente via Sessão (sem usar o banco de dados de cache)
        // Evita gravar múltiplos acessos caso o usuário recarregue (F5) repetidamente
        $sessionKey = 'last_nav_log';
        $lastNav = session()->get($sessionKey, []);
        $now = time();

        if (
            isset($lastNav['path'], $lastNav['time']) &&
            $lastNav['path'] === $path &&
            ($now - $lastNav['time']) < 60 // Janela de 60 segundos contra duplicatas
        ) {
            return $response;
        }

        // Atualiza a sessão com a última navegação
        session()->put($sessionKey, [
            'path' => $path,
            'time' => $now,
        ]);

        // Determina o nome do módulo a partir de config/modules.php ou da rota
        $moduleName = $this->resolveModuleName($request, $path);

        $description = "Visualizou a página do módulo '{$moduleName}' ({$path}).";

        $this->logger->logNavigation(
            module: $moduleName,
            description: $description,
            url: $request->fullUrl()
        );

        return $response;
    }

    /**
     * Resolve o nome legível do módulo acessado.
     */
    protected function resolveModuleName(Request $request, string $currentPath): string
    {
        if ($request->is('dashboard')) {
            return 'Início / Dashboard';
        }

        if ($request->is('activity-logs*')) {
            return 'Logs de Atividades';
        }

        $modules = config('modules', []);
        foreach ($modules as $module) {
            if (!empty($module['url'])) {
                $moduleUrl = '/' . trim($module['url'], '/');
                if ($currentPath === $moduleUrl || Str::startsWith($currentPath, $moduleUrl . '/')) {
                    return $module['name'] ?? 'Módulo';
                }
            }
        }

        // Fallback para o nome da rota formatado
        $routeName = $request->route()?->getName();
        if ($routeName) {
            return ucfirst(explode('.', $routeName)[0]);
        }

        return 'Geral';
    }
}
