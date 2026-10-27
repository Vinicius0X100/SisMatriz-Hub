<?php

namespace App\Listeners;

use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(
        protected ActivityLoggerService $logger
    ) {}

    public function handle(Login $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        $this->logger->logAuth(
            action: 'login',
            description: "Usuário '{$user->name}' ({$user->user}) realizou login com sucesso.",
            user: $user,
            status: 'success',
            paroquiaId: $user->paroquia_id
        );
    }
}
