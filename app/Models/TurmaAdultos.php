<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurmaAdultos extends Model
{
    use HasFactory;

    protected $table = 'turmas_adultos';

    protected $fillable = [
        'turma',
        'tutor',
        'inicio',
        'termino',
        'status',
        'paroquia_id',
    ];

    protected $casts = [
        'inicio' => 'date',
        'termino' => 'date',
        'status' => 'integer',
        'paroquia_id' => 'integer',
    ];

    public function catequista()
    {
        return $this->belongsTo(CatequistaAdultos::class, 'tutor');
    }

    public function catecandos()
    {
        return $this->hasMany(CatecandoAdultos::class, 'turma_id');
    }
}
