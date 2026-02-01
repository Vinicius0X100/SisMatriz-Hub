@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Ofertas e Dízimos</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ofertas e Dízimos</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas / Filtros -->
            <form action="{{ route('ofertas.index') }}" method="GET" class="row g-3 mb-4 align-items-end" id="filterForm">
                
                <!-- Filtro: Comunidade -->
                <div class="col-md-3">
                    <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                    <select name="ent_id" id="ent_id" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                        <option value="">Todas as Comunidades</option>
                        @foreach($entidades as $entidade)
                            <option value="{{ $entidade->ent_id }}" {{ request('ent_id') == $entidade->ent_id ? 'selected' : '' }}>
                                {{ $entidade->ent_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro: Tipo de Lançamento -->
                <div class="col-md-3">
                    <label for="kind" class="form-label fw-bold text-muted small">Tipo de Lançamento</label>
                    <select name="kind" id="kind" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                        <option value="">Todos os Tipos</option>
                        <option value="1" {{ request('kind') == '1' ? 'selected' : '' }}>Dízimo</option>
                        <option value="2" {{ request('kind') == '2' ? 'selected' : '' }}>Oferta</option>
                        <option value="3" {{ request('kind') == '3' ? 'selected' : '' }}>Moedas</option>
                        <option value="4" {{ request('kind') == '4' ? 'selected' : '' }}>Doação em Cofre</option>
                        <option value="5" {{ request('kind') == '5' ? 'selected' : '' }}>Bazares</option>
                        <option value="6" {{ request('kind') == '6' ? 'selected' : '' }}>Vendas (Esporádicos)</option>
                    </select>
                </div>

                <!-- Filtro: Período (De - Até) -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">Período</label>
                    <div class="input-group">
                        <input type="date" name="data_inicio" class="form-control rounded-pill-start" value="{{ request('data_inicio') }}" placeholder="De" style="height: 45px;" onchange="this.form.submit()">
                        <span class="input-group-text bg-light border-start-0 border-end-0">a</span>
                        <input type="date" name="data_fim" class="form-control rounded-pill-end" value="{{ request('data_fim') }}" placeholder="Até" style="height: 45px;" onchange="this.form.submit()">
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-3 text-end d-flex gap-2 justify-content-end">
                    <a href="{{ route('ofertas.index') }}" class="btn btn-light border rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" title="Limpar Filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <button type="button" class="btn btn-danger rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" data-bs-toggle="modal" data-bs-target="#exportPdfModal" title="Exportar PDF">
                        <i class="bi bi-file-earmark-pdf fs-5"></i>
                    </button>
                    
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                            Ações
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                            <li><a class="dropdown-item text-danger" href="#" id="bulkDeleteBtn"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                        </ul>
                    </div>

                    <a href="{{ route('ofertas.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;">
                        <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Novo</span>
                    </a>
                </div>
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
                            <th scope="col" class="text-muted small fw-bold text-uppercase">ID</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Data</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Horário</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Valor Arrecadado</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Celebração</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Tipo de Lançamento</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Comunidade</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Observações</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Registrado Em</th>
                            <th scope="col" class="text-end text-muted small fw-bold text-uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ofertas as $oferta)
                            <tr>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input bulk-checkbox" type="checkbox" value="{{ $oferta->id }}">
                                    </div>
                                </td>
                                <td>#{{ $oferta->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($oferta->data)->format('d/m/Y') }}</td>
                                <td>{{ $oferta->horario ? \Carbon\Carbon::parse($oferta->horario)->format('H:i') : '-' }}</td>
                                <td class="fw-bold text-success">R$ {{ number_format($oferta->valor_total, 2, ',', '.') }}</td>
                                <td>{{ $oferta->tipo ?? '-' }}</td>
                                <td>
                                    @switch($oferta->kind)
                                        @case(1) <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Dízimo</span> @break
                                        @case(2) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Oferta</span> @break
                                        @case(3) <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Moedas</span> @break
                                        @case(4) <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">Doação em Cofre</span> @break
                                        @case(5) <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Bazares</span> @break
                                        @case(6) <span class="badge bg-dark bg-opacity-10 text-dark rounded-pill px-3">Vendas</span> @break
                                        @default <span class="badge bg-light text-muted border rounded-pill px-3">Outro</span>
                                    @endswitch
                                </td>
                                <td>{{ $oferta->entidade->ent_name ?? 'N/A' }}</td>
                                <td class="text-truncate" style="max-width: 150px;" title="{{ $oferta->observacoes }}">{{ $oferta->observacoes ?? '-' }}</td>
                                <td class="small text-muted">{{ $oferta->criado_em ? \Carbon\Carbon::parse($oferta->criado_em)->format('d/m/Y H:i') : '-' }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('ofertas.edit', $oferta->id) }}" class="btn btn-sm btn-light border rounded-circle" title="Editar" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light border text-danger rounded-circle btn-delete-item" 
                                                data-action="{{ route('ofertas.destroy', $oferta->id) }}" 
                                                title="Excluir" 
                                                style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-inbox fs-1 text-muted opacity-25"></i></div>
                                    <div>Nenhum lançamento encontrado.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                <div class="text-muted small">
                    Mostrando <span class="fw-bold">{{ $ofertas->firstItem() ?? 0 }}</span> a <span class="fw-bold">{{ $ofertas->lastItem() ?? 0 }}</span> de <span class="fw-bold">{{ $ofertas->total() }}</span> registros
                </div>
                <div>
                    {{ $ofertas->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exportar PDF -->
<div class="modal fade" id="exportPdfModal" tabindex="-1" aria-labelledby="exportPdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold" id="exportPdfModalLabel">Exportar Relatório</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ofertas.export-pdf') }}" method="POST" target="_blank">
                @csrf
                <div class="modal-body pt-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="export_ent_id" class="form-label fw-bold text-muted small text-uppercase">Comunidade</label>
                            <select name="ent_id" id="export_ent_id" class="form-select form-select-lg rounded-3 fs-6">
                                <option value="all">Todas as Comunidades</option>
                                @foreach($entidades as $entidade)
                                    <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="export_kind" class="form-label fw-bold text-muted small text-uppercase">Tipo de Lançamento</label>
                            <select name="kind" id="export_kind" class="form-select form-select-lg rounded-3 fs-6">
                                <option value="all">Todos os Tipos</option>
                                <option value="1">Dízimo</option>
                                <option value="2">Oferta</option>
                                <option value="3">Moedas</option>
                                <option value="4">Doação em Cofre</option>
                                <option value="5">Bazares</option>
                                <option value="6">Vendas (Esporádicos)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="bg-light rounded-3 p-3 mt-2">
                                <label for="periodo_predefinido" class="form-label fw-bold text-muted small text-uppercase mb-2">Período do Relatório</label>
                                <select id="periodo_predefinido" class="form-select rounded-3 mb-3" onchange="updateDates(this.value)">
                                    <option value="custom">Personalizado</option>
                                    <option value="month">Mês Atual</option>
                                    <option value="last_month">Mês Passado</option>
                                    <option value="trimester">Último Trimestre</option>
                                    <option value="semester">Último Semestre</option>
                                    <option value="year">Este Ano</option>
                                </select>
                                
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1">Início</label>
                                        <input type="date" name="data_inicio" id="export_data_inicio" class="form-control rounded-3" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-1">Fim</label>
                                        <input type="date" name="data_fim" id="export_data_fim" class="form-control rounded-3" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Gerar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmação de Exclusão em Massa -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 d-inline-flex mb-4">
                    <i class="bi bi-exclamation-triangle text-danger display-5"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Você está prestes a excluir <span id="deleteCount" class="fw-bold text-dark"></span> registros selecionados.<br>Esta ação <b>não poderá</b> ser desfeita.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmBulkDeleteBtn">
                        Sim, excluir registros
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação de Exclusão Individual -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 d-inline-flex mb-4">
                    <i class="bi bi-exclamation-triangle text-danger display-5"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Você está prestes a excluir este registro.<br>Esta ação <b>não poderá</b> ser desfeita.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4">
                            Sim, excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateDates(period) {
        const today = new Date();
        let startDate = new Date();
        let endDate = new Date();

        switch(period) {
            case 'month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'last_month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'trimester':
                startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
                break;
            case 'semester':
                startDate = new Date(today.getFullYear(), today.getMonth() - 6, 1);
                break;
            case 'year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
                break;
            case 'custom':
                document.getElementById('export_data_inicio').value = '';
                document.getElementById('export_data_fim').value = '';
                return;
        }

        if (period !== 'custom') {
            // Adjust for timezone offset to avoid previous day issue
            const offset = startDate.getTimezoneOffset();
            startDate = new Date(startDate.getTime() - (offset*60*1000));
            endDate = new Date(endDate.getTime() - (offset*60*1000));
            
            document.getElementById('export_data_inicio').value = startDate.toISOString().split('T')[0];
            document.getElementById('export_data_fim').value = endDate.toISOString().split('T')[0];
        }
    }

    // Bulk & Single Actions Script
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.bulk-checkbox');
        const bulkActionsBtn = document.getElementById('bulkActions');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
        const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');
        
        // Single Delete
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteForm = document.getElementById('deleteForm');
        const deleteButtons = document.querySelectorAll('.btn-delete-item');

        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-action');
                deleteForm.action = action;
                deleteModal.show();
            });
        });

        function updateBulkActions() {
            const checkedCount = document.querySelectorAll('.bulk-checkbox:checked').length;
            bulkActionsBtn.disabled = checkedCount === 0;
            if (checkedCount > 0) {
                bulkActionsBtn.innerHTML = `Ações (${checkedCount})`;
            } else {
                bulkActionsBtn.innerHTML = 'Ações';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateBulkActions();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const checkedCount = document.querySelectorAll('.bulk-checkbox:checked').length;
                document.getElementById('deleteCount').textContent = checkedCount;
                bulkDeleteModal.show();
            });
        }

        if (confirmBulkDeleteBtn) {
            confirmBulkDeleteBtn.addEventListener('click', function() {
                const selectedIds = Array.from(document.querySelectorAll('.bulk-checkbox:checked')).map(cb => cb.value);
                
                // Create a form to submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route("ofertas.bulk-delete") }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
            });
        }
    });
</script>
@endsection
