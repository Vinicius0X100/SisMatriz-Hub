@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Apuração Vicentina</h2>
            <p class="text-muted small mb-0">Atualize os dados do assistido/não assistido.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vicentinos-apuracoes.index') }}" class="text-decoration-none">Apuração de Vicentinos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Apuração</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção!</strong> Verifique os erros abaixo e tente novamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('vicentinos-apuracoes.update', $record->w_id) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Dados Principais -->
                <h5 class="fw-bold text-dark mb-4">Informações do Registro</h5>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                         <label class="form-label fw-bold small text-muted">Pesquisar Pessoa (Registros Vicentinos)</label>
                         <div class="input-group">
                             <input type="text" id="search_person" class="form-control rounded-pill rounded-end-0 bg-light border-0 px-4 py-2" placeholder="Digite o nome ou CPF para pesquisar..." value="{{ $record->name }}">
                             <button type="button" class="btn btn-primary rounded-pill rounded-start-0 px-4" data-bs-toggle="modal" data-bs-target="#searchModal">
                                 <i class="bi bi-search me-2"></i> Pesquisar
                             </button>
                         </div>
                         <small class="text-muted">Utilize a pesquisa para atualizar automaticamente os dados.</small>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold small text-muted">Nome do Assistido <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('name') is-invalid @enderror" value="{{ old('name', $record->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="ent_id" class="form-label fw-bold small text-muted">Comunidade <span class="text-danger">*</span></label>
                        <select name="ent_id" id="ent_id" class="form-select rounded-pill bg-light border-0 px-4 py-2 @error('ent_id') is-invalid @enderror" required>
                            <option value="">Selecione...</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}" {{ old('ent_id', $record->ent_id) == $entidade->ent_id ? 'selected' : '' }}>{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                        @error('ent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="kind" class="form-label fw-bold small text-muted">Tipo <span class="text-danger">*</span></label>
                        <select name="kind" id="kind" class="form-select rounded-pill bg-light border-0 px-4 py-2 @error('kind') is-invalid @enderror" required>
                            <option value="">Selecione...</option>
                            <option value="1" {{ old('kind', $record->kind) == '1' ? 'selected' : '' }}>Assistido</option>
                            <option value="0" {{ old('kind', $record->kind) == '0' ? 'selected' : '' }}>Não Assistido</option>
                        </select>
                        @error('kind')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="month_entire" class="form-label fw-bold small text-muted">Mês de Referência <span class="text-danger">*</span></label>
                        <select name="month_entire" id="month_entire" class="form-select rounded-pill bg-light border-0 px-4 py-2 @error('month_entire') is-invalid @enderror" required>
                            <option value="">Selecione...</option>
                            @php
                                $meses = [
                                    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
                                    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
                                    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
                                ];
                            @endphp
                            @foreach($meses as $num => $nome)
                                <option value="{{ $num }}" {{ old('month_entire', $record->month_entire) == $num ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                        @error('month_entire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold small text-muted">Endereço</label>
                        <input type="text" name="address" id="address" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('address') is-invalid @enderror" value="{{ old('address', $record->address) }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label for="address_number" class="form-label fw-bold small text-muted">Número</label>
                        <input type="text" name="address_number" id="address_number" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('address_number') is-invalid @enderror" value="{{ old('address_number', $record->address_number) }}">
                        @error('address_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="description" class="form-label fw-bold small text-muted">Observações</label>
                    <textarea name="description" id="description" rows="4" class="form-control rounded-4 bg-light border-0 p-4 @error('description') is-invalid @enderror">{{ old('description', $record->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('vicentinos-apuracoes.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Atualizar Apuração</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Pesquisa -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="searchModalLabel">Pesquisar Registro Vicentino</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <input type="text" id="modalSearchInput" class="form-control rounded-pill bg-light border-0 px-4 py-2" placeholder="Digite o nome ou CPF para buscar..." autofocus>
                </div>
                <div class="list-group list-group-flush" id="searchResults">
                    <!-- Resultados da busca aparecerão aqui via JS -->
                    <div class="text-center text-muted py-4">Digite para pesquisar...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_person');
    const modalSearchInput = document.getElementById('modalSearchInput');
    const searchResults = document.getElementById('searchResults');
    const searchModal = new bootstrap.Modal(document.getElementById('searchModal'));
    
    // Sincronizar input principal com modal
    document.querySelector('[data-bs-target="#searchModal"]').addEventListener('click', function() {
        if(searchInput.value) {
            modalSearchInput.value = searchInput.value;
            performSearch(searchInput.value);
        }
        setTimeout(() => modalSearchInput.focus(), 500);
    });

    // Evento de digitação no modal
    let debounceTimer;
    modalSearchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            performSearch(e.target.value);
        }, 300);
    });

    function performSearch(query) {
        if(query.length < 3) {
            searchResults.innerHTML = '<div class="text-center text-muted py-4">Digite pelo menos 3 caracteres...</div>';
            return;
        }

        searchResults.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-arrow-repeat spinner-border spinner-border-sm me-2"></i> Buscando...</div>';

        fetch(`{{ route('vicentinos-apuracoes.search-records') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if(data.length === 0) {
                    searchResults.innerHTML = '<div class="text-center text-muted py-4">Nenhum registro encontrado.</div>';
                    return;
                }

                data.forEach(person => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3';
                    item.innerHTML = `
                        <div>
                            <h6 class="mb-1 fw-bold text-dark">${person.name}</h6>
                            <small class="text-muted"><i class="bi bi-geo-alt me-1"></i> ${person.full_address || 'Endereço não informado'}</small>
                        </div>
                        <span class="badge bg-light text-primary rounded-pill"><i class="bi bi-plus-lg"></i> Selecionar</span>
                    `;
                    
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectPerson(person);
                    });
                    
                    searchResults.appendChild(item);
                });
            })
            .catch(error => {
                console.error('Erro:', error);
                searchResults.innerHTML = '<div class="text-center text-danger py-4">Erro ao buscar registros.</div>';
            });
    }

    function selectPerson(person) {
        document.getElementById('name').value = person.name;
        document.getElementById('address').value = person.address || '';
        document.getElementById('address_number').value = person.address_number || '';
        document.getElementById('search_person').value = person.name; // Atualizar o campo de pesquisa visual também
        
        // Fechar modal
        const modalEl = document.getElementById('searchModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
        
        // Feedback visual (opcional)
        // alert('Dados preenchidos com sucesso!');
    }
});
</script>
@endsection
