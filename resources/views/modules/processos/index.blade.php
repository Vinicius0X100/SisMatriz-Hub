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
        <div class="d-flex gap-2">
            <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#novoProcessoModal">
                <i class="bi bi-plus-lg me-2"></i> Novo Processo
            </button>
        </div>
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

    {{-- Area de Filtros e Tabela --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form id="filtrosForm" method="GET" action="{{ route('processos.index') }}">
                <input type="hidden" name="sort_by" id="sortBy" value="{{ $sortBy ?? '' }}">
                <input type="hidden" name="sort_dir" id="sortDir" value="{{ $sortDir ?? 'desc' }}">

                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-12 col-md-12 mb-2">
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

                    {{-- Busca --}}
                    <div class="col-md-3">
                        <label for="filtroBusca" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="filtroBusca" name="busca" class="form-control ps-5 rounded-pill" placeholder="Protocolo, nome, descrição..." value="{{ $filtroBusca }}" style="height: 45px;">
                        </div>
                    </div>
                    
                    {{-- Assunto --}}
                    <div class="col-md-2">
                        <label for="filtroAssunto" class="form-label fw-bold text-muted small">Assunto</label>
                        <select id="filtroAssunto" name="assunto" class="form-select rounded-pill filter-trigger" style="height: 45px;">
                            <option value="">Todos</option>
                            <option value="pascom"      {{ $filtroAssunto == 'pascom'      ? 'selected' : '' }}>PASCOM</option>
                            <option value="compra"      {{ $filtroAssunto == 'compra'      ? 'selected' : '' }}>Compra</option>
                            <option value="autorizacao" {{ $filtroAssunto == 'autorizacao' ? 'selected' : '' }}>Autorização</option>
                            <option value="oficio"      {{ $filtroAssunto == 'oficio'      ? 'selected' : '' }}>Ofício</option>
                            <option value="manutencao"  {{ $filtroAssunto == 'manutencao'  ? 'selected' : '' }}>Manutenção</option>
                            <option value="outro"       {{ $filtroAssunto == 'outro'       ? 'selected' : '' }}>Outro</option>
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-2">
                        <label for="filtroStatus" class="form-label fw-bold text-muted small">Status</label>
                        <select id="filtroStatus" name="status" class="form-select rounded-pill filter-trigger" style="height: 45px;">
                            <option value="">Todos</option>
                            <option value="0" {{ $filtroStatus === '0' ? 'selected' : '' }}>Pendente</option>
                            <option value="1" {{ $filtroStatus === '1' ? 'selected' : '' }}>Em Processo</option>
                            <option value="2" {{ $filtroStatus === '2' ? 'selected' : '' }}>Finalizado</option>
                            <option value="3" {{ $filtroStatus === '3' ? 'selected' : '' }}>Concluído</option>
                            <option value="4" {{ $filtroStatus === '4' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>

                    {{-- Prioridade --}}
                    <div class="col-md-2">
                        <label for="filtroPrioridade" class="form-label fw-bold text-muted small">Prioridade</label>
                        <select id="filtroPrioridade" name="prioridade" class="form-select rounded-pill filter-trigger" style="height: 45px;">
                            <option value="">Todas</option>
                            <option value="4" {{ $filtroPrioridade == '4' ? 'selected' : '' }}>Urgente</option>
                            <option value="3" {{ $filtroPrioridade == '3' ? 'selected' : '' }}>Alta</option>
                            <option value="2" {{ $filtroPrioridade == '2' ? 'selected' : '' }}>Normal</option>
                            <option value="1" {{ $filtroPrioridade == '1' ? 'selected' : '' }}>Baixa</option>
                        </select>
                    </div>

                    <!-- Ações em Massa -->
                    <div class="col-md-3 text-end d-flex justify-content-end">
                        <div class="dropdown">
                            <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                Ações em Massa
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                                <li><a class="dropdown-item text-danger" href="#" onclick="confirmBulkDelete()"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>

            {{-- Tabela de Processos --}}
            @if($processos->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-diagram-3 text-muted" style="font-size: 4rem;"></i>
                    <h4 class="text-muted mt-3">Nenhum processo encontrado</h4>
                    <p class="text-muted small">Ajuste os filtros ou clique em "Novo Processo".</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" width="40" class="text-center px-3 py-3">
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                    </div>
                                </th>
                                <th class="px-3 py-3 text-muted small fw-bold text-uppercase cursor-pointer sortable" data-sort="protocolo" style="font-size:.75rem;">Protocolo <i class="bi bi-arrow-down-up small ms-1"></i></th>
                                <th class="px-3 py-3 text-muted small fw-bold text-uppercase cursor-pointer sortable" data-sort="assunto" style="font-size:.75rem;">Assunto <i class="bi bi-arrow-down-up small ms-1"></i></th>
                                <th class="px-3 py-3 text-muted small fw-bold text-uppercase cursor-pointer sortable" data-sort="nome_solicitante" style="font-size:.75rem;">Solicitante <i class="bi bi-arrow-down-up small ms-1"></i></th>
                                <th class="px-3 py-3 text-muted small fw-bold text-uppercase cursor-pointer sortable" data-sort="prioridade" style="font-size:.75rem;">Prioridade <i class="bi bi-arrow-down-up small ms-1"></i></th>
                                <th class="px-3 py-3 text-muted small fw-bold text-uppercase cursor-pointer sortable" data-sort="status" style="font-size:.75rem;">Status <i class="bi bi-arrow-down-up small ms-1"></i></th>
                                <th class="px-3 py-3 text-muted small fw-bold text-uppercase" style="font-size:.75rem;">Responsável</th>
                                <th class="px-3 py-3 text-muted small fw-bold text-uppercase cursor-pointer sortable" data-sort="data_limite" style="font-size:.75rem;">Prazo <i class="bi bi-arrow-down-up small ms-1"></i></th>
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
                                    $jaEncerrado = in_array($processo->status, [2, 4]); // Removido status 3 (Concluído não exclui)
                                @endphp
                                <tr class="{{ $souResponsavelAtual ? 'table-primary bg-opacity-10' : '' }}">
                                    <td class="text-center px-3 py-3">
                                        <div class="form-check d-flex justify-content-center mb-0">
                                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $processo->id }}">
                                        </div>
                                    </td>
                                    {{-- Protocolo --}}
                                    <td class="px-3 py-3">
                                        <div class="d-flex align-items-center gap-2">
                                            @if(!$podeIniciarGrupo && !$souResponsavelAtual)
                                                <i class="bi bi-lock-fill text-warning" title="Somente a área responsável pode iniciar a tramitação"></i>
                                            @endif
                                            <a href="{{ url('processos') }}/{{ $processo->id }}/timeline" class="text-decoration-none fw-bold font-monospace small">{{ $processo->protocolo }}</a>
                                        </div>
                                        <div class="text-muted" style="font-size:.7rem;">{{ $processo->created_at->format('d/m/Y H:i') }}</div>
                                    </td>

                                    {{-- Assunto --}}
                                    <td class="px-3 py-3">
                                        <span class="badge {{ $processo->assunto_badge_class }} px-2 py-1 rounded-pill fw-normal">
                                            {{ $processo->assunto_label }}
                                        </span>
                                    </td>

                                    {{-- Solicitante --}}
                                    <td class="px-3 py-3">
                                        <div class="fw-semibold text-dark small text-truncate" style="max-width: 150px;">{{ $processo->nome_solicitante }}</div>
                                        <div class="text-muted" style="font-size:.75rem;">{{ $processo->cargo_funcao }}</div>
                                    </td>

                                    {{-- Prioridade --}}
                                    <td class="px-3 py-3">
                                        <span class="badge {{ $processo->prioridade_badge_class }} px-2 py-1 rounded-pill">
                                            {{ $processo->prioridade_label }}
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-3 py-3">
                                        <span class="badge {{ $processo->status_badge_class }} px-2 py-1 fw-normal rounded-pill">
                                            {{ $processo->status_label }}
                                        </span>
                                    </td>

                                    {{-- Responsável --}}
                                    <td class="px-3 py-3">
                                        @if($processo->responsavelAtual)
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-person-check me-1 text-success"></i>
                                                <span class="small text-dark text-truncate" style="max-width:100px;">{{ $processo->responsavelAtual->name ?? $processo->responsavelAtual->user }}</span>
                                            </div>
                                        @else
                                            @if(in_array($processo->status, [2, 4]))
                                                <span class="text-muted small">—</span>
                                            @elseif($processo->status == 3)
                                                <span class="badge bg-warning text-dark px-2 py-1 fw-normal rounded-pill">
                                                    <i class="bi bi-person-fill-up me-1"></i>Pendente do solicitante
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
                                    <td class="px-4 py-3 text-end text-nowrap">
                                        {{-- Dar Andamento --}}
                                        @if(!in_array($processo->status, [2, 4, 3]) && $podeDarAndamento)
                                            <button type="button"
                                                    class="btn btn-sm btn-primary rounded-pill btn-dar-andamento px-3 me-1"
                                                    data-id="{{ $processo->id }}"
                                                    data-protocolo="{{ $processo->protocolo }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmarAndamentoModal"
                                                    title="Dar Andamento">
                                                <i class="bi bi-arrow-right-circle me-1"></i> Dar Andamento
                                            </button>
                                        @elseif(!in_array($processo->status, [2, 4, 3]))
                                            <span class="btn btn-sm btn-light border rounded-pill disabled text-muted px-3 me-1"
                                                  title="Somente a área responsável pode tramitar inicialmente">
                                                <i class="bi bi-lock-fill me-1"></i> Restrito
                                            </span>
                                        @endif

                                        <button type="button"
                                                class="btn btn-sm btn-light border rounded-pill btn-visualizar px-3 ms-1 text-primary fw-semibold"
                                                data-id="{{ $processo->id }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#visualizarModal"
                                                title="Visualizar Processo">
                                            <i class="bi bi-eye me-1"></i> Visualizar
                                        </button>

                                        {{-- Excluir Processo (Somente Finalizado/Cancelado) --}}
                                        @if($jaEncerrado)
                                            <button class="btn btn-sm btn-light border rounded-pill text-danger ms-1" onclick="confirmDelete({{ $processo->id }})" title="Excluir Processo">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $processos->links() }}</div>
            @endif
        </div>
    </div>
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

@include('modules.processos.partials.modal-novo-processo')

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    @if(request()->has('show_processo'))
    setTimeout(() => {
        const visualizarModalEl = document.getElementById('visualizarModal');
        const dummyBtn = document.createElement('button');
        dummyBtn.dataset.id = "{{ request('show_processo') }}";
        
        // Passa o dummyBtn como relatedTarget para que o evento 'show.bs.modal' dispare corretamente
        const myModal = new bootstrap.Modal(visualizarModalEl);
        
        // Listener temporário que vai fechar e limpar a URL quando o modal for fechado (melhorando UX)
        visualizarModalEl.addEventListener('hidden.bs.modal', function() {
            const url = new URL(window.location);
            url.searchParams.delete('show_processo');
            window.history.replaceState({}, '', url);
        }, { once: true });

        myModal.show(dummyBtn);
    }, 100);
    @endif

    // ── Preservação de Checkboxes (Local Storage) ─────────────────────────
    const STORAGE_KEY = 'processos_selected_ids';
    let globalSelectedIds = new Set(JSON.parse(localStorage.getItem(STORAGE_KEY)) || []);

    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActionsBtn = document.getElementById('bulkActions');

    function updateBulkActionsButton() {
        if(bulkActionsBtn) {
            bulkActionsBtn.disabled = globalSelectedIds.size === 0;
            const badge = globalSelectedIds.size > 0 ? ` <span class="badge bg-danger ms-1">${globalSelectedIds.size}</span>` : '';
            bulkActionsBtn.innerHTML = `Ações em Massa${badge}`;
        }
        localStorage.setItem(STORAGE_KEY, JSON.stringify(Array.from(globalSelectedIds)));
    }

    if (rowCheckboxes.length > 0) {
        rowCheckboxes.forEach(cb => {
            if(globalSelectedIds.has(cb.value)) cb.checked = true;
            cb.addEventListener('change', function() {
                if(this.checked) globalSelectedIds.add(this.value);
                else globalSelectedIds.delete(this.value);
                updateBulkActionsButton();
                updateSelectAllState();
            });
        });
    }

    function updateSelectAllState() {
        if(!selectAllCheckbox) return;
        const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
        const someChecked = Array.from(rowCheckboxes).some(c => c.checked);
        selectAllCheckbox.checked = allChecked && rowCheckboxes.length > 0;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }

    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                cb.checked = this.checked;
                if(this.checked) globalSelectedIds.add(cb.value);
                else globalSelectedIds.delete(cb.value);
            });
            updateBulkActionsButton();
        });
        updateSelectAllState();
    }
    updateBulkActionsButton();

    // ── Exclusão Individual ───────────────────────────────────────────────
    window.confirmDelete = function(id) {
        Swal.fire({
            title: 'Você tem certeza?',
            text: "O histórico completo de trâmites e arquivos será excluído permanentemente. Isso não pode ser desfeito!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ url('processos') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                }).then(res => res.json()).then(data => {
                    Swal.fire('Excluído!', data.message, 'success').then(() => location.reload());
                }).catch(err => {
                    Swal.fire('Erro!', 'Ocorreu um erro ao excluir.', 'error');
                });
            }
        });
    };

    // ── Exclusão Múltipla ─────────────────────────────────────────────────
    window.confirmBulkDelete = function() {
        const ids = Array.from(globalSelectedIds);
        if(ids.length === 0) return;

        Swal.fire({
            title: 'Excluir Selecionados?',
            text: `Tem certeza de que deseja excluir os ${ids.length} processos selecionados (somente os finalizados/cancelados serão apagados)? O histórico completo de trâmites e arquivos deles será perdido!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ route('processos.bulk-delete') }}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids })
                }).then(res => res.json()).then(data => {
                    globalSelectedIds.clear();
                    localStorage.removeItem(STORAGE_KEY);
                    Swal.fire('Excluídos!', data.message, 'success').then(() => location.reload());
                }).catch(err => {
                    Swal.fire('Erro!', 'Ocorreu um erro ao excluir.', 'error');
                });
            }
        });
    };

    // ── Ordenação ─────────────────────────────────────────────────────────
    document.querySelectorAll('.sortable').forEach(el => {
        el.addEventListener('click', function() {
            const sortField = this.dataset.sort;
            const currentSort = document.getElementById('sortBy').value;
            let currentDir = document.getElementById('sortDir').value;

            if (currentSort === sortField) {
                currentDir = currentDir === 'asc' ? 'desc' : 'asc';
            } else {
                currentDir = 'asc';
            }

            document.getElementById('sortBy').value = sortField;
            document.getElementById('sortDir').value = currentDir;
            document.getElementById('filtrosForm').submit();
        });
    });

    // ── Filtros dinâmicos (Submissão e Debounce) ──────────────────────────
    let searchTimer = null;
    function submitFiltros() {
        document.getElementById('filtrosForm').submit();
    }

    document.querySelectorAll('.filter-trigger:not([data-delay])').forEach(el => {
        el.addEventListener('change', submitFiltros);
    });

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
            const protoEl = container.querySelector('[data-protocolo]');
            if (protoEl) protocolo.textContent = protoEl.dataset.protocolo;
        })
        .catch(() => {
            container.innerHTML = '<div class="alert alert-danger">Erro ao carregar o processo.</div>';
        });
    });

    // ── Modal Confirmar Dar Andamento ─────────────────────────────────────
    const andamentoModal = document.getElementById('confirmarAndamentoModal');
    if (andamentoModal) {
        andamentoModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            const id  = btn.dataset.id;
            const protocolo = btn.dataset.protocolo;

            document.getElementById('andamentoProtocolo').textContent = protocolo ? `(${protocolo})` : '';
            document.getElementById('formDarAndamento').action = `/processos/${id}/dar-andamento`;
        });
    }
});
</script>
@endsection
