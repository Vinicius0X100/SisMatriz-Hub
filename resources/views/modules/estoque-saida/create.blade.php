@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Registrar Saída</h2>
            <p class="text-muted small mb-0">Baixa de item no estoque.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('estoque-saida.index') }}" class="text-decoration-none">Saída de Estoque</a></li>
                <li class="breadcrumb-item active" aria-current="page">Registrar</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('estoque-saida.store') }}" method="POST">
                        @csrf

                        <!-- Item Selection -->
                        <div class="mb-4">
                            <label for="s_id" class="form-label fw-bold small text-muted">Item do Estoque <span class="text-danger">*</span></label>
                            <select class="form-select form-select-lg rounded-pill bg-light border-0 px-4" id="s_id" name="s_id" required>
                                <option value="" selected disabled>Selecione um item...</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->s_id }}" data-max="{{ $item->qntd_destributed }}">
                                        {{ $item->description }} (Disp: {{ $item->qntd_destributed }})
                                    </option>
                                @endforeach
                            </select>
                            @error('s_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label for="qntd_distribuida" class="form-label fw-bold small text-muted">Quantidade <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="qntd_distribuida" name="qntd_distribuida" min="1" required>
                                <div id="stockHelp" class="form-text ms-3">Selecione um item para ver o limite.</div>
                                @error('qntd_distribuida') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Date -->
                            <div class="col-md-6">
                                <label for="data_saida" class="form-label fw-bold small text-muted">Data da Saída <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="data_saida" name="data_saida" value="{{ date('Y-m-d') }}" required>
                                @error('data_saida') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <!-- Retirado Por -->
                            <div class="col-md-6">
                                <label for="retirado_por" class="form-label fw-bold small text-muted">Retirado Por <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="retirado_por" name="retirado_por" placeholder="Nome do responsável" required>
                                @error('retirado_por') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <!-- Comunidade -->
                            <div class="col-md-6">
                                <label for="ent_id" class="form-label fw-bold small text-muted">Comunidade Destino <span class="text-danger">*</span></label>
                                <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="ent_id" name="ent_id" required>
                                    <option value="" selected disabled>Selecione...</option>
                                    @foreach($entidades as $ent)
                                        <option value="{{ $ent->ent_id }}">{{ $ent->ent_name }}</option>
                                    @endforeach
                                </select>
                                @error('ent_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('estoque-saida.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">
                                <i class="bi bi-check-lg me-2"></i> Confirmar Saída
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemSelect = document.getElementById('s_id');
        const qtdInput = document.getElementById('qntd_distribuida');
        const stockHelp = document.getElementById('stockHelp');

        itemSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const maxStock = selectedOption.getAttribute('data-max');
            
            if (maxStock) {
                qtdInput.max = maxStock;
                stockHelp.textContent = `Estoque disponível: ${maxStock}`;
                stockHelp.className = 'form-text ms-3 text-success fw-bold';
            } else {
                qtdInput.removeAttribute('max');
                stockHelp.textContent = '';
            }
        });
    });
</script>
@endsection
@endsection
