<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessoParoquial extends Model
{
    protected $table = 'processos_paroquiais';

    protected $fillable = [
        'paroquia_id',
        'register_id',
        'protocolo',
        'nome_solicitante',
        'cargo_funcao',
        'comunidade_id',
        'comunidade_outro',
        'assunto',
        'descricao',
        'data_limite',
        'telefone',
        'email',
        'prioridade',
        'status',
        'responsavel_atual_user_id',
    ];

    protected $casts = [
        'data_limite' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // ── Relationships ───────────────────────────────────────────────────────

    public function arquivos()
    {
        return $this->hasMany(ProcessoArquivo::class, 'processo_id');
    }

    public function tramitacoes()
    {
        return $this->hasMany(ProcessoTramitacao::class, 'processo_id')->orderBy('created_at', 'asc');
    }

    public function ultimaTramitacao()
    {
        return $this->hasOne(ProcessoTramitacao::class, 'processo_id')->latestOfMany();
    }

    public function responsavelAtual()
    {
        return $this->belongsTo(User::class, 'responsavel_atual_user_id');
    }

    // ── Accessors ───────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
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
        return match($this->status) {
            0 => 'bg-warning text-dark',
            1 => 'bg-primary text-white',
            2 => 'bg-success text-white',
            3 => 'bg-success text-white',
            4 => 'bg-danger text-white',
            default => 'bg-secondary text-white',
        };
    }

    public function getPrioridadeLabelAttribute(): string
    {
        return match($this->prioridade) {
            1 => 'Baixa',
            2 => 'Normal',
            3 => 'Alta',
            4 => 'Urgente',
            default => 'Normal',
        };
    }

    public function getPrioridadeBadgeClassAttribute(): string
    {
        return match($this->prioridade) {
            1 => 'bg-secondary text-white',
            2 => 'bg-info text-dark',
            3 => 'bg-warning text-dark',
            4 => 'bg-danger text-white',
            default => 'bg-info text-dark',
        };
    }

    public function getAssuntoLabelAttribute(): string
    {
        return match($this->assunto) {
            'pascom'      => 'PASCOM',
            'compra'      => 'Compra',
            'autorizacao' => 'Autorização',
            'oficio'      => 'Ofício',
            'manutencao'  => 'Manutenção',
            'outro'       => 'Outro',
            default       => ucfirst($this->assunto),
        };
    }

    public function getAssuntoBadgeClassAttribute(): string
    {
        return match($this->assunto) {
            'pascom'      => 'bg-info text-dark',
            'compra'      => 'bg-success text-white',
            'autorizacao' => 'bg-warning text-dark',
            'oficio'      => 'bg-primary text-white',
            'manutencao'  => 'bg-orange text-dark',
            'outro'       => 'bg-secondary text-white',
            default       => 'bg-secondary text-white',
        };
    }

    // Gera número de protocolo único
    public static function gerarProtocolo(): string
    {
        $ano = now()->format('Y');
        $mes = now()->format('m');
        $count = static::whereYear('created_at', $ano)->whereMonth('created_at', $mes)->count() + 1;
        return 'PROC-' . $ano . $mes . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
