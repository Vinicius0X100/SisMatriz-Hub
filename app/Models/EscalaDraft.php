<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EscalaDraft extends Model
{
    use HasFactory;

    protected $table = 'escalas_drafts';

    protected $fillable = [
        'es_id',
        'paroquia_id',
        'user_id',
        'title',
        'payload',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function escala()
    {
        return $this->belongsTo(Escala::class, 'es_id');
    }
}
