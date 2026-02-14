<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campanha extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'categoria_id',
        'descricao',
        'data_inicio',
        'data_fim',
        'paroquia_id',
        'status',
    ];

    protected $casts = [
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    public function categoria()
    {
        return $this->belongsTo(CampanhaCategoria::class, 'categoria_id');
    }

    public function entradas()
    {
        return $this->hasMany(CampanhaEntrada::class);
    }

    public function saidas()
    {
        return $this->hasMany(CampanhaSaida::class);
    }
}
