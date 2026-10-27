<?php

namespace App\Listeners;

use App\Services\ActivityLoggerService;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function __construct(
        protected ActivityLoggerService $logger
    ) {}

    public function handle(Logout $event): void
    {
        /** @var \App\Models\User|null $user */
        $user = $event->user;

        if (!$user) {
            return;
        }

        $this->logger->logAuth(
            action: 'logout',
            description: "Usuário '{$user->name}' ({$user->user}) encerrou a sessão (logout).",
            user: $user,
            status: 'success',
            paroquiaId: $user->paroquia_id
        );
    }
}
