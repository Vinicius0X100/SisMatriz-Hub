<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoTaxaConfig extends Model
{
    use HasFactory;

    protected $table = 'inscricao_taxas_config';

    protected $fillable = [
        'paroquia_id',
        'tipo',
        'inscricao_com_taxa',
        'metodo_pagamento_label',
        'metodo_pagamento_valor',
    ];

    protected $casts = [
        'inscricao_com_taxa' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(InscricaoTaxaItem::class, 'config_id');
    }
}
