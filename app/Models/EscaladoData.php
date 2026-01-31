<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EscaladoData extends Model
{
    protected $table = 'escalados_datas';
    protected $primaryKey = 'cal_id';
    public $timestamps = false;

    protected $fillable = [
        'd_id',
        'escala_id',
        'acolito_id',
        'funcao_id'
    ];

    public function escalaDataHora()
    {
        return $this->belongsTo(EscalaDataHora::class, 'd_id', 'd_id');
    }

    public function acolito()
    {
        return $this->belongsTo(Acolito::class, 'acolito_id');
    }

    public function funcao()
    {
        return $this->belongsTo(AcolitoFuncao::class, 'funcao_id', 'f_id');
    }
}
