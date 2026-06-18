@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Breadcrumb / Voltar --}}
    <div class="mt-4 mb-3">
        <a href="{{ route('processos.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Voltar para Processos
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Cabeçalho do Processo --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-primary text-white py-3 px-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-diagram-3-fill"></i>
                        Tramitação — <span class="font-monospace">{{ $processo->protocolo }}</span>
                    </h5>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-white bg-opacity-25">{{ $processo->assunto_label }}</span>
                        <span class="badge bg-white bg-opacity-25">{{ $processo->prioridade_label }}</span>
                        <span class="badge {{ $processo->status_badge_class }}">{{ $processo->status_label }}</span>
                    </div>
                </div>
                @if($processo->data_limite)
                    <div class="text-white text-end">
                        <div class="small opacity-75">Prazo</div>
                        <div class="fw-bold">{{ $processo->data_limite->format('d/m/Y') }}</div>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="text-uppercase fw-bold small text-muted mb-2 d-block">Solicitante</label>
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                             style="width:36px;height:36px;font-size:.8rem;">
                            {{ strtoupper(substr($processo->nome_solicitante, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">{{ $processo->nome_solicitante }}</div>
                            <div class="text-muted small">{{ $processo->cargo_funcao }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="text-uppercase fw-bold small text-muted mb-2 d-block">Responsável Atual</label>
                    @if($processo->responsavelAtual)
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                 style="width:36px;height:36px;font-size:.8rem;">
                                {{ strtoupper(substr($processo->responsavelAtual->name ?? $processo->responsavelAtual->user, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">{{ $processo->responsavelAtual->name ?? $processo->responsavelAtual->user }}</div>
                                <div class="text-muted small">{{ $processo->responsavelAtual->role_label ?? '—' }}</div>
                            </div>
                        </div>
                    @else
                        <span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i>Aguardando responsável</span>
                    @endif
                </div>
                <div class="col-12">
                    <label class="text-uppercase fw-bold small text-muted mb-2 d-block">Descrição Original</label>
                    <div class="bg-light border rounded-3 p-3 small text-dark">{{ $processo->descricao }}</div>
                </div>
                @if($processo->arquivos->count())
                    <div class="col-12">
                        <label class="text-uppercase fw-bold small text-muted mb-2 d-block">
                            <i class="bi bi-paperclip me-1"></i>Anexos da Solicitação ({{ $processo->arquivos->count() }})
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($processo->arquivos as $arq)
                                <button type="button" 
                                   data-url="{{ $arq->url }}"
                                   data-name="{{ $arq->nome_original }}"
                                   data-type="{{ pathinfo($arq->nome_original, PATHINFO_EXTENSION) }}"
                                   class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-1 btn-preview-anexo">
                                    <i class="bi bi-file-earmark"></i>
                                    <span class="text-truncate" style="max-width:180px;">{{ $arq->nome_original }}</span>
                                    <span class="text-muted" style="font-size:.7rem;">({{ $arq->tamanho_formatado }})</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ============================================================ --}}
        {{-- COLUNA ESQUERDA: Histórico de tramitações                     --}}
        {{-- ============================================================ --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light border-0 py-3 px-4">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-primary"></i>
                        Histórico ({{ $processo->tramitacoes->count() }})
                    </h6>
                </div>
                <div class="card-body p-3" style="max-height:600px;overflow-y:auto;">
                    @if($processo->tramitacoes->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            <span class="small">Nenhuma tramitação ainda.</span>
                        </div>
                    @else
                        <div class="timeline-processos">
                            @foreach($processo->tramitacoes->sortByDesc('created_at') as $idx => $tram)
                                @php
                                    $isAtual = $tramitacaoAtual && $tramitacaoAtual->id === $tram->id;
                                    $isAbertura  = $tram->tipo == 1;
                                    $isMencao    = $tram->tipo == 2;
                                    $headerClass = $isAtual ? 'bg-primary bg-opacity-10' : 'bg-light';
                                    
                                    // Sendo ordem decrescente, a "última tramitação" real é o $loop->first
                                    if ($loop->first) {
                                        if (in_array($processo->status, [2, 3])) {
                                            $iconClass = 'bi-check-circle-fill';
                                            $iconColor = 'bg-success';
                                            $headerClass = 'bg-success bg-opacity-10';
                                        } else {
                                            $iconClass = 'bi-pin-angle-fill';
                                            $iconColor = 'bg-warning text-dark';
                                            $headerClass = 'bg-warning bg-opacity-25';
                                        }
                                    } else {
                                        if ($isAbertura) {
                                            $iconClass = 'bi-folder2-open';
                                            $iconColor = 'bg-success';
                                        } elseif ($isMencao) {
                                            $iconClass = 'bi-chat-quote-fill';
                                            $iconColor = 'bg-info';
                                        } else {
                                            if ($tram->status_processo == 0) {
                                                $iconClass = 'bi-arrow-return-left';
                                                $iconColor = 'bg-warning text-dark';
                                            } elseif (in_array($tram->status_processo, [2, 3])) {
                                                $iconClass = 'bi-check-circle-fill';
                                                $iconColor = 'bg-success';
                                            } elseif ($tram->status_processo == 4) {
                                                $iconClass = 'bi-x-circle-fill';
                                                $iconColor = 'bg-danger';
                                            } else {
                                                $iconClass = 'bi-arrow-right-circle-fill';
                                                $iconColor = 'bg-primary';
                                            }
                                        }
                                    }
                                    $num = $idx + 1;
                                @endphp

                                <div class="timeline-item d-flex gap-3 mb-3 {{ $isAtual ? 'opacity-100' : 'opacity-75' }}">
                                    {{-- Ícone + linha vertical --}}
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rounded-circle {{ $iconColor }} {{ !str_contains($iconColor, 'text-dark') ? 'text-white' : '' }} d-flex align-items-center justify-content-center shadow-sm flex-shrink-0"
                                             style="width:38px;height:38px;z-index:1;">
                                            <i class="bi {{ $iconClass }} small"></i>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="flex-grow-1 border-start border-2 border-light" style="min-height:20px;margin-top:2px;"></div>
                                        @endif
                                    </div>

                                    {{-- Conteúdo --}}
                                    <div class="flex-grow-1 pb-3">
                                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                                            <div class="card-header {{ $headerClass }} py-2 px-3 d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="fw-bold text-dark small">
                                                        {{ $isMencao ? 'Menção' : ($loop->first ? 'Última tramitação' : $num.'ª Tramitação') }}
                                                    </span>
                                                    <span class="badge {{ $tram->status_badge_class }} ms-2 small">
                                                        {{ $tram->status_label }}
                                                    </span>
                                                    @if($isAtual) <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem;">Atual</span> @endif
                                                </div>
                                                <span class="text-muted" style="font-size:.75rem;">
                                                    <i class="bi bi-clock me-1"></i>{{ $tram->created_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                            <div class="card-body p-3">
                                                {{-- De → Para --}}
                                                <div class="row g-2 mb-3 small">
                                                    <div class="col-sm-6">
                                                        <span class="text-muted fw-semibold">De:</span>
                                                        <div class="bg-light border rounded-2 px-2 py-1 mt-1">
                                                            <i class="bi bi-person-fill text-primary me-1"></i>
                                                            <strong>{{ $tram->de_cargo_label }}</strong> /
                                                            #{{ $tram->de_user_id }} —
                                                            {{ $tram->deUser ? ($tram->deUser->name ?? $tram->deUser->user) : '—' }}
                                                        </div>
                                                    </div>
                                                    @if($tram->para_user_id || $tram->para_grupo)
                                                    <div class="col-sm-6">
                                                        <span class="text-muted fw-semibold">Para:</span>
                                                        <div class="bg-light border rounded-2 px-2 py-1 mt-1">
                                                            @if($tram->para_user_id)
                                                                <i class="bi bi-person-check-fill text-success me-1"></i>
                                                                <strong>{{ $tram->paraUser->role_label ?: 'Usuário' }}</strong> /
                                                                #{{ $tram->para_user_id }} —
                                                                {{ $tram->paraUser ? ($tram->paraUser->name ?? $tram->paraUser->user) : '—' }}
                                                            @else
                                                                @php $gLabel = \App\Http\Controllers\ProcessoController::GRUPOS_PASTORAIS[$tram->para_grupo]['label'] ?? $tram->para_grupo; @endphp
                                                                <i class="bi bi-people-fill text-warning me-1"></i>
                                                                Grupo: <strong>{{ $gLabel }}</strong>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>

                                                {{-- Andamento --}}
                                                @if($tram->descricao)
                                                <div class="mb-3">
                                                    <span class="text-muted fw-semibold small">Andamento:</span>
                                                    <div class="bg-light border rounded-2 px-3 py-2 mt-1 small">
                                                        {!! nl2br(e($tram->descricao)) !!}
                                                    </div>
                                                </div>
                                                @endif

                                                {{-- Arquivos --}}
                                                @if($tram->arquivos->count() > 0)
                                                <div>
                                                    <span class="text-muted fw-semibold small d-block mb-2">
                                                        <i class="bi bi-paperclip me-1"></i>Anexos:
                                                    </span>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($tram->arquivos as $arq)
                                                            @if($arq->podeVer(Auth::user(), $userGrupos))
                                                                <button type="button" 
                                                                   data-url="{{ $arq->url }}"
                                                                   data-name="{{ $arq->nome_original }}"
                                                                   data-type="{{ pathinfo($arq->nome_original, PATHINFO_EXTENSION) }}"
                                                                   class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-1 btn-preview-anexo">
                                                                    @if($arq->privacidade > 0)
                                                                        <i class="bi bi-lock-fill text-warning me-1" style="font-size:.7rem;"></i>
                                                                    @endif
                                                                    <i class="bi bi-file-earmark"></i>
                                                                    <span class="text-truncate" style="max-width:130px;">{{ $arq->nome_original }}</span>
                                                                </button>
                                                            @else
                                                                <span class="btn btn-sm btn-light border rounded-pill d-flex align-items-center gap-1 disabled text-muted">
                                                                    <i class="bi bi-lock-fill text-danger"></i>
                                                                    <span class="text-truncate" style="max-width:130px;">{{ $arq->nome_original }}</span>
                                                                </span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- Bloco fixo: Abertura do Processo (Fica no final pois é ordem decrescente) --}}
                            <div class="timeline-item d-flex gap-3 mb-3 opacity-75">
                                <div class="d-flex flex-column align-items-center">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width:38px;height:38px;z-index:1;">
                                        <i class="bi bi-folder-plus small"></i>
                                    </div>
                                    {{-- Como é o último item da lista visual, sem border-start aqui --}}
                                </div>
                                <div class="flex-grow-1 pb-3">
                                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                                        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark small">Abertura do Processo</span>
                                            <span class="text-muted" style="font-size:.75rem;">
                                                <i class="bi bi-clock me-1"></i>{{ $processo->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        <div class="card-body p-3 small">
                                            <span class="text-muted fw-semibold">Solicitante:</span>
                                            <div class="bg-light border rounded-2 px-2 py-1 mt-1">
                                                <i class="bi bi-person-fill text-success me-1"></i>
                                                <strong>{{ $processo->nome_solicitante }}</strong>
                                                @if($processo->cargo_funcao) — {{ $processo->cargo_funcao }} @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- COLUNA DIREITA: Formulário de tramitação                      --}}
        {{-- ============================================================ --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light border-0 py-3 px-4">
                    <h6 class="fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-pencil-square text-primary"></i>
                        Registrar Andamento
                    </h6>
                </div>
                <div class="card-body p-4">

                    {{-- De: informação do usuário logado --}}
                    <div class="alert border-start border-4 border-primary bg-primary bg-opacity-5 mb-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-person-badge-fill text-primary fs-5 flex-shrink-0 mt-1"></i>
                            <div class="small">
                                <div class="fw-bold text-primary mb-1">De:</div>
                                <strong>{{ Auth::user()->role_label ?? 'Usuário' }}</strong>
                                / #{{ Auth::id() }} —
                                {{ Auth::user()->name ?? Auth::user()->user }}
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('processos.tramitar.store', $processo->id) }}" method="POST" enctype="multipart/form-data" id="formTramitar">
                        @csrf
                        @if($tramitacaoAtual)
                            <input type="hidden" name="tramitacao_id" value="{{ $tramitacaoAtual->id }}">
                        @endif

                        {{-- Descrição do andamento --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Andamento / Observações</label>
                            <textarea name="descricao" class="form-control rounded-3" rows="5"
                                      placeholder="Descreva o que foi feito, decidido ou observado nesta etapa...">{{ old('descricao') }}</textarea>
                        </div>

                        {{-- Status do processo --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Status do Processo <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2" id="statusBtns">
                                @foreach([0=>'Pendente',1=>'Em Processo',3=>'Concluído',4=>'Cancelado'] as $val => $lbl)
                                    @php
                                        $cls = match($val) {
                                            0 => 'btn-outline-warning',
                                            1 => 'btn-outline-primary',
                                            2 => 'btn-outline-success',
                                            3 => 'btn-outline-success',
                                            4 => 'btn-outline-danger',
                                        };
                                        $selected = ($processo->status == $val);
                                    @endphp
                                    <button type="button"
                                            class="btn btn-sm {{ $cls }} rounded-pill status-btn {{ $selected ? 'active fw-bold' : '' }}"
                                            data-value="{{ $val }}">
                                        {{ $lbl }}
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" name="status_processo" id="statusProcessoInput" value="{{ $processo->status }}">
                        </div>

                        {{-- Menção a tramitação anterior --}}
                        @if($tramitacoesAnterio->count() > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Mencionar Tramitação Anterior
                                <span class="text-muted fw-normal small">(opcional)</span>
                            </label>
                            <select name="mencao_tramitacao_id" class="form-select rounded-3">
                                <option value="">— Nenhuma menção —</option>
                                @foreach($tramitacoesAnterio as $idx => $t)
                                    <option value="{{ $t->id }}">
                                        #{{ $idx + 1 }} — {{ $t->created_at->format('d/m/Y H:i') }}
                                        — {{ $t->deUser ? ($t->deUser->name ?? $t->deUser->user) : '—' }}
                                        @if($t->descricao)
                                            — "{{ \Illuminate\Support\Str::limit($t->descricao, 50) }}"
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Mensagem de Devolução ao Solicitante --}}
                        <div id="msgDevolucaoSolicitante" class="alert border-primary bg-primary bg-opacity-10 shadow-sm rounded-4 mb-4" style="display:none;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                    <i class="bi bi-person-fill-up text-primary fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-primary-emphasis mb-1">Devolução ao Solicitante</h6>
                                    <p class="mb-0 small text-primary-emphasis">O trâmite interno será pausado e o processo devolvido ao solicitante no Portal Externo. <br><span class="fw-semibold">Dica:</span> Arquivos anexados abaixo marcados como "Público" estarão visíveis para ele.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Mensagem de Cancelamento (Encerrar Processo) --}}
                        <div id="msgCancelamentoSolicitante" class="alert border-danger bg-danger bg-opacity-10 shadow-sm rounded-4 mb-4" style="display:none;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-danger bg-opacity-25 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                    <i class="bi bi-x-circle-fill text-danger fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-danger-emphasis mb-1">Encerramento do Processo</h6>
                                    <p class="mb-0 small text-danger-emphasis">O processo será encerrado permanentemente. Deixe uma mensagem abaixo justificando o cancelamento, ela será exibida ao solicitante inicial.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Para: Grupo ou Pessoa --}}
                        <div class="mb-4" id="encaminharParaContainerWrapper">
                            <label class="form-label fw-bold">Encaminhar Para <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="para_tipo" id="paraGrupo" value="grupo" checked>
                                    <label class="form-check-label fw-semibold small" for="paraGrupo">
                                        <i class="bi bi-people-fill text-warning me-1"></i> Grupo / Pastoral
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="para_tipo" id="paraUsuario" value="usuario">
                                    <label class="form-check-label fw-semibold small" for="paraUsuario">
                                        <i class="bi bi-person-check-fill text-success me-1"></i> Pessoa Específica
                                    </label>
                                </div>
                            </div>

                            {{-- Para: Grupo --}}
                            <div id="paraGrupoContainer">
                                <select name="para_grupo" id="selectParaGrupo" class="form-select rounded-3" required>
                                    <option value="">Selecione o grupo/pastoral...</option>
                                    @foreach(\App\Http\Controllers\ProcessoController::GRUPOS_PASTORAIS as $slug => $grupo)
                                        <option value="{{ $slug }}">{{ $grupo['label'] }}</option>
                                    @endforeach
                                </select>
                                <div class="alert alert-light border mt-2 small text-muted p-2">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Todos os membros do grupo receberão a notificação. Qualquer um poderá assumir a continuidade.
                                </div>
                            </div>

                            {{-- Para: Pessoa Específica --}}
                            <div id="paraUsuarioContainer" style="display:none;" class="bg-light p-3 rounded-4 border">
                                <ul class="nav nav-pills mb-3 small" id="pills-tab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active py-1 px-3 rounded-pill" id="pills-busca-tab" data-bs-toggle="pill" data-bs-target="#pills-busca" type="button" role="tab" aria-selected="true"><i class="bi bi-search me-1"></i> Busca Global</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-1 px-3 rounded-pill" id="pills-grupo-tab" data-bs-toggle="pill" data-bs-target="#pills-grupo" type="button" role="tab" aria-selected="false"><i class="bi bi-funnel me-1"></i> Filtrar por Grupo</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="pills-tabContent">
                                    {{-- Aba: Busca Global --}}
                                    <div class="tab-pane fade show active" id="pills-busca" role="tabpanel" aria-labelledby="pills-busca-tab">
                                        <div class="row g-2 position-relative">
                                            <div class="col-md-7">
                                                <label class="form-label small fw-semibold">Buscar Pessoa</label>
                                                <div class="input-group input-group-sm rounded-3 overflow-hidden">
                                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                                    <input type="text" id="inputBuscaPessoa" class="form-control border-start-0 ps-0" placeholder="Digite nome, email ou usuário..." autocomplete="off">
                                                </div>
                                                <div id="listaBuscaPessoa" class="list-group position-absolute shadow-sm border w-100 mt-1 z-3 bg-white" style="display:none; max-height:220px; overflow-y:auto; border-radius:0.5rem; top:100%;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Aba: Filtrar por Grupo --}}
                                    <div class="tab-pane fade" id="pills-grupo" role="tabpanel" aria-labelledby="pills-grupo-tab">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold">Grupo/Pastoral</label>
                                                <select id="selectGrupoParaPessoa" class="form-select form-select-sm rounded-3">
                                                    <option value="">Selecione o grupo...</option>
                                                    @foreach(\App\Http\Controllers\ProcessoController::GRUPOS_PASTORAIS as $slug => $grupo)
                                                        <option value="{{ $slug }}">{{ $grupo['label'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold">Pessoa</label>
                                                <select id="selectParaUsuarioGrupo" class="form-select form-select-sm rounded-3" disabled>
                                                    <option value="">— Selecione o grupo primeiro —</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Selecionado Final (comum às duas abas) --}}
                                <div class="mt-3 pt-3 border-top">
                                    <label class="form-label small fw-semibold text-primary">Usuário Selecionado para Tramitação:</label>
                                    <input type="hidden" name="para_user_id" id="paraUserIdInput">
                                    <div class="form-control bg-white d-flex align-items-center rounded-3 px-3 border-primary shadow-sm" style="min-height:38px;" id="pessoaSelecionadaLabel">
                                        <span class="text-muted small"><i class="bi bi-dash-circle me-1"></i> Ninguém selecionado</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Arquivos da tramitação --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold d-flex align-items-center justify-content-between">
                                <span>
                                    <i class="bi bi-paperclip me-1"></i>Anexar Arquivos
                                    <span class="text-muted fw-normal small">(até 50, sem vídeos)</span>
                                </span>
                                <span class="badge bg-light text-muted border" id="contadorArquivos">0 selecionados</span>
                            </label>

                            <div class="p-4 border border-2 border-dashed rounded-4 bg-light text-center position-relative" id="dropzone">
                                <i class="bi bi-cloud-arrow-up text-primary fs-1 mb-2 d-block"></i>
                                <span class="fw-bold text-dark d-block mb-1">Arraste ou clique para selecionar</span>
                                <span class="small text-muted d-block">PDF, Imagens, Word, Excel, ZIP, etc. — sem vídeos</span>
                                <input class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                       style="cursor:pointer;"
                                       type="file" id="arquivosInput" name="arquivos[]"
                                       multiple
                                       accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.zip,.rar,.7z,.odt,.ods">
                            </div>

                            {{-- Lista de arquivos com seletor de privacidade --}}
                            <div id="arquivosList" class="mt-3"></div>
                        </div>

                        {{-- Botões --}}
                        <div class="d-flex justify-content-end gap-3 pt-2 border-top">
                            <a href="{{ route('processos.index') }}" class="btn btn-light border rounded-pill px-4">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="btnSalvar">
                                <span class="spinner-border spinner-border-sm me-2 d-none" id="spinnerSalvar"></span>
                                <i class="bi bi-send-fill me-2" id="iconSalvar"></i>
                                Salvar e Tramitar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Status Buttons ────────────────────────────────────────────────────
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active', 'fw-bold'));
            this.classList.add('active', 'fw-bold');
            const val = this.dataset.value;
            document.getElementById('statusProcessoInput').value = val;
            
            const encaminharWrap = document.getElementById('encaminharParaContainerWrapper');
            const msgDevolucao   = document.getElementById('msgDevolucaoSolicitante');
            const msgCancelamento = document.getElementById('msgCancelamentoSolicitante');

            if(val === '2' || val === '3' || val === '4') {
                encaminharWrap.style.display = 'none';
                document.getElementById('selectParaGrupo').removeAttribute('required');

                if (val === '4') {
                    if(msgDevolucao) msgDevolucao.style.display = 'none';
                    if(msgCancelamento) msgCancelamento.style.display = 'block';
                } else {
                    if(msgDevolucao) msgDevolucao.style.display = 'block';
                    if(msgCancelamento) msgCancelamento.style.display = 'none';
                }
            } else {
                encaminharWrap.style.display = 'block';
                if(msgDevolucao) msgDevolucao.style.display = 'none';
                if(msgCancelamento) msgCancelamento.style.display = 'none';
                if(document.getElementById('paraGrupo').checked) {
                    document.getElementById('selectParaGrupo').setAttribute('required', '');
                }
            }
        });
    });

    // Iniciar com estado correto caso já carregue Concluído, Finalizado ou Cancelado
    const currentStatus = document.getElementById('statusProcessoInput').value;
    const encaminharWrap = document.getElementById('encaminharParaContainerWrapper');
    const msgDevolucao   = document.getElementById('msgDevolucaoSolicitante');
    const msgCancelamento = document.getElementById('msgCancelamentoSolicitante');

    if(currentStatus === '2' || currentStatus === '3' || currentStatus === '4') {
        if(encaminharWrap) encaminharWrap.style.display = 'none';
        const selectPG = document.getElementById('selectParaGrupo');
        if(selectPG) selectPG.removeAttribute('required');

        if (currentStatus === '4') {
            if(msgDevolucao) msgDevolucao.style.display = 'none';
            if(msgCancelamento) msgCancelamento.style.display = 'block';
        } else {
            if(msgDevolucao) msgDevolucao.style.display = 'block';
            if(msgCancelamento) msgCancelamento.style.display = 'none';
        }
    }

    // ── Para: Grupo ou Pessoa ─────────────────────────────────────────────
    const radios = document.querySelectorAll('[name="para_tipo"]');
    const grupoContainer   = document.getElementById('paraGrupoContainer');
    const usuarioContainer = document.getElementById('paraUsuarioContainer');
    const selectParaGrupo  = document.getElementById('selectParaGrupo');
    const selectParaUsr    = document.getElementById('selectParaUsuario');

    radios.forEach(r => {
        r.addEventListener('change', function () {
            if (this.value === 'grupo') {
                grupoContainer.style.display   = '';
                usuarioContainer.style.display = 'none';
                selectParaGrupo.setAttribute('required', '');
                selectParaUsr.removeAttribute('required');
            } else {
                grupoContainer.style.display   = 'none';
                usuarioContainer.style.display = '';
                selectParaGrupo.removeAttribute('required');
            }
        });
    });

    // ── Para: Pessoa Específica (Busca AJAX Global) ───────────────────────
    const inputBusca       = document.getElementById('inputBuscaPessoa');
    const listaBusca       = document.getElementById('listaBuscaPessoa');
    const inputIdSelecionado = document.getElementById('paraUserIdInput');
    const labelSelecionado = document.getElementById('pessoaSelecionadaLabel');
    let searchTimerPessoa  = null;

    inputBusca.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(searchTimerPessoa);

        if (query.length < 2) {
            listaBusca.style.display = 'none';
            return;
        }

        searchTimerPessoa = setTimeout(() => {
            listaBusca.innerHTML = '<div class="list-group-item text-muted small py-2"><span class="spinner-border spinner-border-sm me-2"></span>Buscando...</div>';
            listaBusca.style.display = 'block';

            fetch(`/processos/usuarios-busca?q=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(users => {
                listaBusca.innerHTML = '';
                if (users.length === 0) {
                    listaBusca.innerHTML = '<div class="list-group-item text-muted small py-2">Nenhum usuário encontrado.</div>';
                    return;
                }
                users.forEach(u => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action py-2 px-3 text-start border-0 border-bottom';
                    btn.innerHTML = `
                        <div class="fw-semibold text-dark small">${u.name}</div>
                        <div class="text-muted" style="font-size:.7rem;">${u.cargo}</div>
                    `;
                    btn.addEventListener('click', () => {
                        inputIdSelecionado.value = u.id;
                        labelSelecionado.innerHTML = `<span class="text-dark small fw-semibold text-truncate"><i class="bi bi-person-check-fill text-success me-1"></i> ${u.name}</span>`;
                        inputBusca.value = '';
                        listaBusca.style.display = 'none';
                    });
                    listaBusca.appendChild(btn);
                });
            })
            .catch(() => {
                listaBusca.innerHTML = '<div class="list-group-item text-danger small py-2">Erro ao buscar usuários.</div>';
            });
        }, 400);
    });

    // Fechar lista ao clicar fora
    document.addEventListener('click', function(e) {
        if (!inputBusca.contains(e.target) && !listaBusca.contains(e.target)) {
            listaBusca.style.display = 'none';
        }
    });

    // ── Para: Pessoa Específica (Filtrar por Grupo) ────────────────────────
    const selectGrupoPessoa = document.getElementById('selectGrupoParaPessoa');
    const selectParaUsrGrp  = document.getElementById('selectParaUsuarioGrupo');

    selectGrupoPessoa.addEventListener('change', function () {
        const grupo = this.value;
        selectParaUsrGrp.innerHTML = '<option value="">Carregando...</option>';
        selectParaUsrGrp.disabled  = true;

        if (!grupo) {
            selectParaUsrGrp.innerHTML = '<option value="">— Selecione o grupo primeiro —</option>';
            return;
        }

        fetch(`/processos/usuarios-por-grupo?grupo=${grupo}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(users => {
            selectParaUsrGrp.innerHTML = '<option value="">— Selecione a pessoa —</option>';
            if (users.length === 0) {
                selectParaUsrGrp.innerHTML += '<option disabled>Nenhum usuário neste grupo</option>';
            } else {
                users.forEach(u => {
                    const opt = document.createElement('option');
                    opt.value        = u.id;
                    opt.textContent  = u.name;
                    opt.dataset.cargo = u.cargo;
                    selectParaUsrGrp.appendChild(opt);
                });
            }
            selectParaUsrGrp.disabled = false;
        })
        .catch(() => {
            selectParaUsrGrp.innerHTML = '<option value="">Erro ao carregar usuários</option>';
            selectParaUsrGrp.disabled  = false;
        });
    });

    selectParaUsrGrp.addEventListener('change', function () {
        if(this.value) {
            const opt = this.options[this.selectedIndex];
            inputIdSelecionado.value = opt.value;
            labelSelecionado.innerHTML = `<span class="text-dark small fw-semibold text-truncate"><i class="bi bi-person-check-fill text-success me-1"></i> ${opt.textContent}</span>`;
        }
    });

    // ── Upload de Arquivos com Privacidade ────────────────────────────────
    const arquivosInput  = document.getElementById('arquivosInput');
    const arquivosList   = document.getElementById('arquivosList');
    const contador       = document.getElementById('contadorArquivos');
    let arquivosArray    = [];

    arquivosInput.addEventListener('change', function () {
        const novos = Array.from(this.files).filter(f => !f.type.startsWith('video/'));
        arquivosArray = arquivosArray.concat(novos).slice(0, 50);
        renderArquivos();
    });

    function renderArquivos() {
        arquivosList.innerHTML = '';
        contador.textContent   = `${arquivosArray.length} selecionados`;

        if (arquivosArray.length === 0) return;

        const ul = document.createElement('div');
        ul.className = 'list-group rounded-3 overflow-hidden border';

        arquivosArray.forEach((file, idx) => {
            const ext  = file.name.split('.').pop().toLowerCase();
            const icon = getFileIcon(ext);
            const size = formatBytes(file.size);

            const li = document.createElement('div');
            li.className = 'list-group-item d-flex align-items-center gap-3 py-2 px-3';
            li.innerHTML = `
                <i class="bi ${icon} fs-5 text-muted flex-shrink-0"></i>
                <div class="flex-grow-1 min-width-0">
                    <div class="small fw-semibold text-truncate">${file.name}</div>
                    <div class="text-muted" style="font-size:.72rem;">${size}</div>
                </div>
                <select name="privacidade[]" class="form-select form-select-sm rounded-pill border-0 bg-light" style="width:220px;">
                    <option value="0">🔓 Público (todos veem)</option>
                    <option value="1">🔒 Somente próximo responsável</option>
                    <option value="2">👥 Somente meu grupo pastoral</option>
                </select>
                <button type="button" class="btn btn-sm btn-light border rounded-circle p-1 flex-shrink-0"
                        onclick="removerArquivo(${idx})" style="width:28px;height:28px;">
                    <i class="bi bi-x" style="font-size:.8rem;"></i>
                </button>`;
            ul.appendChild(li);
        });

        arquivosList.appendChild(ul);
    }

    window.removerArquivo = function (idx) {
        arquivosArray.splice(idx, 1);
        renderArquivos();
    };

    let isConfirmed = false;
    // Injetar arquivos no submit (recria o input)
    document.getElementById('formTramitar').addEventListener('submit', function (e) {
        e.preventDefault();
        
        const fd = new FormData(this);
        const statusProcesso = fd.get('status_processo');
        
        if ((statusProcesso === '3' || statusProcesso === '2') && !isConfirmed) {
            const modalEl = document.getElementById('modalConfirmarConclusao');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
            return;
        }

        const btn   = document.getElementById('btnSalvar');
        const spin  = document.getElementById('spinnerSalvar');
        const icon  = document.getElementById('iconSalvar');

        btn.disabled    = true;
        spin.classList.remove('d-none');
        icon.classList.add('d-none');

        // Coletar seleções de privacidade
        const privacidades = Array.from(arquivosList.querySelectorAll('select[name="privacidade[]"]'))
            .map(s => s.value);

        // Remover arquivos[]  e privacidade[] gerados automaticamente e adicionar nossos

        // Remover arquivos[]  e privacidade[] gerados automaticamente e adicionar nossos
        fd.delete('arquivos[]');
        fd.delete('privacidade[]');

        arquivosArray.forEach((file, idx) => {
            fd.append('arquivos[]', file);
            fd.append(`privacidade[${idx}]`, privacidades[idx] ?? '0');
        });

        fetch(this.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => {
            if (r.redirected) {
                window.location.href = r.url;
            } else {
                return r.text().then(html => {
                    // Extrai mensagem de erro se houver
                    const parser = new DOMParser();
                    const doc    = parser.parseFromString(html, 'text/html');
                    const erros  = doc.querySelectorAll('.alert-danger li, .alert-danger');
                    if (erros.length) {
                        alert('Erro: ' + Array.from(erros).map(e => e.textContent.trim()).join('\n'));
                    }
                    btn.disabled = false;
                    spin.classList.add('d-none');
                    icon.classList.remove('d-none');
                });
            }
        })
        .catch(() => {
            alert('Erro ao enviar. Tente novamente.');
            btn.disabled = false;
            spin.classList.add('d-none');
            icon.classList.remove('d-none');
        });
    });

    // ── Helpers ───────────────────────────────────────────────────────────
    function formatBytes(b) {
        if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
        if (b >= 1024)    return (b / 1024).toFixed(1) + ' KB';
        return b + ' B';
    }

    function getFileIcon(ext) {
        const map = {
            pdf: 'bi-file-earmark-pdf text-danger',
            doc: 'bi-file-earmark-word text-primary',
            docx: 'bi-file-earmark-word text-primary',
            xls: 'bi-file-earmark-excel text-success',
            xlsx: 'bi-file-earmark-excel text-success',
            csv: 'bi-file-earmark-excel text-success',
            ppt: 'bi-file-earmark-slides text-warning',
            pptx: 'bi-file-earmark-slides text-warning',
            zip: 'bi-file-earmark-zip text-secondary',
            rar: 'bi-file-earmark-zip text-secondary',
            '7z': 'bi-file-earmark-zip text-secondary',
            txt: 'bi-file-earmark-text',
            jpg: 'bi-file-earmark-image text-info',
            jpeg: 'bi-file-earmark-image text-info',
            png: 'bi-file-earmark-image text-info',
            gif: 'bi-file-earmark-image text-info',
            webp: 'bi-file-earmark-image text-info',
        };
        return map[ext] || 'bi-file-earmark';
    }

    // Listener do botão confirmar no modal
    const btnConfirmarConclusao = document.getElementById('btnConfirmarConclusao');
    if (btnConfirmarConclusao) {
        btnConfirmarConclusao.addEventListener('click', function() {
            isConfirmed = true;
            bootstrap.Modal.getInstance(document.getElementById('modalConfirmarConclusao')).hide();
            // Pequeno delay para a animação do modal fechar antes de processar o submit
            setTimeout(() => {
                document.getElementById('formTramitar').dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }, 300);
        });
    }
});
</script>

{{-- Modal de Confirmação de Conclusão --}}
<div class="modal fade" id="modalConfirmarConclusao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body text-center p-5">
                <div class="mb-4">
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                        <i class="bi bi-exclamation-triangle text-warning fs-1"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3">Atenção!</h4>
                <p class="text-muted mb-4">
                    Você está marcando este processo como <strong>Concluído</strong>.<br><br>
                    Isso fará com que o trâmite interno seja pausado e devolvido ao Solicitante original no Portal Externo.
                    O processo só será definitivamente Finalizado após a aprovação do solicitante.
                </p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning rounded-pill px-4 fw-bold" id="btnConfirmarConclusao">
                        Sim, enviar ao solicitante
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@include('modules.processos.partials.modal-preview-anexo')
@endsection
