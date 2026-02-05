@extends('layouts.app')

@section('title', 'Solicitações de Segunda Via')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Solicitações de Segunda Via</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Segunda Via</li>
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

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Form de Pesquisa e Filtros -->
                <div class="col-md-10">
                    <form action="{{ route('solicitacoes-segunda-via.index') }}" method="GET" id="searchForm" class="row g-3">
                        <!-- Pesquisa -->
                        <div class="col-md-7">
                            <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                            <div class="position-relative">
                                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control ps-5 rounded-pill" placeholder="Nome, telefone, sacramento..." style="height: 45px;" oninput="debounceSearch()">
                            </div>
                        </div>
                        
                        <!-- Data Início -->
                        <div class="col-md-2">
                            <label for="data_inicio" class="form-label fw-bold text-muted small">De</label>
                            <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" class="form-control rounded-pill" style="height: 45px;" onchange="document.getElementById('searchForm').submit()">
                        </div>
                        
                        <!-- Data Fim -->
                        <div class="col-md-3">
                            <label for="data_fim" class="form-label fw-bold text-muted small">Até</label>
                            <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" class="form-control rounded-pill" style="height: 45px;" onchange="document.getElementById('searchForm').submit()">
                        </div>
                    </form>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                    <div class="dropdown w-100">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center w-100" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                            <li><a class="dropdown-item" href="#" onclick="submitBulkAction('print')"><i class="bi bi-file-earmark-pdf me-2"></i> Gerar PDF Selecionados</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="submitBulkAction('delete')"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Form para Ação em Massa -->
            <form id="bulkActionForm" action="{{ route('solicitacoes-segunda-via.bulk-action') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="ids" id="bulkIds">
                <input type="hidden" name="action" id="bulkActionType">
            </form>
            
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
                            <th scope="col" class="sortable cursor-pointer">Solicitante</th>
                            <th scope="col" class="sortable cursor-pointer">Telefone</th>
                            <th scope="col" class="sortable cursor-pointer">Sacramento</th>
                            <th scope="col" class="sortable cursor-pointer">Status</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input row-checkbox" type="checkbox" value="{{ $record->id }}" onchange="updateSelection(this)">
                                </div>
                            </td>
                            <td class="fw-bold">
                                @if($record->sacramento === 'matrimonio')
                                    {{ $record->nome_conjuges }}
                                @else
                                    {{ $record->nome_completo }}
                                @endif
                            </td>
                            <td>{{ $record->telefone }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill text-capitalize">
                                    {{ $record->sacramento }}
                                </span>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="statusSwitch{{ $record->id }}" 
                                        {{ $record->status == 1 ? 'checked' : '' }} 
                                        onchange="toggleStatus({{ $record->id }}, this)">
                                    <label class="form-check-label small text-muted" for="statusSwitch{{ $record->id }}">
                                        {{ $record->status == 1 ? 'Finalizado' : 'Pendente' }}
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">{{ $record->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('solicitacoes-segunda-via.print-sheet', $record->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle me-1" title="Imprimir Ficha" target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Detalhes" 
                                    data-record="{{ json_encode($record) }}"
                                    onclick="showDetails(this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" title="Remover" 
                                    onclick="confirmDelete('{{ route('solicitacoes-segunda-via.destroy', $record->id) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                    <i class="bi bi-inbox fs-1 mb-3"></i>
                                    <p class="mb-0 fw-bold">Nenhuma solicitação encontrada</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="mt-4">
                {{ $records->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Detalhes da Solicitação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Sacramento Header -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-bookmark-star me-2"></i>Sacramento</h6>
                    <div class="p-3 border rounded-3 bg-light">
                        <small class="text-muted text-uppercase fw-bold d-block mb-1">Tipo</small>
                        <span class="fs-5 fw-bold text-capitalize" id="detailSacramentoType"></span>
                    </div>
                </div>

                <!-- Dados Específicos -->
                <div class="mb-4">
                    <h6 class="fw-bold mb-3" id="detailSectionTitle"><i class="bi bi-person-lines-fill me-2"></i>Dados</h6>
                    <div class="row g-3" id="detailFields">
                        <!-- Fields will be injected here via JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-danger">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Tem certeza?</h5>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita.</p>
                <div class="d-grid gap-2">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold">Sim, excluir</button>
                        <button type="button" class="btn btn-light rounded-pill w-100 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Opções de Impressão -->
<div class="modal fade" id="printOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Opções de Impressão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3">Selecione os campos que deseja incluir no relatório:</p>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="printSelectAll" checked onchange="togglePrintColumns(this)">
                            <label class="form-check-label fw-bold" for="printSelectAll">Selecionar Todos</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col" type="checkbox" value="data_solicitacao" id="colData" checked>
                            <label class="form-check-label" for="colData">Data Solicitação</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input print-col" type="checkbox" value="solicitante" id="colSolicitante" checked>
                            <label class="form-check-label" for="colSolicitante">Solicitante</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input print-col" type="checkbox" value="telefone" id="colTelefone" checked>
                            <label class="form-check-label" for="colTelefone">Telefone</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check">
                            <input class="form-check-input print-col" type="checkbox" value="sacramento" id="colSacramento" checked>
                            <label class="form-check-label" for="colSacramento">Sacramento</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input print-col" type="checkbox" value="status" id="colStatus" checked>
                            <label class="form-check-label" for="colStatus">Status</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input print-col" type="checkbox" value="detalhes" id="colDetalhes">
                            <label class="form-check-label" for="colDetalhes">Detalhes Principais</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" onclick="confirmPrint()">Gerar Relatório</button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- Auto Search ---
    let timeout = null;
    function debounceSearch() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            document.getElementById('searchForm').submit();
        }, 500);
    }

    // --- Status Switch ---
    function toggleStatus(id, checkbox) {
        const newStatus = checkbox.checked ? 1 : 0;
        const label = checkbox.nextElementSibling;
        
        // Optimistic UI update
        label.innerText = newStatus === 1 ? 'Finalizado' : 'Pendente';

        fetch(`/solicitacoes-segunda-via/${id}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert if failed
                checkbox.checked = !checkbox.checked;
                label.innerText = checkbox.checked ? 'Finalizado' : 'Pendente';
                alert('Erro ao atualizar status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            checkbox.checked = !checkbox.checked;
            label.innerText = checkbox.checked ? 'Finalizado' : 'Pendente';
            alert('Erro de conexão.');
        });
    }

    // --- Bulk Actions & Selection Persistence ---
    const STORAGE_KEY = 'selected_sacramento_ids';
    
    function getSelectedIds() {
        const stored = localStorage.getItem(STORAGE_KEY);
        return stored ? JSON.parse(stored) : [];
    }

    function saveSelectedIds(ids) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(ids));
        updateBulkUI();
    }

    function updateSelection(checkbox) {
        let ids = getSelectedIds();
        const value = checkbox.value;

        if (checkbox.checked) {
            if (!ids.includes(value)) ids.push(value);
        } else {
            ids = ids.filter(id => id !== value);
        }
        saveSelectedIds(ids);
    }

    function updateBulkUI() {
        const ids = getSelectedIds();
        const count = ids.length;
        const bulkBtn = document.getElementById('bulkActions');
        
        if (count > 0) {
            bulkBtn.disabled = false;
            bulkBtn.innerText = `Ações (${count})`;
            bulkBtn.classList.remove('btn-light', 'border');
            bulkBtn.classList.add('btn-primary', 'text-white');
        } else {
            bulkBtn.disabled = true;
            bulkBtn.innerText = 'Ações';
            bulkBtn.classList.add('btn-light', 'border');
            bulkBtn.classList.remove('btn-primary', 'text-white');
        }
    }

    function submitBulkAction(action) {
        const ids = getSelectedIds();
        if (ids.length === 0) return;

        if (action === 'delete') {
            if (!confirm('Tem certeza que deseja excluir os itens selecionados?')) return;
            document.getElementById('bulkIds').value = ids.join(',');
            document.getElementById('bulkActionType').value = action;
            localStorage.removeItem(STORAGE_KEY);
            document.getElementById('bulkActionForm').submit();
        } else if (action === 'print') {
            new bootstrap.Modal(document.getElementById('printOptionsModal')).show();
        }
    }

    function togglePrintColumns(source) {
        document.querySelectorAll('.print-col').forEach(cb => cb.checked = source.checked);
    }

    function confirmPrint() {
        const form = document.getElementById('bulkActionForm');
        // Clear previous hidden inputs if any (except ids and action)
        form.querySelectorAll('input[name="columns[]"]').forEach(el => el.remove());

        document.querySelectorAll('.print-col:checked').forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'columns[]';
            input.value = cb.value;
            form.appendChild(input);
        });

        document.getElementById('bulkActionType').value = 'print';
        document.getElementById('bulkIds').value = getSelectedIds().join(',');
        
        form.submit();
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('printOptionsModal')).hide();
    }

    // Initialize Selection on Load
    document.addEventListener('DOMContentLoaded', function() {
        const ids = getSelectedIds();
        const checkboxes = document.querySelectorAll('.row-checkbox');
        
        checkboxes.forEach(cb => {
            if (ids.includes(cb.value)) {
                cb.checked = true;
            }
        });

        updateBulkUI();

        // Select All Logic (Current Page)
        const selectAll = document.getElementById('selectAll');
        if(selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                let currentIds = getSelectedIds();
                
                document.querySelectorAll('.row-checkbox').forEach(cb => {
                    cb.checked = isChecked;
                    if (isChecked) {
                        if (!currentIds.includes(cb.value)) currentIds.push(cb.value);
                    } else {
                        currentIds = currentIds.filter(id => id !== cb.value);
                    }
                });
                
                saveSelectedIds(currentIds);
            });
        }
    });

    // --- Other Functions ---
    function confirmDelete(url) {
        document.getElementById('deleteForm').action = url;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-BR');
    }

    function createField(label, value, icon = 'bi-text-paragraph') {
        return `
            <div class="col-md-6">
                <div class="p-3 border rounded-3 h-100">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi ${icon} me-2 text-muted"></i>
                        <small class="text-muted text-uppercase fw-bold">${label}</small>
                    </div>
                    <div class="fw-bold text-break">${value || '-'}</div>
                </div>
            </div>
        `;
    }

    function showDetails(button) {
        const record = JSON.parse(button.getAttribute('data-record'));
        
        // Header
        document.getElementById('detailSacramentoType').innerText = record.sacramento;
        
        // Title
        const sectionTitle = document.getElementById('detailSectionTitle');
        if (record.sacramento === 'matrimonio') {
            sectionTitle.innerHTML = '<i class="bi bi-heart-fill me-2"></i>Dados do Matrimônio';
        } else if (record.sacramento === 'batismo') {
            sectionTitle.innerHTML = '<i class="bi bi-water me-2"></i>Dados do Batismo';
        } else {
            sectionTitle.innerHTML = '<i class="bi bi-person-check-fill me-2"></i>Dados do ' + record.sacramento.charAt(0).toUpperCase() + record.sacramento.slice(1);
        }

        // Fields Builder
        let fieldsHtml = '';

        // Common Fields (Phone and Submission Date)
        fieldsHtml += createField('Telefone', record.telefone, 'bi-telephone');
        fieldsHtml += createField('Data de Envio', formatDate(record.created_at), 'bi-calendar3');

        if (record.sacramento === 'batismo') {
            fieldsHtml += createField('Nome Completo', record.nome_completo, 'bi-person-badge');
            fieldsHtml += createField('Data de Nascimento', formatDate(record.data_nascimento), 'bi-calendar-event');
            fieldsHtml += createField('Nome da Mãe', record.nome_mae, 'bi-person-heart');
            fieldsHtml += createField('Paróquia onde ocorreu', record.local_batismo, 'bi-building'); 
            fieldsHtml += createField('Local da Celebração', record.local_celebracao, 'bi-geo-alt');
            fieldsHtml += createField('Data do Batismo', formatDate(record.data_batismo), 'bi-calendar-check');
        } 
        else if (record.sacramento === 'matrimonio') {
            fieldsHtml += createField('Nome dos Cônjuges', record.nome_conjuges, 'bi-people');
            fieldsHtml += createField('Data da Cerimônia', formatDate(record.data_cerimonia), 'bi-calendar-heart');
            fieldsHtml += createField('Testemunhas', record.testemunhas, 'bi-people-fill');
            fieldsHtml += createField('Celebrante', record.celebrante, 'bi-person-check');
        } 
        else if (record.sacramento === 'crisma' || record.sacramento === 'eucaristia') {
            fieldsHtml += createField('Nome Completo', record.nome_completo, 'bi-person-badge');
            fieldsHtml += createField('Nome dos Pais', record.nome_pais, 'bi-people');
            fieldsHtml += createField('Data de Nascimento', formatDate(record.data_nascimento), 'bi-calendar-event');
            fieldsHtml += createField('Data da Celebração', formatDate(record.data_crisma), 'bi-calendar-check');
        }

        // Common Fields at the end
        fieldsHtml += createField('Mais Detalhes', record.mais_detalhes, 'bi-justify-left');
        fieldsHtml += createField('Finalidade', record.finalidade, 'bi-bullseye');

        document.getElementById('detailFields').innerHTML = fieldsHtml;

        new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }
</script>
@endsection
