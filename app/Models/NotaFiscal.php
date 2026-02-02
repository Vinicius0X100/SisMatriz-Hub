<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotaFiscal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'notas_fiscais';

    protected $fillable = [
        'paroquia_id',
        'entidade_id',
        'user_id',
        'numero',
        'serie',
        'chave_acesso',
        'tipo',
        'data_emissao',
        'data_entrada',
        'emitente_nome',
        'emitente_documento',
        'valor_total',
        'valor_desconto',
        'valor_acrescimo',
        'descricao',
        'caminho_arquivo',
        'status',
    ];

    protected $casts = [
        'data_emissao' => 'date',
        'data_entrada' => 'date',
        'valor_total' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_acrescimo' => 'decimal:2',
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'entidade_id', 'ent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
