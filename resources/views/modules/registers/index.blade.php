@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Registros Gerais</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registros Gerais</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Registros</h6>
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
                <div class="col-md-4">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Nome, Email, CPF ou Telefone..." style="height: 45px;">
                    </div>
                </div>
                
                <!-- Filtro: Estado Civil -->
                <div class="col-md-3">
                    <label for="civilStatusFilter" class="form-label fw-bold text-muted small">Estado Civil</label>
                    <select id="civilStatusFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="1">Solteiro(a)</option>
                        <option value="2">Casado(a)</option>
                        <option value="3">União Estável</option>
                        <option value="4">Divorciado</option>
                        <option value="5">Viuvo(a)</option>
                        <option value="6">Nao Declarado</option>
                    </select>
                </div>

                <!-- Filtro: Status -->
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label fw-bold text-muted small">Status</label>
                    <select id="statusFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos os Status</option>
                        <option value="0">Ativo</option>
                        <option value="1">Inativo</option>
                    </select>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                    <button id="mainPdfBtn" class="btn btn-danger rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" title="Gerar PDF">
                        <i class="bi bi-file-earmark-pdf fs-5"></i>
                    </button>
                     <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('registers.create') }}'">
                        <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Novo</span>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                            <li><a class="dropdown-item" href="#" id="bulkPdfBtn"><i class="bi bi-file-earmark-pdf me-2"></i> Gerar PDF Selecionados</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
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
                            <th scope="col" class="sortable cursor-pointer" data-sort="name">Nome <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="email">Email <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="phone">Contato <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="born_date">Nascimento <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="age">Idade <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="sexo">Sexo <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="civil_status">Estado Civil <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="status">Status <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Conteúdo carregado via JS -->
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div>Carregando registros...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3" id="paginationContainer" style="display: none !important;">
                <div class="text-muted small" id="paginationInfo">
                    Mostrando <span class="fw-bold">0</span> de <span class="fw-bold">0</span> registros
                </div>
                <nav aria-label="Navegação">
                    <ul class="pagination pagination-sm mb-0 justify-content-end gap-1" id="paginationLinks">
                        <!-- Links gerados via JS -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Detalhes do Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0" id="detailsModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita. O registro será removido permanentemente do sistema.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal" id="cancelDeleteBtn">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Relatório PDF -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Gerar Relatório PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pdfForm" action="{{ route('registers.pdf') }}" method="POST">
                    @csrf
                    <input type="hidden" name="selected_ids" id="pdfSelectedIds">
                    
                    <div id="pdfTableSelectionMsg" class="alert alert-info py-2 small mb-3" style="display:none;">
                        <i class="bi bi-info-circle me-1"></i> <span id="pdfTableSelectionCount">0</span> registros selecionados da tabela serão incluídos.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Campos para Incluir</label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="name" checked id="colName">
                                    <label class="form-check-label" for="colName">Nome</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="email" checked id="colEmail">
                                    <label class="form-check-label" for="colEmail">Email</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="phone" checked id="colPhone">
                                    <label class="form-check-label" for="colPhone">Telefone</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="cpf" id="colCpf">
                                    <label class="form-check-label" for="colCpf">CPF</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="rg" id="colRg">
                                    <label class="form-check-label" for="colRg">RG</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="civil_status" id="colCivil">
                                    <label class="form-check-label" for="colCivil">Estado Civil</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="sexo" id="colSexo">
                                    <label class="form-check-label" for="colSexo">Sexo</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="mother_name" id="colMother">
                                    <label class="form-check-label" for="colMother">Nome da Mãe</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="father_name" id="colFather">
                                    <label class="form-check-label" for="colFather">Nome do Pai</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="born_date" id="colBorn">
                                    <label class="form-check-label" for="colBorn">Nascimento</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="address" id="colAddress">
                                    <label class="form-check-label" for="colAddress">Endereço</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="address_number" id="colNumber">
                                    <label class="form-check-label" for="colNumber">Número</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="home_situation" id="colNeighborhood">
                                    <label class="form-check-label" for="colNeighborhood">Bairro</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="city" checked id="colCity">
                                    <label class="form-check-label" for="colCity">Cidade</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="state" id="colState">
                                    <label class="form-check-label" for="colState">Estado</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="cep" id="colCep">
                                    <label class="form-check-label" for="colCep">CEP</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="status" checked id="colStatus">
                                    <label class="form-check-label" for="colStatus">Status</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Selecionar Pessoas (Opcional - Vazio para Todos)</label>
                        <div class="position-relative">
                            <input type="text" class="form-control rounded-pill" id="pdfSearchInput" placeholder="Digite o nome ou CPF e pressione Enter...">
                            <div id="pdfSearchResults" class="list-group position-absolute w-100 mt-1 shadow-sm" style="z-index: 1000; display: none;"></div>
                        </div>
                        <div id="selectedBadges" class="d-flex flex-wrap gap-2 mt-3">
                            <!-- Badges serão inseridos aqui -->
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnGeneratePdf">Gerar PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .avatar-sm { width: 32px; height: 32px; object-fit: cover; }
    .avatar-lg { width: 100px; height: 100px; object-fit: cover; }
    .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #64748b; border-bottom-width: 1px !important; }
    .table td { font-size: 0.9rem; color: #334155; }
    .sortable:hover { background-color: #f1f5f9; color: #0f172a; }

    /* Responsividade */
    @media (max-width: 768px) {
        .card-body { padding: 1.5rem !important; }
        .btn { width: 100%; justify-content: center; }
        .d-flex.gap-2 { flex-direction: column; }
        .dropdown { width: 100%; }
        .dropdown-toggle { width: 100%; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;
        let sortBy = 'id';
        let sortDir = 'desc';
        let debounceTimer;
        let deleteId = null;
        let globalSelectedIds = new Set(); // Armazena IDs selecionados entre páginas

        // Elements
        const searchInput = document.getElementById('searchInput');
        const civilStatusFilter = document.getElementById('civilStatusFilter');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.getElementById('tableBody');
        const selectAllCheckbox = document.getElementById('selectAll');
        const bulkActionsBtn = document.getElementById('bulkActions');
        const paginationContainer = document.getElementById('paginationContainer');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationLinks = document.getElementById('paginationLinks');
        
        // Modals
        const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const detailsModalBody = document.getElementById('detailsModalBody');
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        // PDF Elements
        const pdfSearchInput = document.getElementById('pdfSearchInput');
        const pdfSearchResults = document.getElementById('pdfSearchResults');
        const selectedBadges = document.getElementById('selectedBadges');
        const pdfSelectedIds = document.getElementById('pdfSelectedIds');
        const pdfTableSelectionMsg = document.getElementById('pdfTableSelectionMsg');
        const pdfTableSelectionCount = document.getElementById('pdfTableSelectionCount');
        const pdfModalInstance = new bootstrap.Modal(document.getElementById('pdfModal'));

        let pdfSelectedPeople = [];
        let tableSelectedIds = [];
        let pdfSearchDebounce;

        // Initial Load
        fetchData();

        // --- PDF Modal Logic ---
        pdfSearchInput.addEventListener('input', function() {
            clearTimeout(pdfSearchDebounce);
            const query = this.value;
            
            if (query.length < 2) {
                pdfSearchResults.style.display = 'none';
                return;
            }

            pdfSearchDebounce = setTimeout(() => {
                fetch(`{{ route('registers.search') }}?q=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        pdfSearchResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(person => {
                                const item = document.createElement('a');
                                item.href = '#';
                                item.className = 'list-group-item list-group-item-action';
                                item.textContent = `${person.name} (${person.cpf || 'Sem CPF'})`;
                                item.onclick = (e) => {
                                    e.preventDefault();
                                    addPersonToPdf(person);
                                    pdfSearchResults.style.display = 'none';
                                    pdfSearchInput.value = '';
                                };
                                pdfSearchResults.appendChild(item);
                            });
                            pdfSearchResults.style.display = 'block';
                        } else {
                            pdfSearchResults.style.display = 'none';
                        }
                    });
            }, 300);
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!pdfSearchInput.contains(e.target) && !pdfSearchResults.contains(e.target)) {
                pdfSearchResults.style.display = 'none';
            }
        });

        function addPersonToPdf(person) {
            if (pdfSelectedPeople.some(p => p.id === person.id)) return;
            
            pdfSelectedPeople.push(person);
            renderPdfBadges();
            updatePdfHiddenInput();
        }

        function removePersonFromPdf(id) {
            pdfSelectedPeople = pdfSelectedPeople.filter(p => p.id !== id);
            renderPdfBadges();
            updatePdfHiddenInput();
        }

        function renderPdfBadges() {
            selectedBadges.innerHTML = '';
            pdfSelectedPeople.forEach(person => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary rounded-pill pe-2 d-flex align-items-center gap-1';
                badge.innerHTML = `
                    ${person.name}
                    <i class="bi bi-x-circle-fill cursor-pointer" onclick="removePersonFromPdf(${person.id})"></i>
                `;
                // Need to attach event listener properly or make function global
                badge.querySelector('i').onclick = () => removePersonFromPdf(person.id);
                selectedBadges.appendChild(badge);
            });
        }

        function updatePdfHiddenInput() {
            const manualIds = pdfSelectedPeople.map(p => p.id);
            const combined = [...new Set([...manualIds, ...tableSelectedIds])];
            pdfSelectedIds.value = combined.join(',');
        }

        // PDF Button Listeners
        const mainPdfBtn = document.getElementById('mainPdfBtn');
        const bulkPdfBtn = document.getElementById('bulkPdfBtn');

        if (mainPdfBtn) {
            mainPdfBtn.addEventListener('click', function() {
                // Clear table selection context for PDF
                tableSelectedIds = [];
                if (pdfTableSelectionMsg) pdfTableSelectionMsg.style.display = 'none';
                
                // Clear manual selection too
                pdfSelectedPeople = [];
                renderPdfBadges();
                
                updatePdfHiddenInput();
                pdfModalInstance.show();
            });
        }

        if (bulkPdfBtn) {
            bulkPdfBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // Set table selection
                tableSelectedIds = Array.from(globalSelectedIds);
                
                if (tableSelectedIds.length > 0) {
                    if (pdfTableSelectionCount) pdfTableSelectionCount.textContent = tableSelectedIds.length;
                    if (pdfTableSelectionMsg) pdfTableSelectionMsg.style.display = 'block';
                } else {
                    if (pdfTableSelectionMsg) pdfTableSelectionMsg.style.display = 'none';
                }

                // Clear manual selection
                pdfSelectedPeople = [];
                renderPdfBadges();

                updatePdfHiddenInput();
                pdfModalInstance.show();
            });
        }

        // --- Main Table Logic ---

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentPage = 1;
                fetchData();
            }, 300);
        });

        civilStatusFilter.addEventListener('change', function() {
            currentPage = 1;
            fetchData();
        });

        statusFilter.addEventListener('change', function() {
            currentPage = 1;
            fetchData();
        });

        // Sorting
        document.querySelectorAll('.sortable').forEach(th => {
            th.addEventListener('click', function() {
                const column = this.getAttribute('data-sort');
                if (sortBy === column) {
                    sortDir = sortDir === 'asc' ? 'desc' : 'asc';
                } else {
                    sortBy = column;
                    sortDir = 'asc';
                }
                
                document.querySelectorAll('.sortable i').forEach(i => i.className = 'bi bi-arrow-down-up small text-muted ms-1');
                const icon = this.querySelector('i');
                icon.className = sortDir === 'asc' ? 'bi bi-arrow-up text-primary small ms-1' : 'bi bi-arrow-down text-primary small ms-1';
                
                fetchData();
            });
        });

        // Select All
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                toggleSelection(cb.value, this.checked);
            });
            updateBulkActions();
        });

        // Função para gerenciar seleção global
        window.toggleSelection = function(id, isSelected) {
            id = parseInt(id); // Garantir que ID seja número
            if (isSelected) {
                globalSelectedIds.add(id);
            } else {
                globalSelectedIds.delete(id);
            }
            updateBulkActions();
        };

        // Função global para mudar de página
        window.changePage = function(page) {
            if (page && page != currentPage) {
                currentPage = page;
                fetchData();
            }
        };

        function updateBulkActions() {
            // Verificar estado real das checkboxes visíveis
            const visibleCheckboxes = document.querySelectorAll('.row-checkbox');
            const allVisibleChecked = visibleCheckboxes.length > 0 && Array.from(visibleCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allVisibleChecked;

            const count = globalSelectedIds.size;
            bulkActionsBtn.disabled = count === 0;
            bulkActionsBtn.innerHTML = count > 0 ? `Ações (${count})` : 'Ações';
        }

        // Delete Logic
        confirmDeleteBtn.addEventListener('click', function() {
            if (!deleteId) return;

            fetch(`{{ url('registers') }}/${deleteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    deleteModal.hide();
                    // Remover ID deletado da seleção global se estiver lá
                    if (globalSelectedIds.has(deleteId)) {
                        globalSelectedIds.delete(deleteId);
                        updateBulkActions();
                    }
                    fetchData();
                } else {
                    alert('Erro ao excluir registro.');
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Fetch Data Function
        function fetchData(url = null) {
            const params = new URLSearchParams({
                page: currentPage,
                search: searchInput.value,
                civil_status: civilStatusFilter.value,
                status: statusFilter.value,
                sort_by: sortBy,
                sort_dir: sortDir
            });
            
            // Se URL foi fornecida (clique na paginação), extrair parâmetros dela se possível
            // Mas PREFERENCIALMENTE manter os filtros atuais e apenas mudar a página
            let fetchUrl;
            if (url) {
                // Tentar extrair o número da página da URL fornecida
                try {
                    const urlObj = new URL(url);
                    const pageFromUrl = urlObj.searchParams.get('page');
                    if (pageFromUrl) {
                        params.set('page', pageFromUrl);
                        currentPage = pageFromUrl; // Atualizar estado global
                    }
                } catch(e) {
                    // Fallback simples
                    const pageMatch = url.match(/page=(\d+)/);
                    if (pageMatch && pageMatch[1]) {
                        params.set('page', pageMatch[1]);
                        currentPage = pageMatch[1];
                    }
                }
            }

            fetchUrl = `{{ route('registers.index') }}?${params.toString()}`;

            fetch(fetchUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data && data.data) {
                    renderTable(data.data);
                    renderPagination(data);
                } else {
                    console.error('Formato de dados inválido:', data);
                    tableBody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Erro no formato de dados recebidos.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.innerHTML = '<tr><td colspan="9" class="text-center py-4 text-danger">Erro ao carregar dados. Tente recarregar a página.</td></tr>';
            });
        }

        function renderTable(data) {
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Nenhum registro encontrado.</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            data.forEach(item => {
                // Mapeamento de Estado Civil
                let civilStatusLabel = 'Desconhecido';
                const civilStatusMap = {
                    1: 'Solteiro(a)',
                    2: 'Casado(a)',
                    3: 'União Estável',
                    4: 'Divorciado',
                    5: 'Viuvo(a)',
                    6: 'Nao Declarado'
                };
                if (civilStatusMap[item.civil_status]) {
                    civilStatusLabel = civilStatusMap[item.civil_status];
                }

                const avatar = item.photo_url 
                    ? `<img src="${item.photo_url}" class="avatar-sm rounded-circle border">`
                    : `<div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold border">${item.name.substring(0,2).toUpperCase()}</div>`;

                const statusBadge = item.status == 0 
                    ? '<span class="badge bg-success-subtle text-success rounded-pill fw-normal px-3">Ativo</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary rounded-pill fw-normal px-3">Inativo</span>';

                const bornDate = (item.born_date_formatted === '01/01/0001' || !item.born_date_formatted) ? 'Não informado' : item.born_date_formatted;
                const contact = (item.phone || item.email) ? `${item.phone || ''}<br><small class="text-muted">${item.email || ''}</small>` : '<span class="text-muted small">Não informado</span>';
                const sexoLabel = (item.sexo == 1) ? 'Masculino' : ((item.sexo == 2) ? 'Feminino' : '-');

                const tr = document.createElement('tr');
                const isSelected = globalSelectedIds.has(item.id);
                tr.innerHTML = `
                    <td class="text-center"><input class="form-check-input row-checkbox" type="checkbox" value="${item.id}" ${isSelected ? 'checked' : ''} onchange="toggleSelection(this.value, this.checked)"></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            ${avatar}
                            <span class="fw-bold text-dark">${item.name}</span>
                        </div>
                    </td>
                    <td>${item.email || '<span class="text-muted small">Não informado</span>'}</td>
                    <td>${contact}</td>
                    <td>${bornDate}</td>
                    <td>${item.age || '-'}</td>
                    <td>${sexoLabel}</td>
                    <td><span class="badge bg-secondary rounded-pill">${civilStatusLabel}</span></td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-light border rounded-pill px-3 me-1" onclick="showDetails(${item.id})">
                            <i class="bi bi-eye text-primary"></i>
                        </button>
                        <a href="{{ url('registers') }}/${item.id}/edit" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                            <i class="bi bi-pencil text-warning"></i>
                        </a>
                        <button class="btn btn-sm btn-light border rounded-pill px-3" onclick="confirmDelete(${item.id})">
                            <i class="bi bi-trash text-danger"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        }

        function renderPagination(data) {
            if (!data || !data.links) {
                paginationContainer.style.display = 'none';
                return;
            }
            
            paginationContainer.style.display = 'flex';
            paginationInfo.innerHTML = `Mostrando <span class="fw-bold">${data.from || 0}</span> a <span class="fw-bold">${data.to || 0}</span> de <span class="fw-bold">${data.total}</span> registros`;
            
            let linksHtml = '';
            data.links.forEach(link => {
                // Ignorar se url for null e não for active (pontinhos ...)
                if (!link.url && !link.active && link.label !== '...') return;

                let label = link.label;
                let ariaLabel = label;
                
                // Tratamento específico para os textos padrão do Laravel/Bootstrap
                if (label.includes('&laquo;') || label.includes('Previous') || label.includes('pagination.previous')) {
                    label = '<i class="bi bi-chevron-left"></i>';
                    ariaLabel = 'Anterior';
                } else if (label.includes('&raquo;') || label.includes('Next') || label.includes('pagination.next')) {
                    label = '<i class="bi bi-chevron-right"></i>';
                    ariaLabel = 'Próximo';
                }

                const activeClass = link.active ? 'active' : '';
                const disabledClass = link.url ? '' : 'disabled';
                const pointerClass = link.url ? 'cursor-pointer' : '';
                
                // Extrair page number da URL se existir, para evitar reload total
                let pageNum = null;
                if (link.url) {
                    try {
                        const urlObj = new URL(link.url);
                        pageNum = urlObj.searchParams.get('page');
                    } catch(e) {
                        // Fallback se url for relativa ou inválida
                        pageNum = link.url.split('page=')[1];
                    }
                }

                linksHtml += `
                    <li class="page-item ${activeClass} ${disabledClass}">
                        <a class="page-link rounded-circle d-flex align-items-center justify-content-center mx-1 border-0 shadow-sm ${pointerClass}" 
                           href="#" 
                           aria-label="${ariaLabel}"
                           onclick="event.preventDefault(); ${pageNum ? `changePage(${pageNum})` : ''}"
                           style="width: 32px; height: 32px;">
                           ${label}
                        </a>
                    </li>
                `;
            });
            paginationLinks.innerHTML = linksHtml;
        }

        // Global functions for inline onclicks
        window.showDetails = function(id) {
            detailsModal.show();
            // Fetch logic for details...
            fetch(`{{ url('registers') }}/${id}`)
                .then(res => res.json())
                .then(data => {
                    // Populate details modal
                    let html = `
                        <div class="text-center mb-4">
                            ${data.photo_url 
                                ? `<img src="${data.photo_url}" class="rounded-circle shadow-sm" style="width:120px;height:120px;object-fit:cover;">` 
                                : `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width:120px;height:120px;"><span class="display-4 fw-bold text-primary">${data.name.substring(0,1).toUpperCase()}</span></div>`}
                            <h4 class="mt-3 fw-bold text-dark">${data.name}</h4>
                            <div class="text-muted small">${data.email || 'Email não informado'}</div>
                        </div>

                        <div class="card border-0 bg-light rounded-4 mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Informações Pessoais</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">CPF</label>
                                        <div class="fw-medium text-dark">${data.cpf || '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">RG</label>
                                        <div class="fw-medium text-dark">${data.rg || '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Nascimento</label>
                                        <div class="fw-medium text-dark">${data.born_date_formatted}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Sexo</label>
                                        <div class="fw-medium text-dark">${data.sexo_label || '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Estado Civil</label>
                                        <div class="fw-medium text-dark">${data.civil_status_label}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Celular</label>
                                        <div class="fw-medium text-dark">${data.phone || '-'}</div>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted fw-bold text-uppercase">Endereço</label>
                                        <div class="fw-medium text-dark">
                                            ${[data.address, data.address_number, data.home_situation, data.city, data.state, data.cep].filter(Boolean).join(', ') || '-'}
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted fw-bold text-uppercase">Observações</label>
                                        <div class="fw-medium text-dark">
                                            ${(data.observations && data.observations != 0 && data.observations.trim() !== '') ? data.observations : '-'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-light rounded-4 mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Filiação</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Mãe</label>
                                        <div class="fw-medium text-dark">${data.mother_name || '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Contato Mãe</label>
                                        <div class="fw-medium text-dark">${data.motherPhone || '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Pai</label>
                                        <div class="fw-medium text-dark">${data.father_name || '-'}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted fw-bold text-uppercase">Contato Pai</label>
                                        <div class="fw-medium text-dark">${data.fatherPhone || '-'}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-light rounded-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Anexos</h6>
                                ${data.attachments && data.attachments.length > 0 ? `
                                    <div class="list-group list-group-flush bg-transparent">
                                        ${data.attachments.map(att => {
                                            let iconOrPreview = '';
                                            if (att.mime_type && att.mime_type.startsWith('image/')) {
                                                iconOrPreview = `<img src="${att.url}" class="rounded border bg-white" style="width: 40px; height: 40px; object-fit: cover;">`;
                                            } else if (att.mime_type && att.mime_type.includes('pdf')) {
                                                iconOrPreview = `<i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>`;
                                            } else if (att.mime_type && (att.mime_type.includes('word') || att.mime_type.includes('document'))) {
                                                iconOrPreview = `<i class="bi bi-file-earmark-word fs-2 text-primary"></i>`;
                                            } else if (att.mime_type && (att.mime_type.includes('sheet') || att.mime_type.includes('excel'))) {
                                                iconOrPreview = `<i class="bi bi-file-earmark-excel fs-2 text-success"></i>`;
                                            } else {
                                                iconOrPreview = `<i class="bi bi-file-earmark-text fs-2 text-muted"></i>`;
                                            }

                                            return `
                                                <a href="${att.url}" target="_blank" class="list-group-item list-group-item-action bg-transparent d-flex justify-content-between align-items-center px-0 py-2 border-bottom-0">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3 d-flex justify-content-center align-items-center" style="width: 48px; height: 48px;">
                                                            ${iconOrPreview}
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium text-dark text-truncate" style="max-width: 250px;" title="${att.original_name}">${att.original_name || att.filename}</div>
                                                            <div class="small text-muted">${att.size_formatted || ''}</div>
                                                        </div>
                                                    </div>
                                                    <div class="btn btn-sm btn-light rounded-circle p-2">
                                                        <i class="bi bi-download text-primary"></i>
                                                    </div>
                                                </a>
                                            `;
                                        }).join('')}
                                    </div>
                                ` : '<div class="text-center text-muted py-3 small">Nenhum anexo encontrado.</div>'}
                            </div>
                        </div>
                    `;
                    detailsModalBody.innerHTML = html;
                });
        };

        window.confirmDelete = function(id) {
            deleteId = id;
            deleteModal.show();
        };

        // PDF Form Submission with Spinner
        const pdfForm = document.getElementById('pdfForm');
        const btnGeneratePdf = document.getElementById('btnGeneratePdf');

        if (pdfForm && btnGeneratePdf) {
            pdfForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show Spinner
                const originalText = btnGeneratePdf.innerHTML;
                btnGeneratePdf.disabled = true;
                btnGeneratePdf.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Criando seu pdf...';

                const formData = new FormData(this);

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Erro na geração do PDF');
                })
                .then(blob => {
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'relatorio_registros.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                    
                    // Close modal
                    const pdfModalInstance = bootstrap.Modal.getInstance(document.getElementById('pdfModal'));
                    if (pdfModalInstance) {
                        pdfModalInstance.hide();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao gerar o PDF. Tente novamente.');
                })
                .finally(() => {
                    // Reset Button
                    btnGeneratePdf.disabled = false;
                    btnGeneratePdf.innerHTML = originalText;
                });
            });
        }
    });
</script>
@endsection
