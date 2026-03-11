<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParoquiaAjuste extends Model
{
    use HasFactory;

    protected $table = 'paroquias_ajustes';

    protected $fillable = [
        'paroquia_id',
        'secretaria_horarios',
        'confissoes_horarios',
        'adoracao_enabled',
        'adoracao_horarios',
    ];

    protected $casts = [
        'paroquia_id' => 'integer',
        'secretaria_horarios' => 'array',
        'confissoes_horarios' => 'array',
        'adoracao_enabled' => 'boolean',
        'adoracao_horarios' => 'array',
    ];

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}

