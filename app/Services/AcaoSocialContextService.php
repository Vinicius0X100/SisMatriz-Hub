<?php

namespace App\Services;

use App\Models\AcaoSocialEquipe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AcaoSocialContextService
{
    protected const SESSION_KEY = 'acao_social_active_equipe_id';

    /**
     * Retorna a lista de equipes ativas que o usuário está autorizado a acessar na sua paróquia.
     * Administradores (roles 1 e 111) têm acesso a todas as equipes ativas da paróquia.
     * Usuários comuns têm acesso somente àquelas onde estão vinculados em acao_social_equipe_user.
     */
    public function getAuthorizedEquipes(?User $user = null): Collection
    {
        $user = $user ?: Auth::user();
        if (!$user || !$user->paroquia_id) {
            return collect();
        }

        // Administradores gerais/do sistema têm acesso a todas as equipes ativas da paróquia
        if ($user->hasAnyRole(['1', '111'])) {
            return AcaoSocialEquipe::where('paroquia_id', $user->paroquia_id)
                ->where('ativo', true)
                ->orderBy('nome', 'asc')
                ->get();
        }

        // Usuário comum: apenas equipes ativas onde está expressamente vinculado
        return $user->acaoSocialEquipes()
            ->where('acao_social_equipes.paroquia_id', $user->paroquia_id)
            ->where('ativo', true)
            ->orderBy('nome', 'asc')
            ->get();
    }

    /**
     * Resolve a equipe ativa respeitando estritamente a autorização do usuário.
     * Nunca seleciona ou permite acesso a uma equipe para a qual o usuário não possua permissão.
     */
    public function getActiveEquipe(?User $user = null, ?Request $request = null): ?AcaoSocialEquipe
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return null;
        }

        $authorizedEquipes = $this->getAuthorizedEquipes($user);

        // Se o usuário não tem nenhuma equipe autorizada na paróquia, retorna null
        if ($authorizedEquipes->isEmpty()) {
            return null;
        }

        // 1. Verifica se houve troca via parâmetro na requisição
        if ($request && $request->filled('equipe_id')) {
            $requestedId = (int) $request->input('equipe_id');
            $matched = $authorizedEquipes->firstWhere('id', $requestedId);
            if ($matched) {
                session([self::SESSION_KEY => $matched->id]);
                return $matched;
            }
        }

        // 2. Verifica se já existe uma seleção válida na sessão
        $sessionEquipeId = session(self::SESSION_KEY);
        if ($sessionEquipeId) {
            $matched = $authorizedEquipes->firstWhere('id', (int) $sessionEquipeId);
            if ($matched) {
                return $matched;
            }
        }

        // 3. Fallback seguro: Primeira equipe autorizada do usuário
        $defaultEquipe = $authorizedEquipes->first();
        if ($defaultEquipe) {
            session([self::SESSION_KEY => $defaultEquipe->id]);
        }

        return $defaultEquipe;
    }

    /**
     * Define manualmente a equipe ativa na sessão, validando rigorosamente a permissão.
     */
    public function setActiveEquipe(int $equipeId, ?User $user = null): bool
    {
        $user = $user ?: Auth::user();
        if (!$user) {
            return false;
        }

        $authorizedEquipes = $this->getAuthorizedEquipes($user);
        $matched = $authorizedEquipes->firstWhere('id', $equipeId);

        if ($matched) {
            session([self::SESSION_KEY => $matched->id]);
            return true;
        }

        return false;
    }

    /**
     * Resolução específica para chamadas de API (sem depender de sessão web).
     */
    public function resolveForApi(User $user, ?Request $request = null): ?AcaoSocialEquipe
    {
        $authorizedEquipes = $this->getAuthorizedEquipes($user);

        if ($authorizedEquipes->isEmpty()) {
            return null;
        }

        if ($request && $request->filled('equipe_id')) {
            $requestedId = (int) $request->input('equipe_id');
            return $authorizedEquipes->firstWhere('id', $requestedId);
        }

        return $authorizedEquipes->first();
    }

    /**
     * Verifica se a paróquia possui mais de uma equipe ativa cadastrada.
     */
    public function paroquiaHasMultipleEquipes(?User $user = null): bool
    {
        $user = $user ?: Auth::user();
        if (!$user || !$user->paroquia_id) {
            return false;
        }

        return AcaoSocialEquipe::where('paroquia_id', $user->paroquia_id)
            ->where('ativo', true)
            ->count() > 1;
    }

    /**
     * Verifica se o usuário tem mais de uma equipe autorizada.
     */
    public function userHasMultipleEquipes(?User $user = null): bool
    {
        return $this->getAuthorizedEquipes($user)->count() > 1;
    }
}
