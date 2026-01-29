<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaltaJustifyCatequese extends Model
{
    use HasFactory;

    protected $table = 'faltas_justifys_catequese';
    protected $primaryKey = 'j_id'; // Assuming similarity to FaltaJustify
    public $timestamps = false;

    protected $fillable = [
        'faltas_id',
        'justify',
    ];

    public function falta()
    {
        return $this->belongsTo(FaltaCatequese::class, 'faltas_id');
    }
}
