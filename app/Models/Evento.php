<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'eventos';

    protected $fillable = [
        'title',
        'date',
        'time',
        'address',
        'photo',
        'paroquia_id',
    ];
}

