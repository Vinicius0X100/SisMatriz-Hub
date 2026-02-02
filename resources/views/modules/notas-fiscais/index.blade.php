@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Notas Fiscais</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Notas Fiscais</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-3">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Número, Fornecedor..." style="height: 45px;">
                    </div>
                </div>

                <!-- Filtro: Tipo -->
                <div class="col-md-2">
                    <label for="tipoFilter" class="form-label fw-bold text-muted small">Tipo</label>
                    <select id="tipoFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="NFe">NFe</option>
                        <option value="NFCe">NFCe</option>
                        <option value="NFSe">NFSe</option>
                        <option value="Recibo">Recibo</option>
                        <option value="Boleto">Boleto</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <!-- Filtro: Data Início -->
                <div class="col-md-2">
                    <label for="dataInicioFilter" class="form-label fw-bold text-muted small">De</label>
                    <input type="date" id="dataInicioFilter" class="form-control rounded-pill" style="height: 45px;">
                </div>

                <!-- Filtro: Data Fim -->
                <div class="col-md-2">
                    <label for="dataFimFilter" class="form-label fw-bold text-muted small">Até</label>
                    <input type="date" id="dataFimFilter" class="form-control rounded-pill" style="height: 45px;">
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-3 text-end d-flex gap-2 justify-content-end">
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center fw-bold px-3" style="height: 45px;" type="button" id="bulkActionsBtn" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                            <li>
                                <button type="button" class="dropdown-item text-danger py-2" onclick="confirmBulkDelete()">
                                    <i class="bi bi-trash me-2"></i> Excluir Selecionados
                                </button>
                            </li>
                        </ul>
                    </div>
                    
                    <a href="{{ route('notas-fiscais.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2 fw-bold" style="height: 45px;">
                        <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Nova</span>
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
                            <th scope="col" class="sortable cursor-pointer" data-sort="numero">Número <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="tipo">Tipo <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="emitente_nome">Emitente / Descrição <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="valor_total">Valor Total <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="data_emissao">Emissão <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="text-center">Anexo</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div>Carregando notas fiscais...</div>
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

<!-- Modal Exclusão Individual -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita. A nota fiscal e seu anexo serão removidos permanentemente.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão em Massa -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Excluir Selecionados?</h4>
                <p class="text-muted mb-4">Você selecionou <span id="bulkDeleteCount" class="fw-bold">0</span> registros. Esta ação é irreversível.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmBulkDeleteBtn">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="bulkDeleteForm" action="{{ route('notas-fiscais.bulk-destroy') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<style>
    .cursor-pointer { cursor: pointer; }
    .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #64748b; border-bottom-width: 1px !important; }
    .table td { font-size: 0.9rem; color: #334155; }
    .sortable:hover { background-color: #f1f5f9; color: #0f172a; }
    
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
        let sortBy = 'data_emissao';
        let sortDir = 'desc';
        let debounceTimer;
        let deleteId = null;
        let globalSelectedIds = new Set();

        // Elements
        const searchInput = document.getElementById('searchInput');
        const tipoFilter = document.getElementById('tipoFilter');
        const dataInicioFilter = document.getElementById('dataInicioFilter');
        const dataFimFilter = document.getElementById('dataFimFilter');
        const tableBody = document.getElementById('tableBody');
        const selectAllCheckbox = document.getElementById('selectAll');
        const bulkActionsBtn = document.getElementById('bulkActionsBtn');
        const paginationContainer = document.getElementById('paginationContainer');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationLinks = document.getElementById('paginationLinks');
        
        // Modals
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        // Initial Load
        fetchData();

        // --- Event Listeners ---

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentPage = 1;
                fetchData();
            }, 300);
        });

        [tipoFilter, dataInicioFilter, dataFimFilter].forEach(filter => {
            filter.addEventListener('change', function() {
                currentPage = 1;
                fetchData();
            });
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

        // Global Selection Logic
        window.toggleSelection = function(id, isSelected) {
            id = parseInt(id);
            if (isSelected) {
                globalSelectedIds.add(id);
            } else {
                globalSelectedIds.delete(id);
            }
            updateBulkActions();
        };

        window.changePage = function(page) {
            if (page && page != currentPage) {
                currentPage = page;
                fetchData();
            }
        };

        function updateBulkActions() {
            const visibleCheckboxes = document.querySelectorAll('.row-checkbox');
            const allVisibleChecked = visibleCheckboxes.length > 0 && Array.from(visibleCheckboxes).every(cb => cb.checked);
            selectAllCheckbox.checked = allVisibleChecked;

            const count = globalSelectedIds.size;
            bulkActionsBtn.disabled = count === 0;
            bulkActionsBtn.innerHTML = count > 0 ? `Ações (${count})` : 'Ações';
        }

        // Delete Individual
        window.confirmDelete = function(id) {
            deleteId = id;
            deleteModal.show();
        };

        confirmDeleteBtn.addEventListener('click', function() {
            if (!deleteId) return;

            fetch(`{{ url('notas-fiscais') }}/${deleteId}`, {
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
                    if (globalSelectedIds.has(deleteId)) {
                        globalSelectedIds.delete(deleteId);
                        updateBulkActions();
                    }
                    fetchData();
                } else {
                    alert('Erro ao excluir nota fiscal.');
                }
            })
            .catch(error => console.error('Error:', error));
        });

        // Bulk Delete
        window.confirmBulkDelete = function() {
            const ids = Array.from(globalSelectedIds);
            document.getElementById('bulkDeleteCount').textContent = ids.length;
            document.getElementById('bulkDeleteIds').value = ids.join(',');
            bulkDeleteModal.show();
        };

        document.getElementById('confirmBulkDeleteBtn').addEventListener('click', function() {
            document.getElementById('bulkDeleteForm').submit();
        });

        // Fetch Data
        function fetchData(url = null) {
            const params = new URLSearchParams({
                page: currentPage,
                search: searchInput.value,
                tipo: tipoFilter.value,
                data_inicio: dataInicioFilter.value,
                data_fim: dataFimFilter.value,
                sort_by: sortBy,
                sort_dir: sortDir
            });
            
            let fetchUrl;
            if (url) {
                try {
                    const urlObj = new URL(url);
                    const pageFromUrl = urlObj.searchParams.get('page');
                    if (pageFromUrl) {
                        params.set('page', pageFromUrl);
                        currentPage = pageFromUrl;
                    }
                } catch(e) {
                    const pageMatch = url.match(/page=(\d+)/);
                    if (pageMatch && pageMatch[1]) {
                        params.set('page', pageMatch[1]);
                        currentPage = pageMatch[1];
                    }
                }
            }

            fetchUrl = `{{ route('notas-fiscais.index') }}?${params.toString()}`;

            fetch(fetchUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.data) {
                    renderTable(data.data);
                    renderPagination(data);
                } else {
                    tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Erro no formato de dados.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-4 text-danger">Erro ao carregar dados.</td></tr>';
            });
        }

        function renderTable(data) {
            if (!data || data.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bi bi-receipt fs-1 d-block mb-2"></i>Nenhuma nota fiscal encontrada.</td></tr>';
                return;
            }

            tableBody.innerHTML = '';
            data.forEach(item => {
                const tr = document.createElement('tr');
                const isSelected = globalSelectedIds.has(item.id);
                
                const attachment = item.has_arquivo
                    ? `<a href="${item.download_url}" class="btn btn-sm btn-light border rounded-pill" title="Baixar Arquivo"><i class="bi bi-download text-primary"></i></a>`
                    : `<span class="text-muted opacity-50"><i class="bi bi-dash-lg"></i></span>`;

                tr.innerHTML = `
                    <td class="text-center"><input class="form-check-input row-checkbox" type="checkbox" value="${item.id}" ${isSelected ? 'checked' : ''} onchange="toggleSelection(this.value, this.checked)"></td>
                    <td class="fw-bold text-muted">${item.numero}</td>
                    <td><span class="badge rounded-pill bg-info text-dark bg-opacity-10 border border-info px-3 py-2">${item.tipo}</span></td>
                    <td>
                        <div class="fw-bold text-dark">${item.emitente_nome}</div>
                        <small class="text-muted"><i class="bi bi-people me-1"></i>${item.entidade_nome}</small>
                    </td>
                    <td class="fw-bold text-dark">${item.valor_total_formatted}</td>
                    <td class="text-muted">${item.data_emissao_formatted}</td>
                    <td class="text-center">${attachment}</td>
                    <td class="text-end">
                        <a href="${item.edit_url}" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                            <i class="bi bi-pencil text-warning"></i>
                        </a>
                        <button class="btn btn-sm btn-light border rounded-pill px-3" onclick="confirmDelete(${item.id})">
                            <i class="bi bi-trash text-danger"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
            updateBulkActions();
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
                if (!link.url && !link.active && link.label !== '...') return;

                let label = link.label;
                let ariaLabel = label;
                
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
                
                let pageNum = null;
                if (link.url) {
                    try {
                        const urlObj = new URL(link.url);
                        pageNum = urlObj.searchParams.get('page');
                    } catch(e) {
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
    });
</script>
@endsection