<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaltaJustify extends Model
{
    use HasFactory;

    protected $table = 'faltas_justifys';
    protected $primaryKey = 'j_id'; // Assuming j_id based on the screenshot provided in the user prompt
    public $timestamps = false; // Assuming no timestamps based on other models, but can be adjusted

    protected $fillable = [
        'faltas_id',
        'justify',
    ];

    public function falta()
    {
        return $this->belongsTo(FaltaCrisma::class, 'faltas_id');
    }
}
