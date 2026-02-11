@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Refatorado -->
    <div class="d-flex justify-content-between align-items-end mt-4 mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">Gestão de Batismos</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Batismos</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('batismos.create') }}" class="btn btn-primary rounded-pill fw-bold shadow-sm px-4">
                <i class="bi bi-plus-lg me-1"></i> Novo Batismo
            </a>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-droplet fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Registros</h6>
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
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Batizados</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['batizados'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-dash-circle fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Não Batizados</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['nao_batizados'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container Principal -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas Refatorada -->
            <div class="row g-3 mb-4 align-items-center">
                <!-- Pesquisa -->
                <div class="col-md-8">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill bg-light border-0" placeholder="Pesquisar por nome..." style="height: 45px;">
                    </div>
                </div>
                
                <!-- Filtro: Status -->
                <div class="col-md-4">
                    <select id="statusFilter" class="form-select rounded-pill bg-light border-0" style="height: 45px;">
                        <option value="">Todos os Status</option>
                        <option value="1">Batizado</option>
                        <option value="0">Não Batizado</option>
                    </select>
                </div>
            </div>

            <!-- Container da Tabela (Carregado via AJAX) -->
            <div id="tableContainer">
                @include('modules.batismos.partials.list')
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-sm { width: 40px; height: 40px; object-fit: cover; }
    .table th { font-weight: 600; border-bottom-width: 1px !important; }
    .table td { font-size: 0.95rem; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const tableContainer = document.getElementById('tableContainer');
        let searchDebounce;

        function fetchResults(url = null) {
            const query = searchInput.value;
            const status = statusFilter.value;
            
            // Build URL
            let fetchUrl = url || '{{ route("batismos.index") }}';
            const separator = fetchUrl.includes('?') ? '&' : '?';
            
            // If it's a pagination link (url provided), we use it directly but append current filters if needed
            // But usually pagination links generated by Laravel already contain query params if we use appends()
            // Here we are doing simple AJAX, so let's reconstruct params for base URL
            if (!url) {
                const params = new URLSearchParams();
                if (query) params.append('search', query);
                if (status) params.append('status', status);
                fetchUrl = `${fetchUrl}?${params.toString()}`;
            }

            // Show loading state if needed (optional)
            tableContainer.style.opacity = '0.5';

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                tableContainer.innerHTML = html;
                tableContainer.style.opacity = '1';
                
                // Re-attach pagination listeners
                attachPaginationListeners();
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.style.opacity = '1';
            });
        }

        function attachPaginationListeners() {
            const links = tableContainer.querySelectorAll('.pagination a');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchResults(this.href);
                });
            });
        }

        // Event Listeners
        searchInput.addEventListener('input', function() {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                fetchResults();
            }, 500); // 500ms delay
        });

        statusFilter.addEventListener('change', function() {
            fetchResults();
        });

        // Initial attach
        attachPaginationListeners();
    });
</script>
@endsection