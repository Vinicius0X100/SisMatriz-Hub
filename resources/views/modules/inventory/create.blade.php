@extends('layouts.app')

@section('title', 'Novo Item')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Adicionar Item</h2>
            <p class="text-muted small mb-0">Registre um novo item no inventário.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Inventário</a></li>
                <li class="breadcrumb-item active" aria-current="page">Adicionar</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
            <h6 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Erros encontrados:</h6>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
                @csrf

                <div class="row g-4 mb-4">
                    <!-- Item -->
                    <div class="col-md-8">
                        <label for="item" class="form-label fw-bold small text-muted">Nome do Item <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="item" name="item" required value="{{ old('item') }}">
                        @error('item')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Quantidade -->
                    <div class="col-md-4">
                        <label for="qntd_destributed" class="form-label fw-bold small text-muted">Quantidade <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="number" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="qntd_destributed" name="qntd_destributed" required value="{{ old('qntd_destributed', 1) }}" min="0">
                        @error('qntd_destributed')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Categoria -->
                    <div class="col-md-4">
                        <label for="category" class="form-label fw-bold small text-muted">Categoria <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="category" name="category" required>
                            <option value="">Selecione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Comunidade -->
                    <div class="col-md-4">
                        <label for="ent_id" class="form-label fw-bold small text-muted">Comunidade <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="ent_id" name="ent_id" required>
                            <option value="">Selecione...</option>
                            @foreach($comunidades as $com)
                                <option value="{{ $com->ent_id }}" {{ old('ent_id') == $com->ent_id ? 'selected' : '' }}>{{ $com->ent_name }}</option>
                            @endforeach
                        </select>
                        @error('ent_id')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Local (Sala) -->
                    <div class="col-md-4">
                        <label for="sala_id" class="form-label fw-bold small text-muted">Local / Sala <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="sala_id" name="sala_id" required>
                            <option value="">Selecione...</option>
                            @foreach($locais as $local)
                                <option value="{{ $local->id }}" {{ old('sala_id') == $local->id ? 'selected' : '' }}>{{ $local->name }}</option>
                            @endforeach
                        </select>
                        @error('sala_id')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div class="col-12">
                        <label for="description" class="form-label fw-bold small text-muted">Descrição</label>
                        <textarea class="form-control bg-light border-0 px-4 py-3" id="description" name="description" rows="3" style="border-radius: 15px;">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Fotos -->
                    <div class="col-12">
                        <label for="photos" class="form-label fw-bold small text-muted">Fotos do Item</label>
                        <input type="file" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="photos" name="photos[]" multiple accept="image/*">
                        <small class="text-muted ms-3">Formatos aceitos: jpg, png, gif. Máx: 2MB.</small>
                        @error('photos.*')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('inventory.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" id="btnSubmit">
                        <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text">Salvar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('createForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        const spinner = btn.querySelector('.spinner-border');
        const text = btn.querySelector('.btn-text');
        
        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.textContent = 'Salvando...';
    });
</script>
@endsection
