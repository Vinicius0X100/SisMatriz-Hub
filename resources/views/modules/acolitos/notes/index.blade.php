@extends('layouts.app')

@section('title', 'Notas e Avisos dos Acólitos')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Notas e Avisos dos Acólitos</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.index') }}" class="text-decoration-none">Acólitos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Notas e Avisos</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    <div id="ajaxAlertContainer"></div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-4">
                    <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="search" class="form-control ps-5 rounded-pill" placeholder="Buscar por nome..." style="height: 45px;">
                    </div>
                </div>

                <!-- Filtro Data De -->
                <div class="col-md-2">
                    <label for="date_from" class="form-label fw-bold text-muted small">De</label>
                    <div class="input-group">
                         <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-calendar3 text-muted"></i></span>
                         <input type="text" id="date_from" class="form-control border-start-0 rounded-end-pill ps-2" placeholder="dd/mm/aaaa" style="height: 45px;" data-mask="00/00/0000">
                    </div>
                </div>

                <!-- Filtro Data Até -->
                <div class="col-md-2">
                    <label for="date_to" class="form-label fw-bold text-muted small">Até</label>
                    <div class="input-group">
                         <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-calendar3 text-muted"></i></span>
                         <input type="text" id="date_to" class="form-control border-start-0 rounded-end-pill ps-2" placeholder="dd/mm/aaaa" style="height: 45px;" data-mask="00/00/0000">
                    </div>
                </div>

                <!-- Ações -->
                <div class="col-md-4 text-end d-flex gap-2 justify-content-end">
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActionsBtn" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActionsBtn">
                            <li><a class="dropdown-item text-danger" href="#" id="bulkDeleteBtn"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
                    <!-- Botão Novo não necessário aqui pois cria-se notas via perfil do acólito, mas poderia ter -->
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
                            <th scope="col" class="ps-4">Acólito</th>
                            <th scope="col">Data/Hora</th>
                            <th scope="col">Nota</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
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
            <p class="text-muted mb-0">Esta ação removerá <strong>todas as notas</strong> dos acólitos selecionados.</p>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Sim, Excluir Notas</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mask initialization (using jQuery for mask plugin compatibility if loaded, otherwise standard input)
    if (typeof $ !== 'undefined' && $.fn.mask) {
        $('#date_from').mask('00/00/0000');
        $('#date_to').mask('00/00/0000');
    }

    const searchInput = document.getElementById('search');
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationLinks = document.getElementById('paginationLinks');
    const selectAllCheckbox = document.getElementById('selectAll');
    const bulkActionsBtn = document.getElementById('bulkActionsBtn');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const ajaxAlertContainer = document.getElementById('ajaxAlertContainer');

    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    let currentPage = 1;
    let debounceTimer;
    let globalSelectedIds = new Set();
    let selectAllMode = false;
    let totalRecords = 0;
    let isBulkDelete = false;

    // Sorting state
    let currentSortBy = 'send_at';
    let currentSortOrder = 'desc';

    // Initial Fetch
    fetchData();

    // Event Listeners
    // Sort Headers
    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const sortBy = this.dataset.sort;
            
            if (currentSortBy === sortBy) {
                // Toggle order
                currentSortOrder = currentSortOrder === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortBy = sortBy;
                currentSortOrder = (sortBy === 'send_at') ? 'desc' : 'asc';
            }

            // Update UI Icons
            updateSortIcons();

            // Fetch Data
            currentPage = 1;
            resetSelection();
            fetchData();
        });
    });

    function updateSortIcons() {
        document.querySelectorAll('th.sortable').forEach(th => {
            const icon = th.querySelector('.sort-icon');
            const sortBy = th.dataset.sort;
            
            // Reset all to default bidirectional arrow
            icon.className = 'bi bi-arrow-down-up text-muted ms-1 small sort-icon';
            
            if (sortBy === currentSortBy) {
                // Set active arrow
                if (currentSortOrder === 'asc') {
                    icon.className = 'bi bi-arrow-up text-primary ms-1 small sort-icon';
                } else {
                    icon.className = 'bi bi-arrow-down text-primary ms-1 small sort-icon';
                }
            }
        });
    }
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            currentPage = 1;
            resetSelection();
            fetchData();
        }, 300);
    });

    dateFromInput.addEventListener('change', function() {
        currentPage = 1;
        resetSelection();
        fetchData();
    });

    dateToInput.addEventListener('change', function() {
        currentPage = 1;
        resetSelection();
        fetchData();
    });

    selectAllCheckbox.addEventListener('change', function() {
        selectAllMode = this.checked;
        const checkboxes = document.querySelectorAll('.row-checkbox');
        
        if (selectAllMode) {
            globalSelectedIds.clear();
            checkboxes.forEach(cb => cb.checked = true);
        } else {
            globalSelectedIds.clear();
            checkboxes.forEach(cb => cb.checked = false);
        }
        updateBulkActions();
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (globalSelectedIds.size === 0 && !selectAllMode) return;
        
        isBulkDelete = true;
        deleteModal.show();
    });

    confirmDeleteBtn.addEventListener('click', function() {
        if (isBulkDelete) {
            performBulkDelete();
        }
        deleteModal.hide();
    });

    // Functions
    function resetSelection() {
        selectAllMode = false;
        globalSelectedIds.clear();
        selectAllCheckbox.checked = false;
        updateBulkActions();
    }

    function showToast(message, type = 'success') {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill fs-4 me-3"></i>
                    <div><strong>${type === 'success' ? 'Sucesso!' : 'Erro!'}</strong> ${message}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        ajaxAlertContainer.innerHTML = alertHtml;
        setTimeout(() => {
             const alert = ajaxAlertContainer.querySelector('.alert');
             if(alert) {
                 const bsAlert = new bootstrap.Alert(alert);
                 bsAlert.close();
             }
        }, 5000);
    }

    function updateBulkActions() {
        const count = selectAllMode ? totalRecords : globalSelectedIds.size;
        bulkActionsBtn.disabled = count === 0;
        bulkActionsBtn.innerHTML = count > 0 ? `Ações (${count})` : 'Ações';
    }

    function performBulkDelete() {
        const payload = selectAllMode ? {
            select_all: true,
            search: searchInput.value,
            date_from: dateFromInput.value,
            date_to: dateToInput.value
        } : {
            ids: Array.from(globalSelectedIds)
        };

        fetch('{{ route("acolitos.notes.bulk-delete") }}', {
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

    function fetchData(url = null) {
        const params = new URLSearchParams({
            page: currentPage,
            search: searchInput.value,
            date_from: dateFromInput.value,
            date_to: dateToInput.value,
            sort_by: currentSortBy,
            sort_order: currentSortOrder
        });

        if (url) {
             try {
                const urlObj = new URL(url);
                const pageFromUrl = urlObj.searchParams.get('page');
                if (pageFromUrl) {
                    params.set('page', pageFromUrl);
                    currentPage = pageFromUrl;
                }
            } catch(e) {}
        }

        const fetchUrl = `{{ route('acolitos.notes.index') }}?${params.toString()}`;

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
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Erro ao carregar dados.</td></tr>';
        });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        let date = new Date(dateStr);
        if (isNaN(date.getTime()) && typeof dateStr === 'string' && dateStr.includes('/')) {
            const parts = dateStr.split(' ');
            if (parts.length === 2) {
                const dateParts = parts[0].split('/');
                const timeParts = parts[1].split(':');
                if (dateParts.length === 3 && timeParts.length >= 2) {
                    date = new Date(dateParts[2], dateParts[1] - 1, dateParts[0], timeParts[0], timeParts[1]);
                }
            }
        }
        if (isNaN(date.getTime())) return dateStr;
        return date.toLocaleString('pt-BR');
    }

    function renderTable(data) {
        if (!data || data.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="4" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Nenhum registro encontrado.</td></tr>';
            return;
        }

        tableBody.innerHTML = '';
        data.forEach(item => {
            const isSelected = selectAllMode || globalSelectedIds.has(item.id);
            const formattedDate = formatDate(item.send_at);

            const tr = document.createElement('tr');
            tr.className = 'align-middle';
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
                <td class="text-muted">${formattedDate}</td>
                <td class="small text-muted">${item.note}</td>
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
        paginationInfo.innerHTML = `Mostrando <span class="fw-bold">${data.from}</span> a <span class="fw-bold">${data.to}</span> de <span class="fw-bold">${data.total}</span> registros`;

        paginationLinks.innerHTML = '';
        data.links.forEach(link => {
            if (!link.url && !link.active && link.label !== '...') return;

            let label = link.label;
            if (label.includes('&laquo;') || label.includes('Previous') || label.includes('pagination.previous')) label = '<i class="bi bi-chevron-left"></i>';
            if (label.includes('&raquo;') || label.includes('Next') || label.includes('pagination.next')) label = '<i class="bi bi-chevron-right"></i>';

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

    window.toggleSelection = function(id, checked) {
        if (selectAllMode) {
            if (!checked) {
                selectAllMode = false;
                globalSelectedIds.clear();
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(cb => {
                    if (cb.checked) globalSelectedIds.add(parseInt(cb.value));
                });
            }
        } else {
            if (checked) {
                globalSelectedIds.add(id);
            } else {
                globalSelectedIds.delete(id);
            }
        }
        updateBulkActions();
    };
});
</script>
@endsection
