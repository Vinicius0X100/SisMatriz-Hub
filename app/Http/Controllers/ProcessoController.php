<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ProcessoParoquial;
use App\Models\ProcessoArquivo;
use App\Models\ProcessoTramitacao;
use App\Models\ProcessoTramitacaoArquivo;
use App\Models\ProcessoNotificacao;
use App\Models\User;

class ProcessoController extends Controller
{
    // ── Constantes ────────────────────────────────────────────────────────

    const GRUPOS_PASTORAIS = [
        'administracao'         => ['label' => 'Administração',                  'roles' => ['1', '111']],
        'gestao-ministerio'     => ['label' => 'Gestão de Ministério',           'roles' => ['2']],
        'pascom'                => ['label' => 'PASCOM',                         'roles' => ['9', '10']],
        'acolitos'              => ['label' => 'Acólitos',                       'roles' => ['6', '8']],
        'catequese-eucaristia'  => ['label' => 'Catequese — 1ª Eucaristia',      'roles' => ['7', '12']],
        'catequese-crisma'      => ['label' => 'Catequese — Crisma',             'roles' => ['3', '13']],
        'catequese-adultos'     => ['label' => 'Catequese — Adultos',            'roles' => ['17']],
        'vicentinos'            => ['label' => 'Vicentinos',                     'roles' => ['4']],
        'tesouraria'            => ['label' => 'Tesouraria',                     'roles' => ['11', '14']],
        'gestao-espacos'        => ['label' => 'Gestão de Espaços',              'roles' => ['15']],
        'matrimonio'            => ['label' => 'Matrimônio',                     'roles' => ['16']],
    ];

    // Mapeamento: assunto → grupo que pode tramitar PRIMEIRO
    const ASSUNTO_GRUPOS = [
        'pascom'      => 'pascom',
        'compra'      => 'administracao',
        'autorizacao' => 'administracao',
        'oficio'      => 'administracao',
        'manutencao'  => 'administracao',
        'outro'       => 'administracao',
    ];

    // ── Helpers privados ──────────────────────────────────────────────────

    private function getUserGrupos(User $user): array
    {
        $userRoles = $user->roles;
        $grupos = [];
        foreach (self::GRUPOS_PASTORAIS as $slug => $grupo) {
            if (!empty(array_intersect($userRoles, $grupo['roles']))) {
                $grupos[] = $slug;
            }
        }
        return $grupos;
    }

    private function getCargoLabel(User $user): string
    {
        return $user->role_label ?: 'Usuário';
    }

    private function podeTramitarInicio(User $user, string $assunto): bool
    {
        $grupoAssunto = self::ASSUNTO_GRUPOS[$assunto] ?? 'administracao';
        $userGrupos = $this->getUserGrupos($user);
        return in_array($grupoAssunto, $userGrupos);
    }

    // ── Actions ───────────────────────────────────────────────────────────

    /**
     * Listagem de processos com filtros dinâmicos.
     */
    public function index(Request $request)
    {
        $user       = Auth::user();
        $paroquiaId = $user->paroquia_id;

        // Leitura dos filtros (minha_pastoral padrão = true)
        $minhaPastoral   = $request->input('minha_pastoral', '1') === '1';
        $souResponsavel  = $request->input('sou_responsavel', '0') === '1';
        $filtroAssunto   = $request->input('assunto');
        $filtroStatus    = $request->input('status');
        $filtroPrioridade = $request->input('prioridade');
        $filtroBusca     = $request->input('busca');

        $userGrupos = $this->getUserGrupos($user);

        $query = ProcessoParoquial::where('paroquia_id', $paroquiaId)
            ->with(['responsavelAtual', 'ultimaTramitacao.deUser']);

        $sortBy  = $request->input('sort_by');
        $sortDir = $request->input('sort_dir', 'desc');

        if ($sortBy && in_array($sortBy, ['protocolo', 'nome_solicitante', 'prioridade', 'status', 'data_limite', 'created_at'])) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByRaw('FIELD(status, 0, 1, 3, 2, 4)')
                ->orderBy('prioridade', 'desc')
                ->orderBy('created_at', 'desc');
        }

        // Filtro: minha pastoral/movimento
        if ($minhaPastoral) {
            $assuntosDoGrupo = [];
            foreach (self::ASSUNTO_GRUPOS as $assunto => $grupo) {
                if (in_array($grupo, $userGrupos)) {
                    $assuntosDoGrupo[] = $assunto;
                }
            }

            $userId = $user->id;
            $query->where(function ($q) use ($assuntosDoGrupo, $userId, $userGrupos) {
                $q->whereIn('assunto', $assuntosDoGrupo)
                  ->orWhere('responsavel_atual_user_id', $userId)
                  ->orWhereHas('tramitacoes', function ($tq) use ($userId, $userGrupos) {
                      $tq->where('para_user_id', $userId)
                         ->orWhereIn('para_grupo', $userGrupos);
                  });
            });
        }

        // Filtro: sou responsável atual
        if ($souResponsavel) {
            $query->where('responsavel_atual_user_id', $user->id);
        }

        // Filtros de campo
        if ($filtroAssunto)    $query->where('assunto', $filtroAssunto);
        if ($filtroStatus !== null && $filtroStatus !== '') $query->where('status', $filtroStatus);
        if ($filtroPrioridade) $query->where('prioridade', $filtroPrioridade);
        if ($filtroBusca)      $query->where(function ($q) use ($filtroBusca) {
            $q->where('protocolo', 'like', '%' . $filtroBusca . '%')
              ->orWhere('nome_solicitante', 'like', '%' . $filtroBusca . '%')
              ->orWhere('descricao', 'like', '%' . $filtroBusca . '%');
        });

        $processos = $query->paginate(25)->withQueryString();

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');

        return view('modules.processos.index', compact(
            'processos',
            'minhaPastoral',
            'souResponsavel',
            'filtroAssunto',
            'filtroStatus',
            'filtroPrioridade',
            'filtroBusca',
            'userGrupos',
            'sortBy',
            'sortDir'
        ));
    }

    /**
     * Exclui um processo permanentemente (somente se status = 2 ou 4).
     */
    public function destroy($id)
    {
        $processo = ProcessoParoquial::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        if (!in_array($processo->status, [2, 4])) {
            return response()->json(['message' => 'Apenas processos finalizados ou cancelados podem ser excluídos.'], 403);
        }

        // Apagar arquivos do storage
        Storage::disk('public')->deleteDirectory('uploads/processos/' . $processo->id);
        Storage::disk('public')->deleteDirectory('uploads/processos/tramitacoes/' . $processo->id);

        $processo->delete();

        return response()->json(['message' => 'Processo excluído com sucesso.']);
    }

    /**
     * Exclui múltiplos processos.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        
        $processos = ProcessoParoquial::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $ids)
            ->whereIn('status', [2, 4])
            ->get();

        foreach ($processos as $processo) {
            Storage::disk('public')->deleteDirectory('uploads/processos/' . $processo->id);
            Storage::disk('public')->deleteDirectory('uploads/processos/tramitacoes/' . $processo->id);
            $processo->delete();
        }

        return response()->json(['message' => count($processos) . ' processos excluídos com sucesso.']);
    }

    /**
     * Cria um novo processo interno (iniciado pelo próprio usuário do painel).
     */
    public function store(Request $request)
    {
        $request->validate([
            'assunto'     => 'required|in:pascom,compra,autorizacao,oficio,manutencao,outro',
            'descricao'   => 'required|string|max:5000',
            'prioridade'  => 'required|integer|in:1,2,3,4',
            'data_limite' => 'nullable|date',
            'arquivos.*'  => 'nullable|file|max:51200|mimetypes:application/pdf,image/jpeg,image/png,image/gif,image/webp,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,text/plain,text/csv,application/zip,application/x-rar-compressed,application/x-7z-compressed',
        ], [
            'assunto.required'    => 'Selecione o assunto do processo.',
            'descricao.required'  => 'A descrição é obrigatória.',
            'prioridade.required' => 'Selecione a prioridade.',
        ]);

        $user = Auth::user();

        $processo = ProcessoParoquial::create([
            'paroquia_id'               => $user->paroquia_id,
            'protocolo'                 => ProcessoParoquial::gerarProtocolo(),
            'nome_solicitante'          => $user->name,
            'cargo_funcao'              => $this->getCargoLabel($user),
            'assunto'                   => $request->assunto,
            'descricao'                 => $request->descricao,
            'prioridade'                => $request->prioridade,
            'data_limite'               => $request->data_limite,
            'status'                    => 1, // Em Processo
            'responsavel_atual_user_id' => $user->id,
        ]);

        // Anexos iniciais do processo
        if ($request->hasFile('arquivos')) {
            foreach ($request->file('arquivos') as $file) {
                $this->salvarArquivoProcesso($file, $processo);
            }
        }

        // Criar tramitação de abertura automaticamente
        ProcessoTramitacao::create([
            'processo_id'    => $processo->id,
            'paroquia_id'    => $user->paroquia_id,
            'de_user_id'     => $user->id,
            'de_cargo_label' => $this->getCargoLabel($user),
            'descricao'      => 'Processo aberto internamente pelo sistema.',
            'status_processo' => 1,
            'tipo'           => 1, // abertura
        ]);

        return redirect()->route('processos.index')
            ->with('success', "Processo {$processo->protocolo} criado com sucesso!");
    }

    /**
     * Retorna os dados do processo + tramitações (para o modal Visualizar).
     */
    public function timeline($id)
    {
        $user       = Auth::user();
        $userGrupos = $this->getUserGrupos($user);

        $processo = ProcessoParoquial::where('paroquia_id', $user->paroquia_id)
            ->with([
                'arquivos',
                'responsavelAtual',
                'tramitacoes' => function ($q) {
                    $q->with(['deUser', 'paraUser', 'arquivos', 'mencao.deUser'])->orderBy('id', 'asc');
                },
            ])
            ->findOrFail($id);

        // Aplicar filtro de privacidade nos arquivos de cada tramitação
        foreach ($processo->tramitacoes as $tramitacao) {
            $tramitacao->arquivos_visiveis = $tramitacao->arquivos->filter(function ($arquivo) use ($user, $userGrupos) {
                return $arquivo->podeVer($user, $userGrupos);
            });
        }

        $grupos = self::GRUPOS_PASTORAIS;

        return view('modules.processos.partials.timeline', compact('processo', 'grupos', 'userGrupos'));
    }

    /**
     * Confirma assumir um processo e redireciona para tramitar (sem criar tramitação vazia).
     */
    public function darAndamento(Request $request, $id)
    {
        $user    = Auth::user();
        $processo = ProcessoParoquial::where('paroquia_id', $user->paroquia_id)->findOrFail($id);

        // Só pode iniciar se for o grupo correto
        if (!$this->podeTramitarInicio($user, $processo->assunto) && $processo->responsavel_atual_user_id !== $user->id) {
            return back()->with('error', 'Você não tem permissão para assumir este processo.');
        }

        // Atualizar responsável e status
        $processo->responsavel_atual_user_id = $user->id;
        if ($processo->status == 0) {
            $processo->status = 1;
        }
        $processo->save();

        return redirect()
            ->route('processos.tramitar', ['id' => $processo->id])
            ->with('success', 'Você assumiu a responsabilidade pelo processo. Registre o andamento abaixo.');
    }

    /**
     * Exibe a página de tramitação.
     */
    public function showTramitacao(Request $request, $id)
    {
        $user    = Auth::user();
        $userGrupos = $this->getUserGrupos($user);

        $processo = ProcessoParoquial::where('paroquia_id', $user->paroquia_id)
            ->with([
                'arquivos',
                'responsavelAtual',
                'tramitacoes' => function ($q) {
                    $q->with(['deUser', 'paraUser', 'arquivos'])->orderBy('id', 'asc');
                },
            ])
            ->findOrFail($id);

        $tramitacaoAtual = null;
        $tramitacaoId    = $request->integer('tramitacao');

        if ($tramitacaoId) {
            $tramitacaoAtual = $processo->tramitacoes->firstWhere('id', $tramitacaoId);
        }

        // Se não encontrou a tramitação de abertura, verifica se o responsável é o usuário logado
        if (!$tramitacaoAtual && $processo->responsavel_atual_user_id !== $user->id) {
            return redirect()->route('processos.index')
                ->with('error', 'Você não é o responsável atual por este processo.');
        }

        $grupos              = self::GRUPOS_PASTORAIS;
        $tramitacoesAnterio  = $processo->tramitacoes->filter(fn($t) => $t->id !== $tramitacaoId)->values();

        return view('modules.processos.tramitar', compact(
            'processo',
            'tramitacaoAtual',
            'grupos',
            'tramitacoesAnterio',
            'userGrupos'
        ));
    }

    /**
     * Salva o andamento / tramitação completa.
     */
    public function storeTramitacao(Request $request, $id)
    {
        $request->validate([
            'descricao'              => 'nullable|string|max:10000',
            'status_processo'        => 'required|integer|in:0,1,2,3,4',
            'para_tipo'              => 'required|in:grupo,usuario',
            'para_grupo'             => 'nullable|string',
            'para_user_id'           => [
                'nullable',
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if ($value == Auth::id()) {
                        $fail('Você não pode encaminhar o processo para si mesmo.');
                    }
                }
            ],
            'mencao_tramitacao_id'   => 'nullable|integer|exists:processos_tramitacoes,id',
            'tramitacao_id'          => 'nullable|integer|exists:processos_tramitacoes,id',
            'arquivos.*'             => 'nullable|file|max:51200',
            'privacidade.*'          => 'nullable|integer|in:0,1,2',
        ]);

        $user    = Auth::user();
        $processo = ProcessoParoquial::where('paroquia_id', $user->paroquia_id)->findOrFail($id);

        // Recuperar ou criar tramitação
        $tramitacaoId = $request->integer('tramitacao_id');
        $tramitacao   = $tramitacaoId
            ? ProcessoTramitacao::where('processo_id', $processo->id)->find($tramitacaoId)
            : null;

        if (!$tramitacao) {
            $tramitacao                 = new ProcessoTramitacao();
            $tramitacao->processo_id    = $processo->id;
            $tramitacao->paroquia_id    = $user->paroquia_id;
            $tramitacao->de_user_id     = $user->id;
            $tramitacao->de_cargo_label = $this->getCargoLabel($user);
            $tramitacao->tipo           = 0;
        }

        $tramitacao->descricao             = $request->input('descricao');
        $tramitacao->status_processo       = $request->integer('status_processo');
        $tramitacao->mencao_tramitacao_id  = $request->integer('mencao_tramitacao_id') ?: null;

        if ($request->input('para_tipo') === 'grupo') {
            $tramitacao->para_grupo    = $request->input('para_grupo');
            $tramitacao->para_user_id  = null;
        } else {
            $tramitacao->para_user_id  = $request->integer('para_user_id') ?: null;
            $tramitacao->para_grupo    = null;
        }

        $tramitacao->save();

        // Arquivos da tramitação
        if ($request->hasFile('arquivos')) {
            $privacidades = $request->input('privacidade', []);
            foreach ($request->file('arquivos') as $idx => $file) {
                // Rejeitar vídeos
                if (str_starts_with($file->getMimeType(), 'video/')) {
                    continue;
                }
                $privacidade = (int) ($privacidades[$idx] ?? 0);
                $this->salvarArquivoTramitacao($file, $tramitacao, $privacidade);
            }
        }

        // Atualizar processo
        $processo->status = $request->integer('status_processo');

        if ($request->input('para_tipo') === 'usuario' && $request->integer('para_user_id')) {
            $processo->responsavel_atual_user_id = $request->integer('para_user_id');
        } else {
            // Enviado para um grupo → limpa responsável até alguém assumir
            $processo->responsavel_atual_user_id = null;
        }

        $processo->save();

        // Disparar notificações
        if ($request->input('para_tipo') === 'usuario' && $tramitacao->para_user_id) {
            ProcessoNotificacao::create([
                'user_id'       => $tramitacao->para_user_id,
                'processo_id'   => $processo->id,
                'tramitacao_id' => $tramitacao->id,
                'title'         => 'Novo andamento em processo: ' . $processo->protocolo,
                'message'       => $user->name . ' enviou o processo para você.',
                'is_read'       => false,
            ]);
        } elseif ($request->input('para_tipo') === 'grupo' && $tramitacao->para_grupo) {
            // Notifica todos os usuários do grupo
            $grupoRoles = self::GRUPOS_PASTORAIS[$tramitacao->para_grupo]['roles'] ?? [];
            if (!empty($grupoRoles)) {
                $usersGrupo = User::where('paroquia_id', $user->paroquia_id)->where('status', 1)->get()
                    ->filter(fn($u) => !empty(array_intersect($u->roles, $grupoRoles)));

                foreach ($usersGrupo as $u) {
                    ProcessoNotificacao::create([
                        'user_id'       => $u->id,
                        'processo_id'   => $processo->id,
                        'tramitacao_id' => $tramitacao->id,
                        'title'         => 'Processo encaminhado para seu grupo: ' . $processo->protocolo,
                        'message'       => $user->name . ' encaminhou o processo para ' . (self::GRUPOS_PASTORAIS[$tramitacao->para_grupo]['label'] ?? 'seu grupo') . '.',
                        'is_read'       => false,
                    ]);
                }
            }
        }

        return redirect()->route('processos.index')
            ->with('success', 'Andamento registrado com sucesso!');
    }

    /**
     * Retorna usuários gerais via AJAX por nome, email ou user.
     */
    public function searchUsers(Request $request)
    {
        $user  = Auth::user();
        $busca = $request->input('q');

        $query = User::where('id', '!=', $user->id)->where(function($q) use ($user) {
            $q->where(function($q2) use ($user) {
                $q2->where('paroquia_id', $user->paroquia_id)->where('status', 1);
            })->orWhere('rule', 'like', '%"1"%')
              ->orWhere('rule', 'like', '%"111"%')
              ->orWhere('rule', '1')
              ->orWhere('rule', '111');
        });

        if ($busca) {
            $query->where(function($q) use ($busca) {
                $q->where('name', 'like', "%{$busca}%")
                  ->orWhere('user', 'like', "%{$busca}%")
                  ->orWhere('email', 'like', "%{$busca}%");
            });
        }

        $users = $query->take(20)->get()->map(function($u) {
            return [
                'id'    => $u->id,
                'name'  => $u->name ?? $u->user,
                'cargo' => $u->role_label ?: 'Usuário',
            ];
        });

        return response()->json($users);
    }

    /**
     * Retorna usuários de um grupo via AJAX (para os selects dinâmicos na tramitação).
     */
    public function getUsersByGrupo(Request $request)
    {
        $grupo = $request->input('grupo');
        $user  = Auth::user();

        if (!isset(self::GRUPOS_PASTORAIS[$grupo])) {
            return response()->json([]);
        }

        $roles = self::GRUPOS_PASTORAIS[$grupo]['roles'];

        $users = User::where('paroquia_id', $user->paroquia_id)
            ->where('status', 1)
            ->where('id', '!=', $user->id)
            ->get()
            ->filter(fn($u) => !empty(array_intersect($u->roles, $roles)))
            ->map(fn($u) => [
                'id'    => $u->id,
                'name'  => $u->name ?? $u->user,
                'cargo' => $u->role_label,
            ])
            ->values();

        return response()->json($users);
    }

    /**
     * Marca a notificação como lida e redireciona para a página de processos abrindo o modal.
     */
    public function lerNotificacao($id)
    {
        $notificacao = ProcessoNotificacao::where('user_id', Auth::id())->findOrFail($id);
        $notificacao->update(['is_read' => true]);

        return redirect()->route('processos.index', ['show_processo' => $notificacao->processo_id]);
    }

    // ── File helpers ──────────────────────────────────────────────────────

    private function salvarArquivoProcesso($file, ProcessoParoquial $processo): void
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = 'uploads/processos/' . $processo->id . '/' . $filename;

        Storage::disk('public')->putFileAs(
            'uploads/processos/' . $processo->id,
            $file,
            $filename
        );

        ProcessoArquivo::create([
            'processo_id'   => $processo->id,
            'paroquia_id'   => $processo->paroquia_id,
            'nome_original' => $file->getClientOriginalName(),
            'caminho'       => $path,
            'url'           => asset('storage/' . $path),
            'mime_type'     => $file->getMimeType(),
            'tamanho'       => $file->getSize(),
        ]);
    }

    private function salvarArquivoTramitacao($file, ProcessoTramitacao $tramitacao, int $privacidade): void
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path     = 'uploads/processos/tramitacoes/' . $tramitacao->processo_id . '/' . $filename;

        Storage::disk('public')->putFileAs(
            'uploads/processos/tramitacoes/' . $tramitacao->processo_id,
            $file,
            $filename
        );

        ProcessoTramitacaoArquivo::create([
            'tramitacao_id' => $tramitacao->id,
            'paroquia_id'   => $tramitacao->paroquia_id,
            'nome_original' => $file->getClientOriginalName(),
            'caminho'       => $path,
            'url'           => asset('storage/' . $path),
            'mime_type'     => $file->getMimeType(),
            'tamanho'       => $file->getSize(),
            'privacidade'   => $privacidade,
        ]);
    }
}
