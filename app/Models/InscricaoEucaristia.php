<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoEucaristia extends Model
{
    use HasFactory;

    protected $table = 'inscricoes_eucaristia';
    public $timestamps = false;

    protected $fillable = [
        'status',
        'paroquia_id',
        'nome',
        'sexo',
        'nacionalidade',
        'estado',
        'cpf',
        'cep',
        'endereco',
        'numero',
        'telefone1',
        'telefone2',
        'filiacao',
        'batismo',
        'certidao_batismo',
        'data_nascimento',
        'criado_em',
        'taxa_item_id',
        'comprovante_pagamento',
        'taxaPaga',
    ];

    protected $casts = [
        'status' => 'integer',
        'taxaPaga' => 'integer', // User image showed int, Crisma model had boolean but let's stick to table definition or consistent behavior. I'll use integer as per migration/image.
        'batismo' => 'boolean',
        'data_nascimento' => 'date',
        'criado_em' => 'datetime',
    ];

    public function taxa()
    {
        return $this->belongsTo(InscricaoTaxaItem::class, 'taxa_item_id');
    }
}
