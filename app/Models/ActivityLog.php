<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    /**
     * Tabela append-only: possui apenas created_at, sem updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'paroquia_id',
        'user_id',
        'user_name',
        'category',
        'action',
        'module',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'url',
        'method',
        'ip_address',
        'user_agent',
        'status',
        'source',
        'request_id',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    // ── Relacionamentos ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paroquia(): BelongsTo
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // ── Scopes de Consulta e Multi-Tenancy ────────────────────────────────────

    /**
     * Escopo obrigatório para multi-tenancy: restringe os logs à paróquia.
     */
    public function scopeForParoquia($query, $paroquiaId)
    {
        return $query->where('paroquia_id', $paroquiaId);
    }

    public function scopeCategory($query, ?string $category)
    {
        if (!empty($category)) {
            return $query->where('category', $category);
        }
        return $query;
    }

    public function scopeModule($query, ?string $module)
    {
        if (!empty($module)) {
            return $query->where('module', $module);
        }
        return $query;
    }

    public function scopeActionType($query, ?string $action)
    {
        if (!empty($action)) {
            return $query->where('action', $action);
        }
        return $query;
    }

    public function scopeStatus($query, ?string $status)
    {
        if (!empty($status)) {
            return $query->where('status', $status);
        }
        return $query;
    }

    public function scopeForUser($query, $userId)
    {
        if (!empty($userId)) {
            return $query->where('user_id', $userId);
        }
        return $query;
    }

    public function scopePeriod($query, ?string $start, ?string $end)
    {
        if (!empty($start)) {
            $query->where('created_at', '>=', $start . ' 00:00:00');
        }
        if (!empty($end)) {
            $query->where('created_at', '<=', $end . ' 23:59:59');
        }
        return $query;
    }

    public function scopeSearch($query, ?string $term)
    {
        if (!empty($term)) {
            return $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('user_name', 'like', "%{$term}%")
                  ->orWhere('module', 'like', "%{$term}%")
                  ->orWhere('ip_address', 'like', "%{$term}%");
            });
        }
        return $query;
    }

    // ── Accessors Visuais (Badges Bootstrap 5) ────────────────────────────────

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'auth' => 'Autenticação',
            'navigation' => 'Navegação',
            'model' => 'Registro',
            'action' => 'Ação do Usuário',
            'export' => 'Exportação / PDF',
            default => ucfirst($this->category),
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match($this->action) {
            'login' => 'bg-success text-white',
            'logout' => 'bg-secondary text-white',
            'login_failed' => 'bg-danger text-white',
            'create' => 'bg-success text-white',
            'update' => 'bg-primary text-white',
            'delete', 'bulk_delete' => 'bg-danger text-white',
            'export_pdf', 'export_excel' => 'bg-info text-dark',
            'page_view' => 'bg-light text-dark border',
            'interaction' => 'bg-warning text-dark',
            default => 'bg-secondary text-white',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'success' => 'bg-success-subtle text-success border border-success-subtle',
            'failed' => 'bg-danger-subtle text-danger border border-danger-subtle',
            'blocked' => 'bg-warning-subtle text-warning border border-warning-subtle',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'success' => 'Sucesso',
            'failed' => 'Falha',
            'blocked' => 'Bloqueado',
            default => ucfirst($this->status),
        };
    }
}
