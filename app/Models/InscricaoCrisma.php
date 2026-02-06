<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoCrisma extends Model
{
    use HasFactory;

    protected $table = 'inscricoes_crisma';
    public $timestamps = false; // Assuming based on other models, or maybe it has created_at as 'inscrito_em'

    protected $fillable = [
        'situacao',
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
        'certidao_batismo',
        'certidao_primeira_comunhao',
        'data_nascimento',
        'criado_em',
        'taxa_item_id',
        'comprovante_pagamento',
        'taxaPaga',
    ];

    protected $casts = [
        'situacao' => 'integer',
        'taxaPaga' => 'boolean',
        'data_nascimento' => 'date',
        'criado_em' => 'datetime',
        // Assuming certidao_batismo and certidao_primeira_comunhao store file paths or boolean-like strings
    ];

    public function taxa()
    {
        return $this->belongsTo(InscricaoTaxaItem::class, 'taxa_item_id');
    }
}
