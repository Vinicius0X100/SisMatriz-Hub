<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lembrete extends Model
{
    protected $table = 'lembretes';

    protected $fillable = [
        'usuario_id',
        'descricao',
        'data_hora',
        'status',
        'repeat',
        'pref_email',
        'pref_sound',
        'last_email_sent',
        'snooze_until',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
        'last_email_sent' => 'datetime',
        'snooze_until' => 'datetime',
        'pref_email' => 'boolean',
        'pref_sound' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
