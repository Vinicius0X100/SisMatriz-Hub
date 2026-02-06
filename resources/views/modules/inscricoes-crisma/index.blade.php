@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Inscrições de Crisma</h2>
            <p class="text-muted small mb-0">Gerencie as inscrições recebidas para a Crisma.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inscrições de Crisma</li>
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

                <div style="min-width: 150px;">
                    <label class="form-label fw-bold text-muted small">Eucaristia</label>
                    <select id="eucaristia-filter" class="form-select rounded-pill bg-light border-0">
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
                <div class="ms-auto">
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
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Table Container -->
            <div id="table-content">
                @include('modules.inscricoes-crisma.partials.list')
            </div>
        </div>
    </div>
</div>

<!-- Bulk Forms -->
<form id="bulkDeleteForm" action="{{ route('inscricoes-crisma.bulk-destroy') }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
<form id="bulkPrintForm" action="{{ route('inscricoes-crisma.bulk-print') }}" method="POST" target="_blank" class="d-none">
    @csrf
    <input type="hidden" name="ids" id="bulkPrintIds">
</form>

<style>
    .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #64748b; border-bottom-width: 1px !important; }
    .table td { font-size: 0.9rem; color: #334155; }
</style>

<script>
    let searchTimeout;
    const state = {
        selectedIds: new Set(),
        search: '',
        status: '',
        batismo: '',
        eucaristia: '',
        date_from: '',
        date_to: ''
    };

    // Elements
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const batismoFilter = document.getElementById('batismo-filter');
    const eucaristiaFilter = document.getElementById('eucaristia-filter');
    const dateFrom = document.getElementById('date-from');
    const dateTo = document.getElementById('date-to');
    const massActionsBtn = document.getElementById('massActionsBtn');
    const selectedCountSpan = document.getElementById('selectedCount');

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        setupEventListeners();
        updateMassActionsUI();
    });

    function setupEventListeners() {
        // Filter Inputs
        searchInput.addEventListener('input', () => debounceFetch());
        statusFilter.addEventListener('change', () => { state.status = statusFilter.value; fetchResults(); });
        batismoFilter.addEventListener('change', () => { state.batismo = batismoFilter.value; fetchResults(); });
        eucaristiaFilter.addEventListener('change', () => { state.eucaristia = eucaristiaFilter.value; fetchResults(); });
        dateFrom.addEventListener('change', () => { state.date_from = dateFrom.value; fetchResults(); });
        dateTo.addEventListener('change', () => { state.date_to = dateTo.value; fetchResults(); });

        // Delegation for Checkboxes (since table reloads)
        document.getElementById('table-content').addEventListener('change', (e) => {
            if (e.target.matches('.row-checkbox')) {
                handleRowCheckbox(e.target);
            }
            if (e.target.matches('#select-all-checkbox')) {
                handleSelectAll(e.target);
            }
        });

        // Pagination links delegation
        document.getElementById('table-content').addEventListener('click', (e) => {
            if (e.target.matches('.pagination a') || e.target.closest('.pagination a')) {
                e.preventDefault();
                const link = e.target.matches('a') ? e.target : e.target.closest('a');
                const url = link.getAttribute('href');
                if(url) fetchResults(url);
            }
        });
    }

    function debounceFetch() {
        clearTimeout(searchTimeout);
        state.search = searchInput.value;
        searchTimeout = setTimeout(() => fetchResults(), 500);
    }

    function fetchResults(url = null) {
        const fetchUrl = new URL(url || `{{ route('inscricoes-crisma.index') }}`);
        
        // Append current filters
        if(state.search) fetchUrl.searchParams.set('search', state.search);
        if(state.status) fetchUrl.searchParams.set('status', state.status);
        if(state.batismo) fetchUrl.searchParams.set('batismo', state.batismo);
        if(state.eucaristia) fetchUrl.searchParams.set('eucaristia', state.eucaristia);
        if(state.date_from) fetchUrl.searchParams.set('date_from', state.date_from);
        if(state.date_to) fetchUrl.searchParams.set('date_to', state.date_to);

        fetch(fetchUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('table-content').innerHTML = html;
            restoreSelection();
        });
    }

    // Selection Logic
    function handleRowCheckbox(checkbox) {
        if (checkbox.checked) {
            state.selectedIds.add(checkbox.value);
        } else {
            state.selectedIds.delete(checkbox.value);
            document.getElementById('select-all-checkbox').checked = false;
        }
        updateMassActionsUI();
    }

    function handleSelectAll(checkbox) {
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        rowCheckboxes.forEach(cb => {
            cb.checked = checkbox.checked;
            if (checkbox.checked) {
                state.selectedIds.add(cb.value);
            } else {
                state.selectedIds.delete(cb.value);
            }
        });
        updateMassActionsUI();
    }

    function restoreSelection() {
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        let allChecked = true;
        
        if (rowCheckboxes.length === 0) allChecked = false;

        rowCheckboxes.forEach(cb => {
            if (state.selectedIds.has(cb.value)) {
                cb.checked = true;
            } else {
                allChecked = false;
            }
        });

        const selectAll = document.getElementById('select-all-checkbox');
        if(selectAll) selectAll.checked = allChecked;
    }

    function updateMassActionsUI() {
        const count = state.selectedIds.size;
        selectedCountSpan.textContent = count;
        massActionsBtn.disabled = count === 0;
    }

    // Actions
    function confirmBulkDelete() {
        if (!confirm('Tem certeza que deseja excluir os ' + state.selectedIds.size + ' registros selecionados?')) return;

        const form = document.getElementById('bulkDeleteForm');
        
        // Append IDs to form
        const container = document.createElement('div');
        state.selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            container.appendChild(input);
        });
        form.appendChild(container);

        // Submit via fetch to handle JSON response or redirect
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                 'X-Requested-With': 'XMLHttpRequest',
                 'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                state.selectedIds.clear();
                updateMassActionsUI();
                fetchResults(); // Reload table
            } else {
                alert('Erro ao excluir registros.');
            }
        })
        .catch(err => alert('Erro ao processar requisição.'));
    }

    function bulkPrint() {
        const form = document.getElementById('bulkPrintForm');
        document.getElementById('bulkPrintIds').value = Array.from(state.selectedIds).join(',');
        form.submit();
    }
</script>
@endsection