@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Editar Nota Fiscal</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('notas-fiscais.index') }}" class="text-decoration-none">Notas Fiscais</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('notas-fiscais.update', $notaFiscal->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Seção 1: Dados Básicos -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Informações Básicas</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="tipo" class="form-label fw-bold small text-muted">Tipo de Documento <span class="text-danger">*</span></label>
                                <select class="form-select rounded-pill @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                    <option value="NFe" {{ old('tipo', $notaFiscal->tipo) == 'NFe' ? 'selected' : '' }}>Nota Fiscal Eletrônica (NFe)</option>
                                    <option value="NFCe" {{ old('tipo', $notaFiscal->tipo) == 'NFCe' ? 'selected' : '' }}>Nota Fiscal de Consumidor (NFCe)</option>
                                    <option value="NFSe" {{ old('tipo', $notaFiscal->tipo) == 'NFSe' ? 'selected' : '' }}>Nota Fiscal de Serviço (NFSe)</option>
                                    <option value="Boleto" {{ old('tipo', $notaFiscal->tipo) == 'Boleto' ? 'selected' : '' }}>Boleto</option>
                                    <option value="Recibo" {{ old('tipo', $notaFiscal->tipo) == 'Recibo' ? 'selected' : '' }}>Recibo</option>
                                    <option value="Cupom" {{ old('tipo', $notaFiscal->tipo) == 'Cupom' ? 'selected' : '' }}>Cupom Fiscal</option>
                                    <option value="Outro" {{ old('tipo', $notaFiscal->tipo) == 'Outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                                @error('tipo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="numero" class="form-label fw-bold small text-muted">Número <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-pill @error('numero') is-invalid @enderror" id="numero" name="numero" value="{{ old('numero', $notaFiscal->numero) }}" required>
                                @error('numero')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="serie" class="form-label fw-bold small text-muted">Série</label>
                                <input type="text" class="form-control rounded-pill @error('serie') is-invalid @enderror" id="serie" name="serie" value="{{ old('serie', $notaFiscal->serie) }}">
                                @error('serie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="chave_acesso" class="form-label fw-bold small text-muted">Chave de Acesso (44 dígitos)</label>
                                <input type="text" class="form-control rounded-pill @error('chave_acesso') is-invalid @enderror" id="chave_acesso" name="chave_acesso" value="{{ old('chave_acesso', $notaFiscal->chave_acesso) }}" maxlength="44" placeholder="Apenas números">
                                @error('chave_acesso')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12"><hr class="text-muted opacity-25"></div>

                    <!-- Seção 2: Emitente e Datas -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-badge me-2"></i>Emitente e Datas</h6>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label for="emitente_nome" class="form-label fw-bold small text-muted">Nome / Razão Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-pill @error('emitente_nome') is-invalid @enderror" id="emitente_nome" name="emitente_nome" value="{{ old('emitente_nome', $notaFiscal->emitente_nome) }}" required>
                                @error('emitente_nome')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="emitente_documento" class="form-label fw-bold small text-muted">CNPJ / CPF</label>
                                <input type="text" class="form-control rounded-pill @error('emitente_documento') is-invalid @enderror" id="emitente_documento" name="emitente_documento" value="{{ old('emitente_documento', $notaFiscal->emitente_documento) }}">
                                @error('emitente_documento')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="data_emissao" class="form-label fw-bold small text-muted">Data de Emissão <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-pill @error('data_emissao') is-invalid @enderror" id="data_emissao" name="data_emissao" value="{{ old('data_emissao', $notaFiscal->data_emissao->format('Y-m-d')) }}" required>
                                @error('data_emissao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2">
                                <label for="data_entrada" class="form-label fw-bold small text-muted">Data de Entrada</label>
                                <input type="date" class="form-control rounded-pill @error('data_entrada') is-invalid @enderror" id="data_entrada" name="data_entrada" value="{{ old('data_entrada', optional($notaFiscal->data_entrada)->format('Y-m-d')) }}">
                                @error('data_entrada')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12"><hr class="text-muted opacity-25"></div>

                    <!-- Seção 3: Valores e Detalhes -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash-coin me-2"></i>Valores e Detalhes</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="valor_total" class="form-label fw-bold small text-muted">Valor Total (R$) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-pill money-mask @error('valor_total') is-invalid @enderror" id="valor_total" name="valor_total" value="{{ old('valor_total', $notaFiscal->valor_total) }}" required placeholder="R$ 0,00">
                                @error('valor_total')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="valor_desconto" class="form-label fw-bold small text-muted">Desconto (R$)</label>
                                <input type="text" class="form-control rounded-pill money-mask @error('valor_desconto') is-invalid @enderror" id="valor_desconto" name="valor_desconto" value="{{ old('valor_desconto', $notaFiscal->valor_desconto) }}" placeholder="R$ 0,00">
                                @error('valor_desconto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="valor_acrescimo" class="form-label fw-bold small text-muted">Acréscimo/Frete (R$)</label>
                                <input type="text" class="form-control rounded-pill money-mask @error('valor_acrescimo') is-invalid @enderror" id="valor_acrescimo" name="valor_acrescimo" value="{{ old('valor_acrescimo', $notaFiscal->valor_acrescimo) }}" placeholder="R$ 0,00">
                                @error('valor_acrescimo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="entidade_id" class="form-label fw-bold small text-muted">Vincular a Comunidade</label>
                                <select class="form-select rounded-pill @error('entidade_id') is-invalid @enderror" id="entidade_id" name="entidade_id">
                                    <option value="">Nenhuma (Geral)</option>
                                    @foreach($entidades as $entidade)
                                        <option value="{{ $entidade->ent_id }}" {{ old('entidade_id', $notaFiscal->entidade_id) == $entidade->ent_id ? 'selected' : '' }}>
                                            {{ $entidade->ent_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('entidade_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="descricao" class="form-label fw-bold small text-muted">Descrição / Observações</label>
                                <textarea class="form-control rounded-4 @error('descricao') is-invalid @enderror" id="descricao" name="descricao" rows="3">{{ old('descricao', $notaFiscal->descricao) }}</textarea>
                                @error('descricao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12"><hr class="text-muted opacity-25"></div>

                    <!-- Seção 4: Arquivo -->
                    <div class="col-12">
                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-paperclip me-2"></i>Anexo (PDF/XML/Imagem)</h6>
                        
                        @if($notaFiscal->caminho_arquivo)
                            <div class="alert alert-light border d-flex align-items-center mb-3">
                                <i class="bi bi-file-earmark-text fs-4 me-3 text-primary"></i>
                                <div class="flex-grow-1">
                                    <strong>Arquivo Atual:</strong> {{ basename($notaFiscal->caminho_arquivo) }}
                                </div>
                                <a href="{{ route('notas-fiscais.download', $notaFiscal->id) }}" class="btn btn-sm btn-outline-primary rounded-pill me-2">
                                    <i class="bi bi-download me-1"></i> Baixar
                                </a>
                            </div>
                        @endif

                        <div class="input-group">
                            <input type="file" class="form-control rounded-pill @error('arquivo') is-invalid @enderror" id="arquivo" name="arquivo" accept=".pdf,.xml,.jpg,.jpeg,.png">
                            <label class="input-group-text rounded-pill-end" for="arquivo">Substituir</label>
                        </div>
                        <div class="form-text ms-2">Formatos permitidos: PDF, XML, JPG, PNG. Tamanho máximo: 10MB.</div>
                        @error('arquivo')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Botões -->
                    <div class="col-12 d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('notas-fiscais.index') }}" class="btn btn-light rounded-pill px-4 border">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const moneyMasks = document.querySelectorAll('.money-mask');
    
    moneyMasks.forEach(input => {
        // Formata ao digitar
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value === '') {
                e.target.value = '';
                return;
            }
            value = (parseInt(value) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            e.target.value = value;
        });

        // Formata valor inicial (se houver, vindo do old())
        if (input.value) {
            let val = input.value;
            // Se não tiver R$, formata
            if (!val.includes('R$')) {
                // Se for um float 1000.00
                if (val.includes('.')) {
                     val = parseFloat(val).toFixed(2).replace('.', '');
                } else {
                     val = val + '00';
                }
                val = (parseInt(val) / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                input.value = val;
            }
        }
    });
});
</script>
@endsection
