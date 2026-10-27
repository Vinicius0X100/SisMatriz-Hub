<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class FestaEventoEntrada extends Model
{
    use LogsActivity;

    protected string $auditModuleName = 'Eventos e Festas - Financeiro';

    public function getAuditableAttributes(): array
    {
        return [
            'festa_evento_id',
            'valor',
            'descricao',
            'data',
            'user_id',
        ];
    }

    protected $table = 'festas_eventos_entradas';
    public $timestamps = false;

    protected $fillable = [
        'festa_evento_id',
        'valor',
        'descricao',
        'data',
        'user_id',
        'criado_em',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
    ];

    public function festa()
    {
        return $this->belongsTo(FestaEvento::class, 'festa_evento_id');
    }
}

