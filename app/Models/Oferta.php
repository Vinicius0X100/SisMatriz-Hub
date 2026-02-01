<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    protected $table = 'ofertas';
    
    const CREATED_AT = 'criado_em';
    
    protected $fillable = [
        'data',
        'horario',
        'valor_total',
        'tipo', // Celebracao
        'kind',
        'observacoes',
        'ent_id',
        'paroquia_id'
    ];

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id', 'id');
    }
}
