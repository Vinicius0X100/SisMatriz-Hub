<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaltaJustifyAdultos extends Model
{
    use HasFactory;

    protected $table = 'faltas_justify_adultos';

    protected $fillable = [
        'faltas_id',
        'motivo',
        'anexo',
    ];

    public function falta()
    {
        return $this->belongsTo(FaltaAdultos::class, 'faltas_id');
    }
}
