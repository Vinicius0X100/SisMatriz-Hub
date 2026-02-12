<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaMatrimonio extends Model
{
    use HasFactory;

    protected $table = 'reservas_calendario_matrimonio';

    protected $fillable = [
        'titulo',
        'data',
        'horario',
        'ent_id', // ID da comunidade (se houver)
        'local', // Nome do local (se não for comunidade)
        'telefone_noivo',
        'telefone_noiva',
        'efeito_civil',
        'color',
        'paroquia_id',
    ];

    protected $casts = [
        'data' => 'date',
        'efeito_civil' => 'boolean',
    ];

    public function comunidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
