@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Membros Pascom</h2>
            <p class="text-muted small mb-0">Gerencie os membros da Pascom.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Membros Pascom</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Membros</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                        <i class="bi bi-graph-up fs-3 text-info"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Média de Idade</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['avg_age'] ?? 0 }}</h3>
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
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Ativos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h3>
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
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Inativos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['inactive'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Nome..." style="height: 45px;">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">Comunidade</label>
                    <select id="entidadeFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todas</option>
                        @foreach($entidades as $e)
                            <option value="{{ $e->ent_id }}">{{ $e->ent_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-muted small">Tipo</label>
                    <select id="typeFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="0">Fotógrafo</option>
                        <option value="1">Redator</option>
                        <option value="2">Video Maker</option>
                        <option value="3">Designer</option>
                        <option value="4">Editor de Vídeo</option>
                        <option value="5">Streamer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold text-muted small">Status</label>
                    <select id="statusFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="0">Ativo</option>
                        <option value="1">Inativo</option>
                    </select>
                </div>
                <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                    <button id="mainPdfBtn" class="btn btn-danger rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" title="Gerar PDF">
                        <i class="bi bi-file-earmark-pdf fs-5"></i>
                    </button>
                    <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('pascom-membros.create') }}'">
                        <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Novo</span>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                            <li><a class="dropdown-item" href="#" id="bulkPdfBtn"><i class="bi bi-file-earmark-pdf me-2"></i> Gerar PDF Selecionados</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" id="bulkDeleteBtn"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
                </div>
            </div>

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
                            <th scope="col">Tipo</th>
                            <th scope="col">Comunidade</th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="year_member">Ano <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col">Idade</th>
                            <th scope="col" class="sortable cursor-pointer" data-sort="status">Status <i class="bi bi-arrow-down-up small text-muted ms-1"></i></th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div>Carregando membros...</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3" id="paginationContainer" style="display: none !important;">
                <div class="text-muted small" id="paginationInfo"></div>
                <nav aria-label="Navegação">
                    <ul class="pagination pagination-sm mb-0 justify-content-end gap-1" id="paginationLinks"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Excluir membro?</h4>
                <p class="text-muted mb-4">Esta ação é irreversível. O membro <span class="fw-semibold" id="deleteName"></span> será removido permanentemente.</p>
                <form id="deleteForm" method="POST" action="#">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Sim, excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 </div>

<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-triangle display-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Excluir selecionados?</h4>
                <p class="text-muted mb-4">Esta ação é irreversível. <span id="bulkDeleteCount" class="fw-semibold"></span> membro(s) serão removidos permanentemente.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmBulkDeleteBtn">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Gerar Relatório PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pdfForm" action="{{ route('pascom-membros.pdf') }}" method="POST">
                    @csrf
                    <input type="hidden" name="selected_ids" id="pdfSelectedIds">

                    <div id="pdfTableSelectionMsg" class="alert alert-info py-2 small mb-3" style="display:none;">
                        <i class="bi bi-info-circle me-1"></i> <span id="pdfTableSelectionCount">0</span> membros selecionados da tabela serão incluídos.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small">Ordenação</label>
                        <select class="form-select rounded-pill" name="order">
                            <option value="name_asc" selected>Nome (A–Z)</option>
                            <option value="name_desc">Nome (Z–A)</option>
                            <option value="year_member_desc">Ano (maior → menor)</option>
                            <option value="year_member_asc">Ano (menor → maior)</option>
                            <option value="age_desc">Idade (maior → menor)</option>
                            <option value="age_asc">Idade (menor → maior)</option>
                            <option value="status_asc">Status (Ativo → Inativo)</option>
                            <option value="status_desc">Status (Inativo → Ativo)</option>
                        </select>
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
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="type" checked id="colType">
                                    <label class="form-check-label" for="colType">Tipo</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="entidade" checked id="colEntidade">
                                    <label class="form-check-label" for="colEntidade">Comunidade</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="year_member" checked id="colYear">
                                    <label class="form-check-label" for="colYear">Ano</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="age" checked id="colAge">
                                    <label class="form-check-label" for="colAge">Idade</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="status" checked id="colStatus">
                                    <label class="form-check-label" for="colStatus">Status</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="columns[]" value="register_status" id="colLink">
                                    <label class="form-check-label" for="colLink">Vínculo (Registro Geral)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmPdfBtn">Gerar PDF</button>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .badge-status { font-size: 0.75rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    let sortBy = 'id';
    let sortDir = 'desc';
    let debounceTimer;
    let selectedIds = new Set();
    try {
        const saved = sessionStorage.getItem('pascomSelectedIds');
        if (saved) JSON.parse(saved).forEach(id => selectedIds.add(parseInt(id)));
    } catch (_) {}

    const searchInput = document.getElementById('searchInput');
    const entidadeFilter = document.getElementById('entidadeFilter');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('tableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationLinks = document.getElementById('paginationLinks');
    const selectAll = document.getElementById('selectAll');
    const bulkActions = document.getElementById('bulkActions');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkPdfBtn = document.getElementById('bulkPdfBtn');
    const mainPdfBtn = document.getElementById('mainPdfBtn');
    const pdfSelectedIds = document.getElementById('pdfSelectedIds');
    const pdfTableSelectionMsg = document.getElementById('pdfTableSelectionMsg');
    const pdfTableSelectionCount = document.getElementById('pdfTableSelectionCount');
    const confirmPdfBtn = document.getElementById('confirmPdfBtn');
    const pdfForm = document.getElementById('pdfForm');

    function typeLabel(v) {
        const map = {
            0: 'Fotógrafo',
            1: 'Redator',
            2: 'Video Maker',
            3: 'Designer',
            4: 'Editor de Vídeo',
            5: 'Streamer'
        };
        return map[v] ?? '-';
    }

    function statusBadge(v) {
        if (parseInt(v) === 0) return '<span class="badge bg-success badge-status">Ativo</span>';
        return '<span class="badge bg-secondary badge-status">Inativo</span>';
    }

    function saveSelection() {
        sessionStorage.setItem('pascomSelectedIds', JSON.stringify(Array.from(selectedIds)));
    }

    function updateBulkUi() {
        const count = selectedIds.size;
        if (count > 0) {
            bulkActions.removeAttribute('disabled');
        } else {
            bulkActions.setAttribute('disabled', 'true');
        }
        // update selectAll state based on current visible rows
        const currentCheckboxes = Array.from(document.querySelectorAll('.row-select'));
        if (currentCheckboxes.length) {
            const allChecked = currentCheckboxes.every(cb => selectedIds.has(parseInt(cb.value)));
            const someChecked = currentCheckboxes.some(cb => selectedIds.has(parseInt(cb.value)));
            selectAll.checked = allChecked;
            selectAll.indeterminate = !allChecked && someChecked;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    }

    function openPdfModal(useSelection) {
        const ids = useSelection ? Array.from(selectedIds) : [];
        pdfSelectedIds.value = ids.join(',');
        if (useSelection && ids.length > 0) {
            pdfTableSelectionCount.textContent = `${ids.length}`;
            pdfTableSelectionMsg.style.display = 'block';
        } else {
            pdfTableSelectionMsg.style.display = 'none';
        }
        const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
        modal.show();
    }

    function fetchData(page = 1) {
        const params = new URLSearchParams();
        params.set('page', page);
        params.set('sort_by', sortBy);
        params.set('sort_dir', sortDir);
        if (searchInput.value) params.set('search', searchInput.value);
        if (entidadeFilter.value) params.set('ent_id', entidadeFilter.value);
        if (typeFilter.value !== '') params.set('type', typeFilter.value);
        if (statusFilter.value !== '') params.set('status', statusFilter.value);

        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div>Carregando membros...</div>
                </td>
            </tr>`;

        fetch(`{{ route('pascom-membros.index') }}?${params.toString()}`, { headers: {'X-Requested-With':'XMLHttpRequest'} })
            .then(res => res.json())
            .then(data => {
                renderTable(data.data);
                renderPagination(data);
                updateBulkUi();
            });
    }

    function renderTable(rows) {
        if (!rows || rows.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">Nenhum registro encontrado</td></tr>`;
            return;
        }
        tableBody.innerHTML = rows.map(r => `
            <tr>
                <td width="40" class="text-center">
                    <div class="form-check d-flex justify-content-center">
                        <input class="form-check-input row-select" type="checkbox" value="${r.id}" ${selectedIds.has(parseInt(r.id)) ? 'checked' : ''}>
                    </div>
                </td>
                <td class="fw-semibold">${r.register_exists === false ? `<i class="bi bi-exclamation-triangle-fill text-warning me-2" title="Pessoa não encontrada no Registro Geral"></i>` : ''}${r.name}</td>
                <td>${typeLabel(r.type)}</td>
                <td>${r.entidade ?? '-'}</td>
                <td>${r.year_member ?? '-'}</td>
                <td>${r.age ?? '-'}</td>
                <td>${statusBadge(r.status)}</td>
                <td class="text-end">
                    <div class="btn-group">
                        <a href="{{ url('pascom/membros') }}/${r.id}/edit" class="btn btn-sm btn-light border rounded-pill px-3">Editar</a>
                        <button type="button" class="btn btn-sm btn-danger rounded-pill ms-1 px-3 btn-delete" data-id="${r.id}" data-name="${r.name}">Excluir</button>
                    </div>
                </td>
            </tr>
        `).join('');

        document.querySelectorAll('.row-select').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const id = parseInt(cb.value);
                if (cb.checked) {
                    selectedIds.add(id);
                } else {
                    selectedIds.delete(id);
                }
                saveSelection();
                updateBulkUi();
            });
        });
    }

    function renderPagination(meta) {
        paginationContainer.style.display = 'flex';
        paginationInfo.textContent = `Mostrando ${meta.from ?? 0}–${meta.to ?? 0} de ${meta.total ?? 0}`;
        paginationLinks.innerHTML = '';
        const totalPages = meta.last_page || 1;
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === meta.current_page ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', (e) => {
                e.preventDefault();
                currentPage = i;
                fetchData(currentPage);
            });
            paginationLinks.appendChild(li);
        }
    }

    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function() {
            const field = this.getAttribute('data-sort');
            if (sortBy === field) {
                sortDir = sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                sortBy = field;
                sortDir = 'asc';
            }
            fetchData(1);
        });
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchData(1), 300);
    });
    entidadeFilter.addEventListener('change', () => fetchData(1));
    typeFilter.addEventListener('change', () => fetchData(1));
    statusFilter.addEventListener('change', () => fetchData(1));

    selectAll.addEventListener('change', function() {
        const cbs = document.querySelectorAll('.row-select');
        cbs.forEach(cb => {
            const id = parseInt(cb.value);
            if (selectAll.checked) {
                cb.checked = true;
                selectedIds.add(id);
            } else {
                cb.checked = false;
                selectedIds.delete(id);
            }
        });
        saveSelection();
        updateBulkUi();
    });

    bulkDeleteBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const count = selectedIds.size;
        if (count === 0) return;
        document.getElementById('bulkDeleteCount').textContent = `${count}`;
        const modal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        modal.show();
    });

    document.getElementById('confirmBulkDeleteBtn').addEventListener('click', function() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const ids = Array.from(selectedIds);
        fetch(`{{ route('pascom-membros.bulk-delete') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token ?? ''
            },
            body: JSON.stringify({ selected_ids: ids })
        }).then(res => res.json())
          .then(resp => {
              if (resp?.success) {
                  ids.forEach(id => selectedIds.delete(id));
                  saveSelection();
                  fetchData(currentPage);
                  const modalEl = document.getElementById('bulkDeleteModal');
                  const inst = bootstrap.Modal.getInstance(modalEl);
                  if (inst) inst.hide();
              }
          });
    });

    mainPdfBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openPdfModal(false);
    });

    bulkPdfBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (selectedIds.size === 0) return;
        openPdfModal(true);
    });

    confirmPdfBtn.addEventListener('click', function() {
        const checkedColumns = pdfForm.querySelectorAll('input[name="columns[]"]:checked');
        if (!checkedColumns.length) {
            alert('Selecione ao menos um campo para o relatório.');
            return;
        }
        confirmPdfBtn.setAttribute('disabled', 'true');
        confirmPdfBtn.setAttribute('aria-busy', 'true');
        confirmPdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Gerando...';
        pdfForm.submit();
    });

    pdfForm.addEventListener('submit', function() {
        confirmPdfBtn.setAttribute('disabled', 'true');
        confirmPdfBtn.setAttribute('aria-busy', 'true');
        confirmPdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Gerando...';
    });

    fetchData();

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-delete');
        if (btn) {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const deleteName = document.getElementById('deleteName');
            const form = document.getElementById('deleteForm');
            deleteName.textContent = name ?? '';
            form.setAttribute('action', `{{ url('pascom/membros') }}/${id}`);
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    });
});
</script>
@endsection
