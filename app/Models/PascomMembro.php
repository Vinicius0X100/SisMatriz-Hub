<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PascomMembro extends Model
{
    protected $table = 'pascom_membros';
    protected $fillable = [
        'name',
        'ent_id',
        'type',
        'register_id',
        'age',
        'year_member',
        'status',
        'paroquia_id',
    ];
    public $timestamps = false;

    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function register()
    {
        return $this->belongsTo(Register::class, 'register_id', 'id');
    }
}
