<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escala extends Model
{
    protected $table = 'escalas';
    protected $primaryKey = 'es_id';

    protected $fillable = [
        'month',
        'year',
        'church',
        'send_date',
        'qntd_acolitos',
        'situation',
        'paroquia_id'
    ];

    protected $casts = [
        'send_date' => 'date',
        'situation' => 'integer',
        'year' => 'integer',
        'qntd_acolitos' => 'integer'
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }

    public function escalados()
    {
        return $this->hasMany(EscaladoData::class, 'escala_id', 'es_id');
    }
}
