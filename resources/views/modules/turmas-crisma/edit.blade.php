@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Turma de Crisma</h2>
            <p class="text-muted small mb-0">Atualize os dados da turma.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('turmas-crisma.index') }}" class="text-decoration-none">Turmas Crisma</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Turma</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('turmas-crisma.update', $turma->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="turma" class="form-label fw-bold text-muted small">Nome da Turma</label>
                        <input type="text" class="form-control" id="turma" name="turma" value="{{ old('turma', $turma->turma) }}" required>
                        @error('turma')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tutor" class="form-label fw-bold text-muted small">Tutor (Catequista)</label>
                        <select class="form-select" id="tutor" name="tutor" required>
                            <option value="">Selecione um catequista...</option>
                            @foreach($catequistas as $catequista)
                                <option value="{{ $catequista->id }}" {{ old('tutor', $turma->tutor) == $catequista->id ? 'selected' : '' }}>
                                    {{ $catequista->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('tutor')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="inicio" class="form-label fw-bold text-muted small">Data de Início</label>
                        <input type="date" class="form-control" id="inicio" name="inicio" value="{{ old('inicio', $turma->inicio ? $turma->inicio->format('Y-m-d') : '') }}" required>
                        @error('inicio')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="termino" class="form-label fw-bold text-muted small">Data de Término</label>
                        <input type="date" class="form-control" id="termino" name="termino" value="{{ old('termino', $turma->termino ? $turma->termino->format('Y-m-d') : '') }}" required>
                        @error('termino')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label fw-bold text-muted small">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1" {{ old('status', $turma->status) == 1 ? 'selected' : '' }}>Não Iniciada</option>
                            <option value="2" {{ old('status', $turma->status) == 2 ? 'selected' : '' }}>Concluída</option>
                            <option value="3" {{ old('status', $turma->status) == 3 ? 'selected' : '' }}>Em Catequese</option>
                            <option value="4" {{ old('status', $turma->status) == 4 ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('turmas-crisma.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
