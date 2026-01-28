@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Adicionar Catequista de Primeira Eucaristia</h2>
            <p class="text-muted small mb-0">Selecione uma pessoa dos registros gerais para adicionar como catequista.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('catequistas-eucaristia.index') }}" class="text-decoration-none">Catequistas de Primeira Eucaristia</a></li>
                <li class="breadcrumb-item active" aria-current="page">Adicionar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            
            <form action="{{ route('catequistas-eucaristia.store') }}" method="POST">
                @csrf

                <!-- Row 1: Pessoa (Pesquisa) -->
                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <label for="register_search" class="form-label fw-bold small text-muted">Pesquisar Pessoa (Registros Gerais) <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <div class="position-relative">
                            <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="register_search" placeholder="Digite o nome para pesquisar..." autocomplete="off">
                            <input type="hidden" name="register_id" id="register_id" required>
                            
                            <!-- Dropdown de resultados (Hidden by default) -->
                            <div id="search_results" class="position-absolute w-100 bg-white shadow-lg rounded-4 mt-2 border p-0" style="z-index: 1000; max-height: 250px; overflow-y: auto; display: none;">
                                <!-- Resultados via JS -->
                            </div>
                        </div>
                        <div id="selected_person" class="mt-2 d-none">
                            <div class="d-flex align-items-center p-2 bg-primary bg-opacity-10 rounded-3">
                                <i class="bi bi-check-circle-fill text-primary me-2"></i>
                                <span class="fw-bold text-dark" id="selected_person_name"></span>
                                <button type="button" class="btn btn-sm btn-link text-danger ms-auto text-decoration-none" onclick="clearSelection()">Alterar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Comunidade e Status -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="ent_id" class="form-label fw-bold small text-muted">Comunidade <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="ent_id" name="ent_id" required>
                            <option value="">Selecione a comunidade...</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold small text-muted">Status <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="status" name="status" required>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3">
                    <a href="{{ route('catequistas-eucaristia.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Catequista</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const registers = @json($registers); // Load all registers for client-side search (assuming reasonable size)
    const searchInput = document.getElementById('register_search');
    const resultsContainer = document.getElementById('search_results');
    const hiddenInput = document.getElementById('register_id');
    const selectedPersonDiv = document.getElementById('selected_person');
    const selectedPersonName = document.getElementById('selected_person_name');

    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        resultsContainer.innerHTML = '';
        
        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }

        const filtered = registers.filter(r => r.name.toLowerCase().includes(query));

        if (filtered.length > 0) {
            resultsContainer.style.display = 'block';
            filtered.forEach(item => {
                const div = document.createElement('div');
                div.className = 'p-3 border-bottom hover-bg-light cursor-pointer';
                div.style.cursor = 'pointer';
                div.textContent = item.name;
                div.onclick = () => selectPerson(item.id, item.name);
                resultsContainer.appendChild(div);
            });
        } else {
            resultsContainer.style.display = 'block';
            resultsContainer.innerHTML = '<div class="p-3 text-muted text-center">Nenhum registro encontrado.</div>';
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    function selectPerson(id, name) {
        hiddenInput.value = id;
        selectedPersonName.textContent = name;
        
        searchInput.value = '';
        resultsContainer.style.display = 'none';
        
        searchInput.parentElement.classList.add('d-none');
        selectedPersonDiv.classList.remove('d-none');
    }

    function clearSelection() {
        hiddenInput.value = '';
        selectedPersonName.textContent = '';
        
        searchInput.parentElement.classList.remove('d-none');
        selectedPersonDiv.classList.add('d-none');
        searchInput.focus();
    }
</script>

<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
