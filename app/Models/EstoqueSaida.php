<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstoqueSaida extends Model
{
    use HasFactory;

    protected $table = 'estoque_saida';

    public $timestamps = false; // Based on screenshot, there are no created_at/updated_at columns, only data_saida

    protected $fillable = [
        's_id',
        'nome_item',
        'qntd_distribuida',
        'retirado_por',
        'entregue_por',
        'data_saida',
        'ent_id',
        'ano',
        'mes',
        'status',
        'paroquia_id',
    ];

    protected $casts = [
        'data_saida' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(SocialAssistant::class, 's_id', 's_id');
    }

    public function comunidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }
}
