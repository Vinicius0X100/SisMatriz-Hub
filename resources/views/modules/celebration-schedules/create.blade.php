@extends('layouts.app')

@section('title', 'Novo Horário')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Adicionar Horário</h2>
            <p class="text-muted small mb-0">Registre um novo horário de celebração.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('celebration-schedules.index') }}" class="text-decoration-none">Celebrações e Horários</a></li>
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
            <form action="{{ route('celebration-schedules.store') }}" method="POST" id="createForm">
                @csrf

                <div class="row g-4 mb-4">
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

                    <!-- Dia da Semana -->
                    <div class="col-md-4">
                        <label for="dia_semana" class="form-label fw-bold small text-muted">Dia da Semana <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="dia_semana" name="dia_semana" required>
                            <option value="">Selecione...</option>
                            @foreach($diasSemana as $dia)
                                <option value="{{ $dia }}" {{ old('dia_semana') == $dia ? 'selected' : '' }}>{{ $dia }}</option>
                            @endforeach
                        </select>
                        @error('dia_semana')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Horário -->
                    <div class="col-md-4">
                        <label for="horario" class="form-label fw-bold small text-muted">Horário <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="time" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="horario" name="horario" required value="{{ old('horario') }}">
                        @error('horario')
                            <div class="text-danger small mt-1 ps-3 fw-bold">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('celebration-schedules.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
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
