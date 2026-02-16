<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcolitoFaltaJustify extends Model
{
    use HasFactory;

    protected $table = 'faltas_justify_acolitos';
    public $timestamps = false;

    protected $fillable = [
        'faltas_id',
        'motivo',
        'anexo',
    ];

    public function falta()
    {
        return $this->belongsTo(AcolitoFalta::class, 'faltas_id');
    }
}

