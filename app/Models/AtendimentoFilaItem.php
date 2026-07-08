<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtendimentoFilaItem extends Model
{
    use HasFactory;

    protected $table = 'atendimento_fila_itens';

    protected $fillable = [
        'fila_id',
        'register_id',
        'nome',
        'assunto',
        'hora_agendada',
        'tipo',
        'status',
        'telefone',
        'whatsapp_enviado',
    ];

    protected $casts = [
        'fila_id'         => 'integer',
        'register_id'     => 'integer',
        'tipo'            => 'integer',
        'status'          => 'integer',
        'whatsapp_enviado' => 'boolean',
    ];

    // Tipo constants
    const TIPO_WALKIN   = 0;
    const TIPO_AGENDADO = 1;

    // Status constants
    const STATUS_AGUARDANDO     = 0;
    const STATUS_EM_ATENDIMENTO = 1;
    const STATUS_ATENDIDO       = 2;
    const STATUS_AUSENTE        = 3;

    public function fila()
    {
        return $this->belongsTo(AtendimentoFila::class, 'fila_id');
    }

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AGUARDANDO     => 'Aguardando',
            self::STATUS_EM_ATENDIMENTO => 'Em atendimento',
            self::STATUS_ATENDIDO       => 'Atendido',
            self::STATUS_AUSENTE        => 'Ausente',
            default                     => 'Desconhecido',
        };
    }

    public function getTipoLabelAttribute(): string
    {
        return $this->tipo === self::TIPO_AGENDADO ? 'Agendado' : 'Walk-in';
    }

    public function isAgendado(): bool
    {
        return $this->tipo === self::TIPO_AGENDADO;
    }

    public function isWalkin(): bool
    {
        return $this->tipo === self::TIPO_WALKIN;
    }
}
