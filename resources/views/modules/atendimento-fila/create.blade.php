@extends('layouts.app')

@section('title', 'Nova Fila de Atendimento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Nova Fila de Atendimento</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('atendimento-fila.index') }}" class="text-decoration-none">Fila de Atendimento</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova Fila</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('atendimento-fila.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="data" class="form-label fw-semibold">Data da fila</label>
                            <input type="date" class="form-control @error('data') is-invalid @enderror"
                                   id="data" name="data"
                                   value="{{ old('data', today()->format('Y-m-d')) }}"
                                   required>
                            @error('data')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">A secretária poderá adicionar agendamentos nesta data logo após criar a fila.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-2"></i>Criar Fila
                            </button>
                            <a href="{{ route('atendimento-fila.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
