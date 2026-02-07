<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoCatequeseAdultos extends Model
{
    use HasFactory;

    protected $table = 'inscricao_catequese_adultos';
    public $timestamps = false;

    protected $fillable = [
        'paroquia_id',
        'nome',
        'sexo',
        'data_nascimento',
        'nacionalidade',
        'estado',
        'cpf',
        'cep',
        'endereco',
        'numero',
        'telefone1',
        'telefone2',
        'filiacao',
        'estado_civil',
        'possuiBatismo',
        'certidao_batismo',
        'possuiPrimeiraComunicacao',
        'certidao_primeira_comunhao',
        'possuiMatrimonio',
        'certidao_matrimonio',
        'lgpdConsentimento',
        'data_inscricao',
        'criado_em',
        'taxaPaga',
        'taxa_item_id',
        'comprovante_pagamento',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'taxaPaga' => 'boolean',
        'data_nascimento' => 'date',
        'criado_em' => 'datetime',
        'data_inscricao' => 'datetime',
        'possuiBatismo' => 'boolean',
        'possuiPrimeiraComunicacao' => 'boolean',
        'possuiMatrimonio' => 'boolean',
        'lgpdConsentimento' => 'boolean',
    ];

    public function taxa()
    {
        return $this->belongsTo(InscricaoTaxaItem::class, 'taxa_item_id');
    }
}
