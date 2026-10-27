<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção lançada quando uma auditoria crítica atômica falha ao ser persistida,
 * impedindo que operações críticas prossigam sem o devido registro de auditoria.
 */
class AuditLogPersistenceException extends RuntimeException
{
    public function __construct(string $message = 'Falha crítica ao gravar log de auditoria atômica.', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
