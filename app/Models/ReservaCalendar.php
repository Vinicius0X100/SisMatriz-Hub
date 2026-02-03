<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaCalendar extends Model
{
    protected $table = 'reservas_calendar';

    protected $fillable = [
        'data',
        'hora_inicio',
        'hora_fim',
        'descricao',
        'local',
        'responsavel',
        'observacoes',
        'color',
        'paroquia_id',
    ];

    public function localModel()
    {
        return $this->belongsTo(ReservaLocal::class, 'local');
    }
}
