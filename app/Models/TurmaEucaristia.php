<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurmaEucaristia extends Model
{
    use HasFactory;

    protected $table = 'turmas_catequese';

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
        return $this->belongsTo(CatequistaEucaristia::class, 'tutor');
    }

    public function catecandos()
    {
        return $this->hasMany(Catecando::class, 'turma_id');
    }
}
