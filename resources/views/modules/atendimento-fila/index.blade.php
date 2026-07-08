@extends('layouts.app')

@section('title', 'Fila de Atendimento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Fila de Atendimento</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fila de Atendimento</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div><strong>Sucesso!</strong> {{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div><strong>Erro!</strong> {{ session('error') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Ação principal -->
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('atendimento-fila.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nova Fila
        </a>
    </div>

    <!-- Tabela de filas -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            @if($filas->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 mb-3 d-block"></i>
                    <p class="mb-0">Nenhuma fila criada ainda.</p>
                    <a href="{{ route('atendimento-fila.create') }}" class="btn btn-sm btn-primary mt-3">Criar primeira fila</a>
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Data</th>
                            <th>Status</th>
                            <th>Pessoas na fila</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($filas as $fila)
                        <tr>
                            <td class="ps-4 fw-semibold">
                                {{ $fila->data->format('d/m/Y') }}
                                @if($fila->data->isToday())
                                    <span class="badge bg-primary ms-2">Hoje</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $statusClass = match($fila->status) {
                                        0 => 'secondary',
                                        1 => 'success',
                                        2 => 'dark',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}">{{ $fila->status_label }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $fila->itens_count }}</span>
                                <span class="text-muted">pessoa(s)</span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('atendimento-fila.show', $fila->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Gerenciar fila">
                                    <i class="bi bi-list-ul"></i>
                                </a>
                                @if($fila->status === \App\Models\AtendimentoFila::STATUS_ATIVA)
                                <a href="{{ route('atendimento-fila.painel.fila', $fila->id) }}" class="btn btn-sm btn-outline-success me-1" title="Abrir painel do padre" target="_blank">
                                    <i class="bi bi-display"></i>
                                </a>
                                @endif
                                <form id="formExcluirFila{{$fila->id}}" action="{{ route('atendimento-fila.destroy', $fila->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Excluir fila" onclick="abrirConfirmacaoGenerica('formExcluirFila{{$fila->id}}', 'Excluir Fila', 'Tem certeza que deseja excluir a fila do dia <b>{{ $fila->data->format('d/m/Y') }}</b> e todos os seus registros? Isso não pode ser desfeito.', 'danger')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $filas->links() }}
            </div>
            @endif
        </div>
        </div>
    </div>
</div>

<!-- Modal Confirmacao Generica -->
<div class="modal fade" id="modalConfirmacaoGenerica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div id="confirmGenericIcon" class="mt-3 mb-2 text-danger">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                </div>
            </div>
            <div class="modal-body pt-0 text-center px-4">
                <h5 class="fw-bold text-dark mb-3" id="confirmGenericTitle">Confirmar</h5>
                <p class="mb-4 text-muted" id="confirmGenericMessage">Tem certeza?</p>
                
                <div class="d-flex flex-column gap-2">
                    <button type="button" id="confirmGenericBtn" class="btn btn-danger w-100 rounded-pill py-2">Sim</button>
                    <button type="button" class="btn btn-light w-100 rounded-pill py-2 text-muted fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFormIdToSubmit = null;
    
    window.abrirConfirmacaoGenerica = function(formId, titulo, mensagem, corBtn) {
        currentFormIdToSubmit = formId;
        
        document.getElementById('confirmGenericTitle').textContent = titulo;
        document.getElementById('confirmGenericMessage').innerHTML = mensagem;
        
        const btn = document.getElementById('confirmGenericBtn');
        btn.className = `btn btn-${corBtn} w-100 rounded-pill py-2`;
        
        const icon = document.getElementById('confirmGenericIcon');
        icon.className = `mt-3 mb-2 text-${corBtn}`;
        
        new bootstrap.Modal(document.getElementById('modalConfirmacaoGenerica')).show();
    };

    document.getElementById('confirmGenericBtn').addEventListener('click', function() {
        if (currentFormIdToSubmit) {
            document.getElementById(currentFormIdToSubmit).submit();
        }
    });
});
</script>
@endpush
