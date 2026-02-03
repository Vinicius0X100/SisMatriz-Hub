@extends('layouts.app')

@section('title', 'Categorias de Doação')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Categorias de Estoque e Inventário</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Categorias</li>
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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Erro!</strong> {{ session('error') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form id="filterForm" action="{{ route('categorias_doacao.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-8">
                    <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control ps-5 rounded-pill" placeholder="Buscar por nome..." style="height: 45px;">
                    </div>
                </div>

                <!-- Botões -->
                <div class="col-md-4 text-end">
                    <a href="{{ route('categorias_doacao.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center w-100" style="height: 45px;" title="Nova Categoria">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-lg-inline ms-2">Nova Categoria</span>
                    </a>
                </div>
            </form>

            <div class="mb-3 d-flex justify-content-end">
                <button class="btn btn-outline-danger rounded-pill btn-sm" id="bulkDeleteBtn" disabled>
                    <i class="bi bi-trash me-1"></i> Excluir Selecionados
                </button>
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
                            <th scope="col" class="ps-4">Nome</th>
                            <th scope="col" class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input record-checkbox" type="checkbox" value="{{ $record->id }}">
                                </div>
                            </td>
                            <td class="ps-4 fw-bold text-dark">{{ $record->name }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('categorias_doacao.edit', $record->id) }}" class="btn btn-sm btn-light text-primary border-0 rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle delete-btn" data-id="{{ $record->id }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Excluir">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-inbox fs-3 text-secondary"></i>
                                </div>
                                <p class="mb-0">Nenhuma categoria encontrada.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-end mt-4">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-exclamation-triangle-fill fs-1 text-danger"></i>
                </div>
                <h4 class="fw-bold mb-2">Tem certeza?</h4>
                <p class="text-muted mb-0">Esta ação não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exclusão em Massa -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                    <i class="bi bi-trash-fill fs-1 text-danger"></i>
                </div>
                <h4 class="fw-bold mb-2">Excluir Selecionados?</h4>
                <p class="text-muted mb-0">Esta ação excluirá todos os registros selecionados e não poderá ser desfeita.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="bulkDeleteForm" action="{{ route('categorias_doacao.bulk-delete') }}" method="POST" class="d-inline">
                    @csrf
                    <div id="bulkDeleteInputs"></div>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, excluir tudo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Search Auto-submit
    let timeoutId;
    const searchInput = document.getElementById('search');
    const filterForm = document.getElementById('filterForm');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => {
                filterForm.submit();
            }, 500);
        });
        
        // Focus on search input if it has value
        if (searchInput.value) {
            searchInput.focus();
            // Move cursor to end
            const val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }
    }

    // Delete Modal
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const form = document.getElementById('deleteForm');
            form.action = `/categorias_doacao/${id}`;
        });
    }

    // Bulk Delete
    const selectAll = document.getElementById('selectAll');
    const recordCheckboxes = document.querySelectorAll('.record-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));
    const bulkDeleteInputs = document.getElementById('bulkDeleteInputs');

    function updateBulkDeleteBtn() {
        const checkedCount = document.querySelectorAll('.record-checkbox:checked').length;
        bulkDeleteBtn.disabled = checkedCount === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            recordCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteBtn();
        });
    }

    recordCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkDeleteBtn);
    });

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            bulkDeleteInputs.innerHTML = '';
            document.querySelectorAll('.record-checkbox:checked').forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = checkbox.value;
                bulkDeleteInputs.appendChild(input);
            });
            bulkDeleteModal.show();
        });
    }
</script>
@endsection
