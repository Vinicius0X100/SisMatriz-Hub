@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Ficha Vicentina</h2>
            <p class="text-muted small mb-0">Atualize os dados da família assistida.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vicentinos.index') }}" class="text-decoration-none">Registros Vicentinos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar Ficha</li>
            </ol>
        </nav>
    </div>

    <form action="{{ route('vicentinos.update', $record->id) }}" method="POST">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Atenção!</strong> Verifique os erros abaixo e tente novamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Dados Iniciais -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Informações Iniciais</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Data da Ficha</label>
                        <input type="date" name="data_ficha" class="form-control" value="{{ old('data_ficha', $record->data_ficha ? $record->data_ficha->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Conferência</label>
                        <input type="text" name="conferencia" class="form-control" value="{{ old('conferencia', $record->conferencia) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Conselho Particular</label>
                        <input type="text" name="conselho_particular" class="form-control" value="{{ old('conselho_particular', $record->conselho_particular) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Responsável -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-person me-2"></i>Dados do Responsável</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Nome do Responsável *</label>
                        <input type="text" name="responsavel_nome" class="form-control @error('responsavel_nome') is-invalid @enderror" required value="{{ old('responsavel_nome', $record->responsavel_nome) }}">
                        @error('responsavel_nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Data Nascimento</label>
                        <input type="date" name="data_nascimento" id="data_nascimento" class="form-control @error('data_nascimento') is-invalid @enderror" value="{{ old('data_nascimento', $record->data_nascimento ? $record->data_nascimento->format('Y-m-d') : '') }}">
                        @error('data_nascimento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-bold text-muted">Idade</label>
                        <input type="number" name="idade" id="idade" class="form-control @error('idade') is-invalid @enderror" value="{{ old('idade', $record->idade) }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Sexo</label>
                        <select name="sexo" class="form-select">
                            <option value="">Selecione...</option>
                            <option value="Masculino" {{ old('sexo', $record->sexo) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                            <option value="Feminino" {{ old('sexo', $record->sexo) == 'Feminino' ? 'selected' : '' }}>Feminino</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">CPF</label>
                        <input type="text" name="cpf" class="form-control @error('cpf') is-invalid @enderror" placeholder="000.000.000-00" value="{{ old('cpf', $record->cpf) }}">
                        @error('cpf')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">RG</label>
                        <input type="text" name="rg" class="form-control @error('rg') is-invalid @enderror" value="{{ old('rg', $record->rg) }}">
                        @error('rg')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">CEP</label>
                        <input type="text" name="cep" class="form-control" value="{{ old('cep', $record->cep) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Endereço</label>
                        <input type="text" name="endereco" class="form-control" value="{{ old('endereco', $record->endereco) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Número</label>
                        <input type="text" name="endereco_numero" class="form-control" value="{{ old('endereco_numero', $record->endereco_numero) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Bairro</label>
                        <input type="text" name="bairro" class="form-control" value="{{ old('bairro', $record->bairro) }}">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                     <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Cidade</label>
                        <input type="text" name="cidade" class="form-control" value="{{ old('cidade', $record->cidade) }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Estado</label>
                        <input type="text" name="estado" class="form-control" maxlength="2" value="{{ old('estado', $record->estado) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Contato Principal (Telefone) <span class="text-danger fw-bold small">*</span></label>
                        <input type="text" name="telefone" class="form-control @error('telefone') is-invalid @enderror" value="{{ old('telefone', $record->telefone) }}" placeholder="(00) 00000-0000" required>
                        @error('telefone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Contato Recado</label>
                        <input type="text" name="contato_recado" class="form-control" value="{{ old('contato_recado', $record->contato_recado) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Situação Socioeconômica -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
             <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-house me-2"></i>Situação Socioeconômica</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Recebe Bolsa Família?</label>
                        <select name="recebe_bolsa_familia" class="form-select">
                            <option value="0" {{ old('recebe_bolsa_familia', $record->recebe_bolsa_familia) == 0 ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('recebe_bolsa_familia', $record->recebe_bolsa_familia) == 1 ? 'selected' : '' }}>Sim</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Valor Bolsa Família</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" name="valor_bolsa_familia" class="form-control money-mask" value="{{ old('valor_bolsa_familia', $record->valor_bolsa_familia) }}">
                        </div>
                    </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Outro Benefício (Nome)</label>
                        <input type="text" name="outro_beneficio_nome" class="form-control" value="{{ old('outro_beneficio_nome', $record->outro_beneficio_nome) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Valor Outro Benefício</label>
                        <div class="input-group">
                             <span class="input-group-text">R$</span>
                            <input type="text" name="outro_beneficio_valor" class="form-control money-mask" value="{{ old('outro_beneficio_valor', $record->outro_beneficio_valor) }}">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Tipo Residência</label>
                        <select name="tipo_residencia" class="form-select">
                            <option value="">Selecione...</option>
                            <option value="Propria" {{ old('tipo_residencia', $record->tipo_residencia) == 'Propria' ? 'selected' : '' }}>Própria</option>
                            <option value="Alugada" {{ old('tipo_residencia', $record->tipo_residencia) == 'Alugada' ? 'selected' : '' }}>Alugada</option>
                            <option value="Financiada" {{ old('tipo_residencia', $record->tipo_residencia) == 'Financiada' ? 'selected' : '' }}>Financiada</option>
                            <option value="Cedida" {{ old('tipo_residencia', $record->tipo_residencia) == 'Cedida' ? 'selected' : '' }}>Cedida</option>
                            <option value="Invasao" {{ old('tipo_residencia', $record->tipo_residencia) == 'Invasao' ? 'selected' : '' }}>Invasão</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Valor Aluguel/Prestação</label>
                         <div class="input-group">
                             <span class="input-group-text">R$</span>
                            <input type="text" name="valor_aluguel_prestacao" class="form-control money-mask" value="{{ old('valor_aluguel_prestacao', $record->valor_aluguel_prestacao) }}">
                        </div>
                    </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Quem Trabalha?</label>
                        <input type="text" name="quem_trabalha" class="form-control" value="{{ old('quem_trabalha', $record->quem_trabalha) }}">
                    </div>
                     <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Local Trabalho</label>
                        <input type="text" name="local_trabalho" class="form-control" value="{{ old('local_trabalho', $record->local_trabalho) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Religiosidade -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
             <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-heart me-2"></i>Religiosidade</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Religião</label>
                        <input type="text" name="religiao" class="form-control" value="{{ old('religiao', $record->religiao) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Católico c/ Sacramentos?</label>
                         <select name="catolico_tem_sacramentos" class="form-select">
                            <option value="0" {{ old('catolico_tem_sacramentos', $record->catolico_tem_sacramentos) == 0 ? 'selected' : '' }}>Não</option>
                            <option value="1" {{ old('catolico_tem_sacramentos', $record->catolico_tem_sacramentos) == 1 ? 'selected' : '' }}>Sim</option>
                        </select>
                    </div>
                     <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Sacramento Faltando</label>
                        <input type="text" name="sacramento_faltando" class="form-control" value="{{ old('sacramento_faltando', $record->sacramento_faltando) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Composição Familiar (Dinâmico) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-people me-2"></i>Composição Familiar</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" id="addFamilyMember">
                    <i class="bi bi-plus-lg me-1"></i> Adicionar Membro
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="familyTable">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Nome</th>
                                <th>Parentesco</th>
                                <th>Nascimento</th>
                                <th>Profissão</th>
                                <th>Escolaridade</th>
                                <th>Renda (R$)</th>
                                <th class="text-end pe-4">Ação</th>
                            </tr>
                        </thead>
                        <tbody id="familyTableBody">
                            @if(old('families'))
                                @foreach(old('families') as $index => $family)
                                    <tr>
                                        <td class="ps-4">
                                            <input type="hidden" name="families[{{ $index }}][id]" value="{{ $family['id'] ?? '' }}">
                                            <input type="text" name="families[{{ $index }}][nome]" class="form-control form-control-sm" required value="{{ $family['nome'] ?? '' }}">
                                        </td>
                                        <td><input type="text" name="families[{{ $index }}][parentesco]" class="form-control form-control-sm" value="{{ $family['parentesco'] ?? '' }}"></td>
                                        <td><input type="date" name="families[{{ $index }}][nascimento]" class="form-control form-control-sm" value="{{ $family['nascimento'] ?? '' }}"></td>
                                        <td><input type="text" name="families[{{ $index }}][profissao]" class="form-control form-control-sm" value="{{ $family['profissao'] ?? '' }}"></td>
                                        <td><input type="text" name="families[{{ $index }}][escolaridade]" class="form-control form-control-sm" value="{{ $family['escolaridade'] ?? '' }}"></td>
                                        <td><input type="text" name="families[{{ $index }}][renda]" class="form-control form-control-sm money-mask" value="{{ $family['renda'] ?? '' }}"></td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-member">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach($record->families as $index => $family)
                                    <tr>
                                        <td class="ps-4">
                                            <input type="hidden" name="families[{{ $index }}][id]" value="{{ $family->id }}">
                                            <input type="text" name="families[{{ $index }}][nome]" class="form-control form-control-sm" required value="{{ $family->nome }}">
                                        </td>
                                        <td><input type="text" name="families[{{ $index }}][parentesco]" class="form-control form-control-sm" value="{{ $family->parentesco }}"></td>
                                        <td><input type="date" name="families[{{ $index }}][nascimento]" class="form-control form-control-sm" value="{{ $family->nascimento ? $family->nascimento->format('Y-m-d') : '' }}"></td>
                                        <td><input type="text" name="families[{{ $index }}][profissao]" class="form-control form-control-sm" value="{{ $family->profissao }}"></td>
                                        <td><input type="text" name="families[{{ $index }}][escolaridade]" class="form-control form-control-sm" value="{{ $family->escolaridade }}"></td>
                                        <td><input type="text" name="families[{{ $index }}][renda]" class="form-control form-control-sm money-mask" value="{{ $family->renda }}"></td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-member">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="p-4 text-center text-muted" id="noFamilyMembers" style="{{ $record->families->count() > 0 ? 'display: none;' : '' }}">
                    Nenhum membro familiar adicionado. Clique em "Adicionar Membro" para começar.
                </div>
            </div>
        </div>

        <!-- Observações -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-pencil-square me-2"></i>Observações</h5>
            </div>
            <div class="card-body">
                <textarea name="observacoes" class="form-control" rows="4">{{ old('observacoes', $record->observacoes) }}</textarea>
                
                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Responsaveis pela Sindicância</label>
                        <input type="text" name="responsaveis_sindicancia" class="form-control" value="{{ old('responsaveis_sindicancia', $record->responsaveis_sindicancia) }}">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Dispensado em</label>
                        <input type="date" name="data_dispensa" class="form-control" value="{{ old('data_dispensa', $record->data_dispensa ? $record->data_dispensa->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-9">
                        <label class="form-label small fw-bold text-muted">Motivo da Dispensa</label>
                        <textarea name="motivo_dispensa" class="form-control" rows="1">{{ old('motivo_dispensa', $record->motivo_dispensa) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 align-items-center mb-5">
            <div id="submitSpinner" class="d-none d-flex align-items-center me-3">
                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                <span class="text-primary fw-bold small">Salvando...</span>
            </div>
            <a href="{{ route('vicentinos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
            <button type="submit" id="btnSalvar" class="btn btn-primary rounded-pill px-5 fw-bold">Atualizar Ficha</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const familyTableBody = document.getElementById('familyTableBody');
        const addFamilyBtn = document.getElementById('addFamilyMember');
        const noFamilyMembers = document.getElementById('noFamilyMembers');
        const btnSalvar = document.getElementById('btnSalvar');
        const submitSpinner = document.getElementById('submitSpinner');
        const form = document.querySelector('form');
        let memberCount = {{ old('families') ? count(old('families')) : $record->families->count() }}; // Initialize with existing count

        // Prevent double submission and show loading
        form.addEventListener('submit', function() {
            if (form.checkValidity()) {
                btnSalvar.disabled = true;
                btnSalvar.classList.add('opacity-50');
                btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
                if(submitSpinner) submitSpinner.classList.remove('d-none');
            }
        });

        function checkEmpty() {
            if (familyTableBody.children.length === 0) {
                noFamilyMembers.style.display = 'block';
            } else {
                noFamilyMembers.style.display = 'none';
            }
        }
        
        // --- Masks ---
        function maskMoney(event) {
            let value = event.target.value.replace(/\D/g, '');
            value = (value / 100).toFixed(2) + '';
            value = value.replace(".", ",");
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            event.target.value = value;
        }

        function maskCPF(value) {
            return value
                .replace(/\D/g, '')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1,2})/, '$1-$2')
                .replace(/(-\d{2})\d+?$/, '$1');
        }

        function maskRG(value) {
             // Simple RG mask: 99.999.999-9
             return value
                .replace(/\D/g, '')
                .replace(/(\d{2})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d)/, '$1.$2')
                .replace(/(\d{3})(\d{1})/, '$1-$2')
                .replace(/(-\d{1})\d+?$/, '$1');
        }

        function maskPhone(value) {
            return value
                .replace(/\D/g, '')
                .replace(/(\d{2})(\d)/, '($1) $2')
                .replace(/(\d{5})(\d)/, '$1-$2')
                .replace(/(-\d{4})\d+?$/, '$1');
        }
        
        function maskCEP(value) {
            return value
                .replace(/\D/g, '')
                .replace(/(\d{5})(\d)/, '$1-$2')
                .replace(/(-\d{3})\d+?$/, '$1');
        }

        // Apply Masks to existing inputs
        document.querySelectorAll('.money-mask').forEach(input => {
            input.addEventListener('input', maskMoney);
            // Format initial values
            let value = input.value;
            if(value && !value.includes(',')) {
                 value = parseFloat(value).toFixed(2) + '';
                 value = value.replace(".", ",");
                 value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
                 input.value = value;
            }
        });

        const cpfInput = document.querySelector('input[name="cpf"]');
        if(cpfInput) {
            cpfInput.value = maskCPF(cpfInput.value); // Initial mask
            cpfInput.addEventListener('input', function(e) {
                e.target.value = maskCPF(e.target.value);
            });
        }

        const rgInput = document.querySelector('input[name="rg"]');
        if(rgInput) {
            rgInput.value = maskRG(rgInput.value); // Initial mask
            rgInput.addEventListener('input', function(e) {
                e.target.value = maskRG(e.target.value);
            });
        }
        
        const phoneInput = document.querySelector('input[name="telefone"]');
        if(phoneInput) {
             phoneInput.value = maskPhone(phoneInput.value); // Initial mask
             phoneInput.addEventListener('input', function(e) {
                e.target.value = maskPhone(e.target.value);
            });
        }

        const cepInput = document.querySelector('input[name="cep"]');
        if(cepInput) {
            cepInput.value = maskCEP(cepInput.value); // Initial mask
            cepInput.addEventListener('input', function(e) {
                e.target.value = maskCEP(e.target.value);
                
                // Autocomplete Address
                const cleanCep = e.target.value.replace(/\D/g, '');
                if (cleanCep.length === 8) {
                    fetch(`https://viacep.com.br/ws/${cleanCep}/json/`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.erro) {
                                document.querySelector('input[name="endereco"]').value = data.logradouro;
                                document.querySelector('input[name="bairro"]').value = data.bairro;
                                document.querySelector('input[name="cidade"]').value = data.localidade;
                                document.querySelector('input[name="estado"]').value = data.uf;
                                document.querySelector('input[name="endereco_numero"]').focus();
                            }
                        })
                        .catch(err => console.error('Erro ao buscar CEP:', err));
                }
            });
        }

        // --- Family Table Logic ---
        // Add listeners to existing rows
        document.querySelectorAll('.remove-member').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('tr').remove();
                checkEmpty();
            });
        });

        addFamilyBtn.addEventListener('click', function() {
            const index = memberCount++;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="ps-4"><input type="text" name="families[${index}][nome]" class="form-control form-control-sm" required placeholder="Nome completo"></td>
                <td><input type="text" name="families[${index}][parentesco]" class="form-control form-control-sm" placeholder="Ex: Filho"></td>
                <td><input type="date" name="families[${index}][nascimento]" class="form-control form-control-sm"></td>
                <td><input type="text" name="families[${index}][profissao]" class="form-control form-control-sm" placeholder="Ex: Estudante"></td>
                <td><input type="text" name="families[${index}][escolaridade]" class="form-control form-control-sm"></td>
                <td><input type="text" name="families[${index}][renda]" class="form-control form-control-sm money-mask" placeholder="0,00"></td>
                <td class="text-end pe-4">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 remove-member">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            `;
            
            // Add event listener for remove button
            row.querySelector('.remove-member').addEventListener('click', function() {
                row.remove();
                checkEmpty();
            });

            // Add mask to new input
            row.querySelector('.money-mask').addEventListener('input', maskMoney);

            familyTableBody.appendChild(row);
            checkEmpty();
        });

        checkEmpty();

        // --- Calculate Age from Birth Date ---
        const dataNascimentoInput = document.getElementById('data_nascimento');
        const idadeInput = document.getElementById('idade');

        if (dataNascimentoInput && idadeInput) {
            dataNascimentoInput.addEventListener('change', function() {
                const birthDate = new Date(this.value);
                const today = new Date();
                
                if (isNaN(birthDate.getTime())) {
                    idadeInput.value = '';
                    return;
                }

                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                idadeInput.value = age;
            });
        }

        // --- Submit Loading ---
        // form.addEventListener('submit', function() {
        //    btnSalvar.disabled = true;
        //    btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
        // });
    });
</script>
@endsection
