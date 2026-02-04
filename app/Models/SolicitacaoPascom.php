<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoPascom extends Model
{
    use HasFactory;

    protected $table = 'solicitacoes_pascom';

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'phone',
        'cargo',
        'service',
        'pastoral',
        'description',
        'created_at',
        'paroquia_id'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'paroquia_id' => 'integer',
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
