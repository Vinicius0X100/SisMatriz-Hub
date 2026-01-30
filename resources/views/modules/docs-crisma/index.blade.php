@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Documentação de Crisma</h2>
            <p class="text-muted small mb-0">Gerencie a entrega de documentos dos crismandos.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Documentação de Crisma</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row mb-4 g-3">
                <div class="col-md-5">
                    <div class="position-relative">
                        <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="search-input" class="form-control rounded-pill bg-light border-0 ps-5 py-2" placeholder="Pesquisar por nome..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select id="turma-filter" class="form-select rounded-pill bg-light border-0 py-2">
                        <option value="">Todas as Turmas</option>
                        @foreach($turmas as $turma)
                            <option value="{{ $turma->id }}" {{ request('turma_id') == $turma->id ? 'selected' : '' }}>{{ $turma->turma }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <select id="status-filter" class="form-select rounded-pill bg-light border-0 py-2">
                        <option value="">Todos os Status</option>
                        <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Documentação Pendente</option>
                        <option value="obrigatoria_entregue" {{ request('status') == 'obrigatoria_entregue' ? 'selected' : '' }}>Documentação Obrigatória Entregue</option>
                        <option value="entregue" {{ request('status') == 'entregue' ? 'selected' : '' }}>Documentação Entregue</option>
                    </select>
                </div>
            </div>

            <div id="table-content">
                @include('modules.docs-crisma.partials.list')
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimeout;
    const searchInput = document.getElementById('search-input');
    const turmaFilter = document.getElementById('turma-filter');
    const statusFilter = document.getElementById('status-filter');

    function fetchResults() {
        const query = searchInput.value;
        const turmaId = turmaFilter.value;
        const status = statusFilter.value;

        fetch(`{{ route('docs-crisma.index') }}?search=${query}&turma_id=${turmaId}&status=${status}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('table-content').innerHTML = html;
        });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchResults, 500);
    });

    turmaFilter.addEventListener('change', fetchResults);
    statusFilter.addEventListener('change', fetchResults);
</script>
@endsection
