<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessoTramitacaoArquivo extends Model
{
    protected $table = 'processos_tramitacao_arquivos';

    protected $fillable = [
        'tramitacao_id',
        'paroquia_id',
        'nome_original',
        'caminho',
        'url',
        'mime_type',
        'tamanho',
        'privacidade',
    ];

    public function tramitacao()
    {
        return $this->belongsTo(ProcessoTramitacao::class, 'tramitacao_id');
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

    public function getPrivacidadeLabelAttribute(): string
    {
        return match($this->privacidade) {
            1 => 'Somente próximo responsável',
            2 => 'Somente meu grupo pastoral',
            default => 'Público',
        };
    }

    /**
     * Verifica se o usuário dado pode visualizar/baixar este arquivo.
     */
    public function podeVer(User $user, array $userGrupos): bool
    {
        if ($this->privacidade === 0) {
            return true;
        }

        if ($this->privacidade === 1) {
            // Somente o próximo responsável (para_user_id da tramitação)
            $tramitacao = $this->tramitacao;
            return $tramitacao && $tramitacao->para_user_id === $user->id;
        }

        if ($this->privacidade === 2) {
            // Somente o grupo do remetente
            $tramitacao = $this->tramitacao;
            if (!$tramitacao) return false;

            // Descobre o grupo do remetente
            $deUser = $tramitacao->deUser;
            if (!$deUser) return false;

            foreach (\App\Http\Controllers\ProcessoController::GRUPOS_PASTORAIS as $slug => $grupo) {
                if (!empty(array_intersect($deUser->roles, $grupo['roles']))) {
                    if (in_array($slug, $userGrupos)) {
                        return true;
                    }
                }
            }
            return false;
        }

        return false;
    }
}
