<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrazoInscricao extends Model
{
    use HasFactory;

    protected $table = 'prazos_inscricoes';
    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'atualizado_em';

    protected $fillable = [
        'tipo_inscricao',
        'data_inicio',
        'data_fim',
        'ativo',
        'paroquia_id',
        'criado_por'
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean',
    ];
}
