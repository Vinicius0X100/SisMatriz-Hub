<?php

namespace App\Http\Controllers;

use App\Models\AcaoSocialEquipe;
use App\Models\User;
use App\Services\AcaoSocialContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AcaoSocialEquipeController extends Controller
{
    protected AcaoSocialContextService $contextService;

    public function __construct(AcaoSocialContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * Alternar a equipe ativa na sessão.
     */
    public function switchEquipe(Request $request)
    {
        $request->validate([
            'equipe_id' => 'required|integer|exists:acao_social_equipes,id',
        ]);

        $equipeId = (int) $request->input('equipe_id');
        $success = $this->contextService->setActiveEquipe($equipeId);

        if (!$success) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Você não possui autorização para acessar esta equipe.'], 403);
            }
            return back()->with('error', 'Você não possui autorização para acessar esta equipe.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Equipe alterada com sucesso!']);
        }

        return back()->with('success', 'Contexto alterado com sucesso!');
    }

    /**
     * Listagem de equipes da paróquia.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['1', '111']) && !$user->isCoordenadorAcaoSocial()) {
            abort(403, 'Acesso não autorizado à gestão de equipes.');
        }

        $equipes = AcaoSocialEquipe::where('paroquia_id', $user->paroquia_id)
            ->withCount(['familias', 'atendimentos', 'users'])
            ->orderBy('nome', 'asc')
            ->get();

        $activeEquipe = $this->contextService->getActiveEquipe();
        $authorizedEquipes = $this->contextService->getAuthorizedEquipes();

        return view('modules.acoes_sociais.equipes.index', compact('equipes', 'activeEquipe', 'authorizedEquipes'));
    }

    /**
     * Formulário de criação de equipe.
     */
    public function create()
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['1', '111'])) {
            abort(403, 'Apenas administradores podem criar novas equipes de Ações Sociais.');
        }

        $paroquianos = User::where('paroquia_id', $user->paroquia_id)->orderBy('name')->get();

        return view('modules.acoes_sociais.equipes.create', compact('paroquianos'));
    }

    /**
     * Salvar nova equipe.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['1', '111'])) {
            abort(403, 'Apenas administradores podem criar novas equipes.');
        }

        $request->validate([
            'nome'      => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'tipo'      => 'required|in:geral,vicentinos,promocao_humana,pastoral_social',
            'ativo'     => 'nullable|boolean',
            'users'     => 'nullable|array',
            'users.*'   => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $equipe = AcaoSocialEquipe::create([
                'paroquia_id' => $user->paroquia_id,
                'nome'        => $request->nome,
                'descricao'   => $request->descricao,
                'tipo'        => $request->tipo,
                'ativo'       => $request->has('ativo') ? (bool)$request->ativo : true,
            ]);

            // Se indicou membros na criação
            if ($request->has('users') && is_array($request->users)) {
                $attachData = [];
                foreach ($request->users as $userId) {
                    $attachData[$userId] = [
                        'paroquia_id' => $user->paroquia_id,
                        'funcao'      => 'agente',
                    ];
                }
                $equipe->users()->sync($attachData);
            }

            DB::commit();

            return redirect()->route('acoes-sociais.equipes.index')
                ->with('success', 'Equipe de Ações Sociais criada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar equipe: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Formulário de edição de equipe.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $equipe = AcaoSocialEquipe::where('paroquia_id', $user->paroquia_id)->findOrFail($id);

        if (!$user->hasAnyRole(['1', '111']) && !$user->isCoordenadorAcaoSocial($equipe)) {
            abort(403, 'Acesso não autorizado para editar esta equipe.');
        }

        $equipeUsers = $equipe->users()->get();
        $allParoquianos = User::where('paroquia_id', $user->paroquia_id)->orderBy('name')->get();

        return view('modules.acoes_sociais.equipes.edit', compact('equipe', 'equipeUsers', 'allParoquianos'));
    }

    /**
     * Atualizar equipe.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $equipe = AcaoSocialEquipe::where('paroquia_id', $user->paroquia_id)->findOrFail($id);

        if (!$user->hasAnyRole(['1', '111']) && !$user->isCoordenadorAcaoSocial($equipe)) {
            abort(403, 'Acesso não autorizado para editar esta equipe.');
        }

        $request->validate([
            'nome'      => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'tipo'      => 'required|in:geral,vicentinos,promocao_humana,pastoral_social',
            'ativo'     => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $equipe->update([
                'nome'      => $request->nome,
                'descricao' => $request->descricao,
                'tipo'      => $request->tipo,
                'ativo'     => $request->has('ativo') ? (bool)$request->ativo : true,
            ]);

            // Atualização de membros se enviados
            if ($request->has('membros') && is_array($request->membros)) {
                $syncData = [];
                foreach ($request->membros as $userId => $dados) {
                    if (isset($dados['selected']) && $dados['selected'] == '1') {
                        $syncData[$userId] = [
                            'paroquia_id' => $user->paroquia_id,
                            'funcao'      => in_array($dados['funcao'] ?? 'agente', ['coordenador', 'agente']) ? $dados['funcao'] : 'agente',
                        ];
                    }
                }
                $equipe->users()->sync($syncData);
            }

            DB::commit();

            return redirect()->route('acoes-sociais.equipes.index')
                ->with('success', 'Equipe atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar equipe: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Ativar ou desativar equipe (sem exclusão destrutiva).
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['1', '111'])) {
            abort(403, 'Apenas administradores podem desativar ou ativar equipes.');
        }

        $equipe = AcaoSocialEquipe::where('paroquia_id', $user->paroquia_id)->findOrFail($id);
        $equipe->ativo = !$equipe->ativo;
        $equipe->save();

        $statusMsg = $equipe->ativo ? 'ativada' : 'desativada';
        return back()->with('success', "Equipe {$equipe->nome} {$statusMsg} com sucesso!");
    }
}
