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
                <form id="printOptionsForm" action="{{ route('inscricoes-crisma.bulk-print') }}" method="POST" target="_blank">
                    @csrf
                    <!-- Hidden inputs for filters -->
                    <input type="hidden" name="search" id="print-search">
                    <input type="hidden" name="status" id="print-status">
                    <input type="hidden" name="batismo" id="print-batismo">
                    <input type="hidden" name="eucaristia" id="print-eucaristia">
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

<style>
    .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #64748b; border-bottom-width: 1px !important; }
    .table td { font-size: 0.9rem; color: #334155; }
    .hover-bg-light:hover { background-color: #f8f9fa; }
    .cursor-pointer { cursor: pointer; }
    .hover-text-danger:hover { color: #dc3545 !important; }
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
    // Fix for legacy function reference
    window.updateSelection = function(checkbox) {
        handleRowCheckbox(checkbox);
    };

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
        const ids = Array.from(state.selectedIds);
        
        // Populate Modal Data
        document.getElementById('modalSelectedCount').innerText = ids.length;
        document.getElementById('print-ids').value = JSON.stringify(ids);
        
        // Populate Filters
        document.getElementById('print-search').value = state.search;
        document.getElementById('print-status').value = state.status;
        document.getElementById('print-batismo').value = state.batismo;
        document.getElementById('print-eucaristia').value = state.eucaristia;
        document.getElementById('print-date-from').value = state.date_from;
        document.getElementById('print-date-to').value = state.date_to;

        // Reset radio to Selected if items are selected, otherwise disable Selected option?
        // Actually, if nothing selected, we should probably only allow 'All'.
        // But the button is disabled if count === 0 anyway.
        
        if (ids.length > 0) {
            document.getElementById('printScopeSelected').checked = true;
            document.getElementById('printScopeSelected').disabled = false;
        } else {
            document.getElementById('printScopeAll').checked = true;
            document.getElementById('printScopeSelected').disabled = true;
        }
        
        togglePrintScope();

        const modal = new bootstrap.Modal(document.getElementById('printOptionsModal'));
        modal.show();
    }

    function togglePrintScope() {
        const scope = document.querySelector('input[name="scope"]:checked').value;
        // Maybe change button text or something?
    }

    // Share Logic
    let selectedShareUsers = new Map();

    function openShareModal() {
        if (state.selectedIds.size === 0) {
            alert('Selecione pelo menos um registro para compartilhar.');
            return;
        }
        const modal = new bootstrap.Modal(document.getElementById('shareModal'));
        modal.show();
    }

    // User Search
    const userSearchInput = document.getElementById('user-search-input');
    const userSearchResults = document.getElementById('user-search-results');
    let userSearchTimeout;

    userSearchInput.addEventListener('input', () => {
        clearTimeout(userSearchTimeout);
        const query = userSearchInput.value;
        if (query.length < 2) {
            userSearchResults.style.display = 'none';
            return;
        }
        userSearchTimeout = setTimeout(() => searchUsers(query), 300);
    });

    function searchUsers(query) {
        fetch(`{{ route('inscricoes-crisma.search-users') }}?q=${query}`)
            .then(res => res.json())
            .then(users => {
                userSearchResults.innerHTML = '';
                if (users.length === 0) {
                    userSearchResults.innerHTML = '<div class="p-3 text-muted small text-center">Nenhum usuário encontrado</div>';
                } else {
                    users.forEach(user => {
                        if (selectedShareUsers.has(user.id)) return; // Skip already selected
                        
                        const div = document.createElement('div');
                        div.className = 'p-2 hover-bg-light cursor-pointer d-flex align-items-center border-bottom';
                        div.innerHTML = `
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                                ${user.avatar ? `<img src="${user.avatar}" class="rounded-circle w-100 h-100 object-fit-cover">` : user.name.charAt(0)}
                            </div>
                            <span class="small fw-bold text-dark">${user.name}</span>
                        `;
                        div.onclick = () => selectUser(user);
                        userSearchResults.appendChild(div);
                    });
                }
                userSearchResults.style.display = 'block';
            });
    }

    function selectUser(user) {
        selectedShareUsers.set(user.id, user);
        renderSelectedUsers();
        userSearchInput.value = '';
        userSearchResults.style.display = 'none';
    }

    function removeUser(userId) {
        selectedShareUsers.delete(userId);
        renderSelectedUsers();
    }

    function renderSelectedUsers() {
        const container = document.getElementById('selected-users-container');
        const msg = document.getElementById('no-users-msg');
        
        if (selectedShareUsers.size === 0) {
            container.innerHTML = '';
            container.appendChild(msg);
            msg.style.display = 'block';
            return;
        }
        
        msg.style.display = 'none';
        container.innerHTML = ''; 
        container.appendChild(msg); // Keep it but hidden
        
        selectedShareUsers.forEach(user => {
            const badge = document.createElement('div');
            badge.className = 'badge bg-white text-dark border p-2 d-flex align-items-center gap-2 rounded-pill shadow-sm';
            badge.innerHTML = `
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 10px;">
                    ${user.avatar ? `<img src="${user.avatar}" class="rounded-circle w-100 h-100 object-fit-cover">` : user.name.charAt(0)}
                </div>
                <span>${user.name}</span>
                <i class="bi bi-x-circle-fill text-muted cursor-pointer hover-text-danger" onclick="removeUser(${user.id})"></i>
            `;
            container.appendChild(badge);
        });
    }

    function sendShare() {
        if (selectedShareUsers.size === 0) {
            alert('Selecione pelo menos um usuário para compartilhar.');
            return;
        }

        const btn = document.querySelector('#shareModal button[onclick="sendShare()"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enviando...';

        const data = {
            users: Array.from(selectedShareUsers.keys()),
            ids: JSON.stringify(Array.from(state.selectedIds)),
            message: document.getElementById('share-message').value
        };

        fetch(`{{ route('inscricoes-crisma.share') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resp => {
            if (resp.success) {
                alert(resp.message);
                bootstrap.Modal.getInstance(document.getElementById('shareModal')).hide();
                selectedShareUsers.clear();
                renderSelectedUsers();
                document.getElementById('share-message').value = '';
            } else {
                alert('Erro: ' + resp.message);
            }
        })
        .catch(err => alert('Erro ao compartilhar.'))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function exportExcel() {
        const url = new URL("{{ route('inscricoes-crisma.export') }}");
        if(state.search) url.searchParams.set('search', state.search);
        if(state.status) url.searchParams.set('status', state.status);
        if(state.batismo) url.searchParams.set('batismo', state.batismo);
        if(state.eucaristia) url.searchParams.set('eucaristia', state.eucaristia);
        if(state.date_from) url.searchParams.set('date_from', state.date_from);
        if(state.date_to) url.searchParams.set('date_to', state.date_to);
        
        // Pass selected IDs if any
        if (state.selectedIds.size > 0) {
            url.searchParams.set('ids', Array.from(state.selectedIds).join(','));
        }

        window.location.href = url.toString();
    }
</script>
@endsection