<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestaEventoEntrada extends Model
{
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

