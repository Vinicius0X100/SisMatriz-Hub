<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatequistaAdultos extends Model
{
    use HasFactory;

    protected $table = 'catequistas_adultos';

    protected $fillable = [
        'register_id',
        'nome',
        'ent_id',
        'status',
        'paroquia_id',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function turmas()
    {
        return $this->hasMany(TurmaAdultos::class, 'tutor');
    }
}
