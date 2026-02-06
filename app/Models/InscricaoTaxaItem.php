<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoTaxaItem extends Model
{
    use HasFactory;

    protected $table = 'inscricao_taxa_items';

    protected $fillable = [
        'config_id',
        'nome',
        'valor',
        'ativo',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'ativo' => 'boolean',
    ];

    public function config()
    {
        return $this->belongsTo(InscricaoTaxaConfig::class, 'config_id');
    }
}
