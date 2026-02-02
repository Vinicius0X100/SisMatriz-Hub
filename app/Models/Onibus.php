<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Onibus extends Model
{
    protected $table = 'onibus';

    protected $fillable = [
        'paroquia_id',
        'excursao_id',
        'numero',
        'responsavel',
        'telefone_responsavel',
        'local_saida',
        'horario_saida',
        'horario_retorno',
        'capacidade',
        'ativo',
    ];

    protected $casts = [
        'horario_saida' => 'datetime',
        'horario_retorno' => 'datetime',
        'ativo' => 'boolean',
    ];

    public function excursao()
    {
        return $this->belongsTo(Excursao::class, 'excursao_id');
    }

    public function assentosVendidos()
    {
        return $this->hasMany(AssentoVendido::class, 'onibus_id');
    }
}
