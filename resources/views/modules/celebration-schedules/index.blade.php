@extends('layouts.app')

@section('title', 'Celebrations e Horários')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Celebrações e Horários</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Celebrações e Horários</li>
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
            <form id="filterForm" action="{{ route('celebration-schedules.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-6">
                    <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control ps-5 rounded-pill" placeholder="Buscar por comunidade ou dia..." style="height: 45px;">
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-6 text-end d-flex gap-2 justify-content-end">
                     <button type="button" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('celebration-schedules.create') }}';">
                        <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Novo Horário</span>
                    </button>
                </div>
            </form>

            <!-- Bulk Actions -->
            <div id="bulkActions" class="mb-3 d-none">
                <div class="card bg-light border-0 rounded-4">
                    <div class="card-body d-flex align-items-center justify-content-between py-2">
                        <div>
                            <span class="fw-bold text-primary"><span id="selectedCount">0</span> itens selecionados</span>
                        </div>
                        <div>
                            <button type="button" class="btn btn-danger rounded-pill px-3 btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                                <i class="bi bi-trash-fill me-2"></i> Excluir Selecionados
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" style="width: 50px;" class="text-center">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th scope="col">Comunidade</th>
                            <th scope="col">Dia da Semana</th>
                            <th scope="col">Horário</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input record-checkbox" type="checkbox" value="{{ $record->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3 text-primary" style="width: 40px; height: 40px;">
                                        <i class="bi bi-house-heart-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $record->comunidade->ent_name ?? 'Comunidade Removida' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">{{ $record->dia_semana }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($record->horario)->format('H:i') }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('celebration-schedules.edit', $record->id) }}" class="btn btn-light btn-sm rounded-circle text-primary shadow-sm mx-1" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <button type="button" class="btn btn-light btn-sm rounded-circle text-danger shadow-sm mx-1" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $record->id }}" title="Excluir">
                                    <i class="bi bi-trash-fill"></i>
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 shadow rounded-4">
                                            <div class="modal-body p-5 text-center">
                                                <div class="mb-4">
                                                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                        <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                                                    </div>
                                                </div>
                                                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                                                <p class="text-muted mb-4">Você está prestes a excluir este horário. Esta ação não pode ser desfeita.</p>
                                                <form action="{{ route('celebration-schedules.destroy', $record->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold me-2" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, excluir</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Nenhum horário encontrado.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body p-5 text-center">
                <div class="mb-4">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bi bi-trash-fill text-danger fs-1"></i>
                    </div>
                </div>
                <h4 class="fw-bold mb-3">Excluir Selecionados?</h4>
                <p class="text-muted mb-4">Você está prestes a excluir <strong id="bulkDeleteCount">0</strong> itens. Esta ação não pode ser desfeita.</p>
                <form id="bulkDeleteForm" action="{{ route('celebration-schedules.bulk-delete') }}" method="POST" class="d-inline">
                    @csrf
                    <div id="bulkDeleteInputs"></div>
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, excluir tudo</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-submit search on typing
    let timeout = null;
    document.getElementById('search').addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            document.getElementById('filterForm').submit();
        }, 800);
    });

    // Bulk Actions Logic
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.record-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    const bulkDeleteCount = document.getElementById('bulkDeleteCount');
    const bulkDeleteInputs = document.getElementById('bulkDeleteInputs');

    function updateBulkActions() {
        const checked = Array.from(checkboxes).filter(cb => cb.checked);
        selectedCount.textContent = checked.length;
        bulkDeleteCount.textContent = checked.length;
        
        if (checked.length > 0) {
            bulkActions.classList.remove('d-none');
        } else {
            bulkActions.classList.add('d-none');
        }

        // Create hidden inputs for bulk delete form
        bulkDeleteInputs.innerHTML = '';
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = cb.value;
            bulkDeleteInputs.appendChild(input);
        });
    }

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkActions();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
</script>
@endsection
