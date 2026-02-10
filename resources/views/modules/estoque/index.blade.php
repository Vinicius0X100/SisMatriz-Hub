@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Estoque</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Estoque</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-box-seam fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Itens</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form action="{{ route('estoque.index') }}" method="GET">
                <div class="row g-3 mb-4 align-items-end">
                    <!-- Pesquisa -->
                    <div class="col-md-3">
                        <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="search" name="search" class="form-control ps-5 rounded-pill" placeholder="Descrição..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </div>
                    
                    <!-- Filtro: Categoria -->
                    <div class="col-md-2">
                        <label for="category" class="form-label fw-bold text-muted small">Categoria</label>
                        <select id="category" name="category" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Entidade -->
                    <div class="col-md-2">
                        <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                        <select id="ent_id" name="ent_id" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($entidades as $ent)
                                <option value="{{ $ent->ent_id }}" {{ request('ent_id') == $ent->ent_id ? 'selected' : '' }}>{{ $ent->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Sala -->
                    <div class="col-md-2">
                        <label for="sala_id" class="form-label fw-bold text-muted small">Sala/Espaço</label>
                        <select id="sala_id" name="sala_id" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($locais as $local)
                                <option value="{{ $local->id }}" {{ request('sala_id') == $local->id ? 'selected' : '' }}>{{ $local->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="col-md-3 text-end d-flex gap-2 justify-content-end">
                         <button type="button" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('estoque.create') }}'">
                            <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Novo Item</span>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-light border rounded-pill dropdown-toggle d-flex align-items-center justify-content-center" style="height: 45px;" type="button" id="bulkActions" data-bs-toggle="dropdown" aria-expanded="false">
                                Ações
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="bulkActions">
                                <li><a class="dropdown-item" href="#" id="bulkPrintBtn"><i class="bi bi-file-earmark-pdf me-2"></i> Imprimir Relatório</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger disabled" href="#" id="bulkDeleteBtn"><i class="bi bi-trash me-2"></i> Excluir Selecionados</a></li>
                            </ul>
                        </div>
                    </div>
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
                            <th scope="col">Descrição</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Qtd.</th>
                            <th scope="col">Categoria</th>
                            <th scope="col">Comunidade</th>
                            <th scope="col">Última Atualização</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input row-checkbox" type="checkbox" value="{{ $item->s_id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($item->images->count() > 0)
                                            <img src="{{ asset('storage/uploads/estoque/' . $item->images->first()->filename) }}" class="rounded shadow-sm object-fit-cover" width="40" height="40" alt="Item">
                                        @else
                                            <div class="rounded bg-light d-flex align-items-center justify-content-center text-secondary" style="width: 40px; height: 40px;">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->type }}</span></td>
                                <td><span class="fw-bold">{{ $item->qntd_destributed }}</span></td>
                                <td>{{ $item->categoria->name ?? '-' }}</td>
                                <td>{{ $item->entidade->ent_name ?? '-' }}</td>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($item->last_update)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <a href="{{ route('estoque.show', $item->s_id) }}" class="btn btn-sm btn-light border rounded-circle" title="Detalhes">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('estoque.edit', $item->s_id) }}" class="btn btn-sm btn-light border rounded-circle" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('estoque.destroy', $item->s_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border rounded-circle text-danger" title="Excluir">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-inbox fs-1"></i></div>
                                    <div>Nenhum item encontrado no estoque.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                <div class="text-muted small">
                    Mostrando {{ $items->firstItem() ?? 0 }} a {{ $items->lastItem() ?? 0 }} de {{ $items->total() }} registros
                </div>
                <div>
                    {{ $items->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form para Bulk Delete -->
<form id="bulkDeleteForm" action="{{ route('estoque.bulk-destroy') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<!-- Modal de Filtros para PDF -->
<div class="modal fade" id="pdfFilterModal" tabindex="-1" aria-labelledby="pdfFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="pdfFilterModalLabel">Gerar Relatório PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="pdfFilterForm" action="{{ route('estoque.pdf') }}" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="ids" id="pdfIds">

                    <div class="mb-4">
                        <p class="text-muted small mb-3">Escolha as opções para gerar o relatório de estoque.</p>
                        
                        <div class="form-check mb-3" id="selectedItemsOptionContainer" style="display: none;">
                            <input class="form-check-input" type="radio" name="filter_type" id="filterSelected" value="selected" checked>
                            <label class="form-check-label fw-bold" for="filterSelected">
                                Apenas itens selecionados <span id="selectedCountBadge" class="badge bg-primary rounded-pill ms-2">0</span>
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="filter_type" id="filterAll" value="all">
                            <label class="form-check-label fw-bold" for="filterAll">
                                Filtrar base de dados
                            </label>
                        </div>
                    </div>

                    <div id="filterOptions" class="ps-4 border-start border-3 ms-1" style="display: none;">
                        <div class="mb-3">
                            <label for="pdf_category" class="form-label small text-muted fw-bold">Categoria</label>
                            <select class="form-select" id="pdf_category" name="category">
                                <option value="">Todas</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="pdf_ent_id" class="form-label small text-muted fw-bold">Comunidade</label>
                            <select class="form-select" id="pdf_ent_id" name="ent_id">
                                <option value="">Todas</option>
                                @foreach($entidades as $ent)
                                    <option value="{{ $ent->ent_id }}">{{ $ent->ent_name }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="mb-3">
                            <label for="pdf_sala_id" class="form-label small text-muted fw-bold">Sala</label>
                            <select class="form-select" id="pdf_sala_id" name="sala_id">
                                <option value="">Todas</option>
                                @foreach($locais as $local)
                                    <option value="{{ $local->id }}">{{ $local->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Gerar Relatório
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionsBtn = document.getElementById('bulkActions');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const bulkDeleteIds = document.getElementById('bulkDeleteIds');
        const bulkPrintBtn = document.getElementById('bulkPrintBtn');
        
        // Modal elements
        const pdfFilterModal = new bootstrap.Modal(document.getElementById('pdfFilterModal'));
        const filterSelected = document.getElementById('filterSelected');
        const filterAll = document.getElementById('filterAll');
        const filterOptions = document.getElementById('filterOptions');
        const selectedItemsOptionContainer = document.getElementById('selectedItemsOptionContainer');
        const selectedCountBadge = document.getElementById('selectedCountBadge');
        const pdfIds = document.getElementById('pdfIds');
        const pdfFilterForm = document.getElementById('pdfFilterForm');

        function updateBulkActions() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            // bulkActionsBtn.disabled = false; // Always enabled now
            
            if (checkedCount > 0) {
                bulkDeleteBtn.classList.remove('disabled');
            } else {
                bulkDeleteBtn.classList.add('disabled');
            }
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        bulkDeleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.classList.contains('disabled')) return;
            
            if (confirm('Tem certeza que deseja excluir os itens selecionados?')) {
                const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                bulkDeleteIds.value = ids.join(',');
                bulkDeleteForm.submit();
            }
        });

        // PDF Modal Logic
        bulkPrintBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const checkedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
            const count = checkedCheckboxes.length;

            if (count > 0) {
                selectedItemsOptionContainer.style.display = 'block';
                selectedCountBadge.textContent = count;
                filterSelected.checked = true;
                filterOptions.style.display = 'none';
                
                // Populate IDs
                const ids = Array.from(checkedCheckboxes).map(cb => cb.value);
                pdfIds.value = ids.join(',');
                
                // Disable filter selects when specific items are chosen
                toggleFilterSelects(false);
            } else {
                selectedItemsOptionContainer.style.display = 'none';
                filterAll.checked = true;
                filterOptions.style.display = 'block';
                pdfIds.value = '';
                toggleFilterSelects(true);
            }

            pdfFilterModal.show();
        });

        // Toggle filter options visibility
        filterAll.addEventListener('change', function() {
            if (this.checked) {
                filterOptions.style.display = 'block';
                pdfIds.value = ''; // Clear IDs so controller uses filters
                toggleFilterSelects(true);
            }
        });

        filterSelected.addEventListener('change', function() {
            if (this.checked) {
                filterOptions.style.display = 'none';
                // Repopulate IDs
                const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                pdfIds.value = ids.join(',');
                toggleFilterSelects(false);
            }
        });
        
        function toggleFilterSelects(enabled) {
            const selects = filterOptions.querySelectorAll('select');
            selects.forEach(select => {
                if (!enabled) {
                    // select.disabled = true; // Don't disable, just ignore in backend if IDs present
                    select.value = ""; // Reset value
                } else {
                    // select.disabled = false;
                }
            });
        }
    });
</script>
@endsection
@endsection
