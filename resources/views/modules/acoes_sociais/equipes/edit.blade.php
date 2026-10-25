@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Equipe de Ações Sociais</h2>
            <p class="text-muted small mb-0">Atualize os dados e a composição de membros da equipe.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acoes-sociais.equipes.index') }}" class="text-decoration-none">Equipes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
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

            <form action="{{ route('acoes-sociais.equipes.update', $equipe->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <div class="col-md-7">
                        <label class="form-label fw-bold small text-muted">Nome da Equipe / Pastoral <span class="text-danger">*</span></label>
                        <input type="text" name="nome" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('nome') is-invalid @enderror" value="{{ old('nome', $equipe->nome) }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small text-muted">Tipo de Pastoral / Organização <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select rounded-pill bg-light border-0 px-4 py-2 @error('tipo') is-invalid @enderror" required>
                            <option value="geral" {{ old('tipo', $equipe->tipo) == 'geral' ? 'selected' : '' }}>Geral / Assistência Social Paroquial</option>
                            <option value="promocao_humana" {{ old('tipo', $equipe->tipo) == 'promocao_humana' ? 'selected' : '' }}>Pastoral da Promoção Humana</option>
                            <option value="pastoral_social" {{ old('tipo', $equipe->tipo) == 'pastoral_social' ? 'selected' : '' }}>Pastoral Social</option>
                            <option value="vicentinos" {{ old('tipo', $equipe->tipo) == 'vicentinos' ? 'selected' : '' }}>Sociedade de São Vicente de Paulo (Vicentinos - SSVP)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Descrição / Finalidade</label>
                        <textarea name="descricao" class="form-control rounded-4 bg-light border-0 p-3" rows="3">{{ old('descricao', $equipe->descricao) }}</textarea>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="ativoSwitch" name="ativo" value="1" {{ old('ativo', $equipe->ativo) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold small text-dark" for="ativoSwitch">Equipe Ativa</label>
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <h5 class="fw-bold text-dark mb-1">Membros e Funções na Equipe</h5>
                <p class="text-muted small mb-3">Defina quais paroquianos participam desta equipe e atribua os papéis de Coordenador ou Agente de Ações Sociais.</p>

                @php
                    $vinculadosMap = $equipeUsers->keyBy('id');
                @endphp

                <div class="table-responsive rounded-4 border bg-white mb-4" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold" style="width: 50px;">Membro</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">Nome</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold">E-mail</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold text-end" style="width: 200px;">Função na Equipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allParoquianos as $p)
                                @php
                                    $isMember = $vinculadosMap->has($p->id);
                                    $funcao = $isMember ? ($vinculadosMap[$p->id]->pivot->funcao ?? 'agente') : 'agente';
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="form-check">
                                            <input class="form-check-input member-checkbox" type="checkbox" name="membros[{{ $p->id }}][selected]" value="1" id="p_{{ $p->id }}" {{ $isMember ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <label class="form-check-label fw-bold text-dark cursor-pointer mb-0" for="p_{{ $p->id }}">
                                            {{ $p->name }}
                                        </label>
                                    </td>
                                    <td class="text-muted small">{{ $p->email }}</td>
                                    <td class="pe-4 text-end">
                                        <select name="membros[{{ $p->id }}][funcao]" class="form-select form-select-sm rounded-pill bg-light border-0">
                                            <option value="agente" {{ $funcao == 'agente' ? 'selected' : '' }}>Agente</option>
                                            <option value="coordenador" {{ $funcao == 'coordenador' ? 'selected' : '' }}>Coordenador(a)</option>
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('acoes-sociais.equipes.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Atualizar Equipe</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
