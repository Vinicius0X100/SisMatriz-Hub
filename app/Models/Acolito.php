<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Acolito extends Model
{
    use HasFactory;

    protected $table = 'acolitos';
    public $timestamps = false; // Assumindo sem timestamps baseado na estrutura descrita, ajustar se necessário

    protected $fillable = [
        'name',
        'ent_id',
        'type', // 0 = Acolito, 1 = Coroinha
        'register_id',
        'age',
        'graduation_year',
        'status', // 0 = Inativo, 1 = Ativo (assumindo padrão)
        'paroquia_id'
    ];

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }
}
