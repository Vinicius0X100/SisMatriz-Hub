<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessoTramitacao extends Model
{
    protected $table = 'processos_tramitacoes';

    protected $fillable = [
        'processo_id',
        'paroquia_id',
        'de_user_id',
        'de_cargo_label',
        'para_user_id',
        'para_grupo',
        'descricao',
        'status_processo',
        'mencao_tramitacao_id',
        'tipo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────────────────────

    public function processo()
    {
        return $this->belongsTo(ProcessoParoquial::class, 'processo_id');
    }

    public function deUser()
    {
        return $this->belongsTo(User::class, 'de_user_id');
    }

    public function paraUser()
    {
        return $this->belongsTo(User::class, 'para_user_id');
    }

    public function arquivos()
    {
        return $this->hasMany(ProcessoTramitacaoArquivo::class, 'tramitacao_id');
    }

    public function mencao()
    {
        return $this->belongsTo(ProcessoTramitacao::class, 'mencao_tramitacao_id');
    }

    // ── Accessors ───────────────────────────────────────────────────────────

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            1 => 'Abertura',
            2 => 'Menção',
            default => 'Tramitação',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status_processo) {
            0 => 'Pendente',
            1 => 'Em Processo',
            2 => 'Finalizado',
            3 => 'Concluído',
            4 => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status_processo) {
            0 => 'bg-warning text-dark',
            1 => 'bg-primary text-white',
            2 => 'bg-success text-white',
            3 => 'bg-success text-white',
            4 => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getParaLabelAttribute(): string
    {
        if ($this->paraUser) {
            return $this->paraUser->name ?? $this->paraUser->user;
        }
        if ($this->para_grupo) {
            $grupos = \App\Http\Controllers\ProcessoController::GRUPOS_PASTORAIS;
            return $grupos[$this->para_grupo]['label'] ?? ucfirst($this->para_grupo);
        }
        return '—';
    }
}
