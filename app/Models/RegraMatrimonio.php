<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegraMatrimonio extends Model
{
    use HasFactory;

    protected $table = 'paroquia_rules_matrimonio';

    protected $fillable = [
        'paroquia_id',
        'comunidade_id',
        'max_casamentos_por_dia',
        'dias_permitidos',
    ];

    public function comunidade()
    {
        return $this->belongsTo(Entidade::class, 'comunidade_id', 'ent_id');
    }

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }
}
