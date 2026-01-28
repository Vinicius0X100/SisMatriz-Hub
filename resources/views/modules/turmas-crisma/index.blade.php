@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Turmas de Crisma</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Turmas Crisma</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Turmas</h6>
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
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Ativas</h6>
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
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Concluídas/Canceladas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['inactive'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Filters and Actions Toolbar -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Search -->
                <div class="col-md-3">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Nome da turma..." value="{{ request('search') }}" style="height: 45px;">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-2">
                    <label for="statusFilter" class="form-label fw-bold text-muted small">Status</label>
                    <select id="statusFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="1">Não Iniciada</option>
                        <option value="3">Em Catequese</option>
                        <option value="2">Concluída</option>
                        <option value="4">Cancelada</option>
                    </select>
                </div>

                <!-- Date Range Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">Período (Início)</label>
                    <div class="input-group">
                        <input type="date" id="dateFrom" class="form-control rounded-pill rounded-end-0" placeholder="De" style="height: 45px;">
                        <input type="date" id="dateTo" class="form-control rounded-pill rounded-start-0" placeholder="Até" style="height: 45px; margin-left: -1px;">
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="col-md-4 text-end d-flex gap-2 justify-content-end align-items-end">
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações em Massa
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                            <li><a class="dropdown-item text-danger" href="#" onclick="bulkDelete()"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('turmas-crisma.create') }}'">
                        <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Nova Turma</span>
                    </button>
                </div>
            </div>

            <div id="table-container">
                @include('modules.turmas-crisma.partials.list')
            </div>
        </div>
    </div>


    <!-- Manage Modal -->
    <div class="modal fade" id="manageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Gerenciar Turma</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <div id="loadingManage" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>

                    <div id="contentManage" class="d-none">
                        <!-- Info Header -->
                        <div class="bg-light rounded-4 p-3 mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Turma</small>
                                    <span class="fw-bold fs-5" id="manageTurmaName">-</span>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Período</small>
                                    <span class="fw-medium" id="managePeriodo">-</span>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Catequista</small>
                                    <span class="fw-medium" id="manageCatequista">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Students List -->
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h6 class="fw-bold mb-0">Lista de Crismandos(as)</h6>
                            <div class="d-flex gap-2">
                                <select id="filterBatizado" class="form-select form-select-sm rounded-pill" style="width: 150px;">
                                    <option value="">Todos</option>
                                    <option value="1">Batizados</option>
                                    <option value="0">Não Batizados</option>
                                </select>
                                <div class="position-relative" style="width: 200px;">
                                    <input type="text" id="searchStudent" class="form-control form-control-sm rounded-pill ps-4" placeholder="Buscar crismando(a)...">
                                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive border rounded-3" style="max-height: 400px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="cursor-pointer sortable-modal" data-sort="name">Nome <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                        <th class="cursor-pointer sortable-modal" data-sort="phone">Telefone <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                        <th class="cursor-pointer sortable-modal" data-sort="batizado">Status <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                                        <th class="text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsList">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Modal -->
    <div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Transferir Aluno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-4">
                        Você está transferindo o aluno <strong id="transferStudentName" class="text-primary"></strong>.
                        <br><span class="text-muted small">Ao transferir, o histórico de presença passará a ser contabilizado na nova turma.</span>
                    </p>

                    <form id="transferForm">
                        @csrf
                        <input type="hidden" name="student_id" id="transferStudentId">
                        
                        <div class="mb-3">
                            <label for="newTurmaId" class="form-label fw-bold small text-muted">Nova Turma de Destino</label>
                            <select class="form-select rounded-3 p-3 bg-light border-0" name="new_turma_id" id="newTurmaId" required>
                                <option value="">Carregando turmas...</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill py-2">
                                Confirmar Transferência
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    let typingTimer;
    const doneTypingInterval = 500;
    
    // State
    let selectedIds = new Set();
    let currentSort = { column: 'created_at', order: 'desc' };

    // Elements
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const tableContainer = document.getElementById('table-container');
    const bulkActionsBtn = document.getElementById('bulkActions');

    // Event Listeners
    searchInput.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(fetchData, doneTypingInterval);
    });
    statusFilter.addEventListener('change', fetchData);
    dateFrom.addEventListener('change', fetchData);
    dateTo.addEventListener('change', fetchData);

    // Initial Load Setup
    document.addEventListener('DOMContentLoaded', () => {
        setupTableEvents();
    });

    function fetchData(pageUrl = null) {
        const params = new URLSearchParams();
        params.append('search', searchInput.value);
        params.append('status', statusFilter.value);
        params.append('date_from', dateFrom.value);
        params.append('date_to', dateTo.value);
        params.append('sort_by', currentSort.column);
        params.append('sort_order', currentSort.order);

        const url = pageUrl || "{{ route('turmas-crisma.index') }}?" + params.toString();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            tableContainer.innerHTML = html;
            setupTableEvents();
            restoreSelection();
        })
        .catch(error => console.error('Error:', error));
    }

    function setupTableEvents() {
        // Pagination Links
        tableContainer.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetchData(this.href);
            });
        });

        // Sorting Headers
        tableContainer.querySelectorAll('th.sortable').forEach(th => {
            th.addEventListener('click', function() {
                const column = this.dataset.sort;
                if (currentSort.column === column) {
                    currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.order = 'asc'; // Default new sort to asc usually, or desc if preferred
                }
                fetchData();
            });
            
            // Update Icon
            if (th.dataset.sort === currentSort.column) {
                const icon = th.querySelector('i');
                icon.className = currentSort.order === 'asc' ? 'bi bi-arrow-up text-primary' : 'bi bi-arrow-down text-primary';
            }
        });

        // Select All Checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const checkboxes = tableContainer.querySelectorAll('.row-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    if (this.checked) selectedIds.add(cb.value);
                    else selectedIds.delete(cb.value);
                });
                updateBulkActionsUI();
            });
        }

        // Row Checkboxes
        const checkboxes = tableContainer.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) selectedIds.add(this.value);
                else selectedIds.delete(this.value);
                updateBulkActionsUI();
            });
        });
    }

    function restoreSelection() {
        const checkboxes = tableContainer.querySelectorAll('.row-checkbox');
        let allChecked = true;
        if(checkboxes.length === 0) allChecked = false;

        checkboxes.forEach(cb => {
            if (selectedIds.has(cb.value)) {
                cb.checked = true;
            } else {
                allChecked = false;
            }
        });

        const selectAll = document.getElementById('selectAll');
        if(selectAll) selectAll.checked = allChecked;
        
        updateBulkActionsUI();
    }

    function updateBulkActionsUI() {
        if (selectedIds.size > 0) {
            bulkActionsBtn.disabled = false;
            bulkActionsBtn.innerHTML = `(${selectedIds.size}) Ações`;
        } else {
            bulkActionsBtn.disabled = true;
            bulkActionsBtn.innerHTML = 'Ações em Massa';
        }
    }

    function bulkDelete() {
        if (confirm('Tem certeza que deseja excluir as ' + selectedIds.size + ' turmas selecionadas?')) {
            alert('Funcionalidade de exclusão em massa será implementada no backend em breve. IDs: ' + Array.from(selectedIds).join(', '));
        }
    }

    function confirmDelete(id) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        confirmBtn.onclick = function() {
            let form = document.createElement('form');
            form.action = "{{ url('turmas-crisma') }}/" + id;
            form.method = 'POST';
            
            let csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            
            let method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        };
        
        modal.show();
    }

    // Manage Modal Logic
    let currentStudents = [];
    let manageModal;
    let transferModal;
    let currentSortModal = { column: 'name', order: 'asc' };

    function openManageModal(id) {
        if (!manageModal) {
            manageModal = new bootstrap.Modal(document.getElementById('manageModal'));
        }

        document.getElementById('loadingManage').classList.remove('d-none');
        document.getElementById('contentManage').classList.add('d-none');
        manageModal.show();

        fetch(`/turmas-crisma/${id}/students`)
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    alert('Erro ao carregar dados.');
                    manageModal.hide();
                    return;
                }

                // Fill Info
                document.getElementById('manageTurmaName').textContent = data.turma.turma;
                document.getElementById('manageCatequista').textContent = data.turma.catequista ? data.turma.catequista.nome : 'N/A';
                
                let periodo = 'N/A';
                if(data.turma.inicio) {
                    const inicio = new Date(data.turma.inicio).toLocaleDateString('pt-BR');
                    const termino = data.turma.termino ? new Date(data.turma.termino).toLocaleDateString('pt-BR') : '?';
                    periodo = `${inicio} - ${termino}`;
                }
                document.getElementById('managePeriodo').textContent = periodo;

                // Students
                currentStudents = data.students;
                applyModalFilters();

                // Transfer Select Options
                const select = document.getElementById('newTurmaId');
                select.innerHTML = '<option value="">Selecione...</option>';
                data.availableTurmas.forEach(t => {
                    select.innerHTML += `<option value="${t.id}">${t.turma}</option>`;
                });

                document.getElementById('loadingManage').classList.add('d-none');
                document.getElementById('contentManage').classList.remove('d-none');
            })
            .catch(err => {
                console.error(err);
                alert('Erro de conexão.');
                manageModal.hide();
            });
    }

    function applyModalFilters() {
        const searchTerm = document.getElementById('searchStudent').value.toLowerCase();
        const filterBatizado = document.getElementById('filterBatizado').value;

        let filtered = currentStudents.filter(student => {
            const matchesSearch = student.name.toLowerCase().includes(searchTerm);
            let matchesBatizado = true;
            if (filterBatizado !== '') {
                matchesBatizado = (student.batizado == filterBatizado);
            }
            return matchesSearch && matchesBatizado;
        });

        // Sorting
        filtered.sort((a, b) => {
            let valA = a[currentSortModal.column];
            let valB = b[currentSortModal.column];

            // Handle batizado sort (boolean/int)
            if (currentSortModal.column === 'batizado') {
                valA = valA ? 1 : 0;
                valB = valB ? 1 : 0;
            } else if (typeof valA === 'string') {
                valA = valA.toLowerCase();
                valB = valB.toLowerCase();
            }

            if (valA < valB) return currentSortModal.order === 'asc' ? -1 : 1;
            if (valA > valB) return currentSortModal.order === 'asc' ? 1 : -1;
            return 0;
        });

        renderStudents(filtered);
        updateSortIcons();
    }

    function updateSortIcons() {
        document.querySelectorAll('.sortable-modal').forEach(th => {
            const icon = th.querySelector('i');
            if (th.dataset.sort === currentSortModal.column) {
                icon.className = currentSortModal.order === 'asc' ? 'bi bi-arrow-up text-primary' : 'bi bi-arrow-down text-primary';
            } else {
                icon.className = 'bi bi-arrow-down-up small text-muted ms-1';
            }
        });
    }

    function renderStudents(list) {
        const tbody = document.getElementById('studentsList');
        tbody.innerHTML = '';

        if(list.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Nenhum crismando encontrado.</td></tr>';
            return;
        }

        list.forEach(student => {
            const tr = document.createElement('tr');
            
            let statusBadges = '';
            if(student.batizado) statusBadges += '<span class="badge bg-success bg-opacity-10 text-success me-1">Batizado</span>';
            else statusBadges += '<span class="badge bg-warning bg-opacity-10 text-warning me-1">Não Batizado</span>';
            
            if(student.is_transfered) statusBadges += '<span class="badge bg-info bg-opacity-10 text-info">Transferido</span>';

            tr.innerHTML = `
                <td class="fw-medium">${student.name}</td>
                <td class="small text-muted">${student.phone}</td>
                <td>${statusBadges}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="openTransferModal(${student.id}, '${student.name.replace(/'/g, "\\'")}')">
                        <i class="bi bi-arrow-left-right me-1"></i> Transferir
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Modal Filters Event Listeners
    document.getElementById('searchStudent').addEventListener('input', applyModalFilters);
    document.getElementById('filterBatizado').addEventListener('change', applyModalFilters);

    // Modal Sorting Event Listeners
    document.querySelectorAll('.sortable-modal').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.dataset.sort;
            if (currentSortModal.column === column) {
                currentSortModal.order = currentSortModal.order === 'asc' ? 'desc' : 'asc';
            } else {
                currentSortModal.column = column;
                currentSortModal.order = 'asc';
            }
            applyModalFilters();
        });
    });

    function openTransferModal(studentId, studentName) {
        document.getElementById('transferStudentId').value = studentId;
        document.getElementById('transferStudentName').textContent = studentName;
        
        if (!transferModal) {
            transferModal = new bootstrap.Modal(document.getElementById('transferModal'));
        }
        transferModal.show();
    }

    // Submit Transfer
    document.getElementById('transferForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Processando...';

        fetch("{{ route('turmas-crisma.transfer') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                transferModal.hide();
                manageModal.hide();
                fetchData(); // Refresh main table
            } else {
                alert(data.error || 'Erro ao transferir.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Erro ao processar requisição.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

</script>

<!-- Modal Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita. A turma será removida permanentemente.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
