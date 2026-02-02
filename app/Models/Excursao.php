<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Excursao extends Model
{
    protected $table = 'excursoes';

    protected $fillable = [
        'paroquia_id',
        'tipo',
        'destino',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
        'finalizada',
        'finalizada_at',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'status' => 'boolean',
        'finalizada' => 'boolean',
        'finalizada_at' => 'datetime',
    ];

    public function onibus()
    {
        return $this->hasMany(Onibus::class, 'excursao_id');
    }
}
