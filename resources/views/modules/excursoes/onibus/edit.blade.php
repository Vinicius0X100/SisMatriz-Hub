@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Editar Ônibus</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('excursoes.index') }}" class="text-decoration-none">Excursões</a></li>
                <li class="breadcrumb-item"><a href="{{ route('excursoes.show', $excursao) }}" class="text-decoration-none">{{ $excursao->destino }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Ônibus {{ $onibus->numero }}</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('excursoes.onibus.update', [$excursao, $onibus]) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="numero" class="form-label fw-bold text-muted small">Número/Identificação</label>
                        <input type="text" class="form-control rounded-pill @error('numero') is-invalid @enderror" id="numero" name="numero" value="{{ old('numero', $onibus->numero) }}" placeholder="Ex: 01, A, Executivo" required>
                        @error('numero')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="capacidade" class="form-label fw-bold text-muted small">Capacidade</label>
                        <input type="number" class="form-control rounded-pill @error('capacidade') is-invalid @enderror" id="capacidade" name="capacidade" value="{{ old('capacidade', $onibus->capacidade) }}" min="1" required>
                        @error('capacidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="responsavel" class="form-label fw-bold text-muted small">Responsável</label>
                        <input type="text" class="form-control rounded-pill @error('responsavel') is-invalid @enderror" id="responsavel" name="responsavel" value="{{ old('responsavel', $onibus->responsavel) }}">
                        @error('responsavel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="telefone_responsavel" class="form-label fw-bold text-muted small">Telefone do Responsável</label>
                        <input type="text" class="form-control rounded-pill @error('telefone_responsavel') is-invalid @enderror" id="telefone_responsavel" name="telefone_responsavel" value="{{ old('telefone_responsavel', $onibus->telefone_responsavel) }}">
                        @error('telefone_responsavel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="local_saida" class="form-label fw-bold text-muted small">Local de Saída</label>
                        <input type="text" class="form-control rounded-pill @error('local_saida') is-invalid @enderror" id="local_saida" name="local_saida" value="{{ old('local_saida', $onibus->local_saida) }}">
                        @error('local_saida')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="horario_saida" class="form-label fw-bold text-muted small">Horário de Saída</label>
                        <input type="datetime-local" class="form-control rounded-pill @error('horario_saida') is-invalid @enderror" id="horario_saida" name="horario_saida" value="{{ old('horario_saida', $onibus->horario_saida ? $onibus->horario_saida->format('Y-m-d\TH:i') : '') }}">
                        @error('horario_saida')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="horario_retorno" class="form-label fw-bold text-muted small">Horário de Retorno</label>
                        <input type="datetime-local" class="form-control rounded-pill @error('horario_retorno') is-invalid @enderror" id="horario_retorno" name="horario_retorno" value="{{ old('horario_retorno', $onibus->horario_retorno ? $onibus->horario_retorno->format('Y-m-d\TH:i') : '') }}">
                        @error('horario_retorno')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('excursoes.show', $excursao) }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
