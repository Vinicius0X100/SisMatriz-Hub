@extends('layouts.app')

@section('title', 'Nova Função de Acólito/Coroinha')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Nova Função</h2>
            <p class="text-muted small mb-0">Cadastre uma nova função para a equipe de liturgia.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.funcoes.index') }}" class="text-decoration-none">Funções</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('acolitos.funcoes.store') }}" method="POST" id="createForm">
                @csrf

                <div class="row g-4">
                    <div class="col-md-12">
                        <label for="title" class="form-label fw-bold small text-muted">Título da Função <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 rounded-pill px-4" id="title" name="title" required placeholder="Ex: Cruciferário" value="{{ old('title') }}">
                        @error('title')
                            <div class="text-danger small mt-1 ps-3">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5 pt-3 border-top">
                    <a href="{{ route('acolitos.funcoes.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5" id="saveBtn">
                        <i class="bi bi-check-lg me-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('createForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando alterações...';
    });
</script>
@endsection
