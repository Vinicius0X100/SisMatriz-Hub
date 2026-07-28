<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurmaPreCatequese extends Model
{
    use HasFactory;

    protected $table = 'turmas_pre_catequese';

    protected $fillable = [
        'turma',
        'tutor',
        'inicio',
        'termino',
        'status',
        'paroquia_id',
    ];

    protected $casts = [
        'status'      => 'integer',
        'paroquia_id' => 'integer',
        'inicio'      => 'date',
        'termino'     => 'date',
    ];

    public function catequista()
    {
        return $this->belongsTo(CatequistaPreCatequese::class, 'tutor');
    }

    public function catecandos()
    {
        return $this->hasMany(CatecandoPreCatequese::class, 'turma_id');
    }
}
