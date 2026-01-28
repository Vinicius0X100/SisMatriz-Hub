<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurmaCrisma extends Model
{
    use HasFactory;

    protected $table = 'turmas';

    protected $fillable = [
        'turma',
        'tutor',
        'inicio',
        'termino',
        'alunos_qntd',
        'status',
        'paroquia_id',
    ];

    protected $casts = [
        'inicio' => 'date',
        'termino' => 'date',
        'status' => 'integer',
        'alunos_qntd' => 'integer',
        'paroquia_id' => 'integer',
    ];

    public function catequista()
    {
        return $this->belongsTo(CatequistaCrisma::class, 'tutor');
    }

    public function crismandos()
    {
        return $this->hasMany(Crismando::class, 'turma_id');
    }
}
