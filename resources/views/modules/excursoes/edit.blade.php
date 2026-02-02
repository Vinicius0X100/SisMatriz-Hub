@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Editar Excursão</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('excursoes.index') }}" class="text-decoration-none">Excursões</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('excursoes.update', $excursao) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="destino" class="form-label fw-bold text-muted small">Destino</label>
                        <input type="text" class="form-control rounded-pill @error('destino') is-invalid @enderror" id="destino" name="destino" value="{{ old('destino', $excursao->destino) }}" required>
                        @error('destino')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="tipo" class="form-label fw-bold text-muted small">Tipo</label>
                        <select class="form-select rounded-pill @error('tipo') is-invalid @enderror" id="tipo" name="tipo">
                            <option value="excursao" {{ old('tipo', $excursao->tipo) == 'excursao' ? 'selected' : '' }}>Excursão</option>
                            <option value="retiro" {{ old('tipo', $excursao->tipo) == 'retiro' ? 'selected' : '' }}>Retiro</option>
                            <option value="peregrinacao" {{ old('tipo', $excursao->tipo) == 'peregrinacao' ? 'selected' : '' }}>Peregrinação</option>
                            <option value="passeio" {{ old('tipo', $excursao->tipo) == 'passeio' ? 'selected' : '' }}>Passeio</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="descricao" class="form-label fw-bold text-muted small">Descrição</label>
                        <textarea class="form-control rounded-4 @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="4">{{ old('descricao', $excursao->descricao) }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('excursoes.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
