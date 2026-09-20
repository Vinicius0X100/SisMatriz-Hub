@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Avisos Paroquiais</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Avisos Paroquiais</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Sucesso!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Avisos</h6>
                <p class="text-muted mb-0">Cadastre e gerencie os avisos da sua paróquia.</p>
            </div>
            <a href="{{ route('avisos.create') }}" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2">
                <i class="mdi mdi-plus"></i>
                <span>Novo aviso</span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Título ou descrição..." value="{{ request('search') }}" style="height: 45px;">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">Filtrar por importância</label>
                    <select id="importanceFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="0" {{ request('importance') === '0' ? 'selected' : '' }}>Normal</option>
                        <option value="1" {{ request('importance') === '1' ? 'selected' : '' }}>Médio</option>
                        <option value="2" {{ request('importance') === '2' ? 'selected' : '' }}>Alto</option>
                    </select>
                </div>
                <div class="col-md-5 text-end d-flex gap-2 justify-content-end align-items-end">
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                            <li><a class="dropdown-item text-danger" href="#" id="bulkDeleteBtn"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="table-container">
                @include('modules.avisos.partials.list')
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewAvisoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="viewAvisoTitle">Aviso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" id="viewAvisoImportanceBadge">Normal</span>
                        <div class="d-flex align-items-center gap-2 text-muted small" id="viewAvisoDeviceWrapper">
                            <i class="bi" id="viewAvisoDeviceIcon"></i>
                            <span id="viewAvisoDeviceLabel"></span>
                        </div>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>
                        <span id="viewAvisoSendAt"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="rounded-4 bg-light d-flex align-items-center justify-content-center mb-3" style="width: 100%; max-height: 260px; overflow: hidden;">
                        <img src="" alt="" id="viewAvisoImage" class="img-fluid d-none" style="width: 100%; object-fit: cover; object-position: center;">
                        <div id="viewAvisoImagePlaceholder" class="text-muted d-flex flex-column align-items-center justify-content-center py-5 w-100">
                            <i class="bi bi-image fs-1 mb-2"></i>
                            <span class="small">Nenhuma imagem disponível para este aviso.</span>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <h6 class="fw-bold text-muted small text-uppercase mb-1">Descrição</h6>
                    <p class="mb-0" id="viewAvisoLegend"></p>
                </div>
            </div>
            <div class="modal-footer border-top-0 d-flex justify-content-between">
                <a href="#" target="_blank" class="btn btn-outline-secondary rounded-pill px-4 d-none" id="viewAvisoAttachmentBtn">
                    <i class="bi bi-paperclip me-2"></i>
                    Abrir anexo
                </a>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAvisoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita. O aviso será removido permanentemente do sistema.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteAvisoForm" action="#" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Sim, excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Excluir avisos selecionados?</h4>
                <p class="text-muted mb-4">Você está prestes a excluir <strong id="bulkDeleteCount">0</strong> aviso(s) selecionado(s). Esta ação não poderá ser desfeita.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmBulkDeleteBtn" class="btn btn-danger rounded-pill px-4">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('viewAvisoModal');
        const deleteModalEl = document.getElementById('deleteAvisoModal');
        const deleteForm = document.getElementById('deleteAvisoForm');
        const bulkDeleteModalEl = document.getElementById('bulkDeleteModal');
        const bulkDeleteCountEl = document.getElementById('bulkDeleteCount');
        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');

        const searchInput = document.getElementById('searchInput');
        const importanceFilter = document.getElementById('importanceFilter');
        const tableContainer = document.getElementById('table-container');
        const bulkActionsBtn = document.getElementById('bulkActions');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

        let debounceTimer;
        const globalSelectedIds = new Set();

        function getBootstrapModal() {
            if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        function getDeleteModal() {
            if (!deleteModalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getOrCreateInstance(deleteModalEl);
        }
        function getBulkDeleteModal() {
            if (!bulkDeleteModalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getOrCreateInstance(bulkDeleteModalEl);
        }

        function populateViewModal(row) {
            const title = row.dataset.title || '';
            const legend = row.dataset.legend || '';
            const importanceLabel = row.dataset.importanceLabel || '';
            const importanceBadge = row.dataset.importanceBadge || 'secondary';
            const deviceLabel = row.dataset.deviceLabel || '';
            const deviceIcon = row.dataset.deviceIcon || 'bi-globe';
            const sendAt = row.dataset.sendAt || '';
            const imageUrl = row.dataset.imageUrl || '';
            const attachmentUrl = row.dataset.attachmentUrl || '';

            const titleEl = document.getElementById('viewAvisoTitle');
            const legendEl = document.getElementById('viewAvisoLegend');
            const importanceEl = document.getElementById('viewAvisoImportanceBadge');
            const deviceWrapper = document.getElementById('viewAvisoDeviceWrapper');
            const deviceIconEl = document.getElementById('viewAvisoDeviceIcon');
            const deviceLabelEl = document.getElementById('viewAvisoDeviceLabel');
            const sendAtEl = document.getElementById('viewAvisoSendAt');
            const imageEl = document.getElementById('viewAvisoImage');
            const imagePlaceholderEl = document.getElementById('viewAvisoImagePlaceholder');
            const attachmentBtn = document.getElementById('viewAvisoAttachmentBtn');

            if (titleEl) titleEl.textContent = title;
            if (legendEl) legendEl.textContent = legend;
            if (importanceEl) {
                importanceEl.textContent = importanceLabel || 'Normal';
                importanceEl.className = 'badge bg-' + (importanceBadge || 'secondary');
            }

            if (deviceWrapper) {
                if (deviceLabel) {
                    deviceWrapper.classList.remove('d-none');
                    if (deviceIconEl) deviceIconEl.className = 'bi ' + deviceIcon;
                    if (deviceLabelEl) deviceLabelEl.textContent = deviceLabel;
                } else {
                    deviceWrapper.classList.add('d-none');
                }
            }

            if (sendAtEl) sendAtEl.textContent = sendAt;

            if (imageEl && imagePlaceholderEl) {
                if (imageUrl) {
                    imageEl.src = imageUrl;
                    imageEl.classList.remove('d-none');
                    imagePlaceholderEl.classList.add('d-none');
                } else {
                    imageEl.src = '';
                    imageEl.classList.add('d-none');
                    imagePlaceholderEl.classList.remove('d-none');
                }
            }

            if (attachmentBtn) {
                if (attachmentUrl) {
                    attachmentBtn.href = attachmentUrl;
                    attachmentBtn.classList.remove('d-none');
                } else {
                    attachmentBtn.href = '#';
                    attachmentBtn.classList.add('d-none');
                }
            }
        }

        // --- Gerenciamento de Seleção ---
        function toggleSelection(id, isSelected) {
            id = parseInt(id);
            if (isSelected) {
                globalSelectedIds.add(id);
            } else {
                globalSelectedIds.delete(id);
            }
            updateBulkActions();
        }

        function restoreSelection() {
            const checkboxes = tableContainer.querySelectorAll('.row-checkbox');
            let allChecked = checkboxes.length > 0;

            checkboxes.forEach(cb => {
                const id = parseInt(cb.value);
                if (globalSelectedIds.has(id)) {
                    cb.checked = true;
                } else {
                    cb.checked = false;
                    allChecked = false;
                }
            });

            const selectAll = tableContainer.querySelector('#selectAll');
            if (selectAll) {
                selectAll.checked = allChecked;
            }

            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = tableContainer.querySelectorAll('.row-checkbox');
            const allChecked = checkboxes.length > 0 && Array.from(checkboxes).every(cb => cb.checked);
            const selectAll = tableContainer.querySelector('#selectAll');
            if (selectAll) {
                selectAll.checked = allChecked;
            }

            const count = globalSelectedIds.size;
            if (bulkActionsBtn) {
                bulkActionsBtn.disabled = count === 0;
                bulkActionsBtn.innerHTML = count > 0 ? `Ações (${count})` : 'Ações';
            }
        }

        // --- Fetch Data (Paginação AJAX e Filtros) ---
        function fetchData(pageUrl = null) {
            const params = new URLSearchParams();
            if (searchInput && searchInput.value) {
                params.append('search', searchInput.value);
            }
            if (importanceFilter && importanceFilter.value !== '') {
                params.append('importance', importanceFilter.value);
            }

            let url = pageUrl;
            if (!url) {
                url = "{{ route('avisos.index') }}?" + params.toString();
            } else if (params.toString()) {
                const joinChar = url.includes('?') ? '&' : '?';
                url = url + joinChar + params.toString();
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Erro ao carregar avisos.');
                return res.text();
            })
            .then(html => {
                tableContainer.innerHTML = html;
                setupTableEvents();
                restoreSelection();
            })
            .catch(err => {
                console.error('Erro na requisição AJAX:', err);
            });
        }

        function setupTableEvents() {
            // Paginação AJAX: interceptar cliques nos links da paginação
            tableContainer.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    fetchData(this.href);
                });
            });

            // Selecionar Todos na página visível
            const selectAll = tableContainer.querySelector('#selectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    const checkboxes = tableContainer.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(cb => {
                        cb.checked = selectAll.checked;
                        const id = parseInt(cb.value);
                        if (selectAll.checked) {
                            globalSelectedIds.add(id);
                        } else {
                            globalSelectedIds.delete(id);
                        }
                    });
                    updateBulkActions();
                });
            }

            // Checkboxes individuais
            tableContainer.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', function () {
                    toggleSelection(this.value, this.checked);
                });
            });

            // Botões de Visualizar Aviso
            tableContainer.querySelectorAll('.btn-view-aviso').forEach(btn => {
                btn.addEventListener('click', function () {
                    const row = this.closest('tr');
                    if (!row) return;

                    const modal = getBootstrapModal();
                    if (!modal) return;

                    populateViewModal(row);
                    modal.show();
                });
            });

            // Botões de Excluir Aviso (Individual)
            tableContainer.querySelectorAll('.btn-delete-aviso').forEach(btn => {
                btn.addEventListener('click', function () {
                    const url = this.dataset.url;
                    const modal = getDeleteModal();
                    if (!modal || !deleteForm || !url) return;
                    deleteForm.action = url;
                    modal.show();
                });
            });
        }

        // Filtros (Busca e Importância)
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchData();
                }, 300);
            });
        }

        if (importanceFilter) {
            importanceFilter.addEventListener('change', function () {
                fetchData();
            });
        }

        // Botão de Exclusão em Massa
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function (e) {
                e.preventDefault();
                if (globalSelectedIds.size === 0) return;

                if (bulkDeleteCountEl) {
                    bulkDeleteCountEl.textContent = globalSelectedIds.size;
                }
                const modal = getBulkDeleteModal();
                if (modal) modal.show();
            });
        }

        if (confirmBulkDeleteBtn) {
            confirmBulkDeleteBtn.addEventListener('click', function () {
                const ids = Array.from(globalSelectedIds);
                if (ids.length === 0) return;

                confirmBulkDeleteBtn.disabled = true;
                confirmBulkDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Excluindo...';

                fetch("{{ route('avisos.bulk-delete') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ ids: ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const modal = getBulkDeleteModal();
                        if (modal) modal.hide();

                        globalSelectedIds.clear();
                        updateBulkActions();
                        fetchData();
                    } else {
                        alert(data.message || 'Erro ao excluir avisos.');
                    }
                })
                .catch(err => {
                    console.error('Erro ao excluir em massa:', err);
                    alert('Erro na comunicação com o servidor.');
                })
                .finally(() => {
                    confirmBulkDeleteBtn.disabled = false;
                    confirmBulkDeleteBtn.innerHTML = 'Sim, excluir';
                });
            });
        }

        // Inicializar eventos da tabela na carga inicial
        setupTableEvents();
    });
</script>
@endsection
