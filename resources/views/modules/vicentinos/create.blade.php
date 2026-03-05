@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Nova Ficha Vicentina</h2>
            <p class="text-muted small mb-0">Preencha os dados da família assistida.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vicentinos.index') }}" class="text-decoration-none">Registros Vicentinos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova Ficha</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atenção!</strong> Verifique os erros abaixo e tente novamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('vicentinos.store') }}" method="POST">
                @csrf

                <!-- Dados Iniciais -->
                <h5 class="fw-bold text-dark mb-4">Informações Iniciais</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Data da Ficha</label>
                        <input type="date" name="data_ficha" class="form-control rounded-pill bg-light border-0 px-4 py-2" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small text-muted">Conferência</label>
                        <input type="text" name="conferencia" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Conselho Particular</label>
                        <input type="text" name="conselho_particular" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- Responsável -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Dados do Responsável</h5>
                    <button type="button" class="btn btn-outline-primary rounded-pill btn-sm px-3" id="btnImportar" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-download me-2"></i> Importar do Registro Geral
                    </button>
                    <button type="button" class="btn btn-success rounded-pill btn-sm px-3 d-none" id="btnImported" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i> Importação bem sucedida
                    </button>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Nome do Responsável <span class="text-danger fw-bold small">*</span></label>
                        <input type="text" name="responsavel_nome" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('responsavel_nome') is-invalid @enderror" required value="{{ old('responsavel_nome') }}">
                        @error('responsavel_nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Data Nascimento</label>
                        <input type="date" name="data_nascimento" id="data_nascimento" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('data_nascimento') is-invalid @enderror" value="{{ old('data_nascimento') }}">
                        @error('data_nascimento')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold small text-muted">Idade</label>
                        <input type="number" name="idade" id="idade" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('idade') is-invalid @enderror" value="{{ old('idade') }}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Sexo</label>
                        <select name="sexo" class="form-select rounded-pill bg-light border-0 px-4 py-2">
                            <option value="">Selecione...</option>
                            <option value="Masculino" {{ old('sexo') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                            <option value="Feminino" {{ old('sexo') == 'Feminino' ? 'selected' : '' }}>Feminino</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">CPF</label>
                        <input type="text" name="cpf" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('cpf') is-invalid @enderror" placeholder="000.000.000-00" value="{{ old('cpf') }}">
                        @error('cpf')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">RG</label>
                        <input type="text" name="rg" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('rg') is-invalid @enderror" value="{{ old('rg') }}">
                        @error('rg')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">CEP</label>
                        <input type="text" name="cep" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small text-muted">Endereço</label>
                        <input type="text" name="endereco" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Número</label>
                        <input type="text" name="endereco_numero" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Bairro</label>
                        <input type="text" name="bairro" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Cidade</label>
                        <input type="text" name="cidade" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Estado</label>
                        <input type="text" name="estado" class="form-control rounded-pill bg-light border-0 px-4 py-2" maxlength="2">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Telefone <span class="text-danger fw-bold small">*</span></label>
                        <input type="text" name="telefone" class="form-control rounded-pill bg-light border-0 px-4 py-2 @error('telefone') is-invalid @enderror" placeholder="(00) 00000-0000" required value="{{ old('telefone') }}">
                        @error('telefone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                     <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted">Contato Recado</label>
                        <input type="text" name="contato_recado" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- Situação Socioeconômica -->
                <h5 class="fw-bold text-dark mb-4">Situação Socioeconômica</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Recebe Bolsa Família?</label>
                        <select name="recebe_bolsa_familia" class="form-select rounded-pill bg-light border-0 px-4 py-2">
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Valor Bolsa Família</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 rounded-start-pill ps-4">R$</span>
                            <input type="text" name="valor_bolsa_familia" class="form-control bg-light border-0 rounded-end-pill py-2 money-mask">
                        </div>
                    </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Outro Benefício (Nome)</label>
                        <input type="text" name="outro_beneficio_nome" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Valor Outro Benefício</label>
                        <div class="input-group">
                             <span class="input-group-text bg-light border-0 rounded-start-pill ps-4">R$</span>
                            <input type="text" name="outro_beneficio_valor" class="form-control bg-light border-0 rounded-end-pill py-2 money-mask">
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Tipo Residência</label>
                        <select name="tipo_residencia" class="form-select rounded-pill bg-light border-0 px-4 py-2">
                            <option value="">Selecione...</option>
                            <option value="Propria">Própria</option>
                            <option value="Alugada">Alugada</option>
                            <option value="Financiada">Financiada</option>
                            <option value="Cedida">Cedida</option>
                            <option value="Invasao">Invasão</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Valor Aluguel/Prestação</label>
                         <div class="input-group">
                             <span class="input-group-text bg-light border-0 rounded-start-pill ps-4">R$</span>
                            <input type="text" name="valor_aluguel_prestacao" class="form-control bg-light border-0 rounded-end-pill py-2 money-mask">
                        </div>
                    </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Quem Trabalha?</label>
                        <input type="text" name="quem_trabalha" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                     <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Local Trabalho</label>
                        <input type="text" name="local_trabalho" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- Religiosidade -->
                <h5 class="fw-bold text-dark mb-4">Religiosidade</h5>
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Religião</label>
                        <input type="text" name="religiao" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Católico c/ Sacramentos?</label>
                         <select name="catolico_tem_sacramentos" class="form-select rounded-pill bg-light border-0 px-4 py-2">
                            <option value="0">Não</option>
                            <option value="1">Sim</option>
                        </select>
                    </div>
                     <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Sacramento Faltando</label>
                        <input type="text" name="sacramento_faltando" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- Composição Familiar (Dinâmico) -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold text-dark mb-0">Composição Familiar</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="addFamilyMember">
                        <i class="bi bi-plus-lg me-1"></i> Adicionar Membro
                    </button>
                </div>
                
                <div class="card border-0 bg-light rounded-4 mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="familyTable">
                                <thead class="border-bottom">
                                    <tr>
                                        <th class="ps-4 py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Nome</th>
                                        <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Parentesco</th>
                                        <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Nascimento</th>
                                        <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Profissão</th>
                                        <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Escolaridade</th>
                                        <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Renda (R$)</th>
                                        <th class="text-end pe-4 py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Ação</th>
                                    </tr>
                                </thead>
                                <tbody id="familyTableBody" class="border-top-0">
                                    @if(old('families'))
                                        @foreach(old('families') as $index => $family)
                                            <tr>
                                                <td class="ps-4"><input type="text" name="families[{{ $index }}][nome]" class="form-control rounded-pill bg-white border-0 px-3 py-2" required value="{{ $family['nome'] ?? '' }}" placeholder="Nome completo"></td>
                                                <td><input type="text" name="families[{{ $index }}][parentesco]" class="form-control rounded-pill bg-white border-0 px-3 py-2" value="{{ $family['parentesco'] ?? '' }}" placeholder="Ex: Filho"></td>
                                                <td><input type="date" name="families[{{ $index }}][nascimento]" class="form-control rounded-pill bg-white border-0 px-3 py-2" value="{{ $family['nascimento'] ?? '' }}"></td>
                                                <td><input type="text" name="families[{{ $index }}][profissao]" class="form-control rounded-pill bg-white border-0 px-3 py-2" value="{{ $family['profissao'] ?? '' }}" placeholder="Ex: Estudante"></td>
                                                <td><input type="text" name="families[{{ $index }}][escolaridade]" class="form-control rounded-pill bg-white border-0 px-3 py-2" value="{{ $family['escolaridade'] ?? '' }}"></td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white border-0 rounded-start-pill ps-3">R$</span>
                                                        <input type="text" name="families[{{ $index }}][renda]" class="form-control bg-white border-0 rounded-end-pill py-2 money-mask" value="{{ $family['renda'] ?? '' }}" placeholder="0,00">
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle remove-member p-2">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="p-5 text-center text-muted" id="noFamilyMembers">
                            <i class="bi bi-people display-6 mb-3 d-block opacity-50"></i>
                            Nenhum membro familiar adicionado.<br>Clique em "Adicionar Membro" para começar.
                        </div>
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- Observações -->
                <h5 class="fw-bold text-dark mb-4">Outras Informações</h5>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Observações</label>
                        <textarea name="observacoes" class="form-control rounded-4 bg-light border-0 px-4 py-3" rows="4" placeholder="Insira observações adicionais aqui..."></textarea>
                    </div>
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small text-muted">Responsáveis pela Sindicância</label>
                        <input type="text" name="responsaveis_sindicancia" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted">Dispensado em</label>
                        <input type="date" name="data_dispensa" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold small text-muted">Motivo da Dispensa</label>
                        <input type="text" name="motivo_dispensa" class="form-control rounded-pill bg-light border-0 px-4 py-2">
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 align-items-center mt-5">
                    <div id="submitSpinner" class="d-none d-flex align-items-center me-3">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        <span class="text-primary fw-bold small">Salvando...</span>
                    </div>
                    <a href="{{ route('vicentinos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" id="btnSalvar" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Ficha</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')</div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Importar do Registro Geral</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light border-0 ps-3 rounded-start-pill"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-0 rounded-end-pill py-2" id="importSearch" placeholder="Digite o nome ou CPF para buscar...">
                    </div>
                    <div class="list-group list-group-flush" id="importResults" style="max-height: 300px; overflow-y: auto;">
                        <!-- Results -->
                        <div class="text-center text-muted py-5 small">
                            <i class="bi bi-search display-6 mb-2 d-block opacity-25"></i>
                            Digite para buscar pessoas cadastradas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Import Logic ---
        const importSearch = document.getElementById('importSearch');
        const importResults = document.getElementById('importResults');
        const importModalEl = document.getElementById('importModal');
        const importModal = new bootstrap.Modal(importModalEl);
        const btnImportar = document.getElementById('btnImportar');
        const btnImported = document.getElementById('btnImported');

        importSearch.addEventListener('input', function() {
            const term = this.value;
            if(term.length < 3) {
                importResults.innerHTML = '<div class="text-center text-muted py-5 small"><i class="bi bi-search display-6 mb-2 d-block opacity-25"></i>Digite para buscar pessoas cadastradas</div>';
                return;
            }

            fetch(`{{ route('vicentinos.search-registers') }}?term=${term}`)
                .then(response => response.json())
                .then(data => {
                    importResults.innerHTML = '';
                    if(data.length === 0) {
                        importResults.innerHTML = '<div class="text-center text-muted py-3">Nenhum registro encontrado</div>';
                        return;
                    }

                    data.forEach(reg => {
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action py-3 border-0 rounded-3 mb-1 bg-light';
                        item.innerHTML = `
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1 fw-bold text-dark">${reg.name}</h6>
                                    <p class="mb-1 small text-muted"><i class="bi bi-person-vcard me-1"></i> ${reg.cpf || 'Sem CPF'} | <i class="bi bi-telephone me-1"></i> ${reg.phone || 'Sem Telefone'}</p>
                                </div>
                                <i class="bi bi-arrow-right-circle text-primary fs-5"></i>
                            </div>
                        `;
                        item.onclick = (e) => {
                            e.preventDefault();
                            fillForm(reg);
                            importModal.hide();
                            btnImportar.classList.add('d-none');
                            btnImported.classList.remove('d-none');
                            
                            // Clear search
                            importSearch.value = '';
                            importResults.innerHTML = '';
                        };
                        importResults.appendChild(item);
                    });
                });
        });

        function fillForm(data) {
            // Fill basic fields
            const setVal = (name, val) => {
                const el = document.querySelector(`[name="${name}"]`);
                if(el) el.value = val || '';
            };

            setVal('responsavel_nome', data.name);
            setVal('cpf', data.cpf);
            setVal('rg', data.rg);
            setVal('telefone', data.phone);
            
            // Fill Address
            setVal('cep', data.cep);
            setVal('endereco', data.address);
            setVal('endereco_numero', data.address_number);
            setVal('bairro', data.home_situation); 
            setVal('cidade', data.city);
            setVal('estado', data.state);

            // Fill Date of Birth and Age
            if(data.born_date) {
                const bornDate = new Date(data.born_date);
                // Adjust for timezone offset if necessary, but assuming date string YYYY-MM-DD
                // Or if it comes as ISO string. 
                // Let's rely on string parsing if format is consistent or date object
                
                // data.born_date from Laravel JSON is usually "YYYY-MM-DD..."
                const yyyy = data.born_date.substring(0, 4);
                const mm = data.born_date.substring(5, 7);
                const dd = data.born_date.substring(8, 10);
                
                const dateStr = `${yyyy}-${mm}-${dd}`;
                setVal('data_nascimento', dateStr);
                
                // Trigger age calculation manually
                const evt = new Event('change');
                document.getElementById('data_nascimento').dispatchEvent(evt);
            }

            // Fill Sexo
            if(data.sexo) {
                const sexoSelect = document.querySelector('select[name="sexo"]');
                if(sexoSelect) {
                    if(data.sexo == 1 || data.sexo == 'Masculino') sexoSelect.value = 'Masculino';
                    else if(data.sexo == 2 || data.sexo == 'Feminino') sexoSelect.value = 'Feminino';
                }
            }
        }

        const familyTableBody = document.getElementById('familyTableBody');
        const addFamilyBtn = document.getElementById('addFamilyMember');
        const noFamilyMembers = document.getElementById('noFamilyMembers');
        const btnSalvar = document.getElementById('btnSalvar');
        const form = document.querySelector('form');
        let memberCount = {{ count(old('families', [])) }};

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

        // Apply Masks
        document.querySelectorAll('.money-mask').forEach(input => {
            input.addEventListener('input', maskMoney);
        });

        const cpfInput = document.querySelector('input[name="cpf"]');
        if(cpfInput) {
            cpfInput.addEventListener('input', function(e) {
                e.target.value = maskCPF(e.target.value);
            });
        }

        const rgInput = document.querySelector('input[name="rg"]');
        if(rgInput) {
            rgInput.addEventListener('input', function(e) {
                e.target.value = maskRG(e.target.value);
            });
        }
        
        const phoneInput = document.querySelector('input[name="telefone"]');
        if(phoneInput) {
             phoneInput.addEventListener('input', function(e) {
                e.target.value = maskPhone(e.target.value);
            });
        }

        const cepInput = document.querySelector('input[name="cep"]');
        if(cepInput) {
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
                <td class="ps-4"><input type="text" name="families[${index}][nome]" class="form-control rounded-pill bg-white border-0 px-3 py-2" required placeholder="Nome completo"></td>
                <td><input type="text" name="families[${index}][parentesco]" class="form-control rounded-pill bg-white border-0 px-3 py-2" placeholder="Ex: Filho"></td>
                <td><input type="date" name="families[${index}][nascimento]" class="form-control rounded-pill bg-white border-0 px-3 py-2"></td>
                <td><input type="text" name="families[${index}][profissao]" class="form-control rounded-pill bg-white border-0 px-3 py-2" placeholder="Ex: Estudante"></td>
                <td><input type="text" name="families[${index}][escolaridade]" class="form-control rounded-pill bg-white border-0 px-3 py-2"></td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-0 rounded-start-pill ps-3">R$</span>
                        <input type="text" name="families[${index}][renda]" class="form-control bg-white border-0 rounded-end-pill py-2 money-mask" placeholder="0,00">
                    </div>
                </td>
                <td class="text-end pe-4">
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle remove-member p-2">
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
        const submitSpinner = document.getElementById('submitSpinner');
        
        form.addEventListener('submit', function() {
            if (form.checkValidity()) {
                btnSalvar.disabled = true;
                btnSalvar.classList.add('opacity-50');
                btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
                if(submitSpinner) submitSpinner.classList.remove('d-none');
            }
        });
    });
</script>
@endsection
