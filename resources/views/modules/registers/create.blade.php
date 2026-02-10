@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Adicionar Registro</h2>
            <p class="text-muted small mb-0">Preencha os dados abaixo para cadastrar uma nova pessoa.</p>
            <div class="mt-2 small text-muted">
                <i class="bi bi-info-circle me-1 text-primary"></i>
                <span class="text-danger fw-bold">(obrigatório)</span> Indispensável &nbsp;|&nbsp; 
                <span class="text-primary fw-bold">(recomendado)</span> Importante &nbsp;|&nbsp; 
                O CEP preenche o endereço automaticamente.
            </div>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('registers.index') }}" class="text-decoration-none">Registros Gerais</a></li>
                <li class="breadcrumb-item active" aria-current="page">Adicionar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            
            <form action="{{ route('registers.store') }}" method="POST" enctype="multipart/form-data" id="createRegisterForm">
                @csrf
                <input type="hidden" name="status" value="0"> <!-- Default Active -->

                <!-- Row 1: Nome, Email, Celular -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="name" class="form-label fw-bold small text-muted">Nome completo <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="name" name="name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label fw-bold small text-muted">E-mail</label>
                        <input type="email" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="email" name="email">
                    </div>
                    <div class="col-md-4">
                        <label for="phone" class="form-label fw-bold small text-muted">
                            Celular <span class="text-danger fw-bold small">(obrigatório)</span>
                        </label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="phone" name="phone" required maxlength="11">
                        <div class="form-text small text-muted ps-3">
                            <i class="bi bi-info-circle me-1"></i> Apenas números, com DDD (Ex: 11912345678)
                        </div>
                    </div>
                </div>

                <!-- Row 2: RG, CPF, Idade, Nascimento -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="rg" class="form-label fw-bold small text-muted">RG</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="rg" name="rg">
                    </div>
                    <div class="col-md-3">
                        <label for="cpf" class="form-label fw-bold small text-muted">CPF</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="cpf" name="cpf">
                    </div>
                    <div class="col-md-2">
                        <label for="age" class="form-label fw-bold small text-muted">Idade</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="age" name="age" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="born_date" class="form-label fw-bold small text-muted">Data de Nascimento <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="date" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="born_date" name="born_date" required>
                    </div>
                </div>

                <!-- Row 3: CEP, Endereço, Numero, Cidade -->
                <div class="row g-4 mb-4">
                    <div class="col-md-2">
                        <label for="cep" class="form-label fw-bold small text-muted">CEP <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="cep" name="cep">
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold small text-muted">Endereço <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="address" name="address">
                    </div>
                    <div class="col-md-2">
                        <label for="address_number" class="form-label fw-bold small text-muted">Núm.</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="address_number" name="address_number">
                    </div>
                    <div class="col-md-2">
                        <label for="city" class="form-label fw-bold small text-muted">Cidade <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="city" name="city">
                    </div>
                </div>

                <!-- Row 4: Estado, Pais, Bairro -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="state" class="form-label fw-bold small text-muted">Estado <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="state" name="state">
                            <option value="">Selecione...</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <option value="AP">Amapá</option>
                            <option value="AM">Amazonas</option>
                            <option value="BA">Bahia</option>
                            <option value="CE">Ceará</option>
                            <option value="DF">Distrito Federal</option>
                            <option value="ES">Espírito Santo</option>
                            <option value="GO">Goiás</option>
                            <option value="MA">Maranhão</option>
                            <option value="MT">Mato Grosso</option>
                            <option value="MS">Mato Grosso do Sul</option>
                            <option value="MG">Minas Gerais</option>
                            <option value="PA">Pará</option>
                            <option value="PB">Paraíba</option>
                            <option value="PR">Paraná</option>
                            <option value="PE">Pernambuco</option>
                            <option value="PI">Piauí</option>
                            <option value="RJ">Rio de Janeiro</option>
                            <option value="RN">Rio Grande do Norte</option>
                            <option value="RS">Rio Grande do Sul</option>
                            <option value="RO">Rondônia</option>
                            <option value="RR">Roraima</option>
                            <option value="SC">Santa Catarina</option>
                            <option value="SP">São Paulo</option>
                            <option value="SE">Sergipe</option>
                            <option value="TO">Tocantins</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="country" class="form-label fw-bold small text-muted">País</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="country" name="country">
                            <option value="Brasil" selected>Brasil</option>
                            <!-- List populated via JS -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="neighborhood" class="form-label fw-bold small text-muted">Bairro <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="home_situation" name="home_situation">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- Row 5: Estado Civil, Emprego, Raça, Sexo -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="civil_status" class="form-label fw-bold small text-muted">Estado Civil</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="civil_status" name="civil_status">
                            <option value="1">Solteiro(a)</option>
                            <option value="2">Casado(a)</option>
                            <option value="3">União Estável</option>
                            <option value="4">Divorciado</option>
                            <option value="5">Viuvo(a)</option>
                            <option value="6" selected>Nao Declarado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sexo" class="form-label fw-bold small text-muted">Sexo <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="sexo" name="sexo" required>
                            <option value="">Selecione...</option>
                            <option value="1">Masculino</option>
                            <option value="2">Feminino</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="work_state" class="form-label fw-bold small text-muted">Situação de Emprego</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="work_state" name="work_state">
                            <option value="4" selected>Nao Declarado</option>
                            <option value="1">Desempregado</option>
                            <option value="2">Autonomo(a)</option>
                            <option value="3">Empresário</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="race" class="form-label fw-bold small text-muted">Raça/Cor</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="race" name="race">
                            <option value="5" selected>Não declarado</option>
                            <option value="1">Branco</option>
                            <option value="2">Preto</option>
                            <option value="3">Pardo</option>
                            <option value="4">Amarelo</option>
                        </select>
                    </div>
                </div>

                <!-- Row 6: Mae, Pai, Pessoas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="mother_name" class="form-label fw-bold small text-muted">Nome da Mãe</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="mother_name" name="mother_name">
                        
                        <div class="mt-2 d-none" id="mother_phone_container">
                            <label for="mother_phone" class="form-label fw-bold small text-muted">Celular da Mãe</label>
                            <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="mother_phone" name="mother_phone" maxlength="11" placeholder="Ex: 11999999999">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="father_name" class="form-label fw-bold small text-muted">Nome do Pai</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="father_name" name="father_name">

                        <div class="mt-2 d-none" id="father_phone_container">
                            <label for="father_phone" class="form-label fw-bold small text-muted">Celular do Pai</label>
                            <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="father_phone" name="father_phone" maxlength="11" placeholder="Ex: 11999999999">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="familly_qntd" class="form-label fw-bold small text-muted">Num. Pessoas (Na mesma casa)</label>
                        <input type="number" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="familly_qntd" name="familly_qntd">
                    </div>
                </div>

                <!-- Row 7: Observações -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label for="observations" class="form-label fw-bold small text-muted">Observação <span class="text-primary fw-bold small">(opcional)</span></label>
                        <textarea class="form-control rounded-4 bg-light border-0 px-4 py-3" id="observations" name="observations" rows="3" placeholder="Insira observações adicionais aqui..."></textarea>
                    </div>
                </div>

                <!-- Row 8: Foto Drag & Drop -->
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Foto</label>
                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-5 text-center position-relative" id="dropArea">
                            <input type="file" name="photo" id="photo" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/*">
                            <div class="d-flex flex-column align-items-center justify-content-center" id="dropContent">
                                <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i>
                                <h6 class="fw-bold text-dark">Arraste e solte sua foto aqui</h6>
                                <p class="text-muted small mb-0">ou clique para selecionar</p>
                            </div>
                            <div id="previewArea" class="d-none mt-3">
                                <img id="imgPreview" src="#" alt="Preview" class="rounded-4 shadow-sm border" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                <p id="fileName" class="small text-muted mt-2 mb-0"></p>
                                <button type="button" class="btn btn-sm btn-light rounded-pill border mt-2" id="removeFile">Remover</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 8: Anexos Drag & Drop -->
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Anexos (Documentos)</label>
                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-5 text-center position-relative" id="attachmentsDropArea">
                            <input type="file" name="attachments[]" id="attachments" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" multiple>
                            <div class="d-flex flex-column align-items-center justify-content-center" id="attachmentsDropContent">
                                <i class="bi bi-paperclip display-4 text-secondary mb-3"></i>
                                <h6 class="fw-bold text-dark">Arraste e solte documentos aqui</h6>
                                <p class="text-muted small mb-0">ou clique para selecionar (Max 10 arquivos, exceto vídeos)</p>
                            </div>
                        </div>
                        <!-- Preview Container -->
                        <div id="attachmentsPreviewArea" class="row g-3 mt-3 d-none">
                            <!-- Items added via JS -->
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 align-items-center">
                    <div id="submitSpinner" class="d-none d-flex align-items-center me-3">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        <span class="text-primary fw-bold small">Criando registro...</span>
                    </div>
                    <a href="{{ route('registers.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="submitBtn">Salvar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmacao Telefone Duplicado -->
<div class="modal fade" id="duplicatePhoneModal" tabindex="-1" aria-labelledby="duplicatePhoneModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold text-danger" id="duplicatePhoneModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Atenção: Telefone já cadastrado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body py-4">
        <p class="text-muted mb-0">Já existe uma pessoa cadastrada com este número de telefone.</p>
        <p class="text-muted small mt-2">Como não utilizamos CPF, o telefone é o principal identificador único. No entanto, sabemos que algumas pessoas podem compartilhar o mesmo número (ex: casais, idosos).</p>
        <p class="fw-bold text-dark mt-3 mb-0">Deseja realmente prosseguir com o cadastro?</p>
      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="confirmDuplicateBtn">Sim, Registrar</button>
      </div>
    </div>
  </div>
</div>

<style>
    .file-drop-area {
        transition: all 0.2s ease;
        border-color: #dee2e6;
    }
    .file-drop-area:hover, .file-drop-area.dragover {
        border-color: #0d6efd;
        background-color: #f1f8ff !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent Form Submission on Enter
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            const target = event.target;
            if (target.tagName === 'INPUT' || target.tagName === 'SELECT') {
                event.preventDefault();
                return false;
            }
        }
    });

    // Initialize Bootstrap Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // 0. Phone Input Restriction
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 11);
        });
    }

    // Duplicate Phone Check
    const form = document.getElementById('createRegisterForm');
    let phoneChecked = false;

    if (form) {
        form.addEventListener('submit', function(e) {
            if (phoneChecked) return; // If already checked and confirmed, let it submit

            e.preventDefault();
            const phone = document.getElementById('phone').value;
            const submitBtn = document.getElementById('submitBtn');

            // Simple validation before ajax
            if (!phone) {
                 phoneChecked = true;
                 form.submit();
                 return;
            }

            // Show loading state on button (optional but good UX)
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verificando...';
            submitBtn.disabled = true;

            // Check phone via AJAX
            fetch(`{{ route('registers.check-phone') }}?phone=${phone}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;

                if (data.exists) {
                    // Show Modal
                    const modalEl = document.getElementById('duplicatePhoneModal');
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                    
                    // Handle Confirm Button
                    document.getElementById('confirmDuplicateBtn').onclick = function() {
                        phoneChecked = true;
                        modal.hide();
                        form.submit();
                    };
                } else {
                    // No duplicate, proceed
                    phoneChecked = true;
                    form.submit();
                }
            })
            .catch(error => {
                console.error('Error checking phone:', error);
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
                // In case of error, proceed to submit
                phoneChecked = true;
                form.submit();
            });
        });
    }

    // 1. Countries List
    const countrySelect = document.getElementById('country');
    const countries = [
        "Afeganistão", "África do Sul", "Albânia", "Alemanha", "Andorra", "Angola", "Antígua e Barbuda", "Arábia Saudita", "Argélia", "Argentina", "Armênia", "Austrália", "Áustria", "Azerbaijão",
        "Bahamas", "Bangladesh", "Barbados", "Bahrein", "Bélgica", "Belize", "Benin", "Bielorrússia", "Bolívia", "Bósnia e Herzegovina", "Botsuana", "Brasil", "Brunei", "Bulgária", "Burkina Faso", "Burundi", "Butão",
        "Cabo Verde", "Camarões", "Camboja", "Canadá", "Catar", "Cazaquistão", "Chade", "Chile", "China", "Chipre", "Colômbia", "Comores", "Coreia do Norte", "Coreia do Sul", "Costa do Marfim", "Costa Rica", "Croácia", "Cuba",
        "Dinamarca", "Djibuti", "Dominica",
        "Egito", "El Salvador", "Emirados Árabes Unidos", "Equador", "Eritreia", "Eslováquia", "Eslovênia", "Espanha", "Estados Unidos", "Estônia", "Etiópia",
        "Fiji", "Filipinas", "Finlândia", "França",
        "Gabão", "Gâmbia", "Gana", "Geórgia", "Granada", "Grécia", "Guatemala", "Guiana", "Guiné", "Guiné Equatorial", "Guiné-Bissau",
        "Haiti", "Holanda", "Honduras", "Hungria",
        "Iêmen", "Ilhas Marshall", "Ilhas Salomão", "Índia", "Indonésia", "Irã", "Iraque", "Irlanda", "Islândia", "Israel", "Itália",
        "Jamaica", "Japão", "Jordânia",
        "Kiribati", "Kuwait",
        "Laos", "Lesoto", "Letônia", "Líbano", "Libéria", "Líbia", "Liechtenstein", "Lituânia", "Luxemburgo",
        "Macedônia do Norte", "Madagascar", "Malásia", "Malawi", "Maldivas", "Mali", "Malta", "Marrocos", "Maurício", "Mauritânia", "México", "Micronésia", "Moçambique", "Moldávia", "Mônaco", "Mongólia", "Montenegro", "Myanmar",
        "Namíbia", "Nauru", "Nepal", "Nicarágua", "Níger", "Nigéria", "Noruega", "Nova Zelândia",
        "Omã",
        "Palau", "Panamá", "Papua Nova Guiné", "Paquistão", "Paraguai", "Peru", "Polônia", "Portugal",
        "Quênia", "Quirguistão",
        "Reino Unido", "República Centro-Africana", "República Checa", "República Democrática do Congo", "República Dominicana", "República do Congo", "Romênia", "Ruanda", "Rússia",
        "Samoa", "Santa Lúcia", "São Cristóvão e Neves", "São Marinho", "São Tomé e Príncipe", "São Vicente e Granadinas", "Seicheles", "Senegal", "Serra Leoa", "Sérvia", "Singapura", "Síria", "Somália", "Sri Lanka", "Suazilândia", "Sudão", "Sudão do Sul", "Suécia", "Suíça", "Suriname",
        "Tailândia", "Taiwan", "Tajiquistão", "Tanzânia", "Timor-Leste", "Togo", "Tonga", "Trinidad e Tobago", "Tunísia", "Turcomenistão", "Turquia", "Tuvalu",
        "Ucrânia", "Uganda", "Uruguai", "Uzbequistão",
        "Vanuatu", "Vaticano", "Venezuela", "Vietnã",
        "Zâmbia", "Zimbábue"
    ];

    // Append countries except Brasil (already there)
    countries.forEach(c => {
        if (c !== 'Brasil') {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            countrySelect.appendChild(opt);
        }
    });

    // 2. Age Calculation
    const bornDateInput = document.getElementById('born_date');
    const ageInput = document.getElementById('age');

    bornDateInput.addEventListener('change', function() {
        if (this.value) {
            const today = new Date();
            const birthDate = new Date(this.value);
            let age = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            ageInput.value = age;
        } else {
            ageInput.value = '';
        }
    });

    // 3. ViaCEP Integration
    const cepInput = document.getElementById('cep');
    const addressInput = document.getElementById('address');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const neighborhoodInput = document.getElementById('home_situation'); // Bairro (mapped to home_situation)
    const numberInput = document.getElementById('address_number');

    cepInput.addEventListener('blur', function() {
        let cep = this.value.replace(/\D/g, '');
        if (cep.length === 8) {
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(response => response.json())
                .then(data => {
                    if (!data.erro) {
                        addressInput.value = data.logradouro;
                        neighborhoodInput.value = data.bairro;
                        cityInput.value = data.localidade;
                        stateInput.value = data.uf;
                        numberInput.focus();
                    } else {
                        alert('CEP não encontrado.');
                    }
                })
                .catch(err => console.error('Erro ao buscar CEP:', err));
        }
    });

    // 3.1 Dynamic Parent Phones
    const motherNameInput = document.getElementById('mother_name');
    const motherPhoneContainer = document.getElementById('mother_phone_container');
    const fatherNameInput = document.getElementById('father_name');
    const fatherPhoneContainer = document.getElementById('father_phone_container');

    function togglePhoneField(nameInput, container) {
        if (nameInput && container) {
            if (nameInput.value.trim() !== '') {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
                const phoneInput = container.querySelector('input');
                if (phoneInput) phoneInput.value = '';
            }
        }
    }

    if (motherNameInput) {
        motherNameInput.addEventListener('input', function() {
            togglePhoneField(this, motherPhoneContainer);
        });
    }

    if (fatherNameInput) {
        fatherNameInput.addEventListener('input', function() {
            togglePhoneField(this, fatherPhoneContainer);
        });
    }

    // 4. Drag & Drop Photo
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('photo');
    const imgPreview = document.getElementById('imgPreview');
    const previewArea = document.getElementById('previewArea');
    const dropContent = document.getElementById('dropContent');
    const removeFileBtn = document.getElementById('removeFile');
    const fileNameDisplay = document.getElementById('fileName');

    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropArea.classList.add('dragover');
    }

    function unhighlight(e) {
        dropArea.classList.remove('dragover');
    }

    // Handle dropped files
    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    // Handle selected files via click
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onloadend = function() {
                    imgPreview.src = reader.result;
                    fileNameDisplay.textContent = file.name;
                    dropContent.classList.add('d-none');
                    previewArea.classList.remove('d-none');
                    // Sync with input if dropped
                    // Note: You can't programmatically set file input value from drop for security,
                    // but we can use DataTransfer if needed or just rely on the input change if clicked.
                    // If dropped, we need to manually assign to input if possible, or use AJAX.
                    // For standard form submit, input type="file" needs to be set. 
                    // Modern browsers allow setting files property.
                    if (fileInput.files.length === 0 || fileInput.files[0] !== file) {
                         const dataTransfer = new DataTransfer();
                         dataTransfer.items.add(file);
                         fileInput.files = dataTransfer.files;
                    }
                }
            } else {
                alert('Por favor, selecione apenas arquivos de imagem.');
            }
        }
    }

    removeFileBtn.addEventListener('click', function() {
        fileInput.value = '';
        imgPreview.src = '#';
        previewArea.classList.add('d-none');
        dropContent.classList.remove('d-none');
    });

    // 4.1 Attachments Drag & Drop
    const attachmentsDropArea = document.getElementById('attachmentsDropArea');
    const attachmentsInput = document.getElementById('attachments');
    const attachmentsPreviewArea = document.getElementById('attachmentsPreviewArea');
    
    // Store files in a DataTransfer object to manage them
    const attachmentsDT = new DataTransfer();

    if (attachmentsDropArea) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            attachmentsDropArea.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            attachmentsDropArea.addEventListener(eventName, () => attachmentsDropArea.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            attachmentsDropArea.addEventListener(eventName, () => attachmentsDropArea.classList.remove('dragover'), false);
        });

        attachmentsDropArea.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            handleAttachmentFiles(files);
        });

        attachmentsInput.addEventListener('change', function() {
            // Merge new files with existing ones
            handleAttachmentFiles(this.files);
        });
    }

    function handleAttachmentFiles(files) {
        if (!files || files.length === 0) return;

        const maxFiles = 10;

        for (let i = 0; i < files.length; i++) {
            const file = files[i];

            // Check limit
            if (attachmentsDT.items.length >= maxFiles) {
                alert('Limite máximo de 10 arquivos atingido.');
                break;
            }

            // Check mime type (no video)
            if (file.type.startsWith('video/')) {
                alert(`O arquivo "${file.name}" é um vídeo e não é permitido.`);
                continue;
            }

            // Check if already added (by name and size)
            let duplicate = false;
            for (let j = 0; j < attachmentsDT.files.length; j++) {
                if (attachmentsDT.files[j].name === file.name && attachmentsDT.files[j].size === file.size) {
                    duplicate = true;
                    break;
                }
            }
            if (duplicate) continue;

            attachmentsDT.items.add(file);
        }

        // Update input
        attachmentsInput.files = attachmentsDT.files;

        // Render preview
        renderAttachmentsPreview();
    }

    function renderAttachmentsPreview() {
        if (attachmentsDT.items.length > 0) {
            attachmentsPreviewArea.classList.remove('d-none');
        } else {
            attachmentsPreviewArea.classList.add('d-none');
        }

        attachmentsPreviewArea.innerHTML = '';

        Array.from(attachmentsDT.files).forEach((file, index) => {
            const col = document.createElement('div');
            col.className = 'col-md-6 col-lg-4';
            
            const card = document.createElement('div');
            card.className = 'card border shadow-sm h-100 position-relative';
            
            let iconClass = 'bi-file-earmark-text';
            if (file.type.includes('pdf')) iconClass = 'bi-file-earmark-pdf text-danger';
            else if (file.type.includes('image')) iconClass = 'bi-file-earmark-image text-primary';
            else if (file.type.includes('word') || file.type.includes('document')) iconClass = 'bi-file-earmark-word text-primary';
            else if (file.type.includes('sheet') || file.type.includes('excel')) iconClass = 'bi-file-earmark-excel text-success';
            
            card.innerHTML = `
                <div class="card-body d-flex align-items-center p-3">
                    <i class="bi ${iconClass} fs-2 me-3"></i>
                    <div class="overflow-hidden" style="flex: 1;">
                        <h6 class="card-title text-truncate mb-0" title="${file.name}">${file.name}</h6>
                        <small class="text-muted">${formatBytes(file.size)}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger ms-2 p-0" onclick="removeAttachment(${index})">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </button>
                </div>
            `;
            
            col.appendChild(card);
            attachmentsPreviewArea.appendChild(col);
        });
    }

    // Make remove function global so onclick works
    window.removeAttachment = function(index) {
        attachmentsDT.items.remove(index);
        attachmentsInput.files = attachmentsDT.files;
        renderAttachmentsPreview();
    }

    function formatBytes(bytes, decimals = 2) {
        if (!+bytes) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
    }
    
    // 5. Form Validation
    // form variable already declared above
    const submitSpinner = document.getElementById('submitSpinner');
    const submitBtn = document.getElementById('submitBtn');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            let errorMessages = [];

            // Required fields
            const requiredFields = [
                { id: 'name', name: 'Nome completo' },
                { id: 'phone', name: 'Celular' },
                { id: 'born_date', name: 'Data de Nascimento' }
            ];

            requiredFields.forEach(field => {
                const input = document.getElementById(field.id);
                if (input) {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('border-danger');
                        errorMessages.push(`O campo ${field.name} é obrigatório.`);
                    } else {
                        input.classList.remove('border-danger');
                    }
                }
            });
            
            // Phone length check
            const phone = document.getElementById('phone');
            if (phone && phone.value.replace(/\D/g, '').length < 11) {
                 isValid = false;
                 phone.classList.add('border-danger');
                 errorMessages.push('O celular deve conter DDD + 9 dígitos.');
            }

            if (!isValid) {
                event.preventDefault();
                alert('Atenção!\n\nPor favor, preencha os campos obrigatórios destacados em vermelho antes de salvar.');
            } else {
                // Show Spinner
                if (submitSpinner) submitSpinner.classList.remove('d-none');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Salvando...';
                }
            }
        });
        
        // Remove error class on input
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-danger');
            });
        });
    }
});
</script>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const errors = @json($errors->all());
            let errorMsg = "Erros de validação encontrados:\n\n";
            errors.forEach(function(error) {
                errorMsg += "- " + error + "\n";
            });
            alert(errorMsg);
        });
    </script>
@endif
@endsection
