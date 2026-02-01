@extends('layouts.app')

@section('title', 'Novo Lançamento')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Novo Lançamento</h2>
            <p class="text-muted mb-0">Adicione ofertas, dízimos e outras entradas</p>
        </div>
        <a href="{{ route('ofertas.index') }}" class="btn btn-light text-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Voltar
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-0 px-4">
            <ul class="nav nav-pills nav-fill bg-light rounded-pill p-1" id="modeTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab" aria-selected="true">
                        <i class="bi bi-file-earmark-plus me-2"></i>Lançamento Individual
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill" id="multiple-tab" data-bs-toggle="tab" data-bs-target="#multiple" type="button" role="tab" aria-selected="false">
                        <i class="bi bi-collection me-2"></i>Lançamento em Lote (Múltiplo)
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-4">
            <div class="tab-content" id="modeTabContent">
                
                <!-- Single Mode -->
                <div class="tab-pane fade show active" id="single" role="tabpanel" aria-labelledby="single-tab">
                    <form action="{{ route('ofertas.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small">Data <span class="text-danger">*</span></label>
                                <input type="date" name="data" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small">Horário (Opcional)</label>
                                <input type="time" name="horario" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Comunidade <span class="text-danger">*</span></label>
                                <select name="ent_id" class="form-select rounded-3" required>
                                    <option value="">Selecione...</option>
                                    @foreach($entidades as $entidade)
                                        <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">Tipo de Lançamento <span class="text-danger">*</span></label>
                                <select name="kind" class="form-select rounded-3" required>
                                    <option value="">Selecione...</option>
                                    <option value="1">Dízimo</option>
                                    <option value="2">Oferta</option>
                                    <option value="3">Moedas</option>
                                    <option value="4">Doação em Cofre</option>
                                    <option value="5">Bazares</option>
                                    <option value="6">Vendas (Valores esporádicos)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">Valor (R$) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">R$</span>
                                    <input type="text" name="valor_total" class="form-control rounded-3 border-start-0" placeholder="0,00" required oninput="formatMoney(this)">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted small">Celebração / Evento</label>
                                <input type="text" name="tipo" class="form-control rounded-3" placeholder="Ex: Missa das 19h">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold text-muted small">Observações</label>
                                <textarea name="observacoes" class="form-control rounded-3" rows="3"></textarea>
                            </div>
                            
                            <div class="col-12 text-end mt-4">
                                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm" id="btnSaveSingle">
                                    <i class="bi bi-check-lg me-2"></i>Salvar Lançamento
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Multiple Mode -->
                <div class="tab-pane fade" id="multiple" role="tabpanel" aria-labelledby="multiple-tab">
                    <form id="bulkForm">
                        @csrf
                        <div class="row g-3 mb-4 p-3 bg-light rounded-4 border">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small">Data <span class="text-danger">*</span></label>
                                <input type="date" id="bulk_data" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-muted small">Horário (Opcional)</label>
                                <input type="time" id="bulk_horario" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-muted small">Comunidade <span class="text-danger">*</span></label>
                                <select id="bulk_ent_id" class="form-select rounded-3" required>
                                    <option value="">Selecione...</option>
                                    @foreach($entidades as $entidade)
                                        <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> Data, horário e comunidade serão aplicados a todos os itens abaixo.
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered align-middle" id="bulkTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 20%;">Tipo <span class="text-danger">*</span></th>
                                        <th style="width: 20%;">Valor (R$) <span class="text-danger">*</span></th>
                                        <th style="width: 25%;">Celebração</th>
                                        <th>Observações</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="bulkBody">
                                    <!-- Rows will be added here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick="addBulkRow()">
                                <i class="bi bi-plus-lg me-2"></i>Adicionar Linha
                            </button>
                            <div class="h5 mb-0 fw-bold text-primary">
                                Total: R$ <span id="totalDisplay">0,00</span>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm" id="btnSaveBulk">
                                <i class="bi bi-check-all me-2"></i>Salvar Todos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="rowTemplate">
    <tr class="item-row">
        <td>
            <select class="form-select form-select-sm rounded-3 item-kind" required>
                <option value="">Selecione...</option>
                <option value="1">Dízimo</option>
                <option value="2">Oferta</option>
                <option value="3">Moedas</option>
                <option value="4">Doação em Cofre</option>
                <option value="5">Bazares</option>
                <option value="6">Vendas</option>
            </select>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0">R$</span>
                <input type="text" class="form-control rounded-3 border-start-0 item-value" placeholder="0,00" required oninput="formatMoney(this); updateTotal()">
            </div>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm rounded-3 item-tipo" placeholder="Ex: Missa 19h">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm rounded-3 item-obs" placeholder="Opcional">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle" onclick="removeRow(this)">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        addBulkRow(); // Add first row initially
        
        // Handle Single Submit
        const singleForm = document.querySelector('form[action="{{ route("ofertas.store") }}"]');
        if (singleForm) {
            singleForm.addEventListener('submit', function(e) {
                const btn = document.getElementById('btnSaveSingle');
                if (btn.disabled) {
                    e.preventDefault();
                    return;
                }
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';
            });
        }

        // Handle Bulk Submit
        document.getElementById('bulkForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('btnSaveBulk');
            if (btn.disabled) return;

            const originalText = btn.innerHTML;
            
            // Validate Header
            const data = document.getElementById('bulk_data').value;
            const horario = document.getElementById('bulk_horario').value;
            const entId = document.getElementById('bulk_ent_id').value;
            
            if (!data || !entId) {
                alert('Por favor, preencha a Data e a Comunidade.');
                return;
            }

            // Collect Items
            const items = [];
            document.querySelectorAll('.item-row').forEach(row => {
                const kind = row.querySelector('.item-kind').value;
                const valorRaw = row.querySelector('.item-value').value;
                const tipo = row.querySelector('.item-tipo').value;
                const obs = row.querySelector('.item-obs').value;
                
                if (kind && valorRaw) {
                    items.push({
                        kind: kind,
                        valor_total: valorRaw, // Send as string, backend handles conversion
                        tipo: tipo,
                        observacoes: obs
                    });
                }
            });

            if (items.length === 0) {
                alert('Adicione pelo menos um item válido.');
                return;
            }

            // Send Request
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Salvando...';

            fetch('{{ route("ofertas.bulk-store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    data: data,
                    horario: horario,
                    ent_id: entId,
                    items: items
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '{{ route("ofertas.index") }}';
                } else {
                    alert(data.message || 'Erro ao salvar.');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro na requisição.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
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

    function parseMoney(value) {
        if (!value) return 0;
        return parseFloat(value.replace(/\./g, '').replace(',', '.')) || 0;
    }

    function addBulkRow() {
        const template = document.getElementById('rowTemplate');
        const clone = template.content.cloneNode(true);
        document.getElementById('bulkBody').appendChild(clone);
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            updateTotal();
        } else {
            // Clear inputs if it's the last row
            row.querySelectorAll('input, select').forEach(input => input.value = '');
            updateTotal();
        }
    }

    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.item-value').forEach(input => {
            total += parseMoney(input.value);
        });
        document.getElementById('totalDisplay').textContent = total.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
</script>
@endsection
