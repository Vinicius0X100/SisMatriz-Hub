<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialAssistant extends Model
{
    use HasFactory;

    protected $table = 'social_assistant';
    protected $primaryKey = 's_id';
    public $timestamps = false;

    protected $fillable = [
        'type',
        'category',
        'ent_id',
        'sala_id',
        'description',
        'qntd_destributed',
        'qntd_anterior',
        'last_update',
        'paroquia_id',
    ];

    protected $casts = [
        'last_update' => 'datetime',
        'qntd_destributed' => 'integer',
        'qntd_anterior' => 'integer',
        'paroquia_id' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(SocialAssistantImage::class, 'social_assistant_id', 's_id');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaDoacao::class, 'category', 'id');
    }
    
    public function entidade()
    {
        return $this->belongsTo(Entidade::class, 'ent_id', 'ent_id');
    }

    public function sala()
    {
        return $this->belongsTo(ReservaLocal::class, 'sala_id', 'id');
    }
}
