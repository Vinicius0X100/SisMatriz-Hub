@extends('layouts.app')

@section('title', 'Editar Lançamento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Lançamento</h2>
            <p class="text-muted mb-0">Atualize as informações do lançamento</p>
        </div>
        <a href="{{ route('ofertas.index') }}" class="btn btn-light text-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('ofertas.update', $oferta->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Data <span class="text-danger">*</span></label>
                        <input type="date" name="data" class="form-control rounded-3" value="{{ $oferta->data }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Horário (Opcional)</label>
                        <input type="time" name="horario" class="form-control rounded-3" value="{{ $oferta->horario }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">Comunidade <span class="text-danger">*</span></label>
                        <select name="ent_id" class="form-select rounded-3" required>
                            <option value="">Selecione...</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}" {{ $oferta->ent_id == $entidade->ent_id ? 'selected' : '' }}>{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small">Tipo de Lançamento <span class="text-danger">*</span></label>
                        <select name="kind" class="form-select rounded-3" required>
                            <option value="">Selecione...</option>
                            <option value="1" {{ $oferta->kind == 1 ? 'selected' : '' }}>Dízimo</option>
                            <option value="2" {{ $oferta->kind == 2 ? 'selected' : '' }}>Oferta</option>
                            <option value="3" {{ $oferta->kind == 3 ? 'selected' : '' }}>Moedas</option>
                            <option value="4" {{ $oferta->kind == 4 ? 'selected' : '' }}>Doação em Cofre</option>
                            <option value="5" {{ $oferta->kind == 5 ? 'selected' : '' }}>Bazares</option>
                            <option value="6" {{ $oferta->kind == 6 ? 'selected' : '' }}>Vendas (Valores esporádicos)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small">Valor (R$) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">R$</span>
                            <input type="text" name="valor_total" class="form-control rounded-3 border-start-0" value="{{ number_format($oferta->valor_total, 2, ',', '.') }}" required oninput="formatMoney(this)">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-muted small">Celebração / Evento</label>
                        <input type="text" name="tipo" class="form-control rounded-3" value="{{ $oferta->tipo }}">
                    </div>
                    
                    <div class="col-12">
                        <label class="form-label fw-bold text-muted small">Observações</label>
                        <textarea name="observacoes" class="form-control rounded-3" rows="3">{{ $oferta->observacoes }}</textarea>
                    </div>
                    
                    <div class="col-12 text-end mt-4">
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm" id="btnUpdate">
                            <i class="bi bi-check-lg me-2"></i>Atualizar Lançamento
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', function(e) {
            const btn = document.getElementById('btnUpdate');
            if (btn.disabled) {
                e.preventDefault();
                return;
            }
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
        });
    });

    function formatMoney(input) {
        let value = input.value.replace(/\D/g, "");
        if(value === "") {
            input.value = "";
            return;
        }
        value = (value / 100).toFixed(2) + "";
        value = value.replace(".", ",");
        value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        input.value = value;
    }
</script>
@endsection
