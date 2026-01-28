@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Registro</h2>
            <p class="text-muted small mb-0">Atualize os dados de {{ $register->name }}.</p>
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
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            
            <form action="{{ route('registers.update', $register->id) }}" method="POST" enctype="multipart/form-data" id="editRegisterForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" value="{{ $register->status }}">

                <!-- Row 1: Nome, Email, Celular -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="name" class="form-label fw-bold small text-muted">Nome completo <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="name" name="name" value="{{ old('name', $register->name) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="email" class="form-label fw-bold small text-muted">E-mail</label>
                        <input type="email" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="email" name="email" value="{{ old('email', $register->email) }}">
                    </div>
                    <div class="col-md-4">
                        <label for="phone" class="form-label fw-bold small text-muted">
                            Celular <span class="text-danger fw-bold small">(obrigatório)</span>
                        </label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="phone" name="phone" value="{{ old('phone', $register->phone) }}" required maxlength="11">
                        <div class="form-text small text-muted ps-3">
                            <i class="bi bi-info-circle me-1"></i> Apenas números, com DDD (Ex: 11912345678)
                        </div>
                    </div>
                </div>

                <!-- Row 2: RG, CPF, Idade, Nascimento -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="rg" class="form-label fw-bold small text-muted">RG</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="rg" name="rg" value="{{ old('rg', $register->rg) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="cpf" class="form-label fw-bold small text-muted">CPF</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="cpf" name="cpf" value="{{ old('cpf', $register->cpf) }}">
                    </div>
                    <div class="col-md-2">
                        <label for="age" class="form-label fw-bold small text-muted">Idade</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="age" name="age" value="{{ old('age', $register->age) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="born_date" class="form-label fw-bold small text-muted">Data de Nascimento <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="date" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="born_date" name="born_date" value="{{ old('born_date', optional($register->born_date)->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <!-- Row 3: CEP, Endereço, Numero, Cidade -->
                <div class="row g-4 mb-4">
                    <div class="col-md-2">
                        <label for="cep" class="form-label fw-bold small text-muted">CEP <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="cep" name="cep" value="{{ old('cep', $register->cep) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="address" class="form-label fw-bold small text-muted">Endereço <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="address" name="address" value="{{ old('address', $register->address) }}">
                    </div>
                    <div class="col-md-2">
                        <label for="address_number" class="form-label fw-bold small text-muted">Núm.</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="address_number" name="address_number" value="{{ old('address_number', $register->address_number) }}">
                    </div>
                    <div class="col-md-2">
                        <label for="city" class="form-label fw-bold small text-muted">Cidade <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="city" name="city" value="{{ old('city', $register->city) }}">
                    </div>
                </div>

                <!-- Row 4: Estado, Pais, Bairro -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="state" class="form-label fw-bold small text-muted">Estado <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="state" name="state">
                            <option value="">Selecione...</option>
                            @foreach(['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'] as $uf)
                                <option value="{{ $uf }}" {{ (old('state', $register->state) == $uf) ? 'selected' : '' }}>{{ $uf }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="country" class="form-label fw-bold small text-muted">País</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="country" name="country" data-selected="{{ old('country', $register->country) }}">
                            <option value="Brasil">Brasil</option>
                            <!-- List populated via JS -->
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="neighborhood" class="form-label fw-bold small text-muted">Bairro <span class="text-primary fw-bold small">(recomendado)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="home_situation" name="home_situation" value="{{ old('home_situation', $register->home_situation) }}">
                    </div>
                </div>

                <hr class="my-4 text-muted opacity-25">

                <!-- Row 5: Estado Civil, Emprego, Raça, Sexo -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="civil_status" class="form-label fw-bold small text-muted">Estado Civil</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="civil_status" name="civil_status">
                            <option value="1" {{ (old('civil_status', $register->civil_status) == 1) ? 'selected' : '' }}>Solteiro(a)</option>
                            <option value="2" {{ (old('civil_status', $register->civil_status) == 2) ? 'selected' : '' }}>Casado(a)</option>
                            <option value="3" {{ (old('civil_status', $register->civil_status) == 3) ? 'selected' : '' }}>União Estável</option>
                            <option value="4" {{ (old('civil_status', $register->civil_status) == 4) ? 'selected' : '' }}>Divorciado</option>
                            <option value="5" {{ (old('civil_status', $register->civil_status) == 5) ? 'selected' : '' }}>Viuvo(a)</option>
                            <option value="6" {{ (old('civil_status', $register->civil_status) == 6) ? 'selected' : '' }}>Nao Declarado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="sexo" class="form-label fw-bold small text-muted">Sexo <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="sexo" name="sexo" required>
                            <option value="">Selecione...</option>
                            <option value="1" {{ (old('sexo', $register->sexo) == 1) ? 'selected' : '' }}>Masculino</option>
                            <option value="2" {{ (old('sexo', $register->sexo) == 2) ? 'selected' : '' }}>Feminino</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="work_state" class="form-label fw-bold small text-muted">Situação de Emprego</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="work_state" name="work_state">
                            <option value="4" {{ (old('work_state', $register->work_state) == 4) ? 'selected' : '' }}>Nao Declarado</option>
                            <option value="1" {{ (old('work_state', $register->work_state) == 1) ? 'selected' : '' }}>Desempregado</option>
                            <option value="2" {{ (old('work_state', $register->work_state) == 2) ? 'selected' : '' }}>Autonomo(a)</option>
                            <option value="3" {{ (old('work_state', $register->work_state) == 3) ? 'selected' : '' }}>Empresário</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="race" class="form-label fw-bold small text-muted">Raça/Cor</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="race" name="race">
                            <option value="5" {{ (old('race', $register->race) == 5) ? 'selected' : '' }}>Não declarado</option>
                            <option value="1" {{ (old('race', $register->race) == 1) ? 'selected' : '' }}>Branco</option>
                            <option value="2" {{ (old('race', $register->race) == 2) ? 'selected' : '' }}>Preto</option>
                            <option value="3" {{ (old('race', $register->race) == 3) ? 'selected' : '' }}>Pardo</option>
                            <option value="4" {{ (old('race', $register->race) == 4) ? 'selected' : '' }}>Amarelo</option>
                        </select>
                    </div>
                </div>

                <!-- Row 6: Mae, Pai, Pessoas -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="mother_name" class="form-label fw-bold small text-muted">Nome da Mãe</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="mother_name" name="mother_name" value="{{ old('mother_name', $register->mother_name) }}">
                        
                        <div class="mt-2 {{ $register->mother_name ? '' : 'd-none' }}" id="mother_phone_container">
                            <label for="mother_phone" class="form-label fw-bold small text-muted">Celular da Mãe</label>
                            <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="mother_phone" name="mother_phone" value="{{ old('mother_phone', $register->mother_phone) }}" maxlength="11" placeholder="Ex: 11999999999">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="father_name" class="form-label fw-bold small text-muted">Nome do Pai</label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="father_name" name="father_name" value="{{ old('father_name', $register->father_name) }}">

                        <div class="mt-2 {{ $register->father_name ? '' : 'd-none' }}" id="father_phone_container">
                            <label for="father_phone" class="form-label fw-bold small text-muted">Celular do Pai</label>
                            <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="father_phone" name="father_phone" value="{{ old('father_phone', $register->father_phone) }}" maxlength="11" placeholder="Ex: 11999999999">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="familly_qntd" class="form-label fw-bold small text-muted">Num. Pessoas (Na mesma casa)</label>
                        <input type="number" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="familly_qntd" name="familly_qntd" value="{{ old('familly_qntd', $register->familly_qntd) }}">
                    </div>
                </div>

                <!-- Row 7: Foto Drag & Drop -->
                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Foto</label>
                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-5 text-center position-relative" id="dropArea">
                            <input type="file" name="photo" id="photo" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/*">
                            
                            @if($register->photo)
                                <div id="previewArea" class="mt-3">
                                    <img id="imgPreview" src="{{ asset('storage/uploads/registers/' . $register->photo) }}" alt="Preview" class="rounded-4 shadow-sm border" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                                    <p id="fileName" class="small text-muted mt-2 mb-0">Foto Atual</p>
                                    <button type="button" class="btn btn-sm btn-light rounded-pill border mt-2" id="removeFile">Trocar Foto</button>
                                </div>
                                <div class="d-flex flex-column align-items-center justify-content-center d-none" id="dropContent">
                                    <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i>
                                    <h6 class="fw-bold text-dark">Arraste e solte sua foto aqui</h6>
                                    <p class="text-muted small mb-0">ou clique para selecionar</p>
                                </div>
                            @else
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
                            @endif
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 align-items-center">
                    <div id="submitSpinner" class="d-none d-flex align-items-center me-3">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        <span class="text-primary fw-bold small">Salvando...</span>
                    </div>
                    <a href="{{ route('registers.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="submitBtn">Atualizar Registro</button>
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
        <p class="fw-bold text-dark mt-3 mb-0">Deseja realmente prosseguir com a atualização?</p>
      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" id="confirmDuplicateBtn">Sim, Atualizar</button>
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

    // 0. Phone Input Restriction
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 11);
        });
    }

    // Duplicate Phone Check
    const form = document.getElementById('editRegisterForm');
    let phoneChecked = false;
    const registerId = "{{ $register->id }}";

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

            // Show loading state on button
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Verificando...';
            submitBtn.disabled = true;

            // Check phone via AJAX
            fetch(`{{ route('registers.check-phone') }}?phone=${phone}&exclude_id=${registerId}`, {
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
                phoneChecked = true;
                form.submit();
            });
        });
    }

    // 1. Countries List
    const countrySelect = document.getElementById('country');
    const selectedCountry = countrySelect.getAttribute('data-selected');
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
            if (selectedCountry && selectedCountry === c) {
                opt.selected = true;
            }
            countrySelect.appendChild(opt);
        }
    });

    // 2. Age Calculation
    const bornDateInput = document.getElementById('born_date');
    const ageInput = document.getElementById('age');

    function calculateAge(dateString) {
        if (!dateString) return '';
        const today = new Date();
        const birthDate = new Date(dateString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    }

    // Calculate on load if value exists
    if (bornDateInput.value) {
        ageInput.value = calculateAge(bornDateInput.value);
    }

    bornDateInput.addEventListener('change', function() {
        ageInput.value = calculateAge(this.value);
    });

    // 3. ViaCEP Integration (Optional if needed in Edit)
    const cepInput = document.getElementById('cep');
    const addressInput = document.getElementById('address');
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');
    const neighborhoodInput = document.getElementById('home_situation');
    const numberInput = document.getElementById('address_number');

    if (cepInput) {
        cepInput.addEventListener('blur', function() {
            let cep = this.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            if(addressInput) addressInput.value = data.logradouro;
                            if(neighborhoodInput) neighborhoodInput.value = data.bairro;
                            if(cityInput) cityInput.value = data.localidade;
                            if(stateInput) stateInput.value = data.uf;
                            if(numberInput) numberInput.focus();
                        } else {
                            alert('CEP não encontrado.');
                        }
                    })
                    .catch(err => console.error('Erro ao buscar CEP:', err));
            }
        });
    }

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
        // If editing, we might want to revert to original image or show "no image"
        // For now, let's clear it to allow uploading a new one or saving with no change/deletion
        // Ideally we need a hidden input to signal "remove photo" to backend if user wants to delete it.
        // But for this requirement, just clearing the preview/input for new upload is enough.
        imgPreview.src = '#';
        previewArea.classList.add('d-none');
        dropContent.classList.remove('d-none');
    });
    
    // 5. Form Validation
    // form variable already declared above
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
                const submitSpinner = document.getElementById('submitSpinner');
                const submitBtn = document.getElementById('submitBtn');
                
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

@if ($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        let errors = "";
        @foreach ($errors->all() as $error)
            errors += "- {{ $error }}\n";
        @endforeach
        alert('Erros de validação encontrados:\n\n' + errors);
    });
@endif
</script>
@endsection
