<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaltaPreCatequese extends Model
{
    use HasFactory;

    protected $table = 'faltas_pre_catequese';
    public $timestamps = false;

    protected $fillable = [
        'aluno_id',
        'turma_id',
        'title',
        'data_aula',
        'status',
        'justify',
    ];

    protected $casts = [
        'data_aula' => 'date',
        'status'    => 'boolean',
    ];

    public function aluno()
    {
        return $this->belongsTo(Register::class, 'aluno_id');
    }

    public function turma()
    {
        return $this->belongsTo(TurmaPreCatequese::class, 'turma_id');
    }
}
