<?php

namespace App\Traits;

use App\Services\ActivityLoggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

trait LogsActivity
{
    /**
     * Stash temporário dos valores anteriores antes de salvar.
     */
    protected array $auditPendingChanges = [];

    /**
     * Boot do Trait para registrar os listeners dos eventos Eloquent.
     */
    public static function bootLogsActivity(): void
    {
        // Ao criar um registro
        static::created(function (Model $model) {
            /** @var LogsActivity $model */
            $model->recordAuditCreated();
        });

        // Antes de atualizar: captura os valores originais e novos antes que o Eloquent execute o syncOriginal
        static::updating(function (Model $model) {
            /** @var LogsActivity $model */
            $model->captureAuditChanges();
        });

        // Após atualizar com sucesso
        static::updated(function (Model $model) {
            /** @var LogsActivity $model */
            $model->recordAuditUpdated();
        });

        // Ao excluir um registro
        static::deleted(function (Model $model) {
            /** @var LogsActivity $model */
            $model->recordAuditDeleted();
        });
    }

    /**
     * Lista branca de campos auditáveis permitidos para este Model.
     * Deve ser sobrescrita no Model ou definida na propriedade $auditableAttributes.
     * Por padrão (exclusão por padrão), retorna array vazio.
     *
     * @return array<string>
     */
    public function getAuditableAttributes(): array
    {
        if (property_exists($this, 'auditableAttributes') && is_array($this->auditableAttributes)) {
            return $this->auditableAttributes;
        }

        return [];
    }

    /**
     * Retorna o nome amigável do módulo associado a este Model.
     */
    public function getAuditModuleName(): string
    {
        if (property_exists($this, 'auditModuleName') && !empty($this->auditModuleName)) {
            return $this->auditModuleName;
        }

        return class_basename($this);
    }

    /**
     * Gera uma descrição humanizada para a ação de auditoria.
     */
    public function getAuditDescription(string $action): string
    {
        $label = match($action) {
            'create' => 'Cadastrou',
            'update' => 'Alterou',
            'delete' => 'Excluiu',
            default => ucfirst($action),
        };

        $identificador = $this->name ?? ($this->descricao ?? ($this->titulo ?? '#' . $this->getKey()));
        return "{$label} registro em {$this->getAuditModuleName()}: {$identificador}";
    }

    /**
     * Captura os atributos modificados antes do salvamento.
     */
    public function captureAuditChanges(): void
    {
        $allowed = $this->getAuditableAttributes();
        if (empty($allowed)) {
            return;
        }

        $dirty = $this->getDirty();
        $old = [];
        $new = [];

        foreach ($dirty as $key => $newValue) {
            // Apenas campos explicitamente autorizados na lista branca
            if (in_array($key, $allowed, true)) {
                $oldValue = $this->getOriginal($key);

                // Ignora se não houve alteração real (ex: float 100.00 vs string 100.00)
                if ((string)$oldValue !== (string)$newValue) {
                    $old[$key] = $oldValue;
                    $new[$key] = $newValue;
                }
            }
        }

        $this->auditPendingChanges = [
            'old' => $old,
            'new' => $new,
        ];
    }

    /**
     * Define a estratégia transacional de auditoria para o Model:
     * - 'atomic': persistência imediata na transação aberta com aborto/rollback em caso de falha.
     * - 'after_commit': persistência após a confirmação da transação (padrão).
     */
    public function getAuditTransactionalMode(): string
    {
        if (property_exists($this, 'auditTransactionalMode') && is_string($this->auditTransactionalMode)) {
            return $this->auditTransactionalMode;
        }

        return 'after_commit';
    }

    /**
     * Dispara a gravação da criação.
     */
    public function recordAuditCreated(): void
    {
        $allowed = $this->getAuditableAttributes();
        $attributes = $this->getAttributes();
        $new = [];

        if (!empty($allowed)) {
            foreach ($attributes as $key => $val) {
                if (in_array($key, $allowed, true)) {
                    $new[$key] = $val;
                }
            }
        }

        $service = App::make(ActivityLoggerService::class);
        $service->logModelChange(
            $this,
            'create',
            $this->getAuditModuleName(),
            $this->getAuditDescription('create'),
            null,
            !empty($new) ? $new : null,
            $this->getAuditTransactionalMode()
        );
    }

    /**
     * Dispara a gravação da atualização.
     */
    public function recordAuditUpdated(): void
    {
        if (empty($this->auditPendingChanges['old']) && empty($this->auditPendingChanges['new'])) {
            return;
        }

        $service = App::make(ActivityLoggerService::class);
        $service->logModelChange(
            $this,
            'update',
            $this->getAuditModuleName(),
            $this->getAuditDescription('update'),
            $this->auditPendingChanges['old'],
            $this->auditPendingChanges['new'],
            $this->getAuditTransactionalMode()
        );

        $this->auditPendingChanges = [];
    }

    /**
     * Dispara a gravação da exclusão.
     */
    public function recordAuditDeleted(): void
    {
        $allowed = $this->getAuditableAttributes();
        $attributes = $this->getOriginal();
        $old = [];

        if (!empty($allowed)) {
            foreach ($attributes as $key => $val) {
                if (in_array($key, $allowed, true)) {
                    $old[$key] = $val;
                }
            }
        }

        $service = App::make(ActivityLoggerService::class);
        $service->logModelChange(
            $this,
            'delete',
            $this->getAuditModuleName(),
            $this->getAuditDescription('delete'),
            !empty($old) ? $old : null,
            null,
            $this->getAuditTransactionalMode()
        );
    }
}
