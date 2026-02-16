<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcolitoFalta extends Model
{
    protected $table = 'faltas_acolitos';
    public $timestamps = false;

    protected $fillable = [
        'acolito_id',
        'paroquia_id',
        'title',
        'data_aula',
        'status',
        'd_id',
        'grave',
    ];

    protected $casts = [
        'data_aula' => 'date',
        'status' => 'boolean',
        'grave' => 'boolean',
    ];

    public function acolito()
    {
        return $this->belongsTo(Acolito::class, 'acolito_id');
    }

    public function justificativa()
    {
        return $this->hasOne(AcolitoFaltaJustify::class, 'faltas_id');
    }
    
    public function escalaDataHora()
    {
        return $this->belongsTo(EscalaDataHora::class, 'd_id', 'd_id');
    }
}
