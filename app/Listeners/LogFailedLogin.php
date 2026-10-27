<?php

namespace App\Listeners;

use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LogFailedLogin
{
    public function __construct(
        protected ActivityLoggerService $logger
    ) {}

    public function handle(Failed $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;
        $ip = request()->ip() ?? '127.0.0.1';

        // Sanitiza o identificador fornecido pelo atacante/usuário
        $rawUser = $event->credentials['user'] ?? ($event->credentials['email'] ?? 'desconhecido');
        $attemptedUser = Str::limit(strip_tags((string) $rawUser), 50);

        // Proteção contra ataques de força bruta que inundam o banco (Rate limiting em memória/cache por IP)
        $cacheKey = 'failed_login_log_limit_' . md5($ip . '_' . $attemptedUser);
        $attempts = (int) Cache::get($cacheKey, 0);

        if ($attempts >= 10) {
            // Limite atingido: registra apenas a cada 20 tentativas para mitigar DoS no banco
            if ($attempts % 20 !== 0) {
                Cache::put($cacheKey, $attempts + 1, now()->addMinutes(15));
                return;
            }
        }
        Cache::put($cacheKey, $attempts + 1, now()->addMinutes(15));

        // Se o usuário existe no banco, associamos seu ID e paróquia real.
        // Se NÃO existe, deixamos paroquia_id e user_id NULOS para não inventar contexto paroquial.
        $paroquiaId = $user ? $user->paroquia_id : null;
        $userId = $user ? $user->id : null;

        $description = $user
            ? "Tentativa de login malsucedida para a conta existente '{$user->name}' ({$user->user}). Senha incorreta."
            : "Tentativa de login com usuário inexistente no sistema: '{$attemptedUser}'.";

        $this->logger->log([
            'category'    => 'auth',
            'action'      => 'login_failed',
            'module'      => 'Autenticação',
            'description' => $description,
            'user_id'     => $userId,
            'user_name'   => $user ? $user->name : $attemptedUser,
            'paroquia_id' => $paroquiaId,
            'status'      => 'failed',
            'source'      => 'web',
            'ip_address'  => $ip,
        ], false);
    }
}
