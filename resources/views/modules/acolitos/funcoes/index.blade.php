@extends('layouts.app')

@section('title', 'Funções de Acólitos e Coroinhas')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Funções de Acólitos e Coroinhas</h2>
            <p class="text-muted small mb-0">Gerencie as funções disponíveis.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Funções</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-6">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Pesquisar por título..." style="height: 45px;">
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-6 text-end d-flex gap-2 justify-content-end align-items-end">
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActionsBtn" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActionsBtn">
                            <li><a class="dropdown-item text-danger" href="#" id="bulkDeleteBtn"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('acolitos.funcoes.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;">
                        <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Nova Função</span>
                    </a>
                </div>
            </div>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" width="40" class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th scope="col" class="ps-4 text-uppercase text-muted small fw-bold cursor-pointer" onclick="toggleSort('title')">
                                Título
                                <span id="sort-icon-title" class="ms-1"></span>
                            </th>
                            <th scope="col" class="text-end pe-4 text-uppercase text-muted small fw-bold">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
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

<style>
    .cursor-pointer { cursor: pointer; }
    .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #64748b; border-bottom-width: 1px !important; }
    .table td { font-size: 0.9rem; color: #334155; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationLinks = document.getElementById('paginationLinks');
    const selectAllCheckbox = document.getElementById('selectAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkActionsBtn = document.getElementById('bulkActionsBtn');

    // Modal elements
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    let currentPage = 1;
    let debounceTimer;
    let globalSelectedIds = new Set();
    let currentDeleteId = null;
    let isBulkDelete = false;
    let currentSortBy = 'created_at';
    let currentSortOrder = 'desc';

    // Global Functions for onclick (Defined before use)
    window.updateSortIcons = function() {
        // Reset all icons
        document.querySelectorAll('[id^="sort-icon-"]').forEach(el => el.innerHTML = '<i class="bi bi-arrow-down-up text-muted opacity-25"></i>');
        
        // Set current icon
        const icon = document.getElementById(`sort-icon-${currentSortBy}`);
        if (icon) {
            icon.innerHTML = currentSortOrder === 'asc' 
                ? '<i class="bi bi-arrow-up-short text-primary"></i>' 
                : '<i class="bi bi-arrow-down-short text-primary"></i>';
        }
    };

    window.toggleSort = function(column) {
        if (currentSortBy === column) {
            currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortBy = column;
            currentSortOrder = 'asc';
        }
        updateSortIcons();
        fetchData();
    };

    window.toggleSelection = function(id, checked) {
        if (checked) {
            globalSelectedIds.add(parseInt(id));
        } else {
            globalSelectedIds.delete(parseInt(id));
        }
        updateBulkActions();
    };

    window.confirmDelete = function(id) {
        currentDeleteId = id;
        isBulkDelete = false;
        deleteModal.show();
    };
    
    window.changePage = function(page) {
        if (page && page != currentPage) {
            currentPage = page;
            fetchData();
        }
    };

    // Initial Fetch
    updateSortIcons();
    fetchData();

    // Event Listeners
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentPage = 1;
            fetchData();
        }, 300);
    });

    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        const checkboxes = document.querySelectorAll('.row-checkbox');
        
        checkboxes.forEach(cb => {
            cb.checked = isChecked;
            const id = parseInt(cb.value);
            if (isChecked) {
                globalSelectedIds.add(id);
            } else {
                globalSelectedIds.delete(id);
            }
        });
        
        updateBulkActions();
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (globalSelectedIds.size === 0) return;
        
        isBulkDelete = true;
        deleteModal.show();
    });

    confirmDeleteBtn.addEventListener('click', function() {
        if (isBulkDelete) {
            performBulkDelete();
        } else if (currentDeleteId) {
            performSingleDelete(currentDeleteId);
        }
        deleteModal.hide();
    });

    function fetchData(url = null) {
        const params = new URLSearchParams({
            page: currentPage,
            search: searchInput.value,
            sort_by: currentSortBy,
            sort_order: currentSortOrder
        });

        // Handle URL page extraction
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

        const fetchUrl = `{{ route('acolitos.funcoes.index') }}?${params.toString()}`;

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (!data.data) {
                throw new Error('Formato de resposta inválido');
            }
            renderTable(data.data);
            renderPagination(data);
            updateBulkActions();
            
            // Update current page from response
            if (data.current_page) {
                currentPage = data.current_page;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            let msg = 'Erro ao carregar dados.';
            if (error.message) msg += ' ' + error.message;
            tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-5 text-danger"><i class="bi bi-exclamation-triangle fs-1 d-block mb-2"></i>${msg}</td></tr>`;
        });
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Nenhum registro encontrado.</td></tr>';
            return;
        }

        tableBody.innerHTML = '';
        data.forEach(item => {
            const isSelected = globalSelectedIds.has(item.f_id);
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center"><input class="form-check-input row-checkbox" type="checkbox" value="${item.f_id}" ${isSelected ? 'checked' : ''} onchange="toggleSelection(${item.f_id}, this.checked)"></td>
                <td class="ps-4 fw-bold text-dark">${item.title}</td>
                <td class="text-end pe-4">
                    <a href="{{ url('acolitos/funcoes') }}/${item.f_id}/edit" class="btn btn-sm btn-light border rounded-pill px-3 me-1">
                        <i class="bi bi-pencil text-warning"></i>
                    </a>
                    <button class="btn btn-sm btn-light border rounded-pill px-3" onclick="confirmDelete(${item.f_id})">
                        <i class="bi bi-trash text-danger"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    }

    function renderPagination(data) {
        if (!data || !data.links || data.total === 0) {
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
            
            // Extrair page number da URL
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

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked) && checkboxes.length > 0;
        selectAllCheckbox.checked = allChecked;

        if (globalSelectedIds.size > 0) {
            bulkActionsBtn.disabled = false;
            bulkActionsBtn.innerHTML = `Ações (${globalSelectedIds.size})`;
        } else {
            bulkActionsBtn.disabled = true;
            bulkActionsBtn.innerHTML = 'Ações';
        }
    }

    function performSingleDelete(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('acolitos/funcoes') }}/${id}`;
        
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }

    function performBulkDelete() {
        fetch('{{ route("acolitos.funcoes.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: Array.from(globalSelectedIds) })
        })
        .then(response => response.json())
        .then(data => {
            globalSelectedIds.clear();
            fetchData(); // Refresh table
            deleteModal.hide();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao excluir registros.');
        });
    }
});
</script>
@endsection
