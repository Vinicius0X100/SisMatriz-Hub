<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ParoquiaSuperadmin;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class ActivityLogController extends Controller
{
    public function __construct(
        protected ActivityLoggerService $logger
    ) {}

    /**
     * Lista de logs de atividades com filtros e proteção multi-tenant estrita.
     */
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        // 1. Controle de Acesso Restrito: Apenas Roles 1 (Administrador Geral) e 111 (Superadmin)
        if (!$currentUser->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso restrito à administração geral.');
        }

        $query = ActivityLog::query();

        // 2. Isolamento Multi-Tenancy
        $isGlobalAdmin = $currentUser->hasRole('111');
        $selectedParoquia = $request->input('paroquia_id');

        if ($isGlobalAdmin) {
            // Role 111 pode consultar globalmente de forma explícita ('all') ou filtrar por paróquia específica
            if ($selectedParoquia && $selectedParoquia !== 'all') {
                $query->where('paroquia_id', (int) $selectedParoquia);
            } elseif (!$selectedParoquia) {
                // Por padrão, escopa na paróquia atual do usuário se não especificado 'all'
                $query->where('paroquia_id', $currentUser->paroquia_id);
            }
        } else {
            // Role 1: OBRIGATORIAMENTE vê apenas sua própria paróquia.
            // Ignora qualquer tentativa de manipulação via parâmetro paroquia_id
            $query->where('paroquia_id', $currentUser->paroquia_id);
        }

        // 3. Filtros
        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }

        if ($request->filled('end_date')) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // 4. Estatísticas Rápidas do Escopo Atual (para os cards de topo)
        $statsBase = clone $query;
        $stats = [
            'total'      => (clone $statsBase)->count(),
            'auth'       => (clone $statsBase)->where('category', 'auth')->count(),
            'model'      => (clone $statsBase)->where('category', 'model')->count(),
            'navigation' => (clone $statsBase)->where('category', 'navigation')->count(),
            'failures'   => (clone $statsBase)->whereIn('status', ['failed', 'blocked'])->count(),
        ];

        // 5. Paginação eficiente
        $logs = $query->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // 6. Dados auxiliares para os selects de filtro
        $usersQuery = User::query();
        if (!$isGlobalAdmin || ($selectedParoquia && $selectedParoquia !== 'all')) {
            $usersQuery->where('paroquia_id', $isGlobalAdmin ? (int) $selectedParoquia : $currentUser->paroquia_id);
        }
        $users = $usersQuery->orderBy('name')->get(['id', 'name', 'user']);

        $modules = ActivityLog::select('module')
            ->when(!$isGlobalAdmin || ($selectedParoquia && $selectedParoquia !== 'all'), function ($q) use ($currentUser, $selectedParoquia, $isGlobalAdmin) {
                $q->where('paroquia_id', $isGlobalAdmin ? (int) $selectedParoquia : $currentUser->paroquia_id);
            })
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $paroquias = $isGlobalAdmin ? ParoquiaSuperadmin::orderBy('name')->get(['id', 'name']) : collect([]);

        return view('modules.activity-logs.index', compact(
            'logs',
            'stats',
            'users',
            'modules',
            'paroquias',
            'isGlobalAdmin',
            'selectedParoquia'
        ));
    }

    /**
     * Detalhes de um log específico (retorna JSON para visualização em modal).
     */
    public function show($id)
    {
        /** @var User $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->hasAnyRole(['1', '111'])) {
            abort(403, 'Acesso não autorizado.');
        }

        $query = ActivityLog::with(['user', 'paroquia']);

        // Se for Role 1, garante que não pode abrir por ID um log de outra paróquia
        if (!$currentUser->hasRole('111')) {
            $query->where('paroquia_id', $currentUser->paroquia_id);
        }

        $log = $query->findOrFail($id);

        return response()->json([
            'id'             => $log->id,
            'user_name'      => $log->user_name ?? ($log->user?->name ?? 'Sistema'),
            'user_login'     => $log->user?->user ?? '—',
            'paroquia_name'  => $log->paroquia?->name ?? 'Geral / Sem Vínculo',
            'category'       => $log->category,
            'category_label' => $log->category_label,
            'action'         => $log->action,
            'action_badge'   => $log->action_badge_class,
            'module'         => $log->module,
            'description'    => $log->description,
            'auditable_type' => $log->auditable_type ? class_basename($log->auditable_type) : null,
            'auditable_id'   => $log->auditable_id,
            'old_values'     => $this->logger->sanitizeValues($log->old_values),
            'new_values'     => $this->logger->sanitizeValues($log->new_values),
            'url'            => $log->url,
            'method'         => $log->method,
            'ip_address'     => $log->ip_address,
            'user_agent'     => $log->user_agent,
            'status'         => $log->status,
            'status_label'   => $log->status_label,
            'status_badge'   => $log->status_badge_class,
            'source'         => $log->source,
            'request_id'     => $log->request_id,
            'created_at'     => $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '—',
        ]);
    }

    /**
     * Endpoint protegido para registrar interações importantes disparadas pelo frontend.
     */
    public function recordInteraction(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        // Rate limiting por usuário (máximo 60 requisições por minuto)
        $userId = Auth::id();
        $rateKey = 'audit_interaction_' . $userId;
        if (RateLimiter::tooManyAttempts($rateKey, 60)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return response()->json([
                'error' => 'Limite de interações excedido. Tente novamente em alguns segundos.',
                'retry_after' => $seconds,
            ], 429);
        }
        RateLimiter::hit($rateKey, 60);

        // Whitelist rigorosa de ações de interface administrativas permitidas
        $allowedActions = [
            'modal_aberto',
            'exportacao_solicitada',
            'confirmacao_critica',
            'filtro_avancado_aplicado',
            'impressao_iniciada',
            'aba_alternada',
            'exclusao_lote_iniciada',
            'download_relatorio',
            'status_alterado',
        ];

        $request->validate([
            'action'      => ['required', 'string', 'in:' . implode(',', $allowedActions)],
            'module'      => ['required', 'string', 'max:60'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $this->logger->logInteraction(
            module: strip_tags($request->input('module')),
            action: $request->input('action'),
            description: strip_tags($request->input('description')),
            status: 'success'
        );

        return response()->json(['success' => true]);
    }
}
