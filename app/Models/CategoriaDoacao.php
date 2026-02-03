<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaDoacao extends Model
{
    protected $table = 'categorias_doacao';
    
    public $timestamps = false;

    protected $fillable = [
        'name',
        'paroquia_id'
    ];
}
