<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessoArquivo extends Model
{
    protected $table = 'processos_arquivos';

    protected $fillable = [
        'processo_id',
        'paroquia_id',
        'nome_original',
        'caminho',
        'url',
        'mime_type',
        'tamanho',
    ];

    public function processo()
    {
        return $this->belongsTo(ProcessoParoquial::class, 'processo_id');
    }

    public function getExtensaoAttribute(): string
    {
        return strtolower(pathinfo($this->nome_original, PATHINFO_EXTENSION));
    }

    public function getTamanhoFormatadoAttribute(): string
    {
        $bytes = $this->tamanho ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
