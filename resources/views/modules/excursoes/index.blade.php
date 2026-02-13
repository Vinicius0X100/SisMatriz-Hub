@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Excursões</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Excursões</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-bus-front fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Excursões</h6>
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
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Ativas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-flag fs-3 text-secondary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Finalizadas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['finished'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form action="{{ route('excursoes.index') }}" method="GET" id="filterForm">
                <div class="row g-3 mb-4 align-items-end">
                    <!-- Pesquisa -->
                    <div class="col-md-4">
                        <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Destino..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </div>

                    <!-- Filtro: Tipo -->
                    <div class="col-md-3">
                        <label for="tipoFilter" class="form-label fw-bold text-muted small">Tipo</label>
                        <select name="tipo" id="tipoFilter" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todos os Tipos</option>
                            @foreach($types as $tipo)
                                <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>{{ ucfirst($tipo) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Status -->
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label fw-bold text-muted small">Status</label>
                        <select name="status" id="statusFilter" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todos os Status</option>
                            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ativa</option>
                            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Finalizada</option>
                        </select>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                         <a href="{{ route('excursoes.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2 w-100" style="height: 45px;">
                            <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Nova Excursão</span>
                        </a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="py-3 ps-3 rounded-start-pill">Destino</th>
                            <th scope="col" class="py-3">Tipo</th>
                            <th scope="col" class="py-3">Status</th>
                            <th scope="col" class="py-3">Criado em</th>
                            <th scope="col" class="py-3 text-end rounded-end-pill pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($excursoes as $excursao)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">{{ $excursao->destino }}</td>
                            <td><span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">{{ ucfirst($excursao->tipo) }}</span></td>
                            <td>
                                @if($excursao->finalizada)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Finalizada</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativa</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $excursao->created_at->format('d/m/Y') }}</td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('excursoes.show', $excursao) }}" class="btn btn-sm btn-outline-info rounded-pill" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('excursoes.edit', $excursao) }}" class="btn btn-sm btn-outline-primary rounded-pill" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" title="Excluir" onclick="confirmDeleteExcursao('{{ route('excursoes.destroy', $excursao) }}', '{{ addslashes($excursao->destino) }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-bus-front display-4 opacity-25 mb-3"></i>
                                <p class="mb-0">Nenhuma excursão encontrada.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $excursoes->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal fade" id="deleteExcursaoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Excluir Excursão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block mb-3 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Tem certeza?</h4>
                <p class="text-muted mb-0">
                    Você está prestes a excluir a excursão <span id="deleteExcursaoDestino" class="fw-bold text-dark"></span>.
                    <br>Esta ação removerá também todos os ônibus e passagens associadas.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteExcursaoForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDeleteExcursao(url, destino) {
        const form = document.getElementById('deleteExcursaoForm');
        const destinoSpan = document.getElementById('deleteExcursaoDestino');
        
        form.action = url;
        destinoSpan.textContent = destino;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteExcursaoModal'));
        modal.show();
    }
</script>
@endsection
