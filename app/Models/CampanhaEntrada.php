<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampanhaEntrada extends Model
{
    use HasFactory;

    protected $table = 'campanha_entradas';

    protected $fillable = [
        'campanha_id',
        'data',
        'valor',
        'forma',
        'observacoes',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
    ];

    public function campanha()
    {
        return $this->belongsTo(Campanha::class);
    }
}
