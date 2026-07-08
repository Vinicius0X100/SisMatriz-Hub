<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AtendimentoFila extends Model
{
    use HasFactory;

    protected $table = 'atendimento_filas';

    protected $fillable = [
        'paroquia_id',
        'data',
        'status',
        'created_by',
    ];

    protected $casts = [
        'data'       => 'date',
        'status'     => 'integer',
        'paroquia_id' => 'integer',
        'created_by' => 'integer',
    ];

    // Status constants
    const STATUS_AGUARDANDO = 0;
    const STATUS_ATIVA      = 1;
    const STATUS_ENCERRADA  = 2;

    public function itens()
    {
        return $this->hasMany(AtendimentoFilaItem::class, 'fila_id');
    }

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Retorna os itens da fila ordenados pela regra de prioridade:
     *  1. Agendados (tipo=1) ordenados por hora_agendada ASC
     *  2. Walk-ins  (tipo=0) ordenados por created_at ASC
     */
    public function itensOrdenados()
    {
        return $this->itens()
            ->whereIn('status', [AtendimentoFilaItem::STATUS_AGUARDANDO, AtendimentoFilaItem::STATUS_EM_ATENDIMENTO])
            ->orderByRaw('tipo DESC') // tipo 1 (Agendado) vem antes do tipo 0 (Walk-in)
            ->orderByRaw('CASE WHEN tipo = 1 THEN hora_agendada ELSE created_at END ASC');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AGUARDANDO => 'Aguardando abertura',
            self::STATUS_ATIVA      => 'Ativa',
            self::STATUS_ENCERRADA  => 'Encerrada',
            default                 => 'Desconhecido',
        };
    }
}
