@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Editar Batismo</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('batismos.index') }}" class="text-decoration-none">Batismos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <div class="d-flex align-items-center mb-5">
                <div class="avatar-lg rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-4 display-5 fw-bold shadow-sm" style="width: 80px; height: 80px;">
                    {{ substr($batismo->register->name ?? 'N', 0, 1) }}
                </div>
                <div>
                    <h3 class="fw-bold text-dark mb-1">{{ $batismo->register->name ?? 'Sem Nome' }}</h3>
                    <div class="d-flex gap-3 text-muted small">
                        <span><i class="bi bi-person-vcard me-1"></i> {{ $batismo->register->cpf ?? 'CPF não informado' }}</span>
                        <span><i class="bi bi-calendar-event me-1"></i> {{ $batismo->register->born_date ? \Carbon\Carbon::parse($batismo->register->born_date)->format('d/m/Y') : 'Data não informada' }}</span>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-pill px-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('batismos.update', $batismo->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <div class="col-12">
                        <h5 class="fw-bold text-primary mb-3 pb-2 border-bottom">Informações do Batismo</h5>
                    </div>

                    <div class="col-md-4">
                        <label for="is_batizado" class="form-label fw-bold text-muted small">Status de Batismo</label>
                        <select name="is_batizado" id="is_batizado" class="form-select rounded-pill @error('is_batizado') is-invalid @enderror" style="height: 45px;">
                            <option value="1" {{ old('is_batizado', $batismo->is_batizado) ? 'selected' : '' }}>Batizado</option>
                            <option value="0" {{ !old('is_batizado', $batismo->is_batizado) ? 'selected' : '' }}>Não Batizado</option>
                        </select>
                        @error('is_batizado')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="data_batismo" class="form-label fw-bold text-muted small">Data do Batismo</label>
                        <input type="date" name="data_batismo" id="data_batismo" class="form-control rounded-pill @error('data_batismo') is-invalid @enderror" value="{{ old('data_batismo', $batismo->data_batismo ? $batismo->data_batismo->format('Y-m-d') : '') }}" style="height: 45px;">
                        @error('data_batismo')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="local_batismo" class="form-label fw-bold text-muted small">Paróquia/Local</label>
                        <input type="text" name="local_batismo" id="local_batismo" class="form-control rounded-pill @error('local_batismo') is-invalid @enderror" placeholder="Ex: Paróquia São José" value="{{ old('local_batismo', $batismo->local_batismo) }}" style="height: 45px;">
                        @error('local_batismo')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="celebrante" class="form-label fw-bold text-muted small">Celebrante (Padre/Diácono)</label>
                        <input type="text" name="celebrante" id="celebrante" class="form-control rounded-pill @error('celebrante') is-invalid @enderror" placeholder="Nome do celebrante" value="{{ old('celebrante', $batismo->celebrante) }}" style="height: 45px;">
                        @error('celebrante')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <!-- Spacer -->
                    </div>

                    <div class="col-12 mt-4">
                        <h5 class="fw-bold text-primary mb-3 pb-2 border-bottom">Padrinhos</h5>
                    </div>

                    <div class="col-md-6">
                        <label for="padrinho_nome" class="form-label fw-bold text-muted small">Nome do Padrinho</label>
                        <input type="text" name="padrinho_nome" id="padrinho_nome" class="form-control rounded-pill @error('padrinho_nome') is-invalid @enderror" placeholder="Nome completo do padrinho" value="{{ old('padrinho_nome', $batismo->padrinho_nome) }}" style="height: 45px;">
                        @error('padrinho_nome')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="madrinha_nome" class="form-label fw-bold text-muted small">Nome da Madrinha</label>
                        <input type="text" name="madrinha_nome" id="madrinha_nome" class="form-control rounded-pill @error('madrinha_nome') is-invalid @enderror" placeholder="Nome completo da madrinha" value="{{ old('madrinha_nome', $batismo->madrinha_nome) }}" style="height: 45px;">
                        @error('madrinha_nome')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mt-4">
                        <h5 class="fw-bold text-primary mb-3 pb-2 border-bottom">Registro no Livro</h5>
                    </div>

                    <div class="col-md-4">
                        <label for="livro" class="form-label fw-bold text-muted small">Livro</label>
                        <input type="text" name="livro" id="livro" class="form-control rounded-pill @error('livro') is-invalid @enderror" placeholder="Ex: 10-A" value="{{ old('livro', $batismo->livro) }}" style="height: 45px;">
                        @error('livro')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="folha" class="form-label fw-bold text-muted small">Folha</label>
                        <input type="text" name="folha" id="folha" class="form-control rounded-pill @error('folha') is-invalid @enderror" placeholder="Ex: 55v" value="{{ old('folha', $batismo->folha) }}" style="height: 45px;">
                        @error('folha')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="registro" class="form-label fw-bold text-muted small">Número do Registro</label>
                        <input type="text" name="registro" id="registro" class="form-control rounded-pill @error('registro') is-invalid @enderror" placeholder="Ex: 12345" value="{{ old('registro', $batismo->registro) }}" style="height: 45px;">
                        @error('registro')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <label for="obs" class="form-label fw-bold text-muted small">Observações</label>
                        <textarea name="obs" id="obs" rows="4" class="form-control rounded-4 @error('obs') is-invalid @enderror" placeholder="Informações adicionais...">{{ old('obs', $batismo->obs) }}</textarea>
                        @error('obs')
                            <div class="invalid-feedback ps-3">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex justify-content-between gap-2 mt-4">
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-1"></i> Excluir
                        </button>
                        <div class="d-flex gap-2">
                            <a href="{{ route('batismos.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Alterações</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger" id="deleteModalLabel">Excluir Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-center text-muted">Tem certeza que deseja excluir este registro de batismo? Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('batismos.destroy', $batismo->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
