@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Gerenciar Assentos - Ônibus {{ $onibus->numero }}</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('excursoes.index') }}" class="text-decoration-none">Excursões</a></li>
                <li class="breadcrumb-item"><a href="{{ route('excursoes.show', $excursao) }}" class="text-decoration-none">{{ $excursao->destino }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ônibus {{ $onibus->numero }}</li>
            </ol>
        </nav>
    </div>

    <div class="d-flex gap-2 mb-4">
        <a href="{{ route('excursoes.onibus.passagens-recorte', [$excursao, $onibus]) }}" target="_blank" class="btn btn-outline-secondary rounded-pill shadow-sm">
            <i class="bi bi-scissors me-2"></i>Imprimir Bilhetes (Recorte)
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Mapa de Assentos -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary mb-4">Mapa de Assentos</h5>
                    
                    <div class="row row-cols-2 row-cols-md-4 row-cols-lg-4 g-3">
                        @for($i = 1; $i <= $onibus->capacidade; $i++)
                            @php
                                $assento = $seats[$i] ?? null;
                            @endphp
                            <div class="col">
                                <div class="card h-100 border-0 shadow-sm {{ $assento ? 'bg-danger bg-opacity-10' : 'bg-light' }}">
                                    <div class="card-body text-center p-2">
                                        <h5 class="fw-bold mb-1 {{ $assento ? 'text-danger' : 'text-muted' }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</h5>
                                        
                                        @if($assento)
                                            <small class="d-block text-truncate fw-bold text-dark mb-1" title="{{ $assento->passageiro_nome }}">
                                                {{ explode(' ', $assento->passageiro_nome)[0] }}
                                            </small>
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#viewSeatModal{{ $i }}">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <a href="{{ route('excursoes.onibus.passagens.show', [$excursao, $onibus, $assento]) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-circle" title="Imprimir Passagem">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <button type="button" class="btn btn-xs btn-outline-danger rounded-circle" onclick="confirmDelete('{{ route('excursoes.onibus.assentos.destroy', [$excursao, $onibus, $assento]) }}', '{{ $assento->poltrona }}', '{{ addslashes($assento->passageiro_nome) }}')">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        @else
                                            <small class="d-block text-muted mb-1">Livre</small>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100" data-bs-toggle="modal" data-bs-target="#sellSeatModal{{ $i }}">
                                                Vender
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Vender Assento -->
                            @if(!$assento)
                            <div class="modal fade" id="sellSeatModal{{ $i }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title fw-bold">Vender Assento {{ $i }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if($errors->any())
                                                <div class="alert alert-danger rounded-4 shadow-sm">
                                                    <ul class="mb-0 small">
                                                        @foreach($errors->all() as $error)
                                                            <li>{{ $error }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                            
                                            <form action="{{ route('excursoes.onibus.assentos.store', [$excursao, $onibus]) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="poltrona" value="{{ $i }}">
                                                
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-muted">Nome do Passageiro</label>
                                                    <input type="text" class="form-control rounded-pill" name="passageiro_nome" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-muted">RG</label>
                                                    <input type="text" class="form-control rounded-pill" name="passageiro_rg">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-muted">Telefone</label>
                                                    <input type="text" class="form-control rounded-pill" name="passageiro_telefone">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-muted">Posição</label>
                                                    <select class="form-select rounded-pill" name="posicao" required>
                                                        <option value="janela">Janela</option>
                                                        <option value="corredor">Corredor</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" name="menor" id="menor{{ $i }}" value="1" onchange="toggleResponsavel(this, {{ $i }})">
                                                    <label class="form-check-label" for="menor{{ $i }}">Menor de Idade</label>
                                                </div>

                                                <div id="responsavel-container-{{ $i }}" class="d-none border rounded-3 p-3 mb-3 bg-light">
                                                    <h6 class="fw-bold small text-muted mb-3">Dados do Responsável</h6>
                                                    <div class="mb-2">
                                                        <label class="form-label small text-muted mb-1">Nome</label>
                                                        <input type="text" class="form-control form-control-sm rounded-pill" name="responsavel_nome">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label small text-muted mb-1">RG</label>
                                                        <input type="text" class="form-control form-control-sm rounded-pill" name="responsavel_rg">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label small text-muted mb-1">Telefone</label>
                                                        <input type="text" class="form-control form-control-sm rounded-pill" name="responsavel_telefone">
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small text-muted">Embarque</label>
                                                    <div class="d-flex gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="embarque_ida" id="embarque_ida{{ $i }}" value="1" checked>
                                                            <label class="form-check-label" for="embarque_ida{{ $i }}">Ida</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="embarque_volta" id="embarque_volta{{ $i }}" value="1" checked>
                                                            <label class="form-check-label" for="embarque_volta{{ $i }}">Volta</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary rounded-pill fw-bold">Confirmar Venda</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                            <!-- Modal Visualizar Assento -->
                            <div class="modal fade" id="viewSeatModal{{ $i }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold ps-2">Detalhes do Assento {{ $i }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <!-- Dados do Passageiro -->
                                            <div class="mb-4">
                                                <h6 class="fw-bold text-secondary text-uppercase small mb-3">Dados do Passageiro</h6>
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3 text-primary">
                                                        <i class="bi bi-person-fill fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Nome Completo</small>
                                                        <span class="fw-bold text-dark">{{ $assento->passageiro_nome }}</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-card-heading text-secondary me-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">RG</small>
                                                                <span class="fw-medium">{{ $assento->passageiro_rg ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-telephone text-secondary me-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Telefone</small>
                                                                <span class="fw-medium">{{ $assento->passageiro_telefone ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Detalhes da Viagem -->
                                            <div class="mb-4">
                                                <h6 class="fw-bold text-secondary text-uppercase small mb-3">Detalhes da Viagem</h6>
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                         <div class="p-2 border rounded-3 bg-light text-center h-100 d-flex flex-column justify-content-center">
                                                            <small class="text-muted d-block mb-1">Posição</small>
                                                            <span class="badge bg-white text-dark border shadow-sm text-uppercase">
                                                                {{ ucfirst($assento->posicao ?? 'N/A') }}
                                                            </span>
                                                         </div>
                                                    </div>
                                                    <div class="col-6">
                                                         <div class="p-2 border rounded-3 bg-light text-center h-100 d-flex flex-column justify-content-center">
                                                            <small class="text-muted d-block mb-1">Tipo</small>
                                                            @if($assento->menor)
                                                                <span class="badge bg-warning text-dark">Menor de Idade</span>
                                                            @else
                                                                <span class="badge bg-secondary">Adulto</span>
                                                            @endif
                                                         </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="d-flex gap-2">
                                                            <span class="badge {{ $assento->embarque_ida ? 'bg-success' : 'bg-light text-muted border' }} flex-fill py-2">
                                                                <i class="bi bi-check-circle me-1"></i> Embarque Ida
                                                            </span>
                                                            <span class="badge {{ $assento->embarque_volta ? 'bg-success' : 'bg-light text-muted border' }} flex-fill py-2">
                                                                <i class="bi bi-check-circle me-1"></i> Embarque Volta
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Dados do Responsável (Se for menor) -->
                                            @if($assento->menor)
                                            <div class="mt-4 pt-3 border-top">
                                                <h6 class="fw-bold text-secondary text-uppercase small mb-3">Responsável</h6>
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="bg-warning bg-opacity-10 rounded-circle p-2 me-3 text-warning">
                                                        <i class="bi bi-shield-check fs-4"></i>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Nome do Responsável</small>
                                                        <span class="fw-bold text-dark">{{ $assento->responsavel_nome }}</span>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-card-heading text-secondary me-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">RG</small>
                                                                <span class="fw-medium">{{ $assento->responsavel_rg ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-telephone text-secondary me-2"></i>
                                                            <div>
                                                                <small class="text-muted d-block" style="font-size: 0.75rem;">Telefone</small>
                                                                <span class="fw-medium">{{ $assento->responsavel_telefone ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumo -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-secondary mb-3">Resumo</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                            Capacidade Total
                            <span class="badge bg-primary rounded-pill">{{ $onibus->capacidade }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                            Vendidos
                            <span class="badge bg-danger rounded-pill">{{ $onibus->assentosVendidos->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent px-0">
                            Livres
                            <span class="badge bg-success rounded-pill">{{ $onibus->capacidade - $onibus->assentosVendidos->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Exclusão -->
<div class="modal fade" id="deleteSeatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Liberar Assento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="bg-danger bg-opacity-10 rounded-circle p-3 d-inline-block mb-3 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <h4 class="fw-bold mb-2">Tem certeza?</h4>
                <p class="text-muted mb-0">
                    Você está prestes a liberar o assento <span id="deleteSeatNumber" class="fw-bold text-dark"></span> 
                    de <span id="deleteSeatPassenger" class="fw-bold text-dark"></span>.
                    <br>Esta ação não pode ser desfeita.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteSeatForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Sim, Liberar Assento</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(url, seatNumber, passengerName) {
        const form = document.getElementById('deleteSeatForm');
        const seatSpan = document.getElementById('deleteSeatNumber');
        const passengerSpan = document.getElementById('deleteSeatPassenger');
        
        form.action = url;
        seatSpan.textContent = seatNumber;
        passengerSpan.textContent = passengerName;
        
        const modal = new bootstrap.Modal(document.getElementById('deleteSeatModal'));
        modal.show();
    }

    function toggleResponsavel(checkbox, index) {
        const container = document.getElementById(`responsavel-container-${index}`);
        const inputs = container.querySelectorAll('input');
        
        if (checkbox.checked) {
            container.classList.remove('d-none');
            inputs.forEach(input => input.required = true);
        } else {
            container.classList.add('d-none');
            inputs.forEach(input => input.required = false);
        }
    }

    // Reopen modal if there are errors
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            var oldPoltrona = "{{ old('poltrona') }}";
            if (oldPoltrona) {
                var modalId = '#sellSeatModal' + oldPoltrona;
                var modal = new bootstrap.Modal(document.querySelector(modalId));
                modal.show();
            }
        });
    @endif
</script>
@endsection
