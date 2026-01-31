<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscalaDataHora extends Model
{
    protected $table = 'escalas_datas_horas';
    protected $primaryKey = 'd_id';
    public $timestamps = false;

    protected $fillable = [
        'es_id',
        'data',
        'dia',
        'celebration',
        'hora',
        'ent_id'
    ];

    public function escala()
    {
        return $this->belongsTo(Escala::class, 'es_id', 'es_id');
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function escalados()
    {
        return $this->hasMany(EscaladoData::class, 'd_id', 'd_id');
    }
}
