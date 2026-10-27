<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InscricaoPreCatequese extends Model
{
    use HasFactory, LogsActivity;

    protected string $auditModuleName = 'Catequese - Pré-Catequese';

    public function getAuditableAttributes(): array
    {
        return [
            'status',
            'paroquia_id',
            'nome',
            'sexo',
            'nacionalidade',
            'telefone1',
            'batismo',
        ];
    }

    protected $table = 'inscricoes_pre_catequese';
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
        'status'          => 'integer',
        'taxaPaga'        => 'integer',
        'batismo'         => 'boolean',
        'data_nascimento' => 'date',
        'criado_em'       => 'datetime',
    ];

    public function taxa()
    {
        return $this->belongsTo(InscricaoTaxaItem::class, 'taxa_item_id');
    }
}
