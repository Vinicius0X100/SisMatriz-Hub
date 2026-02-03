@extends('layouts.app')

@section('title', 'Apuração de Vicentinos')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Apuração de Vicentinos</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Vicentinos</li>
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

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total</h6>
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
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Assistidos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['assistidos'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-person-x fs-3 text-secondary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Não Assistidos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['nao_assistidos'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form id="filterForm" action="{{ route('vicentinos.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-3">
                    <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control ps-5 rounded-pill" placeholder="Buscar por nome..." style="height: 45px;">
                    </div>
                </div>

                <!-- Filtro Comunidade -->
                <div class="col-md-3">
                    <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                    <select name="ent_id" id="ent_id" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todas</option>
                        @foreach($entidades as $entidade)
                            <option value="{{ $entidade->ent_id }}" {{ request('ent_id') == $entidade->ent_id ? 'selected' : '' }}>{{ $entidade->ent_name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro Tipo -->
                <div class="col-md-2">
                    <label for="kind" class="form-label fw-bold text-muted small">Tipo</label>
                    <select name="kind" id="kind" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        <option value="1" {{ request('kind') == '1' ? 'selected' : '' }}>Assistido</option>
                        <option value="0" {{ request('kind') == '0' ? 'selected' : '' }}>Não Assistido</option>
                    </select>
                </div>

                <!-- Filtro Mês -->
                <div class="col-md-2">
                    <label for="month" class="form-label fw-bold text-muted small">Mês</label>
                    <select name="month" id="month" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Botões -->
                <div class="col-md-2 text-end">
                    <a href="{{ route('vicentinos.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center w-100" style="height: 45px;" title="Nova Apuração">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-lg-inline ms-2">Nova Apuração</span>
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
                            <th scope="col">Endereço</th>
                            <th scope="col">Comunidade</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Mês</th>
                            <th scope="col">Enviado Por</th>
                            <th scope="col">Observação</th>
                            <th scope="col" class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input record-checkbox" type="checkbox" value="{{ $record->w_id }}">
                                </div>
                            </td>
                            <td class="ps-4 fw-bold text-dark">{{ $record->name }}</td>
                            <td class="small text-muted">
                                {{ $record->address }}
                                @if($record->address_number) , {{ $record->address_number }} @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $record->entidade->ent_name ?? 'N/A' }}</span></td>
                            <td>
                                @if($record->kind == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Assistido</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Não Assistido</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $months = [
                                        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                    ];
                                @endphp
                                <span class="badge bg-info bg-opacity-10 text-info border border-info rounded-pill">
                                    {{ $months[$record->month_entire] ?? $record->month_entire }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $record->sendby }}</td>
                            <td class="small text-muted text-truncate" style="max-width: 150px;" title="{{ $record->description }}">{{ $record->description ?? '-' }}</td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('vicentinos.edit', $record->w_id) }}" class="btn btn-sm btn-light text-primary border-0 rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-danger border-0 rounded-circle delete-btn" data-id="{{ $record->w_id }}" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Excluir">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                    <i class="bi bi-inbox fs-3 text-secondary"></i>
                                </div>
                                <p class="mb-0">Nenhum registro encontrado.</p>
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
            <p class="text-muted mb-0">Esta ação é <strong>irreversível</strong> e removerá permanentemente o registro do sistema.</p>
        </div>
        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
        </form>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteBtn">Sim, Excluir</button>
      </div>
    </div>
  </div>
</div>

<script type="module">
    // Automatic Filtering Logic
    const filterForm = document.getElementById('filterForm');
    const inputs = filterForm.querySelectorAll('select, input');

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    inputs.forEach(input => {
        if (input.tagName === 'SELECT') {
            input.addEventListener('change', () => filterForm.submit());
        } else if (input.tagName === 'INPUT' && input.type === 'text') {
            // Focus restoration trick not needed for full reload, but nice to have if we used AJAX.
            // For full reload, the focus is lost anyway.
            input.addEventListener('input', debounce(() => filterForm.submit(), 800));
        }
    });

    // Delete Modal Logic
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            deleteForm.action = `/vicentinos/${id}`;
            deleteModal.show();
        });
    });

    confirmDeleteBtn.addEventListener('click', () => deleteForm.submit());

    // Bulk Delete Logic
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.record-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    function updateBulkButton() {
        const checked = document.querySelectorAll('.record-checkbox:checked');
        bulkDeleteBtn.disabled = checked.length === 0;
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkButton();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButton);
    });

    bulkDeleteBtn.addEventListener('click', function() {
        if (!confirm('Tem certeza que deseja excluir os registros selecionados?')) return;

        const selectedIds = Array.from(document.querySelectorAll('.record-checkbox:checked')).map(cb => cb.value);
        
        fetch('{{ route("vicentinos.bulk-delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            location.reload();
        })
        .catch(error => alert('Erro ao excluir registros.'));
    });
</script>
@endsection
