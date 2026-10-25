@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Nova Equipe de Ações Sociais</h2>
            <p class="text-muted small mb-0">Cadastre uma nova equipe ou pastoral de assistência social na paróquia.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acoes-sociais.equipes.index') }}" class="text-decoration-none">Equipes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova Equipe</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção!</strong> Verifique os erros no formulário e tente novamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('acoes-sociais.equipes.store') }}" method="POST">
                @csrf

                <div class="row g-4 mb-4">
                    <div class="col-md-7">
                        <label class="form-label fw-bold small text-muted">Nome da Equipe / Pastoral <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('nome') is-invalid @enderror" placeholder="Ex: Pastoral da Promoção Humana, Vicentinos..." value="{{ old('nome') }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small text-muted">Tipo de Pastoral / Organização <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select rounded-pill bg-light border-0 px-4 py-2 @error('tipo') is-invalid @enderror" required>
                            <option value="geral" {{ old('tipo') == 'geral' ? 'selected' : '' }}>Geral / Assistência Social Paroquial</option>
                            <option value="promocao_humana" {{ old('tipo') == 'promocao_humana' ? 'selected' : '' }}>Pastoral da Promoção Humana</option>
                            <option value="pastoral_social" {{ old('tipo') == 'pastoral_social' ? 'selected' : '' }}>Pastoral Social</option>
                            <option value="vicentinos" {{ old('tipo') == 'vicentinos' ? 'selected' : '' }}>Sociedade de São Vicente de Paulo (Vicentinos - SSVP)</option>
                        </select>
                        <small class="text-muted d-block mt-1">Selecione "Vicentinos" caso a equipe utilize Conferência e Conselho Particular.</small>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Descrição / Finalidade</label>
                        <textarea name="descricao" class="form-control rounded-4 bg-light border-0 p-3" rows="3" placeholder="Informações adicionais sobre o grupo, setor de atuação ou paróquia...">{{ old('descricao') }}</textarea>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="ativoSwitch" name="ativo" value="1" checked>
                        <label class="form-check-label fw-bold small text-dark" for="ativoSwitch">Equipe Ativa</label>
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <h5 class="fw-bold text-dark mb-3">Vincular Agentes e Membros</h5>
                <p class="text-muted small mb-3">Selecione os usuários que poderão registrar e visualizar atendimentos desta equipe.</p>

                <div class="card border-0 bg-light rounded-4 p-3 mb-4" style="max-height: 250px; overflow-y: auto;">
                    <div class="row g-2">
                        @foreach($paroquianos as $p)
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check p-2 bg-white rounded-3 border border-light">
                                    <input class="form-check-input ms-1" type="checkbox" name="users[]" value="{{ $p->id }}" id="user_{{ $p->id }}" {{ in_array($p->id, old('users', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label ms-2 small fw-semibold text-dark" for="user_{{ $p->id }}">
                                        {{ $p->name }}
                                        <span class="d-block text-muted" style="font-size: 0.75rem;">{{ $p->email }}</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('acoes-sociais.equipes.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Equipe</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
