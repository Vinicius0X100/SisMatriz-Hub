@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Iniciar Processo de Batismo</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('batismos.index') }}" class="text-decoration-none">Batismos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Novo</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-4 d-inline-block mb-3">
                            <i class="bi bi-search fs-1 text-primary"></i>
                        </div>
                        <h4 class="fw-bold">Buscar Pessoa</h4>
                        <p class="text-muted">Localize a pessoa no cadastro geral para iniciar o registro de batismo.</p>
                    </div>

                    <div class="mb-4 position-relative">
                        <label for="personSearch" class="form-label fw-bold text-muted small">Pesquisar por Nome ou CPF</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-search"></i></span>
                            <input type="text" id="personSearch" class="form-control bg-light border-0 rounded-end-pill py-3" placeholder="Digite o nome completo ou CPF..." autocomplete="off">
                        </div>
                        <div id="searchResults" class="list-group position-absolute w-100 mt-2 shadow-lg rounded-4 overflow-hidden" style="z-index: 1000; display: none;">
                            <!-- Resultados via JS -->
                        </div>
                    </div>

                    <div id="selectedPersonCard" class="card border bg-light rounded-4 mb-4" style="display: none;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-md rounded-circle bg-white text-primary d-flex align-items-center justify-content-center me-3 fw-bold shadow-sm" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    <span id="selectedInitials"></span>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="fw-bold text-dark mb-1" id="selectedName"></h5>
                                    <div class="text-muted small">
                                        <i class="bi bi-person-vcard me-1"></i> <span id="selectedCpf"></span>
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-calendar me-1"></i> <span id="selectedBorn"></span>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" id="removeSelectionBtn">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('batismos.store') }}" method="POST" id="createBatismoForm">
                        @csrf
                        <input type="hidden" name="register_id" id="selectedRegisterId">
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill py-3 fw-bold" id="submitBtn" disabled>
                                Iniciar Batismo
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="small text-muted mb-0">A pessoa não está cadastrada?</p>
                        <a href="{{ route('registers.create') }}" class="fw-bold text-primary text-decoration-none">Cadastrar nova pessoa</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('personSearch');
        const searchResults = document.getElementById('searchResults');
        const selectedPersonCard = document.getElementById('selectedPersonCard');
        const selectedName = document.getElementById('selectedName');
        const selectedCpf = document.getElementById('selectedCpf');
        const selectedBorn = document.getElementById('selectedBorn');
        const selectedInitials = document.getElementById('selectedInitials');
        const selectedRegisterId = document.getElementById('selectedRegisterId');
        const submitBtn = document.getElementById('submitBtn');
        const removeSelectionBtn = document.getElementById('removeSelectionBtn');
        let searchDebounce;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchDebounce);
            const query = this.value;
            
            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchDebounce = setTimeout(() => {
                fetch(`{{ route('registers.search') }}?q=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.length > 0) {
                            data.forEach(person => {
                                const item = document.createElement('a');
                                item.href = '#';
                                item.className = 'list-group-item list-group-item-action border-0 py-3 px-4';
                                item.innerHTML = `
                                    <div class="d-flex align-items-center">
                                        <div class="fw-bold text-dark">${person.name}</div>
                                        <div class="ms-auto text-muted small">${person.cpf || 'Sem CPF'}</div>
                                    </div>
                                `;
                                item.onclick = (e) => {
                                    e.preventDefault();
                                    selectPerson(person);
                                };
                                searchResults.appendChild(item);
                            });
                            searchResults.style.display = 'block';
                        } else {
                            searchResults.innerHTML = '<div class="list-group-item border-0 py-3 px-4 text-muted text-center">Nenhuma pessoa encontrada</div>';
                            searchResults.style.display = 'block';
                        }
                    });
            }, 300);
        });

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        function selectPerson(person) {
            selectedRegisterId.value = person.id;
            selectedName.textContent = person.name;
            selectedCpf.textContent = person.cpf || 'CPF não informado';
            
            // Format Date (assuming YYYY-MM-DD from backend)
            let bornDate = 'Data não informada';
            if (person.born_date) {
                const parts = person.born_date.split('-');
                if (parts.length === 3) bornDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            selectedBorn.textContent = bornDate;

            // Initials
            selectedInitials.textContent = person.name.charAt(0).toUpperCase();

            selectedPersonCard.style.display = 'block';
            searchInput.value = '';
            searchResults.style.display = 'none';
            submitBtn.disabled = false;
            
            // Disable search input to prevent confusion
            searchInput.disabled = true;
            searchInput.placeholder = 'Pessoa selecionada';
        }

        removeSelectionBtn.addEventListener('click', function() {
            selectedRegisterId.value = '';
            selectedPersonCard.style.display = 'none';
            submitBtn.disabled = true;
            
            searchInput.disabled = false;
            searchInput.placeholder = 'Digite o nome completo ou CPF...';
            searchInput.focus();
        });
    });
</script>
@endsection
