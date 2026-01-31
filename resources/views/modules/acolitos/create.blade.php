@extends('layouts.app')

@section('title', 'Novo Acólito/Coroinha')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Adicionar Acólito/Coroinha</h2>
            <p class="text-muted small mb-0">Cadastre um novo membro na equipe de liturgia.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.index') }}" class="text-decoration-none">Acólitos e Coroinhas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Adicionar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('acolitos.store') }}" method="POST" id="createAcolitoForm">
                @csrf

                <!-- Busca de Pessoa (Register) -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">Pessoa (Registro Geral) <span class="text-danger fw-bold small">(obrigatório)</span></label>
                    
                    <!-- Campo de busca -->
                    <div class="position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-pill ps-4"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control bg-light border-0 rounded-end-pill py-2" placeholder="Digite o nome para pesquisar..." autocomplete="off">
                        </div>
                        <div id="searchResults" class="position-absolute w-100 bg-white shadow-sm border rounded-4 mt-1 overflow-hidden" style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                    </div>

                    <!-- Pessoa Selecionada -->
                    <div id="selectedPersonDiv" class="alert alert-primary d-flex align-items-center justify-content-between mt-2 mb-0 d-none rounded-pill px-4">
                        <div>
                            <i class="bi bi-person-check-fill me-2"></i>
                            <span id="selectedPersonName" class="fw-bold"></span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-primary" onclick="clearSelection()">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>
                    </div>

                    <input type="hidden" name="register_id" id="hiddenInput">
                    @error('register_id')
                        <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-4 mb-4">
                    <!-- Comunidade -->
                    <div class="col-md-6">
                        <label for="ent_id" class="form-label fw-bold small text-muted">Comunidade <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="ent_id" name="ent_id" required>
                            <option value="">Selecione...</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                        @error('ent_id')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-6">
                        <label for="type" class="form-label fw-bold small text-muted">Tipo <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="type" name="type" required>
                            <option value="">Selecione...</option>
                            <option value="0">Acólito</option>
                            <option value="1">Coroinha</option>
                        </select>
                        @error('type')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ano Formação -->
                    <div class="col-md-6">
                        <label for="graduation_year" class="form-label fw-bold small text-muted">Ano de Formação <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="number" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="graduation_year" name="graduation_year" placeholder="Ex: 2023" required>
                        @error('graduation_year')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold small text-muted">Status <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="status" name="status" required>
                            <option value="0" selected>Ativo</option>
                            <option value="1">Inativo</option>
                        </select>
                        @error('status')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 align-items-center mt-5">
                    <a href="{{ route('acolitos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="submitBtn">
                        <i class="bi bi-check-lg me-2"></i>Salvar
                    </button>
                </div>

                <!-- Hidden Input for User ID -->
                <input type="hidden" name="user_id" id="userIdInput">
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Vínculo com Usuário -->
<div class="modal fade" id="userMatchModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg rounded-4">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title fw-bold text-primary">Vínculo com Usuário Encontrado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center py-3">
            <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                <i class="bi bi-person-badge fs-1 text-primary"></i>
            </div>
            <h5 class="fw-bold mb-3">Encontramos um usuário com este nome!</h5>
            <p class="text-muted mb-4">Deseja vincular este acólito/coroinha ao usuário existente no sistema?</p>
            
            <div class="card bg-light border-0 rounded-4 mb-3">
                <div class="card-body text-start">
                    <h6 class="fw-bold text-dark mb-3 small text-uppercase">Dados do Usuário</h6>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-person text-muted me-2"></i>
                        <span id="modalUserName" class="fw-bold text-dark"></span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-envelope text-muted me-2"></i>
                        <span id="modalUserEmail" class="text-muted"></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-lock text-muted me-2"></i>
                        <span id="modalUserRole" class="badge bg-secondary rounded-pill"></span>
                    </div>
                </div>
            </div>
            
            <p class="small text-muted mb-0">Isso permitirá que o sistema associe as atividades deste acólito à conta de usuário correspondente.</p>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
        <button type="button" class="btn btn-light rounded-pill px-4" id="btnRejectMatch">Não, são pessoas diferentes</button>
        <button type="button" class="btn btn-primary rounded-pill px-4" id="btnConfirmMatch">Sim, vincular usuário</button>
      </div>
    </div>
  </div>
</div>

<script type="module">
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('searchResults');
    const hiddenInput = document.getElementById('hiddenInput');
    const selectedPersonDiv = document.getElementById('selectedPersonDiv');
    const selectedPersonName = document.getElementById('selectedPersonName');
    const userIdInput = document.getElementById('userIdInput');
    const createForm = document.getElementById('createAcolitoForm');
    
    // Modal Elements
    const userMatchModal = new bootstrap.Modal(document.getElementById('userMatchModal'));
    const modalUserName = document.getElementById('modalUserName');
    const modalUserEmail = document.getElementById('modalUserEmail');
    const modalUserRole = document.getElementById('modalUserRole');
    const btnConfirmMatch = document.getElementById('btnConfirmMatch');
    const btnRejectMatch = document.getElementById('btnRejectMatch');

    let timeoutId;
    let pendingUserId = null;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const query = this.value;
        
        if (query.length < 3) {
            resultsContainer.style.display = 'none';
            return;
        }

        timeoutId = setTimeout(() => {
            fetch(`{{ route('acolitos.search-registers') }}?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length > 0) {
                        resultsContainer.style.display = 'block';
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'p-3 border-bottom hover-bg-light cursor-pointer';
                            div.style.cursor = 'pointer';
                            div.innerHTML = `
                                <div class="fw-bold text-dark">${item.name}</div>
                                <div class="small text-muted">Idade: ${item.age || 'N/A'}</div>
                            `;
                            div.onclick = () => selectPerson(item.id, item.name);
                            resultsContainer.appendChild(div);
                        });
                    } else {
                        resultsContainer.style.display = 'block';
                        resultsContainer.innerHTML = '<div class="p-3 text-muted text-center small">Nenhum registro encontrado.</div>';
                    }
                });
        }, 500);
    });

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
        
        searchInput.closest('.position-relative').classList.add('d-none');
        selectedPersonDiv.classList.remove('d-none');
    }
    window.selectPerson = selectPerson;

    function clearSelection() {
        hiddenInput.value = '';
        selectedPersonName.textContent = '';
        userIdInput.value = ''; // Clear user ID too
        
        searchInput.closest('.position-relative').classList.remove('d-none');
        selectedPersonDiv.classList.add('d-none');
        searchInput.focus();
    }
    window.clearSelection = clearSelection;

    // Intercept Submit
    createForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // If user_id is already set or explicitly rejected (we can track rejection via a flag if needed, but here we just check if we haven't checked yet)
        // Actually, best logic:
        // 1. If we haven't checked user yet, check it.
        // 2. If check returns found, show modal.
        // 3. Modal confirm -> set user_id -> submit.
        // 4. Modal reject -> submit directly.
        // 5. If check returns not found -> submit directly.
        
        // But we need to know if we already checked. 
        // We can use a flag `userChecked`.
        
        if (this.dataset.userChecked === 'true') {
            submitForm();
            return;
        }

        const name = selectedPersonName.textContent;
        if (!name) {
            // Should be caught by HTML5 required on hiddenInput but it's hidden so maybe not.
            // But let's proceed to validation if empty.
            submitForm(); 
            return;
        }

        // Check for User
        const btn = document.getElementById('submitBtn');
        const originalBtnContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verificando...';

        fetch('{{ route("acolitos.check-user") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: name })
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalBtnContent;

            if (data.found) {
                pendingUserId = data.user.id;
                modalUserName.textContent = data.user.name;
                modalUserEmail.textContent = data.user.email || 'Sem e-mail';
                modalUserRole.textContent = data.user.rule || 'N/A';
                
                if (!userMatchModal && window.bootstrap) {
                     userMatchModal = new window.bootstrap.Modal(document.getElementById('userMatchModal'));
                }
                if (userMatchModal) userMatchModal.show();
            } else {
                createForm.dataset.userChecked = 'true';
                submitForm();
            }
        })
        .catch(err => {
            console.error(err);
            // On error, just submit normally to avoid blocking user
            createForm.dataset.userChecked = 'true';
            submitForm();
        });
    });

    btnConfirmMatch.addEventListener('click', function() {
        userIdInput.value = pendingUserId;
        createForm.dataset.userChecked = 'true';
        if (userMatchModal) userMatchModal.hide();
        submitForm();
    });

    btnRejectMatch.addEventListener('click', function() {
        userIdInput.value = '';
        createForm.dataset.userChecked = 'true';
        if (userMatchModal) userMatchModal.hide();
        submitForm();
    });

    function submitForm() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando alterações...';
        createForm.submit();
    }
</script>

<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
