@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Ações Sociais — Equipes e Pastorais</h2>
            <p class="text-muted small mb-0">Gerencie as equipes de assistência social e seus respectivos agentes e coordenadores.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acoes-sociais.familias.index') }}" class="text-decoration-none">Ações Sociais</a></li>
                <li class="breadcrumb-item active" aria-current="page">Equipes</li>
            </ol>
        </nav>
    </div>

    <!-- Sub-menu e Contexto -->
    @include('modules.acoes_sociais.partials.team_context_header')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">Equipes Cadastradas</h5>
                    <p class="text-muted small mb-0">Cada equipe opera de forma independente, com seu próprio histórico de famílias e atendimentos.</p>
                </div>
                @if(Auth::user()->hasAnyRole(['1', '111']))
                    <a href="{{ route('acoes-sociais.equipes.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="bi bi-plus-lg me-2"></i> Nova Equipe
                    </a>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start ps-4 py-3 text-secondary text-uppercase small fw-bold">Nome da Equipe</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold">Tipo</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold text-center">Famílias</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold text-center">Atendimentos</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold text-center">Agentes</th>
                            <th class="border-0 py-3 text-secondary text-uppercase small fw-bold text-center">Status</th>
                            <th class="border-0 rounded-end pe-4 py-3 text-secondary text-uppercase small fw-bold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipes as $eq)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $eq->nome }}</div>
                                    @if($eq->descricao)
                                        <small class="text-muted text-truncate d-block" style="max-width: 350px;">{{ $eq->descricao }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $tipos = [
                                            'vicentinos' => ['label' => 'Vicentinos (SSVP)', 'class' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25'],
                                            'promocao_humana' => ['label' => 'Promoção Humana', 'class' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25'],
                                            'pastoral_social' => ['label' => 'Pastoral Social', 'class' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25'],
                                            'geral' => ['label' => 'Geral / Outros', 'class' => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25'],
                                        ];
                                        $tipoInfo = $tipos[$eq->tipo] ?? $tipos['geral'];
                                    @endphp
                                    <span class="badge {{ $tipoInfo['class'] }} rounded-pill px-3 py-1 fw-semibold">
                                        {{ $tipoInfo['label'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark rounded-pill px-3 py-1">{{ $eq->familias_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark rounded-pill px-3 py-1">{{ $eq->atendimentos_count }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark rounded-pill px-3 py-1">{{ $eq->users_count }}</span>
                                </td>
                                <td class="text-center">
                                    @if($eq->ativo)
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-semibold">Ativa</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1 rounded-pill fw-semibold">Inativa</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('acoes-sociais.equipes.edit', $eq->id) }}" class="btn btn-sm btn-light text-primary rounded-circle shadow-sm" title="Editar Equipe e Membros">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if(Auth::user()->hasAnyRole(['1', '111']))
                                            <form action="{{ route('acoes-sociais.equipes.toggle-status', $eq->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja alterar o status desta equipe?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-light {{ $eq->ativo ? 'text-warning' : 'text-success' }} rounded-circle shadow-sm" title="{{ $eq->ativo ? 'Desativar Equipe' : 'Ativar Equipe' }}">
                                                    <i class="bi {{ $eq->ativo ? 'bi-pause-fill' : 'bi-play-fill' }}"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-6 mb-3 d-block opacity-25"></i>
                                    Nenhuma equipe cadastrada nesta paróquia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
