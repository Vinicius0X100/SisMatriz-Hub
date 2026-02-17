<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FestaEventoItemSaida extends Model
{
    protected $table = 'festas_eventos_itens_saida';
    public $timestamps = false;

    protected $fillable = [
        'festa_evento_id',
        'item',
        'quantidade',
        'observacao',
        'data',
        'user_id',
        'criado_em',
    ];

    protected $casts = [
        'data' => 'date',
        'quantidade' => 'integer',
    ];

    public function festa()
    {
        return $this->belongsTo(FestaEvento::class, 'festa_evento_id');
    }
}

