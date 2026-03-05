<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VicentinosFamily extends Model
{
    use HasFactory;

    protected $fillable = [
        'vicentinos_record_id',
        'user_id',
        'paroquia_id',
        'nome',
        'parentesco',
        'nascimento',
        'profissao',
        'escolaridade',
        'renda',
    ];

    protected $casts = [
        'nascimento' => 'date',
        'renda' => 'decimal:2',
    ];

    public function vicentinosRecord()
    {
        return $this->belongsTo(VicentinosRecord::class);
    }
}
