@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Apuração de Vicentinos</h2>
            <p class="text-muted small mb-0">Controle mensal de assistidos e não assistidos.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Apuração de Vicentinos</li>
            </ol>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-list-check text-primary fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Total de Registros</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-check-circle text-success fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Assistidos</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ $stats['assistidos'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="bi bi-x-circle text-warning fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Não Assistidos</h6>
                        <h3 class="fw-bold text-dark mb-0">{{ $stats['nao_assistidos'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters & Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Toolbar -->
            <form action="{{ route('vicentinos-apuracoes.index') }}" method="GET" id="filterForm">
                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" class="form-control ps-5 rounded-pill bg-light border-0" id="search" placeholder="Nome..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3 col-sm-6">
                        <label for="mes_ano" class="form-label fw-bold text-muted small">Mês Lançamento</label>
                        <select name="mes_ano" class="form-select rounded-pill bg-light border-0" id="mes_ano" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach($mesesDisponiveis as $mes)
                                <option value="{{ $mes['value'] }}" {{ request('mes_ano') == $mes['value'] ? 'selected' : '' }}>{{ $mes['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-sm-6">
                        <label for="kind" class="form-label fw-bold text-muted small">Tipo</label>
                        <select name="kind" class="form-select rounded-pill bg-light border-0" id="kind" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="1" {{ request('kind') == '1' ? 'selected' : '' }}>Assistido</option>
                            <option value="0" {{ request('kind') == '0' ? 'selected' : '' }}>Não Assistido</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                        <select name="ent_id" class="form-select rounded-pill bg-light border-0" id="ent_id" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}" {{ request('ent_id') == $entidade->ent_id ? 'selected' : '' }}>{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="d-flex flex-wrap gap-2 justify-content-end mb-4">
                    <button type="button" id="bulkDeleteBtn" class="btn btn-danger rounded-pill px-4 fw-bold text-nowrap d-none">
                        <i class="bi bi-trash me-2"></i> Excluir Selecionados (<span id="selectedCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#reportModal">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i> Gerar Relatório
                    </button>
                    <a href="{{ route('vicentinos-apuracoes.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold text-nowrap">
                        <i class="bi bi-plus-lg me-2"></i> Nova Apuração
                    </a>
                </div>
            </form>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start ps-4 py-3" style="width: 50px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Nome</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Endereço</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Comunidade</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Mês Ref.</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Tipo</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Data Envio</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Enviado por</th>
                            <th class="border-0 rounded-end py-3 text-end pe-4 text-secondary text-uppercase small fw-bold">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td class="ps-4">
                                <div class="form-check">
                                    <input class="form-check-input row-checkbox" type="checkbox" value="{{ $record->w_id }}">
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $record->name }}</div>
                            </td>
                            <td>
                                <div class="text-muted small">
                                    {{ $record->address }} {{ $record->address_number ? ', ' . $record->address_number : '' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $record->entidade->ent_name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="text-muted">
                                    @php
                                        $meses = [
                                            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                        ];
                                        echo $meses[$record->month_entire] ?? 'N/A';
                                    @endphp
                                </span>
                            </td>
                            <td>
                                @if($record->kind == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Assistido</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Não Assistido</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-muted small">
                                    {{ $record->created_at ? $record->created_at->format('d/m/Y') : '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle bg-secondary bg-opacity-10 text-secondary small fw-bold me-2" style="width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="text-muted small">{{ $record->sender->name ?? $record->sendby }}</span>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('vicentinos-apuracoes.edit', $record->w_id) }}" class="btn btn-sm btn-light text-primary rounded-circle shadow-sm" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('vicentinos-apuracoes.destroy', $record->w_id) }}" method="POST" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle shadow-sm" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-inbox fs-1 mb-3 opacity-25"></i>
                                    <p class="mb-0">Nenhum registro encontrado.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $records->withQueryString()->links() }}
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Confirmar Exclusão</h5>
                    <p class="text-muted mb-4" id="deleteConfirmMessage">Tem certeza que deseja excluir este registro?</p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, Excluir</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Relatório -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="reportModalLabel">Gerar Relatório de Apuração</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="generateReportForm" action="{{ route('vicentinos-apuracoes.pdf') }}" method="GET">
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Período -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Período</label>
                                <select name="periodo_tipo" id="periodo_tipo" class="form-select rounded-3 bg-light border-0 mb-2">
                                    <option value="">Usar filtro atual da tela</option>
                                    <option value="mes_atual">Mês Atual</option>
                                    <option value="bimestral">Últimos 2 meses (Bimestral)</option>
                                    <option value="trimestral">Últimos 3 meses (Trimestral)</option>
                                    <option value="todo">Todo o período</option>
                                    <option value="personalizado">Personalizado (De - Até)</option>
                                </select>
                                <div id="customDateRange" class="d-none row g-2">
                                    <div class="col-6">
                                        <input type="date" name="data_inicio" class="form-control rounded-3 bg-light border-0" placeholder="De">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" name="data_fim" class="form-control rounded-3 bg-light border-0" placeholder="Até">
                                    </div>
                                </div>
                            </div>

                            <!-- Mês Referência (Se não usar período) -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Mês Referência Específico</label>
                                <select name="mes_ano" class="form-select rounded-3 bg-light border-0">
                                    <option value="">Todos</option>
                                    @foreach($mesesDisponiveis as $mes)
                                        <option value="{{ $mes['value'] }}" {{ request('mes_ano') == $mes['value'] ? 'selected' : '' }}>{{ $mes['label'] }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Usado apenas se nenhum período acima for selecionado.</div>
                            </div>

                            <!-- Colunas -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark mb-3">Colunas do Relatório</label>
                                <div class="card bg-light border-0 rounded-3">
                                    <div class="card-body p-3">
                                        <div class="form-check mb-2 border-bottom pb-2">
                                            <input class="form-check-input" type="checkbox" id="selectAllColumns" checked>
                                            <label class="form-check-label fw-bold" for="selectAllColumns">
                                                Selecionar Todas
                                            </label>
                                        </div>
                                        <div class="row g-2">
                                            @php
                                                $cols = [
                                                    'name' => 'Nome',
                                                    'address' => 'Endereço',
                                                    'entidade' => 'Comunidade',
                                                    'month_entire' => 'Mês Ref.',
                                                    'kind' => 'Tipo',
                                                    'created_at' => 'Data Envio',
                                                    'sender' => 'Enviado por'
                                                ];
                                            @endphp
                                            @foreach($cols as $val => $label)
                                            <div class="col-md-4 col-sm-6">
                                                <div class="form-check">
                                                    <input class="form-check-input col-checkbox" type="checkbox" name="colunas[]" value="{{ $val }}" id="col_{{ $val }}" checked>
                                                    <label class="form-check-label" for="col_{{ $val }}">{{ $label }}</label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Formato -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark mb-3">Formato de Exportação</label>
                                <div class="d-flex gap-3">
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check" name="formato" id="formato_pdf" value="pdf" checked>
                                        <label class="btn btn-outline-danger w-100 rounded-3 p-3 text-start d-flex align-items-center" for="formato_pdf">
                                            <i class="bi bi-file-earmark-pdf fs-2 me-3"></i>
                                            <div>
                                                <div class="fw-bold">Arquivo PDF</div>
                                                <div class="small opacity-75">Ideal para impressão</div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="flex-fill">
                                        <input type="radio" class="btn-check" name="formato" id="formato_csv" value="csv">
                                        <label class="btn btn-outline-success w-100 rounded-3 p-3 text-start d-flex align-items-center" for="formato_csv">
                                            <i class="bi bi-file-earmark-excel fs-2 me-3"></i>
                                            <div>
                                                <div class="fw-bold">Arquivo CSV (Excel)</div>
                                                <div class="small opacity-75">Ideal para planilhas</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos Ocultos (Para manter os filtros atuais de pesquisa/tipo/comunidade) -->
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="kind" value="{{ request('kind') }}">
                            <input type="hidden" name="ent_id" value="{{ request('ent_id') }}">

                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnGenerateReport" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <span class="normal-text"><i class="bi bi-cloud-download me-2"></i> Gerar Relatório</span>
                            <span class="loading-text d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Gerando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<script>
    let timeout = null;
    let deleteAction = null; // Stores the function to execute on confirmation

    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 800);
    });

    // Mass Actions Logic
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const selectedCountSpan = document.getElementById('selectedCount');
        const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const deleteConfirmMessage = document.getElementById('deleteConfirmMessage');

        // Configurar botão de confirmação do modal
        confirmDeleteBtn.addEventListener('click', function() {
            if (deleteAction) {
                deleteAction();
                deleteConfirmModal.hide();
            }
        });

        // Intercept individual delete forms
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                deleteConfirmMessage.textContent = 'Tem certeza que deseja excluir este registro? Esta ação não pode ser desfeita.';
                deleteAction = () => form.submit();
                deleteConfirmModal.show();
            });
        });

        function updateBulkActions() {
            const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
            selectedCountSpan.textContent = selectedCount;
            
            if (selectedCount > 0) {
                bulkDeleteBtn.classList.remove('d-none');
            } else {
                bulkDeleteBtn.classList.add('d-none');
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });
        }

        rowCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkActions();
                // Update Select All checkbox state
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length;
                    selectAll.checked = allChecked;
                }
            });
        });

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function() {
                const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                
                if (selectedIds.length === 0) return;

                deleteConfirmMessage.textContent = `Tem certeza que deseja excluir ${selectedIds.length} registro(s)? Esta ação não pode ser desfeita.`;
                
                deleteAction = () => {
                    // Show loading state on bulk button (optional visual feedback)
                    const originalText = bulkDeleteBtn.innerHTML;
                    bulkDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Excluindo...';
                    bulkDeleteBtn.disabled = true;

                    fetch('{{ route("vicentinos-apuracoes.bulk-delete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: selectedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Erro:', error);
                        alert('Ocorreu um erro ao excluir os registros.');
                        // Reset button
                        bulkDeleteBtn.innerHTML = originalText;
                        bulkDeleteBtn.disabled = false;
                    });
                };

                deleteConfirmModal.show();
            });
        }

        // --- Report Modal Logic ---
        
        // Select All Columns
        const selectAllColumns = document.getElementById('selectAllColumns');
        const colCheckboxes = document.querySelectorAll('.col-checkbox');
        if (selectAllColumns) {
            selectAllColumns.addEventListener('change', function() {
                colCheckboxes.forEach(cb => cb.checked = this.checked);
            });
            colCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const allChecked = Array.from(colCheckboxes).every(c => c.checked);
                    selectAllColumns.checked = allChecked;
                });
            });
        }

        // Custom Date Range toggle
        const periodoTipo = document.getElementById('periodo_tipo');
        const customDateRange = document.getElementById('customDateRange');
        if (periodoTipo) {
            periodoTipo.addEventListener('change', function() {
                if (this.value === 'personalizado') {
                    customDateRange.classList.remove('d-none');
                } else {
                    customDateRange.classList.add('d-none');
                }
            });
        }

        // Handle Report Generation Submit
        const generateReportForm = document.getElementById('generateReportForm');
        const btnGenerateReport = document.getElementById('btnGenerateReport');
        
        if (generateReportForm) {
            generateReportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show spinner
                const normalText = btnGenerateReport.querySelector('.normal-text');
                const loadingText = btnGenerateReport.querySelector('.loading-text');
                
                normalText.classList.add('d-none');
                loadingText.classList.remove('d-none');
                btnGenerateReport.disabled = true;

                // Build query string
                const formData = new FormData(this);
                const queryParams = new URLSearchParams(formData).toString();
                const url = this.action + '?' + queryParams;
                
                const isPdf = formData.get('formato') === 'pdf';

                if (isPdf) {
                    // Open PDF in new tab
                    window.open(url, '_blank');
                    
                    // Reset button after a short delay
                    setTimeout(() => {
                        normalText.classList.remove('d-none');
                        loadingText.classList.add('d-none');
                        btnGenerateReport.disabled = false;
                        bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                    }, 1000);
                } else {
                    // CSV - trigger download in current window (won't navigate away because of content-disposition attachment)
                    window.location.href = url;
                    
                    // Reset button
                    setTimeout(() => {
                        normalText.classList.remove('d-none');
                        loadingText.classList.add('d-none');
                        btnGenerateReport.disabled = false;
                        bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();
                    }, 1500);
                }
            });
        }
    });
</script>
@endsection
@endsection
