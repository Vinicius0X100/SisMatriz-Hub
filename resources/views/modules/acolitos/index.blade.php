@extends('layouts.app')

@section('title', 'Lista de Acólitos e Coroinhas')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Lista de Acólitos e Coroinhas</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Acólitos e Coroinhas</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Sucesso!</strong> {{ session('success') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Erro!</strong> {{ session('error') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div id="ajaxAlertContainer"></div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Ativos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-x-circle fs-3 text-danger"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Inativos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['inactive'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-3">
                    <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="search" class="form-control ps-5 rounded-pill" placeholder="Buscar por nome..." style="height: 45px;">
                    </div>
                </div>

                <!-- Filtro Comunidade -->
                <div class="col-md-3">
                    <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                    <select id="ent_id" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todas</option>
                        @foreach($entidades as $entidade)
                            <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Tipo -->
                <div class="col-md-2">
                    <label for="type" class="form-label fw-bold text-muted small">Tipo</label>
                    <select id="type" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="0">Acólito</option>
                        <option value="1">Coroinha</option>
                    </select>
                </div>

                <!-- Filtro Status -->
                <div class="col-md-2">
                    <label for="status" class="form-label fw-bold text-muted small">Status</label>
                    <select id="status" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="0">Ativo</option>
                        <option value="1">Inativo</option>
                    </select>
                </div>

                <!-- Ações e Novo -->
                <div class="col-md-3 text-end d-flex gap-2 justify-content-end">
                    @if(auth()->user() && auth()->user()->rule != 8)
                    <a href="{{ route('acolitos.chamada') }}" class="btn btn-outline-primary rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;">
                        <i class="bi bi-clipboard-check me-2"></i>
                        <span class="d-none d-lg-inline">Fazer Chamada</span>
                    </a>
                    @endif
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActionsBtn" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActionsBtn">
                            <li><a class="dropdown-item text-primary" href="#" id="bulkLinkBtn"><i class="bi bi-link-45deg me-2"></i> Vincular Usuários</a></li>
                            <li><a class="dropdown-item text-danger" href="#" id="bulkDeleteBtn"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('acolitos.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" title="Novo Cadastro">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-lg-inline ms-2">Novo</span>
                    </a>
                </div>
            </div>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" width="40" class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th scope="col" class="ps-4">Nome</th>
                            <th scope="col">Comunidade</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Idade</th>
                            <th scope="col">Ano Formação</th>
                            <th scope="col">Vínculo</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div>Carregando registros...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3" id="paginationContainer" style="display: none;">
                <div class="text-muted small" id="paginationInfo"></div>
                <nav aria-label="Navegação">
                    <ul class="pagination pagination-sm mb-0 justify-content-end gap-1" id="paginationLinks"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-danger">Confirmar Exclusão</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-4">
            <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <i class="bi bi-exclamation-triangle-fill fs-1 text-danger"></i>
            </div>
            <h5 class="fw-bold mb-2">Tem certeza?</h5>
            <p class="text-muted mb-0">Esta ação é <strong>irreversível</strong> e removerá permanentemente o registro do sistema.</p>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Sim, Excluir</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Vinculação em Massa -->
<div class="modal fade" id="bulkLinkModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-primary">Vinculação em Massa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <!-- State: Searching -->
        <div id="bulkLinkSearching" class="text-center py-5">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h5 class="fw-bold">Procurando vínculos...</h5>
            <p class="text-muted">Analisando nomes e buscando correspondências na base de usuários.</p>
        </div>

        <!-- State: Results -->
        <div id="bulkLinkResults" style="display: none;">
            <div class="alert alert-info border-0 rounded-4 mb-4 d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Atenção:</strong> A vinculação automática pode conter falhas. Verifique cuidadosamente os nomes antes de confirmar.
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th width="40" class="text-center">
                                <input class="form-check-input" type="checkbox" id="selectAllLinks" checked>
                            </th>
                            <th>Acólito/Coroinha</th>
                            <th>Usuário Encontrado</th>
                            <th>Confiança</th>
                        </tr>
                    </thead>
                    <tbody id="bulkLinkTableBody">
                        <!-- Content via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- State: No Matches -->
        <div id="bulkLinkNoMatches" style="display: none;" class="text-center py-5">
            <div class="rounded-circle bg-secondary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                <i class="bi bi-search fs-1 text-secondary"></i>
            </div>
            <h5 class="fw-bold mb-2">Nenhuma correspondência encontrada</h5>
            <p class="text-muted">Não encontramos usuários com nomes idênticos aos selecionados.</p>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-end pb-4" id="bulkLinkFooter" style="display: none;">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary rounded-pill px-4" id="confirmBulkLinkBtn">Confirmar Vinculação</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Confirmação de Vínculo com Usuário -->
<div class="modal fade" id="userMatchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-primary">Vínculo com Usuário Encontrado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-3">
            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                <i class="bi bi-person-badge fs-1 text-primary"></i>
            </div>
            <h5 class="fw-bold mb-3">Encontramos um usuário com este nome!</h5>
            <p class="text-muted mb-4">Deseja vincular este acólito/coroinha ao usuário existente no sistema?</p>
            
            <div class="card bg-light border-0 rounded-4 mb-3">
                <div class="card-body text-start">
                    <h6 class="fw-bold text-dark mb-3 small text-uppercase">Dados do Usuário</h6>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-person text-muted me-2"></i>
                        <span id="modalUserName" class="fw-bold text-dark"></span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-envelope text-muted me-2"></i>
                        <span id="modalUserEmail" class="text-muted"></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-lock text-muted me-2"></i>
                        <span id="modalUserRole" class="badge bg-secondary rounded-pill"></span>
                    </div>
                </div>
            </div>
            
            <p class="small text-muted mb-0">Isso permitirá que o sistema associe as atividades deste acólito à conta de usuário correspondente.</p>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary rounded-pill px-4" id="btnConfirmLink">Sim, vincular usuário</button>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const entIdSelect = document.getElementById('ent_id');
    const typeSelect = document.getElementById('type');
    const statusSelect = document.getElementById('status');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationLinks = document.getElementById('paginationLinks');
    const selectAllCheckbox = document.getElementById('selectAll');
    const bulkActionsBtn = document.getElementById('bulkActionsBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkLinkBtn = document.getElementById('bulkLinkBtn');
    const ajaxAlertContainer = document.getElementById('ajaxAlertContainer');

    // Modal elements
    let deleteModal;
    try {
        const deleteModalEl = document.getElementById('deleteModal');
        if (deleteModalEl) {
            deleteModal = new bootstrap.Modal(deleteModalEl);
        } else {
            console.error('Modal element #deleteModal not found!');
        }
    } catch (e) {
        console.error('Error initializing Delete Modal:', e);
    }

    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    // Bulk Link Modal Elements
    const bulkLinkModal = new bootstrap.Modal(document.getElementById('bulkLinkModal'));
    const bulkLinkSearching = document.getElementById('bulkLinkSearching');
    const bulkLinkResults = document.getElementById('bulkLinkResults');
    const bulkLinkNoMatches = document.getElementById('bulkLinkNoMatches');
    const bulkLinkTableBody = document.getElementById('bulkLinkTableBody');
    const bulkLinkFooter = document.getElementById('bulkLinkFooter');
    const confirmBulkLinkBtn = document.getElementById('confirmBulkLinkBtn');
    const selectAllLinks = document.getElementById('selectAllLinks');
    let bulkMatches = [];

    // Link User Modal Elements
    let userMatchModal;
    const modalUserName = document.getElementById('modalUserName');
    const modalUserEmail = document.getElementById('modalUserEmail');
    const modalUserRole = document.getElementById('modalUserRole');
    const btnConfirmLink = document.getElementById('btnConfirmLink');
    let pendingLinkAcolitoId = null;
    let pendingLinkUserId = null;

    let currentPage = 1;
    let debounceTimer;
    let globalSelectedIds = new Set();
    let selectAllMode = false;
    let totalRecords = 0;
    let currentDeleteId = null;
    let isBulkDelete = false;

    // Initial Fetch
    fetchData();

    // Event Listeners
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentPage = 1;
            resetSelection();
            fetchData();
        }, 300);
    });

    entIdSelect.addEventListener('change', function() {
        currentPage = 1;
        resetSelection();
        fetchData();
    });

    typeSelect.addEventListener('change', function() {
        currentPage = 1;
        resetSelection();
        fetchData();
    });

    statusSelect.addEventListener('change', function() {
        currentPage = 1;
        resetSelection();
        fetchData();
    });

    selectAllCheckbox.addEventListener('change', function() {
        selectAllMode = this.checked;
        const checkboxes = document.querySelectorAll('.row-checkbox');
        
        if (selectAllMode) {
            // Se selecionar tudo, marcamos visualmente os da página atual
            // e limpamos a lista de IDs manuais (pois selectAllMode cobre tudo)
            globalSelectedIds.clear();
            checkboxes.forEach(cb => {
                cb.checked = true;
                // Não adicionamos ao globalSelectedIds pois selectAllMode trata disso
            });
        } else {
            // Desmarcar tudo
            globalSelectedIds.clear();
            checkboxes.forEach(cb => cb.checked = false);
        }
        updateBulkActions();
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (globalSelectedIds.size === 0 && !selectAllMode) return;
        
        isBulkDelete = true;
        if (deleteModal) {
            deleteModal.show();
        } else {
            if (confirm('Tem certeza que deseja excluir os registros selecionados?')) {
                performBulkDelete();
            }
        }
    });

    bulkLinkBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (globalSelectedIds.size === 0 && !selectAllMode) return;
        
        // Reset Modal State
        bulkLinkSearching.style.display = 'block';
        bulkLinkResults.style.display = 'none';
        bulkLinkNoMatches.style.display = 'none';
        bulkLinkFooter.style.display = 'none';
        bulkMatches = [];
        
        bulkLinkModal.show();
        
        // Prepare Payload
        const payload = selectAllMode ? {
            select_all: true,
            search: searchInput.value,
            ent_id: entIdSelect.value,
            type: typeSelect.value,
            status: statusSelect.value
        } : {
            ids: Array.from(globalSelectedIds)
        };

        // Fetch Matches
        fetch('{{ route("acolitos.check-bulk-matches") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        })
        .then(async response => {
            if (response.status === 419 || response.status === 401) {
                throw new Error('Sessão expirada. Por favor, recarregue a página.');
            }

            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (!response.ok) {
                    throw new Error(data.error || data.message || 'Erro no servidor (' + response.status + ')');
                }
                return data;
            } catch (e) {
                console.error('Invalid JSON response:', text);
                if (!response.ok) throw new Error('Erro no servidor: ' + response.statusText);
                throw new Error('Resposta inválida do servidor. Contate o suporte.');
            }
        })
        .then(data => {
            bulkLinkSearching.style.display = 'none';
            bulkMatches = data.matches || [];
            
            if (bulkMatches.length > 0) {
                renderBulkMatches(bulkMatches);
                bulkLinkResults.style.display = 'block';
                bulkLinkFooter.style.display = 'flex';
            } else {
                bulkLinkNoMatches.style.display = 'block';
            }
        })
        .catch(error => {
            console.error(error);
            bulkLinkSearching.style.display = 'none';
            alert('Erro ao buscar vínculos: ' + error.message);
            bulkLinkModal.hide();
        });
    });

    selectAllLinks.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.link-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    confirmBulkLinkBtn.addEventListener('click', function() {
        const selectedLinks = [];
        document.querySelectorAll('.link-checkbox:checked').forEach(cb => {
            const index = cb.value;
            const match = bulkMatches[index];
            if (match) {
                selectedLinks.push({
                    acolito_id: match.acolito_id,
                    user_id: match.user_id
                });
            }
        });

        if (selectedLinks.length === 0) return;

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Vinculando...';

        fetch('{{ route("acolitos.bulk-link-users") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ links: selectedLinks })
        })
        .then(async response => {
            if (response.status === 419 || response.status === 401) {
                throw new Error('Sessão expirada. Por favor, recarregue a página.');
            }
            
            const text = await response.text();
            try {
                const data = JSON.parse(text);
                if (!response.ok) {
                    throw new Error(data.error || data.message || 'Erro ao vincular (' + response.status + ')');
                }
                return data;
            } catch (e) {
                console.error('Invalid JSON response:', text);
                if (!response.ok) throw new Error('Erro no servidor: ' + response.statusText);
                throw new Error('Resposta inválida do servidor ao vincular.');
            }
        })
        .then(data => {
            bulkLinkModal.hide();
            showToast(data.message, 'success');
            resetSelection();
            fetchData();
        })
        .catch(error => {
            console.error(error);
            showToast('Erro ao vincular usuários.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    function renderBulkMatches(matches) {
        bulkLinkTableBody.innerHTML = '';
        matches.forEach((match, index) => {
            const isHighConfidence = match.confidence === 'high';
            const rowClass = isHighConfidence ? '' : 'table-warning';
            const confidenceBadge = isHighConfidence 
                ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill">Alta</span>'
                : '<span class="badge bg-warning text-dark rounded-pill" data-bs-toggle="tooltip" title="Nome incompleto ou curto. Verifique com atenção.">Baixa</span>';
            
            const tr = document.createElement('tr');
            if (!isHighConfidence) tr.classList.add('table-warning');
            
            tr.innerHTML = `
                <td class="text-center">
                    <input class="form-check-input link-checkbox" type="checkbox" value="${index}" checked>
                </td>
                <td>
                    <div class="fw-bold text-dark">${match.acolito_name}</div>
                    <div class="small text-muted">ID: ${match.acolito_id}</div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark">${match.user_name}</div>
                            <div class="small text-muted">${match.user_email || 'Sem e-mail'}</div>
                        </div>
                    </div>
                </td>
                <td>${confidenceBadge}</td>
            `;
            bulkLinkTableBody.appendChild(tr);
        });

        // Re-init tooltips inside modal
        if (window.bootstrap) {
            const tooltipTriggerList = [].slice.call(bulkLinkTableBody.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new window.bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    confirmDeleteBtn.addEventListener('click', function() {
        if (isBulkDelete) {
            performBulkDelete();
        } else if (currentDeleteId) {
            performSingleDelete(currentDeleteId);
        }
        if (deleteModal) deleteModal.hide();
    });

    // Functions
    function resetSelection() {
        selectAllMode = false;
        globalSelectedIds.clear();
        selectAllCheckbox.checked = false;
        updateBulkActions();
    }

    function fetchData(url = null) {
        const params = new URLSearchParams({
            page: currentPage,
            search: searchInput.value,
            ent_id: entIdSelect.value,
            type: typeSelect.value,
            status: statusSelect.value
        });

        if (url) {
             try {
                const urlObj = new URL(url);
                const pageFromUrl = urlObj.searchParams.get('page');
                if (pageFromUrl) {
                    params.set('page', pageFromUrl);
                    currentPage = pageFromUrl;
                }
            } catch(e) {
                // Fallback
            }
        }

        const fetchUrl = `{{ route('acolitos.index') }}?${params.toString()}`;

        fetch(fetchUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            totalRecords = data.total;
            renderTable(data.data);
            renderPagination(data);
            updateBulkActions();
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Erro ao carregar dados.</td></tr>';
        });
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Nenhum registro encontrado.</td></tr>';
            return;
        }

        tableBody.innerHTML = '';
        data.forEach(item => {
            // Checkbox logic:
            // 1. If selectAllMode is ON, everything is checked.
            // 2. If selectAllMode is OFF, check if ID is in globalSelectedIds.
            const isSelected = selectAllMode || globalSelectedIds.has(item.id);

            // 0 = Ativo, 1 = Inativo
            const statusBadge = item.status == 0 
                ? '<span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Ativo</span>' 
                : '<span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Inativo</span>';
            
            const typeBadge = item.type == 0 
                ? '<span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill">Acólito</span>'
                : '<span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Coroinha</span>';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center"><input class="form-check-input row-checkbox" type="checkbox" value="${item.id}" ${isSelected ? 'checked' : ''} onchange="toggleSelection(${item.id}, this.checked)"></td>
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-initial rounded-circle bg-primary text-white me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            ${item.name.substring(0, 1)}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">${item.name}</h6>
                        </div>
                    </div>
                </td>
                <td>${item.ent_name}</td>
                <td>${typeBadge}</td>
                <td>${item.age == 0 ? 'Não informado' : item.age + ' anos'}</td>
                <td>${item.graduation_year}</td>
                <td>
                    ${item.user_id ? 
                        '<i class="bi bi-shield-check text-success fs-5" data-bs-toggle="tooltip" data-bs-title="Vinculado com sucesso"></i>' : 
                        `<i class="bi bi-shield-x text-danger fs-5 cursor-pointer" style="cursor: pointer;" data-bs-toggle="tooltip" data-bs-title="Sem vínculo" data-name="${item.name.replace(/"/g, '&quot;')}" onclick="checkAndLinkUser(this, ${item.id}, this.getAttribute('data-name'))"></i>`
                    }
                </td>
                <td>${statusBadge}</td>
                <td class="text-end pe-4">
                    <div class="d-flex gap-2 justify-content-end">
                        @if(auth()->user() && auth()->user()->rule != 8)
                            <a href="{{ url('acolitos') }}/${item.id}/attendance-history" class="btn btn-light text-warning btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" data-bs-title="Faltas">
                                <i class="bi bi-calendar-x"></i>
                            </a>
                        @endif
                        <a href="{{ url('acolitos') }}/${item.id}/edit" class="btn btn-light text-primary btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" data-bs-title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-light text-danger btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" data-bs-toggle="tooltip" data-bs-title="Excluir" onclick="confirmDelete(${item.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        // Initialize Bootstrap tooltips
        if (window.bootstrap) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new window.bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    function renderPagination(data) {
        if (!data || !data.links || data.total === 0) {
            paginationContainer.style.display = 'none';
            return;
        }

        paginationContainer.style.display = 'flex';
        paginationInfo.innerHTML = `Mostrando <span class="fw-bold">${data.from}</span> a <span class="fw-bold">${data.to}</span> de <span class="fw-bold">${data.total}</span> registros`;

        paginationLinks.innerHTML = '';
        data.links.forEach(link => {
            if (!link.url && !link.active && link.label !== '...') return;

            let label = link.label;
            if (label.includes('&laquo;') || label.includes('Previous')) label = '<i class="bi bi-chevron-left"></i>';
            if (label.includes('&raquo;') || label.includes('Next')) label = '<i class="bi bi-chevron-right"></i>';

            const li = document.createElement('li');
            li.className = `page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`;
            
            const a = document.createElement('a');
            a.className = 'page-link';
            a.innerHTML = label;
            a.href = link.url || '#';
            if (link.url) {
                a.onclick = (e) => {
                    e.preventDefault();
                    fetchData(link.url);
                };
            }

            li.appendChild(a);
            paginationLinks.appendChild(li);
        });
    }

    window.checkAndLinkUser = function(btnElement, id, name) {
        // Show spinner
        const originalDisplay = btnElement.style.display;
        btnElement.style.display = 'none';
        
        const loader = document.createElement('span');
        loader.className = 'd-inline-flex align-items-center text-muted small';
        loader.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Procurando vínculo...';
        
        btnElement.parentNode.insertBefore(loader, btnElement.nextSibling);
        
        fetch('{{ route("acolitos.check-user") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => response.json())
        .then(data => {
            if (data.found) {
                pendingLinkAcolitoId = id;
                pendingLinkUserId = data.user.id;
                modalUserName.textContent = data.user.name;
                modalUserEmail.textContent = data.user.email || 'Sem e-mail';
                modalUserRole.textContent = data.user.rule || 'N/A';
                
                if (!userMatchModal && window.bootstrap) {
                     userMatchModal = new window.bootstrap.Modal(document.getElementById('userMatchModal'));
                }
                if (userMatchModal) userMatchModal.show();
            } else {
                alert('Nenhum usuário correspondente encontrado para este nome.');
            }
        })
        .catch(err => console.error(err))
        .finally(() => {
            // Restore icon (if table wasn't refreshed yet)
            if (loader && loader.parentNode) {
                loader.remove();
            }
            if (btnElement) {
                btnElement.style.display = originalDisplay;
            }
        });
    };

    btnConfirmLink.addEventListener('click', function() {
        if (!pendingLinkAcolitoId || !pendingLinkUserId) return;

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Vinculando...';

        fetch(`{{ url('acolitos') }}/${pendingLinkAcolitoId}/link-user`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ user_id: pendingLinkUserId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (userMatchModal) userMatchModal.hide();
                fetchData(); // Refresh table
                // Show success toast/alert
            }
        })
        .catch(err => console.error(err))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    window.toggleSelection = function(id, checked) {
        if (selectAllMode) {
            // Se está em modo Select All e desmarca um, sai do modo Select All
            // e seleciona apenas os que estão visíveis e marcados.
            // Esta é uma simplificação para evitar complexidade de "Select All Except One"
            if (!checked) {
                selectAllMode = false;
                globalSelectedIds.clear();
                // Adiciona todos os VISÍVEIS que estão marcados
                document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                    globalSelectedIds.add(parseInt(cb.value));
                });
                selectAllCheckbox.checked = false;
            }
        } else {
            if (checked) {
                globalSelectedIds.add(parseInt(id));
            } else {
                globalSelectedIds.delete(parseInt(id));
            }
        }
        updateBulkActions();
    };

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        
        // Se estiver em modo Select All, o header deve estar marcado
        if (selectAllMode) {
            selectAllCheckbox.checked = true;
            // E todos os checkboxes visíveis também (caso não estejam)
            checkboxes.forEach(cb => cb.checked = true);
        } else {
             // Marca o header se todos visíveis estiverem marcados
             if (checkboxes.length > 0) {
                 selectAllCheckbox.checked = Array.from(checkboxes).every(cb => cb.checked);
             } else {
                 selectAllCheckbox.checked = false;
             }
        }

        let count = selectAllMode ? totalRecords : globalSelectedIds.size;

        if (count > 0) {
            bulkActionsBtn.disabled = false;
            bulkActionsBtn.innerHTML = `Ações (${count})`;
        } else {
            bulkActionsBtn.disabled = true;
            bulkActionsBtn.innerHTML = 'Ações';
        }
    }

    window.confirmDelete = function(id) {
        console.log('Confirm Delete called for ID:', id);
        currentDeleteId = id;
        isBulkDelete = false;
        
        if (deleteModal) {
            deleteModal.show();
        } else {
            console.error('Delete modal is not initialized.');
            // Fallback
            if (confirm('Tem certeza que deseja excluir este registro?')) {
                performSingleDelete(id);
            }
        }
    };

    function performSingleDelete(id) {
        fetch(`{{ url('acolitos') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url; // If backend redirects
                return;
            }
            // If backend returns JSON or just status
            if (response.ok) {
                showToast('Registro excluído com sucesso!', 'success');
                // Se era o único registro da página, volta uma página se possível
                // Mas fetchData lida com isso se não achar nada
                fetchData();
            } else {
                showToast('Erro ao excluir registro.', 'error');
            }
        })
        .catch(error => {
            showToast('Erro ao excluir registro.', 'error');
        });
    }

    function performBulkDelete() {
        const payload = selectAllMode ? {
            select_all: true,
            search: searchInput.value,
            ent_id: entIdSelect.value,
            type: typeSelect.value,
            status: statusSelect.value
        } : {
            ids: Array.from(globalSelectedIds)
        };

        fetch('{{ route("acolitos.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            resetSelection();
            showToast(data.message || 'Registros excluídos com sucesso!', 'success');
            fetchData();
        })
        .catch(error => {
            showToast('Erro ao excluir registros.', 'error');
        });
    }

    function showToast(message, type = 'success') {
        const icon = type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill';
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const title = type === 'success' ? 'Sucesso!' : 'Erro!';

        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi ${icon} fs-4 me-3"></i>
                    <div>
                        <strong>${title}</strong> ${message}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        ajaxAlertContainer.innerHTML = alertHtml;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }
});
</script>
@endsection
