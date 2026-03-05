@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Registros Vicentinos</h2>
            <p class="text-muted small mb-0">Gerencie as fichas e acompanhamentos vicentinos.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registros Vicentinos</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-people-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Total de Fichas</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-person-heart text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Total de Familiares</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ $stats['families_total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Toolbar -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
                <div class="d-flex flex-column flex-md-row gap-3 w-100">
                    <div class="flex-grow-1">
                        <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" class="form-control ps-5 rounded-pill bg-light border-0" id="searchInput" placeholder="Nome, CPF, RG ou Telefone...">
                        </div>
                    </div>
                    <div style="min-width: 200px;">
                        <label for="bairroFilter" class="form-label fw-bold text-muted small">Filtrar por Bairro</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0" id="bairroFilter" placeholder="Digite o bairro...">
                    </div>
                    <div style="min-width: 150px;">
                        <label for="statusFilter" class="form-label fw-bold text-muted small">Status</label>
                        <select class="form-select rounded-pill bg-light border-0" id="statusFilter">
                            <option value="">Todos</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Dispensado</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle rounded-pill px-4 fw-bold text-nowrap" type="button" id="bulkActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            <i class="bi bi-layers me-2"></i> Ações
                        </button>
                        <ul class="dropdown-menu border-0 shadow rounded-3" aria-labelledby="bulkActionsDropdown">
                            <li><a class="dropdown-item py-2" href="#" id="bulkPdfBtn"><i class="bi bi-file-earmark-pdf me-2"></i> Gerar Relatório PDF</a></li>
                        </ul>
                    </div>
                    <a href="{{ route('vicentinos.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold text-nowrap">
                        <i class="bi bi-plus-lg me-2"></i> Nova Ficha
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start ps-4 py-3 text-secondary text-uppercase small fw-bold" style="width: 40px;">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold sortable cursor-pointer" data-sort="responsavel_nome">
                                Responsável <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                            </th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">CPF/Telefone</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold sortable cursor-pointer" data-sort="bairro">
                                Bairro <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                            </th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold text-center">Familiares</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold sortable cursor-pointer" data-sort="created_at">
                                Data Cadastro <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                            </th>
                            <th class="border-0 rounded-end py-3 text-secondary text-uppercase small fw-bold text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Content loaded via JS -->
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Carregando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-4" id="paginationContainer" style="display: none !important;">
                <div class="text-muted small" id="paginationInfo"></div>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0 gap-1" id="paginationLinks"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                    <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Excluir Ficha?</h4>
                <p class="text-muted mb-4">Esta ação não pode ser desfeita. Todos os dados, incluindo familiares, serão removidos permanentemente.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Sim, Excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PDF Modal -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Gerar Relatório PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Selecione as colunas que deseja incluir no relatório.</p>
                <form id="pdfForm" action="{{ route('vicentinos.pdf') }}" method="POST">
                    @csrf
                    <input type="hidden" name="selected_ids" id="pdfSelectedIds">
                    
                    <div class="row g-2 mb-4">
                        <div class="col-12"><h6 class="fw-bold text-secondary small text-uppercase border-bottom pb-1 mb-2">Dados Pessoais</h6></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="responsavel_nome" checked disabled><label class="form-check-label small">Nome</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="cpf" checked><label class="form-check-label small">CPF</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="rg"><label class="form-check-label small">RG</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="data_nascimento"><label class="form-check-label small">Data Nasc.</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="telefone" checked><label class="form-check-label small">Telefone</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="sexo"><label class="form-check-label small">Sexo</label></div></div>
                        
                        <div class="col-12 mt-3"><h6 class="fw-bold text-secondary small text-uppercase border-bottom pb-1 mb-2">Endereço</h6></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="endereco"><label class="form-check-label small">Endereço</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="bairro" checked><label class="form-check-label small">Bairro</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="cidade"><label class="form-check-label small">Cidade</label></div></div>
                        
                        <div class="col-12 mt-3"><h6 class="fw-bold text-secondary small text-uppercase border-bottom pb-1 mb-2">Situação</h6></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="quem_trabalha"><label class="form-check-label small">Quem Trabalha</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="local_trabalho"><label class="form-check-label small">Local Trab.</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="recebe_bolsa_familia"><label class="form-check-label small">Bolsa Família</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="valor_bolsa_familia"><label class="form-check-label small">Valor Bolsa</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="tipo_residencia"><label class="form-check-label small">Tipo Residência</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="valor_aluguel_prestacao"><label class="form-check-label small">Valor Aluguel</label></div></div>
                        
                        <div class="col-12 mt-3"><h6 class="fw-bold text-secondary small text-uppercase border-bottom pb-1 mb-2">Outros</h6></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="religiao"><label class="form-check-label small">Religião</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="catolico_tem_sacramentos"><label class="form-check-label small">Católico?</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="responsaveis_sindicancia"><label class="form-check-label small">Resp. Sindicância</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="data_dispensa"><label class="form-check-label small">Data Dispensa</label></div></div>
                        <div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="columns[]" value="motivo_dispensa"><label class="form-check-label small">Motivo Dispensa</label></div></div>
                        
                        <div class="col-12 mt-3"><h6 class="fw-bold text-secondary small text-uppercase border-bottom pb-1 mb-2">Opções Adicionais</h6></div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_family" id="includeFamily" value="1">
                                <label class="form-check-label small" for="includeFamily">
                                    Incluir Composição Familiar 
                                    <span class="text-muted fst-italic ms-1" style="font-size: 0.8em;">(Disponível apenas no modo Ficha individual - mais de 10 colunas selecionadas)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Gerar PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .table th { font-weight: 600; font-size: 0.85rem; text-transform: uppercase; color: #64748b; border-bottom-width: 1px !important; }
    .table td { font-size: 0.9rem; color: #334155; }
    .sortable:hover { background-color: #f1f5f9; color: #0f172a; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;
        let sortBy = 'created_at';
        let sortDir = 'desc';
        let debounceTimer;
        let deleteId = null;

        // Elements
        const searchInput = document.getElementById('searchInput');
        const bairroFilter = document.getElementById('bairroFilter');
        const statusFilter = document.getElementById('statusFilter');
        const tableBody = document.getElementById('tableBody');
        const paginationContainer = document.getElementById('paginationContainer');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationLinks = document.getElementById('paginationLinks');
        
        // Bulk Actions Elements
        const selectAll = document.getElementById('selectAll');
        const bulkActionsDropdown = document.getElementById('bulkActionsDropdown');
        const bulkPdfBtn = document.getElementById('bulkPdfBtn');
        const pdfSelectedIds = document.getElementById('pdfSelectedIds');
        const pdfModal = new bootstrap.Modal(document.getElementById('pdfModal'));
        
        // Modals
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

        // Initial Load
        fetchData();

        // Search Logic
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentPage = 1;
                fetchData();
            }, 300);
        });

        bairroFilter.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                currentPage = 1;
                fetchData();
            }, 300);
        });

        statusFilter.addEventListener('change', function() {
            currentPage = 1;
            fetchData();
        });
        
        // Bulk Actions Logic
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActionsState();
        });

        function updateBulkActionsState() {
            const checkboxes = document.querySelectorAll('.row-checkbox:checked');
            bulkActionsDropdown.disabled = checkboxes.length === 0;
            if (checkboxes.length > 0) {
                bulkActionsDropdown.innerHTML = `<i class="bi bi-layers me-2"></i> Ações (${checkboxes.length})`;
            } else {
                bulkActionsDropdown.innerHTML = `<i class="bi bi-layers me-2"></i> Ações`;
            }
        }
        
        bulkPdfBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const selected = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
            pdfSelectedIds.value = selected.join(',');
            pdfModal.show();
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

        // Delete Logic
        window.openDeleteModal = function(id) {
            deleteId = id;
            deleteModal.show();
        };

        confirmDeleteBtn.addEventListener('click', function() {
            if (!deleteId) return;

            const btn = this;
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Excluindo...';

            fetch(`/vicentinos/${deleteId}`, {
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
                    fetchData();
                } else {
                    alert('Erro ao excluir registro.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir registro.');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                deleteId = null;
            });
        });

        // Fetch Data Function
        function fetchData() {
            // Reset select all
            selectAll.checked = false;
            updateBulkActionsState();

            const params = new URLSearchParams({
                page: currentPage,
                search: searchInput.value,
                bairro: bairroFilter.value,
                status: statusFilter.value,
                sort_by: sortBy,
                sort_dir: sortDir
            });

            fetch(`{{ route('vicentinos.index') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                renderTable(data.data); // Laravel paginate returns data in 'data' key
                renderPagination(data);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5 text-danger">
                            <i class="bi bi-exclamation-circle fs-1 mb-3 d-block"></i>
                            Erro ao carregar dados. Tente novamente.
                        </td>
                    </tr>
                `;
            });
        }

        function renderTable(records) {
            if (records.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="mb-3"><i class="bi bi-inbox fs-1 text-secondary opacity-25"></i></div>
                            <p class="mb-0">Nenhuma ficha encontrada.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = records.map(record => `
                <tr>
                    <td class="ps-4">
                        <input class="form-check-input row-checkbox" type="checkbox" value="${record.id}">
                    </td>
                    <td>
                        <div class="fw-bold text-dark">${record.responsavel_nome}</div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="small text-dark fw-semibold"><i class="bi bi-person-vcard me-1 text-muted"></i>${record.cpf || '-'}</span>
                            <span class="small text-muted"><i class="bi bi-telephone me-1"></i>${record.telefone || '-'}</span>
                        </div>
                    </td>
                    <td>${record.bairro || '-'}</td>
                    <td class="text-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">
                            ${record.families_count}
                        </span>
                    </td>
                    <td>${record.created_at_formatted}</td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <a href="/vicentinos/${record.id}" class="btn btn-sm btn-outline-secondary border-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Ver Detalhes">
                                <i class="bi bi-eye-fill"></i>
                            </a>
                            <a href="/vicentinos/${record.id}/edit" class="btn btn-sm btn-outline-primary border-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Editar">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <button onclick="openDeleteModal(${record.id})" class="btn btn-sm btn-outline-danger border-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Excluir">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
            
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.addEventListener('change', updateBulkActionsState);
            });
        }

        function renderPagination(data) {
            if (data.total === 0) {
                paginationContainer.style.setProperty('display', 'none', 'important');
                return;
            }

            paginationContainer.style.setProperty('display', 'flex', 'important');
            paginationInfo.textContent = `Mostrando ${data.from || 0} a ${data.to || 0} de ${data.total} registros`;

            let html = '';
            
            // Previous
            html += `<li class="page-item ${data.prev_page_url ? '' : 'disabled'}">
                        <a class="page-link rounded-start border-0 bg-light text-secondary" href="#" onclick="event.preventDefault(); changePage(${currentPage - 1})"><i class="bi bi-chevron-left"></i></a>
                     </li>`;

            // Pages (simple implementation)
            for (let i = 1; i <= data.last_page; i++) {
                if (i === 1 || i === data.last_page || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                                <a class="page-link border-0 ${i === currentPage ? 'bg-primary text-white shadow-sm rounded-3' : 'bg-light text-secondary rounded-3 mx-1'}" href="#" onclick="event.preventDefault(); changePage(${i})">${i}</a>
                             </li>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<li class="page-item disabled"><span class="page-link border-0 bg-transparent">...</span></li>`;
                }
            }

            // Next
            html += `<li class="page-item ${data.next_page_url ? '' : 'disabled'}">
                        <a class="page-link rounded-end border-0 bg-light text-secondary" href="#" onclick="event.preventDefault(); changePage(${currentPage + 1})"><i class="bi bi-chevron-right"></i></a>
                     </li>`;

            paginationLinks.innerHTML = html;
        }

        window.changePage = function(page) {
            if (page < 1) return;
            currentPage = page;
            fetchData();
        };
    });
</script>
@endsection