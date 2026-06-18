{{-- Partial: timeline do processo (carregado via fetch no modal Visualizar) --}}
<span data-protocolo="{{ $processo->protocolo }}" class="d-none"></span>

{{-- Cabeçalho do processo --}}
<div class="d-flex align-items-start gap-3 mb-4 p-3 bg-light rounded-3 border">
    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
        <i class="bi bi-diagram-3-fill text-primary fs-5"></i>
    </div>
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <span class="fw-bold text-dark font-monospace fs-6">{{ $processo->protocolo }}</span>
                <span class="badge ms-2 {{ $processo->assunto_badge_class }}">{{ $processo->assunto_label }}</span>
                <span class="badge ms-1 {{ $processo->prioridade_badge_class }}">{{ $processo->prioridade_label }}</span>
            </div>
            <span class="badge {{ $processo->status_badge_class }} px-3 py-2">{{ $processo->status_label }}</span>
        </div>
        <p class="text-muted small mb-1 mt-1">
            <i class="bi bi-person me-1"></i>{{ $processo->nome_solicitante }} — {{ $processo->cargo_funcao }}
        </p>
        @if($processo->data_limite)
            <p class="text-muted small mb-0">
                <i class="bi bi-calendar-event me-1"></i>Prazo: {{ $processo->data_limite->format('d/m/Y') }}
            </p>
        @endif
    </div>
</div>

{{-- Descrição original --}}
<div class="mb-4">
    <label class="text-uppercase fw-bold small text-muted mb-2 d-block">
        <i class="bi bi-file-text me-1"></i>Descrição da Solicitação
    </label>
    <div class="bg-light border rounded-3 p-3 small">{{ $processo->descricao }}</div>
</div>

{{-- Anexos originais --}}
@if($processo->arquivos->count() > 0)
<div class="mb-4">
    <label class="text-uppercase fw-bold small text-muted mb-2 d-block">
        <i class="bi bi-paperclip me-1"></i>Anexos da Solicitação ({{ $processo->arquivos->count() }})
    </label>
    <div class="d-flex flex-wrap gap-2">
        @foreach($processo->arquivos as $arq)
            <a href="{{ $arq->url }}" target="_blank"
               class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-1">
                <i class="bi bi-file-earmark"></i>
                <span class="text-truncate" style="max-width:160px;">{{ $arq->nome_original }}</span>
                <span class="text-muted" style="font-size:.7rem;">({{ $arq->tamanho_formatado }})</span>
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Responsável atual --}}
<div class="mb-4 d-flex align-items-center gap-2">
    <span class="text-uppercase fw-bold small text-muted">Responsável atual:</span>
    @if($processo->responsavelAtual)
        <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-3 py-2">
            <i class="bi bi-person-check me-1"></i>{{ $processo->responsavelAtual->name ?? $processo->responsavelAtual->user }}
        </span>
    @else
        @if($processo->status == 3)
            <span class="badge bg-warning text-dark px-3 py-2">
                <i class="bi bi-person-fill-up me-1"></i>Pendente do solicitante aprovar
            </span>
        @else
            <span class="badge bg-warning text-dark px-3 py-2">
                <i class="bi bi-hourglass-split me-1"></i>Aguardando alguém assumir
            </span>
        @endif
    @endif
</div>

{{-- Timeline de tramitações --}}
<div class="border-top pt-4">
    <label class="text-uppercase fw-bold small text-muted mb-3 d-block">
        <i class="bi bi-arrow-repeat me-1"></i>Histórico de Tramitações ({{ $processo->tramitacoes->count() }})
    </label>

    <div class="timeline-processos">
        {{-- Bloco fixo: Abertura do Processo --}}
        <div class="timeline-item d-flex gap-3 mb-3">
            <div class="d-flex flex-column align-items-center">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width:38px;height:38px;z-index:1;">
                    <i class="bi bi-folder-plus small"></i>
                </div>
                @if($processo->tramitacoes->isNotEmpty())
                    <div class="flex-grow-1 border-start border-2 border-light" style="min-height:20px;margin-top:2px;"></div>
                @endif
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

        @if($processo->tramitacoes->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                <span class="small">Nenhuma tramitação registrada ainda.</span>
            </div>
        @else
            @foreach($processo->tramitacoes as $idx => $tram)
                @php
                    $isAbertura  = $tram->tipo == 1;
                    $isMencao    = $tram->tipo == 2;
                    $headerClass = 'bg-light';
                    
                    if ($loop->last) {
                        if (in_array($processo->status, [2, 3])) { // Finalizado / Concluído
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
                            if ($tram->status_processo == 0) { // Pendente / Devolvido
                                $iconClass = 'bi-arrow-return-left';
                                $iconColor = 'bg-warning text-dark';
                            } elseif (in_array($tram->status_processo, [2, 3])) { // Finalizado / Concluído
                                $iconClass = 'bi-check-circle-fill';
                                $iconColor = 'bg-success';
                            } elseif ($tram->status_processo == 4) { // Cancelado
                                $iconClass = 'bi-x-circle-fill';
                                $iconColor = 'bg-danger';
                            } else { // Em Processo (Indo)
                                $iconClass = 'bi-arrow-right-circle-fill';
                                $iconColor = 'bg-primary';
                            }
                        }
                    }
                    $num = $idx + 1;
                @endphp

                <div class="timeline-item d-flex gap-3 mb-3">
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
                                        {{ $isMencao ? 'Menção' : ($loop->last ? 'Última tramitação' : $num.'ª Tramitação') }}
                                    </span>
                                    <span class="badge {{ $tram->status_badge_class }} ms-2 small">
                                        {{ $tram->status_label }}
                                    </span>
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
                                    @if($tram->para_user_id || $tram->para_grupo || $tram->status_processo == 3)
                                    <div class="col-sm-6">
                                        <span class="text-muted fw-semibold">Para:</span>
                                        <div class="bg-light border rounded-2 px-2 py-1 mt-1">
                                            @if($tram->status_processo == 3)
                                                <i class="bi bi-person-fill-up text-primary me-1"></i>
                                                <strong>Solicitante inicial</strong>
                                            @elseif($tram->para_user_id)
                                                <i class="bi bi-person-check-fill text-success me-1"></i>
                                                <strong>{{ $tram->paraUser->role_label ?: 'Usuário' }}</strong> /
                                                #{{ $tram->para_user_id }} —
                                                {{ $tram->paraUser ? ($tram->paraUser->name ?? $tram->paraUser->user) : '—' }}
                                            @else
                                                @php $gLabel = self::GRUPOS_PASTORAIS[$tram->para_grupo]['label'] ?? $tram->para_grupo; @endphp
                                                <i class="bi bi-people-fill text-warning me-1"></i>
                                                Grupo: <strong>{{ $gLabel }}</strong>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- Menção --}}
                                @if($tram->mencao)
                                <div class="alert alert-info border-0 py-2 px-3 mb-3 small">
                                    <i class="bi bi-chat-quote me-1"></i>
                                    <strong>Mencionando tramitação de {{ $tram->mencao->created_at->format('d/m/Y') }}:</strong>
                                    {{ \Illuminate\Support\Str::limit($tram->mencao->descricao, 100) }}
                                </div>
                                @endif

                                {{-- Andamento --}}
                                @if($tram->descricao)
                                <div class="mb-3">
                                    <span class="text-muted fw-semibold small">Andamento:</span>
                                    <div class="bg-light border rounded-2 px-3 py-2 mt-1 small">
                                        {!! nl2br(e($tram->descricao)) !!}
                                    </div>
                                </div>
                                @endif

                                {{-- Arquivos da tramitação --}}
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
                                                   class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center gap-1 btn-preview-anexo"
                                                   title="{{ $arq->privacidade_label }}">
                                                    @if($arq->privacidade > 0)
                                                        <i class="bi bi-lock-fill text-warning me-1" style="font-size:.7rem;"></i>
                                                    @endif
                                                    <i class="bi bi-file-earmark"></i>
                                                    <span class="text-truncate" style="max-width:130px;">{{ $arq->nome_original }}</span>
                                                    <span class="text-muted" style="font-size:.7rem;">({{ $arq->tamanho_formatado }})</span>
                                                </button>
                                            @else
                                                <span class="btn btn-sm btn-light border rounded-pill d-flex align-items-center gap-1 disabled text-muted"
                                                      title="Arquivo privado: {{ $arq->privacidade_label }}">
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
        </div>
    @endif
</div>

<style>
.timeline-processos .timeline-item:last-child .flex-grow-1.border-start { display: none; }
</style>
