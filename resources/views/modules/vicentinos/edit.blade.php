@extends('layouts.app')

@section('title', 'Editar Apuração Vicentina')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Apuração</h2>
            <p class="text-muted small mb-0">Atualize as informações da apuração.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vicentinos.index') }}" class="text-decoration-none">Vicentinos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('vicentinos.update', $record->w_id) }}" method="POST" id="editVicentinoForm">
                @csrf
                @method('PUT')

                <!-- Busca de Pessoa (Register) - Opcional na edição, mas útil se quiser mudar a pessoa -->
                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted">Pessoa (Registro Geral)</label>
                    
                    <!-- Campo de busca -->
                    <div class="position-relative {{ $record->name ? 'd-none' : '' }}" id="searchContainer">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-pill ps-4"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control bg-light border-0 rounded-end-pill py-2" placeholder="Digite o nome para pesquisar..." autocomplete="off">
                        </div>
                        <div id="searchResults" class="position-absolute w-100 bg-white shadow-sm border rounded-4 mt-1 overflow-hidden" style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    
                    <!-- Pessoa Selecionada -->
                    <div id="selectedPersonDiv" class="alert alert-primary d-flex align-items-center justify-content-between mt-2 mb-0 {{ $record->name ? '' : 'd-none' }} rounded-pill px-4">
                        <div>
                            <i class="bi bi-person-check-fill me-2"></i>
                            <span id="selectedPersonName" class="fw-bold">{{ $record->name }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 text-primary" onclick="clearSelection()">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>
                    </div>

                    <small class="text-muted ms-3 {{ $record->name ? 'd-none' : '' }}" id="searchHelpText">Selecione uma pessoa da lista para preencher automaticamente.</small>
                </div>

                <div class="row g-4 mb-4">
                    <!-- Nome (Hidden) -->
                    <input type="hidden" id="name" name="name" value="{{ old('name', $record->name) }}" required>

                    <!-- Endereço -->
                    <div class="col-md-8">
                        <label for="address" class="form-label fw-bold small text-muted">Endereço</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="address" name="address" value="{{ old('address', $record->address) }}">
                        @error('address')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Número -->
                    <div class="col-md-4">
                        <label for="address_number" class="form-label fw-bold small text-muted">Número</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="address_number" name="address_number" value="{{ old('address_number', $record->address_number) }}">
                        @error('address_number')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Comunidade -->
                    <div class="col-md-6">
                        <label for="ent_id" class="form-label fw-bold small text-muted">Comunidade <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="ent_id" name="ent_id" required>
                            <option value="">Selecione...</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}" {{ old('ent_id', $record->ent_id) == $entidade->ent_id ? 'selected' : '' }}>{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                        @error('ent_id')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipo (Kind) -->
                    <div class="col-md-6">
                        <label for="kind" class="form-label fw-bold small text-muted">Tipo <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="kind" name="kind" required>
                            <option value="">Selecione...</option>
                            <option value="0" {{ old('kind', $record->kind) == '0' ? 'selected' : '' }}>Não Assistido</option>
                            <option value="1" {{ old('kind', $record->kind) == '1' ? 'selected' : '' }}>Assistido</option>
                        </select>
                        @error('kind')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mês -->
                    <div class="col-md-6">
                        <label for="month_entire" class="form-label fw-bold small text-muted">Mês de Referência <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="month_entire" name="month_entire" required>
                            <option value="">Selecione...</option>
                            @php
                                $months = [
                                    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                ];
                            @endphp
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ old('month_entire', $record->month_entire) == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('month_entire')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div class="col-md-12">
                        <label for="description" class="form-label fw-bold small text-muted">Descrição / Observações</label>
                        <textarea class="form-control bg-light border-0 px-4 py-3" id="description" name="description" rows="3" style="border-radius: 1rem;">{{ old('description', $record->description) }}</textarea>
                        @error('description')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 align-items-center mt-5">
                    <a href="{{ route('vicentinos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="submitBtn">
                        <i class="bi bi-check-lg me-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="module">
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('searchResults');
    const nameInput = document.getElementById('name');
    const addressInput = document.getElementById('address');
    const addressNumberInput = document.getElementById('address_number');
    const searchContainer = document.getElementById('searchContainer');
    const selectedPersonDiv = document.getElementById('selectedPersonDiv');
    const selectedPersonName = document.getElementById('selectedPersonName');
    const searchHelpText = document.getElementById('searchHelpText');
    
    let timeoutId;

    searchInput.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const query = this.value;
        
        if (query.length < 3) {
            resultsContainer.style.display = 'none';
            return;
        }

        timeoutId = setTimeout(() => {
            fetch(`{{ route('vicentinos.search-registers') }}?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    if (data.length > 0) {
                        resultsContainer.style.display = 'block';
                        data.forEach(person => {
                            const item = document.createElement('div');
                            item.className = 'p-3 border-bottom hover-bg-light cursor-pointer';
                            item.style.cursor = 'pointer';
                            item.innerHTML = `
                                <div class="fw-bold text-dark">${person.name}</div>
                                <div class="small text-muted">${person.address || 'Sem endereço'} ${person.address_number ? ', ' + person.address_number : ''}</div>
                            `;
                            
                            item.addEventListener('click', () => {
                                selectPerson(person);
                            });
                            
                            resultsContainer.appendChild(item);
                        });
                    } else {
                        resultsContainer.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 300);
    });

    function selectPerson(person) {
        // Set hidden inputs
        nameInput.value = person.name;
        addressInput.value = person.address || '';
        addressNumberInput.value = person.address_number || '';

        // UI Updates
        selectedPersonName.textContent = person.name;
        
        searchInput.value = '';
        resultsContainer.style.display = 'none';
        
        searchContainer.classList.add('d-none');
        if(searchHelpText) searchHelpText.classList.add('d-none');
        selectedPersonDiv.classList.remove('d-none');
    }

    function clearSelection() {
        // Clear hidden inputs
        nameInput.value = '';
        addressInput.value = '';
        addressNumberInput.value = '';

        // UI Updates
        selectedPersonName.textContent = '';
        
        searchContainer.classList.remove('d-none');
        if(searchHelpText) searchHelpText.classList.remove('d-none');
        selectedPersonDiv.classList.add('d-none');
        searchInput.focus();
    }
    // Expose to window for inline onclick
    window.clearSelection = clearSelection;

    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    // Loading state for submit button
    const form = document.getElementById('editVicentinoForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
        });
    }
</script>

<style>
    .hover-bg-light:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection
