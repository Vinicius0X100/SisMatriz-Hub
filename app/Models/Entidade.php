<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entidade extends Model
{
    protected $table = 'entidades';
    protected $primaryKey = 'ent_id';
    public $timestamps = false; // Assuming no timestamps based on typical legacy structure, but can be adjusted

    protected $fillable = [
        'ent_name',
        // Add other fields if known, but these are the required ones
    ];
}
