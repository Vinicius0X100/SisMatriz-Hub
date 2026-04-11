@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Editar Postagem</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pascom.postagens.index') }}" class="text-decoration-none">Postagens Pascom</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 p-lg-5">
            <form action="{{ route('pascom.postagens.update', $postagem->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="data" class="form-label fw-bold text-muted small">Data <span class="text-danger">*</span></label>
                        <input type="date" name="data" id="data" class="form-control rounded-pill px-4 @error('data') is-invalid @enderror" value="{{ old('data', $postagem->data?->format('Y-m-d')) }}" required>
                        @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="horario" class="form-label fw-bold text-muted small">Horário <span class="text-danger">*</span></label>
                        <input type="time" name="horario" id="horario" class="form-control rounded-pill px-4 @error('horario') is-invalid @enderror" value="{{ old('horario', \Carbon\Carbon::parse($postagem->horario)->format('H:i')) }}" required>
                        @error('horario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="celebrante" class="form-label fw-bold text-muted small">Celebrante <span class="text-danger">*</span></label>
                        <input type="text" name="celebrante" id="celebrante" class="form-control rounded-pill px-4 @error('celebrante') is-invalid @enderror" value="{{ old('celebrante', $postagem->celebrante) }}" required>
                        @error('celebrante')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label for="comunidade_id" class="form-label fw-bold text-muted small">Comunidade <span class="text-danger">*</span></label>
                        <select name="comunidade_id" id="comunidade_id" class="form-select rounded-pill px-4 @error('comunidade_id') is-invalid @enderror" required>
                            <option value="">Selecione uma comunidade</option>
                            @foreach($comunidades as $comunidade)
                                <option value="{{ $comunidade->ent_id }}" {{ (string)old('comunidade_id', $postagem->comunidade_id) === (string)$comunidade->ent_id ? 'selected' : '' }}>
                                    {{ $comunidade->ent_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('comunidade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label for="descricao" class="form-label fw-bold text-muted small">Descrição <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea name="descricao" id="descricao" rows="4" class="form-control rounded-4 p-3 @error('descricao') is-invalid @enderror">{{ old('descricao', $postagem->descricao) }}</textarea>
                        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="border-top pt-4">
                    <h6 class="fw-bold text-primary mb-3">Arquivos</h6>
                    <div class="row g-2">
                        @forelse($postagem->arquivos as $arquivo)
                            <div class="col-12 col-md-6">
                                <div class="d-flex align-items-center justify-content-between border rounded-4 p-3 bg-light">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $arquivo->type === 'video' ? 'bi-camera-reels' : 'bi-image' }} text-primary"></i>
                                        <div class="small fw-semibold text-dark text-truncate" style="max-width: 240px;">
                                            {{ $arquivo->original_name }}
                                        </div>
                                    </div>
                                    <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ asset('storage/uploads/pascom/' . $arquivo->filename) }}" download>
                                        <i class="bi bi-download me-1"></i> Baixar
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-muted small">Nenhum arquivo anexado.</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <hr class="my-5">

                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('pascom.postagens.index') }}" class="btn btn-light border rounded-pill px-4 py-2 fw-medium">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold d-flex align-items-center">
                        <i class="bi bi-check-lg me-2"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
