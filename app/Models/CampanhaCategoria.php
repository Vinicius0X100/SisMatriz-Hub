<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampanhaCategoria extends Model
{
    use HasFactory;

    protected $table = 'campanha_categorias';

    protected $fillable = [
        'nome',
        'paroquia_id',
    ];

    public function campanhas()
    {
        return $this->hasMany(Campanha::class, 'categoria_id');
    }
}
