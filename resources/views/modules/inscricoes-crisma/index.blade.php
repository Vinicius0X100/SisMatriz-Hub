@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Inscrições de Crisma</h2>
            <p class="text-muted small mb-0">Gerencie as inscrições recebidas para a Crisma.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inscrições de Crisma</li>
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
            </div>

            <div id="table-content">
                @include('modules.inscricoes-crisma.partials.list')
            </div>
        </div>
    </div>
</div>

<script>
    let searchTimeout;
    const searchInput = document.getElementById('search-input');

    function fetchResults() {
        const query = searchInput.value;

        fetch(`{{ route('inscricoes-crisma.index') }}?search=${query}`, {
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
</script>
@endsection
