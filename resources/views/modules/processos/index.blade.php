@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3 text-primary"></i> Processos
            </h2>
            <p class="text-muted small mb-0">Gerencie e tramite os processos paroquiais.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                data-bs-toggle="modal" data-bs-target="#novoProcessoModal">
            <i class="bi bi-plus-lg me-2"></i> Novo Processo
        </button>
    </div>

    {{-- Alertas de sessão --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4">
            <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filtros dinâmicos --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form id="filtrosForm" method="GET" action="{{ route('processos.index') }}">
                <div class="row g-3 align-items-center">

                    {{-- Checkboxes --}}
                    <div class="col-12 col-md-auto">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input filter-trigger" type="checkbox"
                                       id="minhaPastoral" name="minha_pastoral" value="1"
                                       {{ $minhaPastoral ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="minhaPastoral">
                                    <i class="bi bi-people-fill text-primary me-1"></i>
                                    Processos da minha pastoral/movimento
                                </label>
                            </div>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input filter-trigger" type="checkbox"
                                       id="souResponsavel" name="sou_responsavel" value="1"
                                       {{ $souResponsavel ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold small" for="souResponsavel">
                                    <i class="bi bi-person-check-fill text-success me-1"></i>
                                    Você é o responsável
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md">
                        <div class="row g-2">
                            {{-- Busca --}}
                            <div class="col-sm-4">
                                <input type="text" class="form-control form-control-sm rounded-3 filter-trigger"
                                       name="busca" id="filtroBusca"
                                       value="{{ $filtroBusca }}"
                                       placeholder="Protocolo, nome, descrição..."
                                       data-delay="500">
                            </div>
                            {{-- Assunto --}}
                            <div class="col-sm-3">
                                <select class="form-select form-select-sm rounded-3 filter-trigger" name="assunto" id="filtroAssunto">
                                    <option value="">Todos os assuntos</option>
                                    <option value="pascom"      {{ $filtroAssunto == 'pascom'      ? 'selected' : '' }}>PASCOM</option>
                                    <option value="compra"      {{ $filtroAssunto == 'compra'      ? 'selected' : '' }}>Compra</option>
                                    <option value="autorizacao" {{ $filtroAssunto == 'autorizacao' ? 'selected' : '' }}>Autorização</option>
                                    <option value="oficio"      {{ $filtroAssunto == 'oficio'      ? 'selected' : '' }}>Ofício</option>
                                    <option value="manutencao"  {{ $filtroAssunto == 'manutencao'  ? 'selected' : '' }}>Manutenção</option>
                                    <option value="outro"       {{ $filtroAssunto == 'outro'       ? 'selected' : '' }}>Outro</option>
                                </select>
                            </div>
                            {{-- Status --}}
                            <div class="col-sm-3">
                                <select class="form-select form-select-sm rounded-3 filter-trigger" name="status" id="filtroStatus">
                                    <option value="">Todos os status</option>
                                    <option value="0" {{ $filtroStatus === '0' ? 'selected' : '' }}>Pendente</option>
                                    <option value="1" {{ $filtroStatus === '1' ? 'selected' : '' }}>Em Processo</option>
                                    <option value="2" {{ $filtroStatus === '2' ? 'selected' : '' }}>Finalizado</option>
                                    <option value="3" {{ $filtroStatus === '3' ? 'selected' : '' }}>Concluído</option>
                                    <option value="4" {{ $filtroStatus === '4' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            {{-- Prioridade --}}
                            <div class="col-sm-2">
                                <select class="form-select form-select-sm rounded-3 filter-trigger" name="prioridade" id="filtroPrioridade">
                                    <option value="">Prioridade</option>
                                    <option value="4" {{ $filtroPrioridade == '4' ? 'selected' : '' }}>Urgente</option>
                                    <option value="3" {{ $filtroPrioridade == '3' ? 'selected' : '' }}>Alta</option>
                                    <option value="2" {{ $filtroPrioridade == '2' ? 'selected' : '' }}>Normal</option>
                                    <option value="1" {{ $filtroPrioridade == '1' ? 'selected' : '' }}>Baixa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Tabela de Processos --}}
    @if($processos->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-diagram-3 text-muted" style="font-size: 4rem;"></i>
            <h4 class="text-muted mt-3">Nenhum processo encontrado</h4>
            <p class="text-muted small">Ajuste os filtros ou clique em "Novo Processo".</p>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Protocolo</th>
                            <th class="px-3 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Assunto</th>
                            <th class="px-3 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Solicitante</th>
                            <th class="px-3 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Prioridade</th>
                            <th class="px-3 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Status</th>
                            <th class="px-3 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Responsável</th>
                            <th class="px-3 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Prazo</th>
                            <th class="px-4 py-3 text-muted small fw-bold text-uppercase text-end" style="font-size:.75rem;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processos as $processo)
                            @php
                                $podeIniciar = \App\Http\Controllers\ProcessoController::ASSUNTO_GRUPOS[$processo->assunto] ?? 'administracao';
                                $podeIniciarGrupo = in_array($podeIniciar, $userGrupos);
                                $souResponsavelAtual = $processo->responsavel_atual_user_id === Auth::id();
                                $podeDarAndamento = $podeIniciarGrupo || $souResponsavelAtual
                                    || ($processo->tramitacoes->last() && $processo->tramitacoes->last()->para_user_id === Auth::id());
                                $jaEncerrado = in_array($processo->status, [3, 4]);
                            @endphp
                            <tr class="{{ $souResponsavelAtual ? 'table-primary bg-opacity-10' : '' }}">
                                {{-- Protocolo --}}
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if(!$podeIniciarGrupo && !$souResponsavelAtual)
                                            <i class="bi bi-lock-fill text-warning" title="Somente a área responsável pode iniciar a tramitação"></i>
                                        @endif
                                        <span class="fw-bold font-monospace text-dark small">{{ $processo->protocolo }}</span>
                                    </div>
                                    <div class="text-muted" style="font-size:.7rem;">{{ $processo->created_at->format('d/m/Y H:i') }}</div>
                                </td>

                                {{-- Assunto --}}
                                <td class="px-3 py-3">
                                    <span class="badge {{ $processo->assunto_badge_class }} px-2 py-1">
                                        {{ $processo->assunto_label }}
                                    </span>
                                </td>

                                {{-- Solicitante --}}
                                <td class="px-3 py-3">
                                    <div class="fw-semibold text-dark small">{{ $processo->nome_solicitante }}</div>
                                    <div class="text-muted" style="font-size:.75rem;">{{ $processo->cargo_funcao }}</div>
                                </td>

                                {{-- Prioridade --}}
                                <td class="px-3 py-3">
                                    <span class="badge {{ $processo->prioridade_badge_class }} px-2 py-1">
                                        {{ $processo->prioridade_label }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td class="px-3 py-3">
                                    <span class="badge {{ $processo->status_badge_class }} px-2 py-1">
                                        {{ $processo->status_label }}
                                    </span>
                                </td>

                                {{-- Responsável --}}
                                <td class="px-3 py-3">
                                    @if($processo->responsavelAtual)
                                        <div class="d-flex align-items-center gap-2">
                                            @php
                                                $r = $processo->responsavelAtual;
                                                $rName = $r->name ?? $r->user;
                                                $rParts = explode(' ', trim($rName));
                                                $rInitials = strtoupper(substr($rParts[0], 0, 1));
                                                if(count($rParts) > 1) $rInitials .= strtoupper(substr(end($rParts), 0, 1));
                                            @endphp
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                 style="width:28px;height:28px;font-size:.7rem;">{{ $rInitials }}</div>
                                            <span class="small text-dark text-truncate" style="max-width:100px;">{{ $rName }}</span>
                                        </div>
                                    @else
                                        @if($processo->status == 3)
                                            <span class="badge bg-warning text-dark px-2 py-1 fw-normal rounded-pill">
                                                <i class="bi bi-person-fill-up me-1"></i>Pendente do solicitante aprovar
                                            </span>
                                        @else
                                            <span class="text-muted small">
                                                <i class="bi bi-hourglass-split me-1"></i>Aguardando
                                            </span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Prazo --}}
                                <td class="px-3 py-3">
                                    @if($processo->data_limite)
                                        @php $vencido = $processo->data_limite->isPast() && !in_array($processo->status, [2,3,4]); @endphp
                                        <span class="small {{ $vencido ? 'text-danger fw-bold' : 'text-muted' }}">
                                            @if($vencido)<i class="bi bi-exclamation-triangle-fill me-1"></i>@endif
                                            {{ $processo->data_limite->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                                {{-- Ações --}}
                                <td class="px-4 py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- Visualizar Processo --}}
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary rounded-pill btn-visualizar"
                                                data-id="{{ $processo->id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#visualizarModal"
                                                title="Visualizar Processo">
                                            <i class="bi bi-eye me-1"></i> Visualizar
                                        </button>

                                        {{-- Dar Andamento --}}
                                        @if(!$jaEncerrado && $podeDarAndamento)
                                            <button type="button"
                                                    class="btn btn-sm btn-primary rounded-pill btn-dar-andamento"
                                                    data-id="{{ $processo->id }}"
                                                    data-protocolo="{{ $processo->protocolo }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmarAndamentoModal"
                                                    title="Dar Andamento">
                                                <i class="bi bi-arrow-right-circle me-1"></i> Dar Andamento
                                            </button>
                                        @elseif(!$jaEncerrado)
                                            <span class="btn btn-sm btn-light border rounded-pill disabled text-muted"
                                                  title="Somente a área responsável pode tramitar inicialmente">
                                                <i class="bi bi-lock-fill me-1"></i> Restrito
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $processos->links() }}</div>
    @endif
</div>

{{-- ================================================================ --}}
{{-- Modal: Visualizar Processo                                         --}}
{{-- ================================================================ --}}
<div class="modal fade" id="visualizarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Visualizar Processo</h5>
                        <p class="mb-0 small text-muted" id="modalProtocolo">Carregando...</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="timelineContainer">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-3 small">Carregando dados do processo...</p>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================ --}}
{{-- Modal: Confirmar Dar Andamento                                     --}}
{{-- ================================================================ --}}
<div class="modal fade" id="confirmarAndamentoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-warning bg-opacity-10">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                    <h5 class="modal-title fw-bold mb-0">Confirmar Responsabilidade</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-dark">
                    Ao clicar em <strong>Assumir e Tramitar</strong>, você estará confirmando que
                    é o responsável pela continuidade deste processo.
                </p>
                <div class="alert alert-light border-start border-4 border-warning">
                    <div class="d-flex gap-2">
                        <i class="bi bi-info-circle-fill text-warning mt-1 flex-shrink-0"></i>
                        <div class="small">
                            O sistema registrará seu nome, cargo e a data/hora em que você assumiu o processo
                            <strong id="andamentoProtocolo"></strong>. Você poderá preencher o andamento na próxima tela.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="formDarAndamento" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">
                        <i class="bi bi-arrow-right-circle me-2"></i> Assumir e Tramitar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ================================================================ --}}
{{-- Modal: Novo Processo Interno                                       --}}
{{-- ================================================================ --}}
<div class="modal fade" id="novoProcessoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-primary text-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle-fill fs-4"></i>
                    <h5 class="modal-title fw-bold">Novo Processo Interno</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('processos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border-primary border-start border-4 shadow-sm mb-4">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle-fill text-primary fs-5 flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-primary mb-1">Processo Interno</h6>
                                <p class="small text-muted mb-0">
                                    O processo será registrado com seu nome e cargo. Uma tramitação de abertura será criada automaticamente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Assunto <span class="text-danger">*</span></label>
                            <select name="assunto" class="form-select rounded-3" required>
                                <option value="">Selecione...</option>
                                <option value="pascom">PASCOM</option>
                                <option value="compra">Compra</option>
                                <option value="autorizacao">Autorização</option>
                                <option value="oficio">Ofício</option>
                                <option value="manutencao">Manutenção</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Prioridade <span class="text-danger">*</span></label>
                            <select name="prioridade" class="form-select rounded-3" required>
                                <option value="2" selected>Normal</option>
                                <option value="1">Baixa</option>
                                <option value="3">Alta</option>
                                <option value="4">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Prazo</label>
                            <input type="date" name="data_limite" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Descrição <span class="text-danger">*</span></label>
                            <textarea name="descricao" class="form-control rounded-3" rows="4" required
                                      placeholder="Descreva detalhadamente o processo..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Anexos (opcional)</label>
                            <div class="p-4 border border-2 border-dashed rounded-4 bg-light text-center position-relative">
                                <i class="bi bi-cloud-arrow-up text-primary fs-1 mb-2 d-block"></i>
                                <span class="fw-bold text-dark d-block mb-1">Arraste ou clique para selecionar</span>
                                <span class="small text-muted d-block mb-3">PDF, Imagens, Word, Excel — sem vídeos (máx. 50 arquivos)</span>
                                <input class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                       style="cursor:pointer;" type="file"
                                       name="arquivos[]" multiple
                                       accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.zip,.rar,.7z">
                            </div>
                            <div id="novoArquivosList" class="mt-2 small text-muted"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i> Criar Processo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Filtros dinâmicos ─────────────────────────────────────────────────
    let searchTimer = null;

    @if(request()->has('show_processo'))
    setTimeout(() => {
        const visualizarModalEl = document.getElementById('visualizarModal');
        const dummyBtn = document.createElement('button');
        dummyBtn.dataset.id = "{{ request('show_processo') }}";
        const myModal = new bootstrap.Modal(visualizarModalEl);
        myModal.show(dummyBtn);
    }, 100);
    @endif

    function submitFiltros() {
        document.getElementById('filtrosForm').submit();
    }

    // Selects e checkboxes: submitem imediatamente
    document.querySelectorAll('.filter-trigger:not([data-delay])').forEach(el => {
        el.addEventListener('change', submitFiltros);
    });

    // Campo de busca: debounce 500ms
    const buscaEl = document.getElementById('filtroBusca');
    if (buscaEl) {
        buscaEl.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(submitFiltros, 500);
        });
    }

    // ── Modal Visualizar Processo ─────────────────────────────────────────
    const visualizarModal = document.getElementById('visualizarModal');
    visualizarModal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        const id  = btn.dataset.id;
        const container = document.getElementById('timelineContainer');
        const protocolo = document.getElementById('modalProtocolo');

        protocolo.textContent = 'Carregando...';
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="text-muted mt-3 small">Carregando dados do processo...</p>
            </div>`;

        fetch(`/processos/${id}/timeline`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            container.innerHTML = html;
            // Tenta pegar o protocolo do HTML carregado
            const protoEl = container.querySelector('[data-protocolo]');
            if (protoEl) protocolo.textContent = protoEl.dataset.protocolo;
        })
        .catch(() => {
            container.innerHTML = '<div class="alert alert-danger">Erro ao carregar o processo.</div>';
        });
    });

    // ── Modal Confirmar Dar Andamento ─────────────────────────────────────
    const andamentoModal = document.getElementById('confirmarAndamentoModal');
    andamentoModal.addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        const id  = btn.dataset.id;
        const protocolo = btn.dataset.protocolo;

        document.getElementById('andamentoProtocolo').textContent = protocolo ? `(${protocolo})` : '';
        document.getElementById('formDarAndamento').action = `/processos/${id}/dar-andamento`;
    });

    // ── Preview de arquivos no modal Novo Processo ────────────────────────
    const novoInput = document.querySelector('[name="arquivos[]"]');
    if (novoInput) {
        novoInput.addEventListener('change', function () {
            const list = document.getElementById('novoArquivosList');
            list.innerHTML = '';
            if (!this.files.length) return;
            const ul = document.createElement('ul');
            ul.className = 'list-unstyled mb-0';
            Array.from(this.files).forEach(f => {
                const li = document.createElement('li');
                li.className = 'd-flex align-items-center mb-1';
                li.innerHTML = `<i class="bi bi-check-circle-fill text-success me-2"></i>
                    ${f.name} <span class="text-muted ms-2">(${formatBytes(f.size)})</span>`;
                ul.appendChild(li);
            });
            list.appendChild(ul);
        });
    }

    function formatBytes(b) {
        if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
        if (b >= 1024)    return (b / 1024).toFixed(1) + ' KB';
        return b + ' B';
    }
});
</script>

@include('modules.processos.partials.modal-preview-anexo')
@endsection
