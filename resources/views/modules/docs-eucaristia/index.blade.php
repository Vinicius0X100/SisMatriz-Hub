@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Documentação de Primeira Eucaristia</h2>
            <p class="text-muted small mb-0">Gerencie a entrega de documentos dos catecandos.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Documentação de Eucaristia</li>
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
            <div class="d-flex justify-content-between align-items-center mb-4 gap-3">
                <div class="position-relative w-100">
                    <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="search-input" class="form-control rounded-pill bg-light border-0 ps-5 py-2" placeholder="Pesquisar por nome...">
                </div>
                <div class="w-50">
                    <select id="turma-filter" class="form-select rounded-pill bg-light border-0 py-2">
                        <option value="">Todas as Turmas</option>
                        @foreach($turmas as $turma)
                            <option value="{{ $turma->id }}">{{ $turma->turma }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="table-content">
                @include('modules.docs-eucaristia.partials.list')
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimeout;
    const searchInput = document.getElementById('search-input');
    const turmaFilter = document.getElementById('turma-filter');

    function fetchResults() {
        const query = searchInput.value;
        const turmaId = turmaFilter.value;

        fetch(`{{ route('docs-eucaristia.index') }}?search=${query}&turma_id=${turmaId}`, {
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
</script>
@endsection
