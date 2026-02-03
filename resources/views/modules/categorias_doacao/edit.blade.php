@extends('layouts.app')

@section('title', 'Editar Categoria de Doação')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Categoria</h2>
            <p class="text-muted small mb-0">Atualize as informações da categoria.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('categorias_doacao.index') }}" class="text-decoration-none">Categorias</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('categorias_doacao.update', $record->id) }}" method="POST" id="editCategoriaForm">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <!-- Nome -->
                    <div class="col-md-12">
                        <label for="name" class="form-label fw-bold small text-muted">Nome da Categoria <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="name" name="name" value="{{ old('name', $record->name) }}" required>
                        @error('name')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 align-items-center mt-5">
                    <a href="{{ route('categorias_doacao.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="submitBtn">
                        <i class="bi bi-check-lg me-2"></i>Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Loading state for submit button
    const form = document.getElementById('editCategoriaForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
        });
    }
</script>
@endsection
