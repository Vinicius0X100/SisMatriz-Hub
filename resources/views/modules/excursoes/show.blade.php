@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Detalhes da Excursão</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('excursoes.index') }}" class="text-decoration-none">Excursões</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $excursao->destino }}</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Detalhes -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary mb-3">Informações</h5>
                    
                    <div class="mb-3">
                        <label class="small text-muted fw-bold text-uppercase">Destino</label>
                        <p class="fs-5 fw-bold text-dark mb-0">{{ $excursao->destino }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-bold text-uppercase">Tipo</label>
                        <p class="mb-0"><span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">{{ ucfirst($excursao->tipo) }}</span></p>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-bold text-uppercase">Status</label>
                        <p class="mb-0">
                            @if($excursao->finalizada)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Finalizada</span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativa</span>
                            @endif
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="small text-muted fw-bold text-uppercase">Descrição</label>
                        <p class="text-muted mb-0">{{ $excursao->descricao ?: 'Sem descrição.' }}</p>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('excursoes.edit', $excursao) }}" class="btn btn-outline-primary rounded-pill fw-bold">
                            <i class="bi bi-pencil me-2"></i> Editar Informações
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Ônibus -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-secondary">Ônibus e Transporte</h5>
                        <a href="{{ route('excursoes.onibus.create', $excursao) }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-plus-lg me-2"></i> Adicionar Ônibus
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-3 rounded-start-pill">Identificação</th>
                                    <th class="py-3">Capacidade</th>
                                    <th class="py-3">Responsável</th>
                                    <th class="py-3">Saída</th>
                                    <th class="py-3 text-end rounded-end-pill pe-3">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($excursao->onibus as $onibus)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-bus-front"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">Ônibus {{ $onibus->numero }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $onibus->capacidade }} lugares</td>
                                    <td>{{ $onibus->responsavel }}</td>
                                    <td>{{ $onibus->horario_saida ? $onibus->horario_saida->format('d/m H:i') : '-' }}</td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group">
                                            <a href="{{ route('excursoes.onibus.show', [$excursao, $onibus]) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1" title="Gerenciar Assentos">
                                                <i class="bi bi-grid-3x3-gap"></i>
                                            </a>
                                            <a href="{{ route('excursoes.onibus.manifesto', [$excursao, $onibus]) }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill me-1" title="Imprimir Lista">
                                                <i class="bi bi-file-earmark-spreadsheet"></i>
                                            </a>
                                            <a href="{{ route('excursoes.onibus.edit', [$excursao, $onibus]) }}" class="btn btn-sm btn-outline-secondary rounded-pill me-1" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" title="Excluir" onclick="confirmDeleteOnibus('{{ route('excursoes.onibus.destroy', [$excursao, $onibus]) }}', '{{ addslashes($onibus->numero) }}')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-bus-front display-4 opacity-25 mb-3"></i>
                                        <p class="mb-0">Nenhum ônibus cadastrado.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão de Ônibus -->
<div class="modal fade" id="deleteOnibusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Excluir Ônibus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block mb-3 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Tem certeza?</h4>
                <p class="text-muted mb-0">
                    Você está prestes a excluir o ônibus número <span id="deleteOnibusNumero" class="fw-bold text-dark"></span>.
                    <br>Esta ação removerá também todas as passagens vendidas para este ônibus.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteOnibusForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDeleteOnibus(url, numero) {
        const form = document.getElementById('deleteOnibusForm');
        const numeroSpan = document.getElementById('deleteOnibusNumero');
        
        form.action = url;
        numeroSpan.textContent = numero;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteOnibusModal'));
        modal.show();
    }
</script>
@endsection
