<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatequistaPreCatequese extends Model
{
    use HasFactory;

    protected $table = 'catequistas_pre_catequese';
    public $timestamps = false;

    protected $fillable = [
        'register_id',
        'nome',
        'ent_id',
        'status',
        'paroquia_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id');
    }

    public function turmas()
    {
        return $this->hasMany(TurmaPreCatequese::class, 'tutor');
    }
}
