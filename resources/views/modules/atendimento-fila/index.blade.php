@extends('layouts.app')

@section('title', 'Fila de Atendimento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Fila de Atendimento</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fila de Atendimento</li>
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
                            <input type="text" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Data da fila..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </div>
                </div>

                <div class="col-md-8 text-end d-flex gap-2 justify-content-end">
                    <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('atendimento-fila.create') }}'">
                        <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Nova Fila</span>
                    </button>
                </div>
            </div>

            <!-- Tabela e Paginação (AJAX) -->
            <div id="filas-list">
                @include('modules.atendimento-fila.partials.list')
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmacao Generica -->
<div class="modal fade" id="modalConfirmacaoGenerica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div id="confirmGenericIcon" class="mt-3 mb-2 text-danger">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                </div>
            </div>
            <div class="modal-body pt-0 text-center px-4">
                <h5 class="fw-bold text-dark mb-3" id="confirmGenericTitle">Confirmar</h5>
                <p class="mb-4 text-muted" id="confirmGenericMessage">Tem certeza?</p>

                <div class="d-flex flex-column gap-2">
                    <button type="button" id="confirmGenericBtn" class="btn btn-danger w-100 rounded-pill py-2">Sim</button>
                    <button type="button" class="btn btn-light w-100 rounded-pill py-2 text-muted fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Dynamic Search
    let typingTimer;
    const doneTypingInterval = 500; // 0.5s
    const searchInput = document.getElementById('searchInput');
    const listContainer = document.getElementById('filas-list');

    searchInput.addEventListener('input', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(performSearch, doneTypingInterval);
    });

    function performSearch() {
        const query = searchInput.value;
        const url = new URL(window.location.href);
        if (query) {
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

    // Modal Confirmacao Generica
    document.addEventListener('DOMContentLoaded', function() {
        let currentFormIdToSubmit = null;

        window.abrirConfirmacaoGenerica = function(formId, titulo, mensagem, corBtn) {
            currentFormIdToSubmit = formId;

            document.getElementById('confirmGenericTitle').textContent = titulo;
            document.getElementById('confirmGenericMessage').innerHTML = mensagem;

            const btn = document.getElementById('confirmGenericBtn');
            btn.className = `btn btn-${corBtn} w-100 rounded-pill py-2`;

            const icon = document.getElementById('confirmGenericIcon');
            icon.className = `mt-3 mb-2 text-${corBtn}`;

            new bootstrap.Modal(document.getElementById('modalConfirmacaoGenerica')).show();
        };

        document.getElementById('confirmGenericBtn').addEventListener('click', function() {
            if (currentFormIdToSubmit) {
                document.getElementById(currentFormIdToSubmit).submit();
            }
        });
    });
</script>
@endsection
