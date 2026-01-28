<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatequistaCrisma extends Model
{
    protected $table = 'catequistas_crisma';

    protected $fillable = [
        'register_id',
        'nome',
        'ent_id',
        'status',
        'paroquia_id',
        'created_at',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
    ];

    public $timestamps = false; // We handle created_at manually or it's just created_at

    // Relationships
    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id');
    }

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }
}
