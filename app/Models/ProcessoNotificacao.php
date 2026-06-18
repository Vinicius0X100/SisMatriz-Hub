<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessoNotificacao extends Model
{
    protected $table = 'processo_notificacoes';

    protected $fillable = [
        'user_id',
        'processo_id',
        'tramitacao_id',
        'title',
        'message',
        'is_read',
    ];

    public function processo()
    {
        return $this->belongsTo(ProcessoParoquial::class, 'processo_id');
    }

    public function tramitacao()
    {
        return $this->belongsTo(ProcessoTramitacao::class, 'tramitacao_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
