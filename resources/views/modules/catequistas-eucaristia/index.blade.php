@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Catequistas de Primeira Eucaristia</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Catequistas de Primeira Eucaristia</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-4">
                    <div>
                        <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Nome ou Comunidade..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8 text-end d-flex gap-2 justify-content-end">
                     <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('catequistas-eucaristia.create') }}'">
                        <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Novo Catequista</span>
                    </button>
                </div>
            </div>

            <!-- Tabela e Paginação (AJAX) -->
            <div id="catequistas-list">
                @include('modules.catequistas-eucaristia.partials.list')
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body p-5 text-center">
                <div class="mb-4 text-danger bg-danger bg-opacity-10 p-3 rounded-circle d-inline-flex">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita. O catequista será removido permanentemente.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Sim, excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        const form = document.getElementById('deleteForm');
        form.action = `/catequistas-eucaristia/${id}`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // Dynamic Search
    let typingTimer;
    const doneTypingInterval = 500; // 0.5s
    const searchInput = document.getElementById('searchInput');
    const listContainer = document.getElementById('catequistas-list');

    searchInput.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(performSearch, doneTypingInterval);
    });

    function performSearch() {
        const query = searchInput.value;
        const url = new URL(window.location.href);
        if(query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        
        // Reset page to 1 on new search
        url.searchParams.delete('page');

        window.history.pushState({}, '', url);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            listContainer.innerHTML = html;
        })
        .catch(error => console.error('Error:', error));
    }

    // Handle pagination clicks via AJAX
    listContainer.addEventListener('click', function(e) {
        if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
            e.preventDefault();
            const url = e.target.href;
            window.history.pushState({}, '', url);
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                listContainer.innerHTML = html;
                window.scrollTo(0, 0);
            });
        }
    });
</script>
@endsection
