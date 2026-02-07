@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Inscrições de Primeira Eucaristia</h2>
            <p class="text-muted small mb-0">Gerencie as inscrições recebidas para a Primeira Eucaristia.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inscrições de Primeira Eucaristia</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $deadlineActive = false;
        $deadlineMessage = 'Prazo de inscrições não definido.';
        $deadlineColor = 'secondary';
        $daysRemaining = 0;
        $deadlineIcon = 'bi-calendar-x';

        if (isset($deadline)) {
            $now = \Carbon\Carbon::now();
            $start = \Carbon\Carbon::parse($deadline->data_inicio)->startOfDay();
            $end = \Carbon\Carbon::parse($deadline->data_fim)->endOfDay();

            if ($deadline->ativo) {
                if ($now->between($start, $end)) {
                    $deadlineActive = true;
                    $daysRemaining = ceil($now->floatDiffInDays($end, false));
                    $deadlineMessage = "Inscrições abertas! Restam {$daysRemaining} dias.";
                    $deadlineColor = 'success';
                    $deadlineIcon = 'bi-calendar-check';
                } elseif ($now->lt($start)) {
                    $deadlineMessage = "Inscrições abrirão em " . $start->format('d/m/Y');
                    $deadlineColor = 'info';
                    $deadlineIcon = 'bi-calendar-plus';
                } else {
                    $deadlineMessage = "Inscrições encerradas em " . $end->format('d/m/Y');
                    $deadlineColor = 'danger';
                    $deadlineIcon = 'bi-calendar-x';
                }
            } else {
                $deadlineMessage = "Inscrições pausadas/inativas.";
                $deadlineColor = 'danger';
                $deadlineIcon = 'bi-pause-circle';
            }
        }
    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-{{ $deadlineColor }} bg-opacity-10">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center p-4">
            <div class="d-flex align-items-center mb-3 mb-md-0">
                <div class="rounded-circle bg-{{ $deadlineColor }} text-white p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi {{ $deadlineIcon }} fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-{{ $deadlineColor }} mb-1">{{ $deadlineMessage }}</h5>
                    @if(isset($deadline))
                        <p class="mb-0 text-{{ $deadlineColor }} small opacity-75">
                            Período: {{ $deadline->data_inicio->format('d/m/Y') }} até {{ $deadline->data_fim->format('d/m/Y') }}
                        </p>
                    @endif
                </div>
            </div>
            <button class="btn btn-{{ $deadlineColor }} rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#deadlineModal">
                <i class="bi bi-gear me-2"></i> Configurar Prazos
            </button>
            <button class="btn btn-light border rounded-pill px-4 fw-bold shadow-sm ms-2" data-bs-toggle="modal" data-bs-target="#taxModal">
                <i class="bi bi-currency-dollar me-2"></i> Configurar Taxas
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Inscrições</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Aprovados</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['approved'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-clock-history fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Pendentes</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['pending'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-x-circle fs-3 text-danger"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Reprovados</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['rejected'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            
            <!-- Toolbar -->
            <div class="d-flex flex-wrap gap-3 mb-4 align-items-end">
                <!-- Search -->
                <div class="flex-grow-1" style="min-width: 250px;">
                    <label class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search-input" class="form-control rounded-pill bg-light border-0 ps-5" placeholder="Nome..." value="{{ request('search') }}">
                    </div>
                </div>

                <!-- Filters -->
                <div style="min-width: 150px;">
                    <label class="form-label fw-bold text-muted small">Status</label>
                    <select id="status-filter" class="form-select rounded-pill bg-light border-0">
                        <option value="">Todos</option>
                        <option value="0">Pendente</option>
                        <option value="1">Aprovado</option>
                        <option value="2">Reprovado</option>
                    </select>
                </div>

                <div style="min-width: 150px;">
                    <label class="form-label fw-bold text-muted small">Batismo</label>
                    <select id="batismo-filter" class="form-select rounded-pill bg-light border-0">
                        <option value="">Todos</option>
                        <option value="1">Com certidão</option>
                        <option value="0">Sem certidão</option>
                    </select>
                </div>

                <div style="min-width: 140px;">
                    <label class="form-label fw-bold text-muted small">De</label>
                    <input type="date" id="date-from" class="form-control rounded-pill bg-light border-0">
                </div>

                <div style="min-width: 140px;">
                    <label class="form-label fw-bold text-muted small">Até</label>
                    <input type="date" id="date-to" class="form-control rounded-pill bg-light border-0">
                </div>

                <!-- Mass Actions -->
                <div class="ms-auto d-flex gap-2">
                     <div>
                        <label class="form-label fw-bold text-muted small d-block">&nbsp;</label>
                        <button class="btn btn-success border rounded-pill" type="button" onclick="exportExcel()">
                            <i class="bi bi-file-earmark-excel me-2"></i> Exportar
                        </button>
                     </div>
                     <div>
                        <label class="form-label fw-bold text-muted small d-block">&nbsp;</label>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-light border rounded-pill dropdown-toggle" type="button" id="massActionsBtn" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                                Ações em Massa (<span id="selectedCount">0</span>)
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                <li>
                                    <button class="dropdown-item text-danger" onclick="confirmBulkDelete()">
                                        <i class="bi bi-trash me-2"></i> Deletar selecionados
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-primary" onclick="bulkPrint()">
                                        <i class="bi bi-printer me-2"></i> Imprimir fichas selecionadas
                                    </button>
                                </li>
                                <li>
                                    <button class="dropdown-item text-info" onclick="openShareModal()">
                                        <i class="bi bi-share me-2"></i> Compartilhar
                                    </button>
                                </li>
                            </ul>
                        </div>
                     </div>
                </div>
            </div>

            <!-- Table Container -->
            <div id="table-content">
                @include('modules.inscricoes-eucaristia.partials.list')
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 d-inline-block mb-3">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-danger"></i>
                </div>
                <h5 class="fw-bold mb-2">Tem certeza?</h5>
                <p class="text-muted mb-0" id="deleteModalMessage">Você está prestes a excluir este registro. Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    <input type="hidden" name="_method" id="deleteFormMethod" value="DELETE">
                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash me-2"></i> Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Opções de Impressão -->
<div class="modal fade" id="printOptionsModal" tabindex="-1" aria-labelledby="printOptionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="printOptionsModalLabel">Imprimir Fichas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Selecione quais fichas deseja imprimir.</p>
                <form id="printOptionsForm" action="{{ route('inscricoes-eucaristia.bulk-print') }}" method="POST" target="_blank">
                    @csrf
                    <!-- Hidden inputs for filters -->
                    <input type="hidden" name="search" id="print-search">
                    <input type="hidden" name="status" id="print-status">
                    <input type="hidden" name="batismo" id="print-batismo">
                    <input type="hidden" name="date_from" id="print-date-from">
                    <input type="hidden" name="date_to" id="print-date-to">
                    
                    <input type="hidden" name="ids" id="print-ids">

                    <div class="d-grid gap-2">
                        <div class="form-check p-3 border rounded-3 hover-bg-light cursor-pointer">
                            <input class="form-check-input" type="radio" name="scope" id="printScopeSelected" value="selected" checked onchange="togglePrintScope()">
                            <label class="form-check-label w-100 cursor-pointer" for="printScopeSelected">
                                <span class="d-block fw-bold text-dark">Fichas Individuais (Selecionados)</span>
                                <span class="d-block text-muted small">Imprime apenas os <span id="modalSelectedCount">0</span> itens marcados na lista.</span>
                            </label>
                        </div>
                        <div class="form-check p-3 border rounded-3 hover-bg-light cursor-pointer">
                            <input class="form-check-input" type="radio" name="scope" id="printScopeAll" value="all" onchange="togglePrintScope()">
                            <label class="form-check-label w-100 cursor-pointer" for="printScopeAll">
                                <span class="d-block fw-bold text-dark">Todas as Fichas (Filtradas)</span>
                                <span class="d-block text-muted small">Imprime todos os resultados da busca atual.</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-light border rounded-pill me-2" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" onclick="setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('printOptionsModal')).hide(), 500)">
                            <i class="bi bi-printer me-2"></i> Imprimir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shareModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Compartilhar Fichas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-4">Compartilhe as fichas selecionadas com outros usuários do sistema via e-mail.</p>
                
                <div class="mb-4">
                    <label class="form-label fw-bold small">Buscar Usuário</label>
                    <div class="position-relative">
                        <input type="text" id="user-search-input" class="form-control rounded-pill bg-light border-0 ps-4" placeholder="Digite o nome do usuário...">
                        <div id="user-search-results" class="position-absolute w-100 bg-white shadow rounded-3 mt-1 overflow-hidden" style="z-index: 1000; display: none; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Usuários Selecionados</label>
                    <div id="selected-users-container" class="d-flex flex-wrap gap-2 p-3 bg-light rounded-3" style="min-height: 60px;">
                        <span class="text-muted small w-100 text-center py-2" id="no-users-msg">Nenhum usuário selecionado</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Mensagem (Opcional)</label>
                    <textarea id="share-message" class="form-control bg-light border-0 rounded-3" rows="3" placeholder="Escreva uma mensagem..."></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-light border rounded-pill me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" onclick="sendShare()">
                        <i class="bi bi-send me-2"></i> Enviar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deadline Modal -->
<div class="modal fade" id="deadlineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Configurar Prazos de Inscrição</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="deadlineForm" action="{{ route('inscricoes-eucaristia.store-deadline') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Data de Início</label>
                        <input type="date" name="data_inicio" class="form-control rounded-pill bg-light border-0" value="{{ isset($deadline) ? $deadline->data_inicio->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Data de Fim</label>
                        <input type="date" name="data_fim" class="form-control rounded-pill bg-light border-0" value="{{ isset($deadline) ? $deadline->data_fim->format('Y-m-d') : '' }}" required>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="ativo" value="0">
                        <input class="form-check-input" type="checkbox" id="deadlineActive" name="ativo" value="1" {{ isset($deadline) && $deadline->ativo ? 'checked' : '' }}>
                        <label class="form-check-label" for="deadlineActive">Prazo Ativo</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill fw-bold">Salvar Configuração</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tax Modal -->
<div class="modal fade" id="taxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Configurar Taxas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="taxForm" action="{{ route('inscricoes-eucaristia.store-tax-config') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Título da Taxa (Ex: Taxa de Inscrição)</label>
                        <input type="text" name="titulo" class="form-control rounded-pill bg-light border-0" value="{{ $taxConfig->metodo_pagamento_label ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Chave PIX</label>
                        <input type="text" name="chave_pix" class="form-control rounded-pill bg-light border-0" value="{{ $taxConfig->metodo_pagamento_valor ?? '' }}">
                    </div>
                    <div class="mb-3">
                         <label class="form-label fw-bold small">Opções de Valores</label>
                         <div id="taxItemsContainer">
                            @if(isset($taxConfig) && $taxConfig->items->count() > 0)
                                @foreach($taxConfig->items as $index => $item)
                                    <div class="d-flex gap-2 mb-2 tax-item">
                                        <input type="text" name="items[{{ $index }}][nome]" class="form-control rounded-pill bg-light border-0" placeholder="Nome (Ex: Com Bíblia)" value="{{ $item->nome }}" required>
                                        <input type="text" name="items[{{ $index }}][valor]" class="form-control rounded-pill bg-light border-0" placeholder="Valor" style="max-width: 140px;" value="{{ 'R$ ' . number_format($item->valor, 2, ',', '.') }}" oninput="formatCurrency(this)" required>
                                        <button type="button" class="btn btn-outline-danger rounded-circle" onclick="removeTaxItem(this)"><i class="bi bi-trash"></i></button>
                                    </div>
                                @endforeach
                            @else
                                <div class="d-flex gap-2 mb-2 tax-item">
                                    <input type="text" name="items[0][nome]" class="form-control rounded-pill bg-light border-0" placeholder="Nome (Ex: Taxa Única)" required>
                                    <input type="text" name="items[0][valor]" class="form-control rounded-pill bg-light border-0" placeholder="Valor" style="max-width: 140px;" oninput="formatCurrency(this)" required>
                                    <button type="button" class="btn btn-outline-danger rounded-circle" onclick="removeTaxItem(this)"><i class="bi bi-trash"></i></button>
                                </div>
                            @endif
                         </div>
                         <button type="button" class="btn btn-sm btn-outline-primary rounded-pill mt-2" onclick="addTaxItem()">
                            <i class="bi bi-plus-circle me-1"></i> Adicionar Opção
                         </button>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="taxActive" name="ativo" value="1" {{ isset($taxConfig) && $taxConfig->inscricao_com_taxa ? 'checked' : '' }}>
                        <label class="form-check-label" for="taxActive">Taxas Ativas</label>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill fw-bold">Salvar Configuração</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let searchTimeout;
    
    // Listeners for filters
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchData, 500);
    });

    document.getElementById('status-filter').addEventListener('change', fetchData);
    document.getElementById('batismo-filter').addEventListener('change', fetchData);
    document.getElementById('date-from').addEventListener('change', fetchData);
    document.getElementById('date-to').addEventListener('change', fetchData);

    function fetchData() {
        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-filter').value;
        const batismo = document.getElementById('batismo-filter').value;
        const dateFrom = document.getElementById('date-from').value;
        const dateTo = document.getElementById('date-to').value;

        // Update hidden inputs for print form
        document.getElementById('print-search').value = search;
        document.getElementById('print-status').value = status;
        document.getElementById('print-batismo').value = batismo;
        document.getElementById('print-date-from').value = dateFrom;
        document.getElementById('print-date-to').value = dateTo;

        const url = new URL("{{ route('inscricoes-eucaristia.index') }}");
        if(search) url.searchParams.set('search', search);
        if(status) url.searchParams.set('status', status);
        if(batismo) url.searchParams.set('batismo', batismo);
        if(dateFrom) url.searchParams.set('date_from', dateFrom);
        if(dateTo) url.searchParams.set('date_to', dateTo);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('table-content').innerHTML = html;
            updateMassActionState();
        });
    }

    // Checkbox Handling
    function handleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateMassActionState();
    }

    function handleRowCheckbox(source) {
        const selectAll = document.getElementById('select-all-checkbox');
        if (!source.checked) {
            selectAll.checked = false;
        } else {
            const allChecked = Array.from(document.querySelectorAll('.row-checkbox')).every(cb => cb.checked);
            selectAll.checked = allChecked;
        }
        updateMassActionState();
    }

    function updateMassActionState() {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        const btn = document.getElementById('massActionsBtn');
        const countSpan = document.getElementById('selectedCount');
        const modalSelectedCount = document.getElementById('modalSelectedCount');
        
        countSpan.innerText = selectedCount;
        if(modalSelectedCount) modalSelectedCount.innerText = selectedCount;

        if (selectedCount > 0) {
            btn.disabled = false;
            btn.classList.remove('btn-light', 'text-muted');
            btn.classList.add('btn-primary', 'text-white');
        } else {
            btn.disabled = true;
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-light', 'text-muted');
        }
    }

    // Mass Delete
    function confirmBulkDelete() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) return;

        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        const form = document.getElementById('deleteForm');
        const message = document.getElementById('deleteModalMessage');

        message.innerText = `Você está prestes a excluir ${selectedIds.length} registro(s). Esta ação não pode ser desfeita.`;
        form.action = "{{ route('inscricoes-eucaristia.bulk-destroy') }}";
        
        // Remove previous hidden inputs
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        
        // Add IDs
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        modal.show();
    }

    // Single Delete
    function openDeleteModal(url) {
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        const form = document.getElementById('deleteForm');
        const message = document.getElementById('deleteModalMessage');
        
        message.innerText = "Você está prestes a excluir este registro. Esta ação não pode ser desfeita.";
        form.action = url;
        // Clean bulk inputs
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        
        modal.show();
    }

    // Bulk Print
    function bulkPrint() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        
        // Open options modal
        const modal = new bootstrap.Modal(document.getElementById('printOptionsModal'));
        
        // Set IDs to hidden input
        document.getElementById('print-ids').value = JSON.stringify(selectedIds);
        
        // Update UI based on selection
        const radioSelected = document.getElementById('printScopeSelected');
        const radioAll = document.getElementById('printScopeAll');
        
        if (selectedIds.length > 0) {
            radioSelected.checked = true;
            radioSelected.disabled = false;
            document.getElementById('modalSelectedCount').innerText = selectedIds.length;
        } else {
            radioAll.checked = true;
            radioSelected.disabled = true;
            document.getElementById('modalSelectedCount').innerText = 0;
        }

        modal.show();
    }

    function togglePrintScope() {
        // Logic handled by form submission
    }

    function exportExcel() {
        const search = document.getElementById('search-input').value;
        const status = document.getElementById('status-filter').value;
        const batismo = document.getElementById('batismo-filter').value;
        const dateFrom = document.getElementById('date-from').value;
        const dateTo = document.getElementById('date-to').value;

        const url = new URL("{{ route('inscricoes-eucaristia.export') }}");
        if(search) url.searchParams.set('search', search);
        if(status) url.searchParams.set('status', status);
        if(batismo) url.searchParams.set('batismo', batismo);
        if(dateFrom) url.searchParams.set('date_from', dateFrom);
        if(dateTo) url.searchParams.set('date_to', dateTo);

        window.location.href = url.toString();
    }

    // Share Logic
    let selectedUsers = [];

    function openShareModal() {
        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            alert('Selecione pelo menos uma inscrição para compartilhar.');
            return;
        }
        
        // Reset state
        selectedUsers = [];
        updateSelectedUsersUI();
        document.getElementById('user-search-input').value = '';
        document.getElementById('share-message').value = '';
        document.getElementById('user-search-results').style.display = 'none';
        
        const modal = new bootstrap.Modal(document.getElementById('shareModal'));
        modal.show();
    }

    document.getElementById('user-search-input').addEventListener('input', function(e) {
        const query = e.target.value;
        const resultsContainer = document.getElementById('user-search-results');
        
        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }

        fetch(`{{ route('inscricoes-eucaristia.search-users') }}?query=${query}`)
            .then(response => response.json())
            .then(users => {
                resultsContainer.innerHTML = '';
                if (users.length > 0) {
                    users.forEach(user => {
                        const div = document.createElement('div');
                        div.className = 'p-2 hover-bg-light cursor-pointer border-bottom';
                        div.innerHTML = `<div class="fw-bold small">${user.name}</div><div class="text-muted small" style="font-size: 0.75rem;">${user.email}</div>`;
                        div.onclick = () => addUserToShare(user);
                        resultsContainer.appendChild(div);
                    });
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.style.display = 'none';
                }
            });
    });
    
    function addUserToShare(user) {
        if (!selectedUsers.find(u => u.id === user.id)) {
            selectedUsers.push(user);
            updateSelectedUsersUI();
        }
        document.getElementById('user-search-input').value = '';
        document.getElementById('user-search-results').style.display = 'none';
    }

    function removeUserFromShare(userId) {
        selectedUsers = selectedUsers.filter(u => u.id !== userId);
        updateSelectedUsersUI();
    }

    function updateSelectedUsersUI() {
        const container = document.getElementById('selected-users-container');
        const msg = document.getElementById('no-users-msg');
        
        if (selectedUsers.length === 0) {
            container.innerHTML = '';
            container.appendChild(msg);
            msg.style.display = 'block';
            return;
        }

        msg.style.display = 'none';
        container.innerHTML = ''; // Clear but keep msg logic? No, just clear.
        
        selectedUsers.forEach(user => {
            const badge = document.createElement('span');
            badge.className = 'badge bg-white text-dark border d-flex align-items-center gap-2 p-2 rounded-pill';
            badge.innerHTML = `
                <span>${user.name}</span>
                <i class="bi bi-x-circle-fill text-danger cursor-pointer" onclick="removeUserFromShare(${user.id})"></i>
            `;
            container.appendChild(badge);
        });
    }

    function sendShare() {
        if (selectedUsers.length === 0) {
            alert('Selecione pelo menos um usuário para compartilhar.');
            return;
        }

        const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        const message = document.getElementById('share-message').value;
        const userIds = selectedUsers.map(u => u.id);

        const btn = document.querySelector('#shareModal .btn-primary');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';

        fetch("{{ route('inscricoes-eucaristia.share') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                ids: selectedIds,
                users: userIds,
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Compartilhamento realizado com sucesso!');
                bootstrap.Modal.getInstance(document.getElementById('shareModal')).hide();
            } else {
                alert('Erro ao compartilhar: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocorreu um erro ao tentar compartilhar.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    // Tax Item Management
    let taxItemCount = {{ isset($taxConfig) ? $taxConfig->items->count() : 1 }};

    function formatCurrency(input) {
        let value = input.value.replace(/\D/g, '');
        if (value === '') {
            input.value = '';
            return;
        }
        value = (Number(value) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        input.value = value;
    }
    
    function addTaxItem() {
        const container = document.getElementById('taxItemsContainer');
        const div = document.createElement('div');
        div.className = 'd-flex gap-2 mb-2 tax-item';
        div.innerHTML = `
            <input type="text" name="items[${taxItemCount}][nome]" class="form-control rounded-pill bg-light border-0" placeholder="Nome (Ex: Com Bíblia)" required>
            <input type="text" name="items[${taxItemCount}][valor]" class="form-control rounded-pill bg-light border-0" placeholder="Valor" style="max-width: 140px;" oninput="formatCurrency(this)" required>
            <button type="button" class="btn btn-outline-danger rounded-circle" onclick="removeTaxItem(this)"><i class="bi bi-trash"></i></button>
        `;
        container.appendChild(div);
        taxItemCount++;

        // Auto-enable tax active switch
        const taxSwitch = document.getElementById('taxActive');
        if (taxSwitch && !taxSwitch.checked) {
            taxSwitch.checked = true;
        }
    }

    function removeTaxItem(btn) {
        const items = document.querySelectorAll('.tax-item');
        if (items.length > 1) {
            btn.closest('.tax-item').remove();
        } else {
            alert('É necessário ter pelo menos uma opção de taxa.');
        }
    }

</script>

@endsection
