<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcolitoRegraComposicao extends Model
{
    protected $table = 'acolitos_regras_composicao';

    protected $fillable = [
        'paroquia_id',
        'acolito_id',
        'tipo_regra',
        'configuracao',
        'ativo'
    ];

    protected $casts = [
        'configuracao' => 'array',
        'ativo' => 'boolean',
    ];

    public function acolito()
    {
        return $this->belongsTo(Acolito::class, 'acolito_id');
    }

    public function paroquia()
    {
        return $this->belongsTo(Paroquia::class, 'paroquia_id');
    }
}
