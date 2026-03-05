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
            <form action="{{ route('vicentinos-apuracoes.index') }}" method="GET" class="d-flex flex-column flex-md-row justify-content-between align-items-end gap-3 mb-4" id="filterForm">
                <div class="d-flex flex-column flex-md-row gap-3 w-100">
                    <div class="flex-grow-1">
                        <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" class="form-control ps-5 rounded-pill bg-light border-0" id="search" placeholder="Nome..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div style="min-width: 150px;">
                        <label for="month" class="form-label fw-bold text-muted small">Mês</label>
                        <select name="month" class="form-select rounded-pill bg-light border-0" id="month" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @php
                                $meses = [
                                    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                ];
                            @endphp
                            @foreach($meses as $num => $nome)
                                <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="min-width: 150px;">
                        <label for="kind" class="form-label fw-bold text-muted small">Tipo</label>
                        <select name="kind" class="form-select rounded-pill bg-light border-0" id="kind" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="1" {{ request('kind') == '1' ? 'selected' : '' }}>Assistido</option>
                            <option value="0" {{ request('kind') == '0' ? 'selected' : '' }}>Não Assistido</option>
                        </select>
                    </div>
                    <div style="min-width: 200px;">
                        <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                        <select name="ent_id" class="form-select rounded-pill bg-light border-0" id="ent_id" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}" {{ request('ent_id') == $entidade->ent_id ? 'selected' : '' }}>{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="bulkDeleteBtn" class="btn btn-danger rounded-pill px-4 fw-bold text-nowrap d-none">
                        <i class="bi bi-trash me-2"></i> Excluir Selecionados (<span id="selectedCount">0</span>)
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
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Mês</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Tipo</th>
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
    });
</script>
@endsection
@endsection
