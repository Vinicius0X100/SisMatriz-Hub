<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CelebrationSchedule extends Model
{
    use HasFactory;

    protected $table = 'horarios_celebracoes';
    
    public $timestamps = false;

    protected $fillable = [
        'ent_id',
        'dia_semana',
        'horario',
        'paroquia_id',
    ];

    public function comunidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }
}
