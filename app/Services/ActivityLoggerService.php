<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ActivityLoggerService
{
    /**
     * Chaves que nunca devem ser persistidas nos logs por motivos de segurança e LGPD.
     */
    protected const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'secret',
        'api_key',
        'hash',
        'cartao',
        'cvv',
        'cartao_credito',
        'access_token',
    ];

    /**
     * ID único por ciclo de requisição para correlação de eventos.
     */
    protected static ?string $requestId = null;

    /**
     * Obtém ou inicializa o UUID da requisição atual.
     */
    public static function getRequestId(): string
    {
        if (static::$requestId === null) {
            static::$requestId = (string) Str::uuid();
        }
        return static::$requestId;
    }

    /**
     * Gravação genérica de log com suporte a sanitização, integridade transacional e fail-safe.
     *
     * Modos transacionais suportados:
     * - 'atomic': grava DENTRO da transação aberta. Garante atomicidade rigorosa: se a transação der rollback,
     *             o log é desfeito. Se a gravação do log falhar, lança AuditLogPersistenceException para abortar
     *             a operação crítica (ideal para mudanças de permissões críticas e movimentações financeiras sensíveis).
     * - 'after_commit' (ou true): aguarda a conclusão da transação para persistir. Impede registro de sucesso em rollback.
     *                             Em caso de falha pós-commit, registra incidente em Log::critical.
     * - 'fail_safe' (ou false): persistência imediata com captura total de erros, indicada para navegação e eventos não críticos.
     *
     * @param array $data Dados do log
     * @param bool|string $transactionalMode 'atomic', 'after_commit' (true) ou 'fail_safe' (false)
     * @return ActivityLog|null
     */
    public function log(array $data, bool|string $transactionalMode = false): ?ActivityLog
    {
        // 1. Modo Atômico (Same-Transaction): o log faz parte da transação aberta
        if ($transactionalMode === 'atomic') {
            return $this->persistLog($data, strict: true, isCritical: true);
        }

        // 2. Modo Pós-Commit: aguarda commit da transação aberta (evita falso positivo de sucesso em rollback)
        $isAfterCommit = ($transactionalMode === true || $transactionalMode === 'after_commit');
        if ($isAfterCommit && DB::transactionLevel() > 0) {
            DB::afterCommit(function () use ($data) {
                $this->persistLog($data, strict: false, isCritical: true);
            });
            return null;
        }

        // 3. Modo Padrão / Fail-Safe
        return $this->persistLog($data, strict: false, isCritical: false);
    }

    /**
     * Persistência efetiva do log no banco de dados com tratamento de falhas e canal de emergência.
     */
    protected function persistLog(array $data, bool $strict, bool $isCritical = false): ?ActivityLog
    {
        try {
            /** @var User|null $user */
            $user = Auth::user();

            $userId = $data['user_id'] ?? ($user?->id ?? null);
            $userName = $data['user_name'] ?? ($user?->name ?? ($user?->user ?? null));
            $paroquiaId = array_key_exists('paroquia_id', $data) 
                ? $data['paroquia_id'] 
                : ($user?->paroquia_id ?? null);

            $payload = [
                'paroquia_id'    => $paroquiaId,
                'user_id'        => $userId,
                'user_name'      => $userName ? Str::limit($userName, 100) : null,
                'category'       => $data['category'] ?? 'action',
                'action'         => $data['action'] ?? 'generic',
                'module'         => Str::limit($data['module'] ?? 'Geral', 60),
                'description'    => Str::limit($data['description'] ?? '', 255),
                'auditable_type' => $data['auditable_type'] ?? null,
                'auditable_id'   => $data['auditable_id'] ?? null,
                'old_values'     => $this->sanitizeValues($data['old_values'] ?? null),
                'new_values'     => $this->sanitizeValues($data['new_values'] ?? null),
                'url'            => Str::limit($data['url'] ?? (app()->runningInConsole() ? 'CLI' : request()->fullUrl()), 255),
                'method'         => Str::limit($data['method'] ?? (app()->runningInConsole() ? 'CLI' : request()->method()), 10),
                'ip_address'     => $data['ip_address'] ?? (app()->runningInConsole() ? '127.0.0.1' : request()->ip()),
                'user_agent'     => Str::limit($data['user_agent'] ?? (app()->runningInConsole() ? 'Console' : request()->userAgent()), 255),
                'status'         => $data['status'] ?? 'success',
                'source'         => $data['source'] ?? (app()->runningInConsole() ? 'system' : 'web'),
                'request_id'     => static::getRequestId(),
                'created_at'     => now(),
            ];

            return ActivityLog::create($payload);

        } catch (\Throwable $e) {
            $sanitizedData = array_diff_key($data, array_flip(self::SENSITIVE_KEYS));

            if ($isCritical || $strict) {
                // Registra em canal de emergência crítico para garantia de rastreabilidade forense
                Log::critical('[AUDIT CRITICAL FAILURE] Falha ao persistir auditoria de segurança/financeira: ' . $e->getMessage(), [
                    'request_id' => static::getRequestId(),
                    'error'      => $e->getMessage(),
                    'trace'      => $e->getTraceAsString(),
                    'data'       => $sanitizedData,
                ]);

                if ($strict) {
                    throw new \App\Exceptions\AuditLogPersistenceException(
                        'Falha crítica ao gravar log de auditoria atômica: ' . $e->getMessage(),
                        0,
                        $e
                    );
                }
            } else {
                Log::error('[ActivityLoggerService] Falha ao persistir log de auditoria: ' . $e->getMessage(), [
                    'request_id' => static::getRequestId(),
                    'data'       => $sanitizedData,
                ]);
            }

            return null;
        }
    }

    /**
     * Atalho para registrar eventos de autenticação (Login, Logout, Tentativa com erro).
     */
    public function logAuth(
        string $action,
        string $description,
        ?User $user = null,
        string $status = 'success',
        ?int $paroquiaId = null,
        ?string $attemptedUser = null
    ): ?ActivityLog {
        return $this->log([
            'category'    => 'auth',
            'action'      => $action,
            'module'      => 'Autenticação',
            'description' => $description,
            'user_id'     => $user?->id ?? null,
            'user_name'   => $user?->name ?? ($user?->user ?? $attemptedUser),
            'paroquia_id' => $paroquiaId ?? $user?->paroquia_id,
            'status'      => $status,
            'source'      => 'web',
        ], true); // Autenticação usa consistência estrita
    }

    /**
     * Atalho para registrar navegação (visitas de páginas).
     */
    public function logNavigation(string $module, string $description, ?string $url = null): ?ActivityLog
    {
        return $this->log([
            'category'    => 'navigation',
            'action'      => 'page_view',
            'module'      => $module,
            'description' => $description,
            'url'         => $url ?? request()->fullUrl(),
            'status'      => 'success',
            'source'      => 'web',
        ], false); // Navegação é puramente informativa (fail-safe)
    }

    /**
     * Atalho para registrar alterações em Models (Created, Updated, Deleted).
     */
    public function logModelChange(
        Model $model,
        string $action,
        string $module,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        bool|string $transactionalMode = true
    ): ?ActivityLog {
        $paroquiaId = $model->paroquia_id ?? (Auth::user()?->paroquia_id ?? null);

        return $this->log([
            'category'       => 'model',
            'action'         => $action,
            'module'         => $module,
            'description'    => $description,
            'auditable_type' => get_class($model),
            'auditable_id'   => $model->getKey(),
            'paroquia_id'    => $paroquiaId,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'status'         => 'success',
        ], $transactionalMode);
    }

    /**
     * Atalho para registrar interações relevantes do usuário (botões e modais).
     */
    public function logInteraction(
        string $module,
        string $action,
        string $description,
        string $status = 'success'
    ): ?ActivityLog {
        return $this->log([
            'category'    => 'action',
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'status'      => $status,
            'source'      => 'frontend',
        ], false);
    }

    /**
     * Atalho para registrar downloads e emissão de documentos/relatórios.
     */
    public function logExport(
        string $module,
        string $documentType,
        string $description,
        ?int $recordId = null,
        ?string $auditableType = null
    ): ?ActivityLog {
        return $this->log([
            'category'       => 'export',
            'action'         => 'export_' . Str::slug($documentType, '_'),
            'module'         => $module,
            'description'    => $description,
            'auditable_id'   => $recordId,
            'auditable_type' => $auditableType,
            'status'         => 'success',
        ], true);
    }

    /**
     * Sanitiza arrays de dados removendo chaves sensíveis e valores nulos ou vazios desnecessários.
     */
    public function sanitizeValues(?array $values): ?array
    {
        if (empty($values)) {
            return null;
        }

        $sanitized = [];
        foreach ($values as $key => $val) {
            $lowerKey = strtolower((string) $key);

            // Verifica se a chave é sensível
            $isSensitive = false;
            foreach (self::SENSITIVE_KEYS as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '***[DADO CONFIDENCIAL PROTEGIDO]***';
                continue;
            }

            // Sanitiza strings muito longas para economizar espaço
            if (is_string($val) && strlen($val) > 500) {
                $sanitized[$key] = Str::limit($val, 500) . ' [TRUNCADO]';
            } else {
                $sanitized[$key] = $val;
            }
        }

        return !empty($sanitized) ? $sanitized : null;
    }
}
