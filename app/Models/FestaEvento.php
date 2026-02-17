<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Entidade;
use App\Models\FestaEventoEntrada;
use App\Models\FestaEventoSaida;
use App\Models\FestaEventoItemEntrada;
use App\Models\FestaEventoItemSaida;

class FestaEvento extends Model
{
    protected $table = 'festas_eventos';
    public $timestamps = false;

    protected $fillable = [
        'titulo',
        'data_inicio',
        'data_fim',
        'comunidade_id',
        'descricao',
        'meta',
        'paroquia_id',
        'criado_em',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'meta' => 'decimal:2',
    ];

    public function comunidade()
    {
        return $this->belongsTo(Entidade::class, 'comunidade_id', 'ent_id');
    }

    public function entradas()
    {
        return $this->hasMany(FestaEventoEntrada::class, 'festa_evento_id');
    }

    public function saidas()
    {
        return $this->hasMany(FestaEventoSaida::class, 'festa_evento_id');
    }

    public function itensEntradas()
    {
        return $this->hasMany(FestaEventoItemEntrada::class, 'festa_evento_id');
    }

    public function itensSaidas()
    {
        return $this->hasMany(FestaEventoItemSaida::class, 'festa_evento_id');
    }
}
