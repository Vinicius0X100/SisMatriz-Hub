@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Comunicação em Massa</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Comunicação em Massa</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Registers List -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="min-height: 600px;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold mb-3 text-primary">
                        <i class="bi bi-people me-2"></i>Selecionar Destinatários
                    </h5>
                    <div class="d-flex gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="selectAllPageBtn">
                            <i class="bi bi-check2-square me-1"></i> Selecionar todos (página)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="clearSelectionBtn">
                            <i class="bi bi-x-circle me-1"></i> Limpar seleção
                        </button>
                    </div>
                    <div class="position-relative mb-3">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="searchInput" class="form-control ps-5 rounded-pill bg-light border-0" placeholder="Buscar por nome ou telefone..." style="height: 45px;">
                    </div>
                </div>
                <div class="card-body px-0 pt-2 position-relative" id="registers-container">
                    <!-- Loading Overlay -->
                    <div id="loadingOverlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-flex align-items-center justify-content-center d-none" style="z-index: 10;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                    
                    <div id="table-content">
                        @include('modules.mass-communication.registers-table')
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Message Composition & History -->
        <div class="col-lg-7">
             <!-- Compose Message -->
             <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 text-primary">
                        <i class="bi bi-whatsapp me-2"></i>Nova Mensagem
                    </h5>
                    
                    <form action="{{ route('mass-communication.send') }}" method="POST" id="sendForm">
                        @csrf
                        
                        <!-- Selected Recipients Area -->
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase mb-2">
                                Destinatários Selecionados <span class="badge bg-primary rounded-pill ms-2" id="recipientCount">0</span>
                            </label>
                            <div class="alert alert-info rounded-4 border-0 shadow-sm d-none" id="groupSelectionAlert">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                                    <div>
                                        <div class="fw-bold mb-1">Envio por grupo</div>
                                        <div class="small" id="groupSelectionText"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="selectedBadges" class="border rounded-4 p-3 d-flex flex-wrap gap-2 bg-light" style="min-height: 60px; max-height: 150px; overflow-y: auto;">
                                <span class="text-muted small align-self-center w-100 text-center" id="noRecipientsMsg">
                                    <i class="bi bi-person-plus fs-4 d-block mb-1 opacity-50"></i>
                                    Clique nos nomes à esquerda para selecionar
                                </span>
                            </div>
                            <!-- Hidden inputs container -->
                            <div id="hiddenInputsContainer"></div>
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label text-muted small fw-bold text-uppercase mb-2">Mensagem</label>
                            <textarea name="message" id="message" rows="5" class="form-control rounded-4 p-3" placeholder="Digite sua mensagem aqui..." required style="resize: none;"></textarea>
                            <div class="d-flex align-items-start mt-2 text-muted small">
                                <i class="bi bi-info-circle me-2 mt-1"></i>
                                <div>
                                    As variáveis <strong>Nome do Destinatário</strong> e <strong>Seu Nome</strong> serão inseridas automaticamente pelo template.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            @php
                                $canGroup = ($groupCapabilities['eucaristia'] ?? false) || ($groupCapabilities['crisma'] ?? false) || ($groupCapabilities['adultos'] ?? false);
                            @endphp
                            @if($canGroup)
                                <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold" id="groupSelectBtn" data-bs-toggle="modal" data-bs-target="#groupSelectModal">
                                    <i class="bi bi-people-fill me-2"></i> Enviar a um grupo
                                </button>
                            @else
                                <div></div>
                            @endif
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" id="submitBtn">
                                <i class="bi bi-send me-2"></i> Enviar Mensagem
                            </button>
                        </div>
                    </form>
                </div>
             </div>

             <!-- History -->
             <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold text-dark mb-0">Histórico Recente</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-bottom-4">
                        @forelse($history as $msg)
                            <div class="list-group-item px-4 py-3 border-light">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="me-3">
                                        <div class="fw-bold text-dark mb-1">{{ $msg->recipient->name ?? 'Desconhecido' }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 350px;">
                                            <i class="bi bi-chat-left-text me-1"></i> {{ $msg->message_body }}
                                        </div>
                                    </div>
                                    <div class="text-end" style="min-width: 100px;">
                                        @if($msg->status == 'sent')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill mb-1">
                                                <i class="bi bi-check-all me-1"></i> Enviado
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill mb-1">
                                                <i class="bi bi-exclamation-circle me-1"></i> Falha
                                            </span>
                                        @endif
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            {{ $msg->created_at->format('d/m H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted">
                                <small>Nenhum envio recente.</small>
                            </div>
                        @endforelse
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

<style>
    .cursor-pointer { cursor: pointer; }
    .register-row:hover { background-color: #f8f9fa; }
    .badge .bi-x-circle-fill:hover { opacity: 0.8; }
    
    /* Custom Scrollbar for badges area */
    #selectedBadges::-webkit-scrollbar {
        width: 6px;
    }
    #selectedBadges::-webkit-scrollbar-track {
        background: #f1f1f1; 
        border-radius: 4px;
    }
    #selectedBadges::-webkit-scrollbar-thumb {
        background: #ccc; 
        border-radius: 4px;
    }
    #selectedBadges::-webkit-scrollbar-thumb:hover {
        background: #aaa; 
    }
</style>

@php
    $canGroup = ($groupCapabilities['eucaristia'] ?? false) || ($groupCapabilities['crisma'] ?? false) || ($groupCapabilities['adultos'] ?? false);
    $groupTypes = [];
    if ($groupCapabilities['eucaristia'] ?? false) $groupTypes[] = 'eucaristia';
    if ($groupCapabilities['crisma'] ?? false) $groupTypes[] = 'crisma';
    if ($groupCapabilities['adultos'] ?? false) $groupTypes[] = 'adultos';
    $groupTypeLabels = [
        'eucaristia' => 'Primeira Eucaristia',
        'crisma' => 'Crisma',
        'adultos' => 'Catequese de Adultos',
    ];
    $groupTitle = count($groupTypes) === 1
        ? ('Enviar para turmas de ' . ($groupTypeLabels[$groupTypes[0]] ?? 'Catequese'))
        : 'Enviar para turmas de Catequese';
    $defaultGroupType = $groupTypes[0] ?? null;
    $canAllCatecheses = ($groupCapabilities['eucaristia'] ?? false) && ($groupCapabilities['crisma'] ?? false) && ($groupCapabilities['adultos'] ?? false);
@endphp
@if($canGroup)
<div class="modal fade" id="groupSelectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom-0 px-4 py-3">
                <div>
                    <h5 class="modal-title fw-bold mb-1">{{ $groupTitle }}</h5>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">
                            <i class="bi bi-shield-check me-1"></i> Acesso: Coordenador
                        </span>
                        @foreach($groupTypes as $t)
                            <span class="badge bg-light text-dark border rounded-pill px-3">
                                <i class="bi bi-mortarboard me-1"></i> {{ $groupTypeLabels[$t] ?? $t }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info rounded-4 border-0 shadow-sm">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <div>
                            <div class="fw-bold mb-1">Como funciona</div>
                            <div class="small text-muted">
                                Este envio considera apenas pessoas que já estão vinculadas em alguma turma. Você pode selecionar uma ou mais turmas e, se quiser, desmarcar destinatários individualmente antes de aplicar.
                            </div>
                        </div>
                    </div>
                </div>

                @if($canAllCatecheses)
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 p-lg-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <div class="fw-bold text-dark mb-1">Notificar todas as turmas (todas as catequeses)</div>
                                <div class="text-muted small">
                                    Seleciona automaticamente todos os alunos que estão em alguma turma de Eucaristia, Crisma e Adultos.
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="allTurmasSwitch">
                                <label class="form-check-label fw-semibold" for="allTurmasSwitch">Habilitar</label>
                            </div>
                        </div>
                        <div class="alert alert-warning rounded-4 border-0 shadow-sm mt-3 d-none" id="allTurmasHint">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                                <div>
                                    <div class="fw-bold mb-1">Atenção</div>
                                    <div class="small text-muted">
                                        Todos os selecionados receberão a mesma mensagem. Após aplicar, você pode desmarcar destinatários manualmente na lista principal, se necessário.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 d-none" id="allTurmasLoading">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <div class="text-muted small">Carregando turmas e alunos...</div>
                        </div>
                        <div class="text-muted small mt-2 d-none" id="allTurmasSummary"></div>
                    </div>
                </div>
                @endif

                @if(count($groupTypes) > 1)
                <ul class="nav nav-pills gap-2 mb-4" id="groupTabs" role="tablist">
                    @if($groupCapabilities['eucaristia'])
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $defaultGroupType === 'eucaristia' ? 'active' : '' }} rounded-pill" id="tab-eucaristia" data-bs-toggle="pill" data-bs-target="#pane-eucaristia" type="button" role="tab">Eucaristia</button>
                        </li>
                    @endif
                    @if($groupCapabilities['crisma'])
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $defaultGroupType === 'crisma' ? 'active' : '' }} rounded-pill" id="tab-crisma" data-bs-toggle="pill" data-bs-target="#pane-crisma" type="button" role="tab">Crisma</button>
                        </li>
                    @endif
                    @if($groupCapabilities['adultos'])
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $defaultGroupType === 'adultos' ? 'active' : '' }} rounded-pill" id="tab-adultos" data-bs-toggle="pill" data-bs-target="#pane-adultos" type="button" role="tab">Adultos</button>
                        </li>
                    @endif
                </ul>
                @endif

                <div class="tab-content">
                    @if($groupCapabilities['eucaristia'])
                        <div class="tab-pane fade {{ $defaultGroupType === 'eucaristia' ? 'show active' : '' }}" id="pane-eucaristia" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Turma</label>
                                    <select class="form-select rounded-pill" id="turmaSelect-eucaristia"></select>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button class="btn btn-outline-primary rounded-pill px-4" type="button" onclick="addTurmaToSelection('eucaristia')">
                                        <i class="bi bi-plus-lg me-1"></i> Adicionar turma
                                    </button>
                                </div>
                            </div>
                            <div id="turmasSelected-eucaristia"></div>
                        </div>
                    @endif

                    @if($groupCapabilities['crisma'])
                        <div class="tab-pane fade {{ $defaultGroupType === 'crisma' ? 'show active' : '' }}" id="pane-crisma" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Turma</label>
                                    <select class="form-select rounded-pill" id="turmaSelect-crisma"></select>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button class="btn btn-outline-primary rounded-pill px-4" type="button" onclick="addTurmaToSelection('crisma')">
                                        <i class="bi bi-plus-lg me-1"></i> Adicionar turma
                                    </button>
                                </div>
                            </div>
                            <div id="turmasSelected-crisma"></div>
                        </div>
                    @endif

                    @if($groupCapabilities['adultos'])
                        <div class="tab-pane fade {{ $defaultGroupType === 'adultos' ? 'show active' : '' }}" id="pane-adultos" role="tabpanel">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-muted small">Turma</label>
                                    <select class="form-select rounded-pill" id="turmaSelect-adultos"></select>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button class="btn btn-outline-primary rounded-pill px-4" type="button" onclick="addTurmaToSelection('adultos')">
                                        <i class="bi bi-plus-lg me-1"></i> Adicionar turma
                                    </button>
                                </div>
                            </div>
                            <div id="turmasSelected-adultos"></div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 px-4 py-3">
                <div class="me-auto text-muted small d-flex align-items-center">
                    <i class="bi bi-arrow-right-circle me-2"></i> Ao aplicar, os destinatários serão adicionados ao formulário principal.
                </div>
                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="applyGroupBtn">
                    <i class="bi bi-check-lg me-1"></i> Aplicar destinatários
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmAllTurmaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="text-primary mb-3">
                    <i class="bi bi-people-fill display-4"></i>
                </div>
                <h5 class="fw-bold mb-3">Incluir todos?</h5>
                <p class="text-muted small mb-0" id="confirmAllTurmaText"></p>
            </div>
            <div class="modal-footer bg-white border-top-0 px-4 py-3 justify-content-center gap-2">
                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal" id="confirmAllNoBtn">Não</button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="confirmAllYesBtn">Sim</button>
            </div>
        </div>
    </div>
</div>

@if($canAllCatecheses)
<div class="modal fade" id="confirmAllCatechesesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4">
                <div class="d-flex align-items-start gap-3">
                    <div class="text-danger">
                        <i class="bi bi-megaphone-fill display-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-2">Confirmar envio para todas as turmas</h5>
                        <p class="text-muted small mb-0">
                            Você está prestes a selecionar alunos de <strong>todas as turmas</strong> das catequeses (Eucaristia, Crisma e Adultos).
                            <br>Todos receberão a <strong>mesma mensagem</strong>.
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 px-4 py-3 justify-content-end gap-2">
                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal" id="confirmAllCatechesesCancel">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmAllCatechesesConfirm">
                    <i class="bi bi-check-lg me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<div id="massCommConfig"
     data-index-url="{{ route('mass-communication.index') }}"
     data-turmas-url="{{ route('mass-communication.turmas') }}"
     data-turmas-base-url="{{ url('mass-communication/turmas') }}"
     data-turmas-all-url="{{ route('mass-communication.turmas.all-recipients') }}"
     data-can-group="{{ $canGroup ? 1 : 0 }}"
     data-group-capabilities="{{ json_encode($groupCapabilities ?? ['eucaristia' => false, 'crisma' => false, 'adultos' => false]) }}">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedRecipients = new Map(); // ID -> Name
        const selectedFromGroups = { eucaristia: new Map(), crisma: new Map(), adultos: new Map() };
        let groupSelectionSummary = [];
        const cfgEl = document.getElementById('massCommConfig');
        const indexUrl = cfgEl ? cfgEl.getAttribute('data-index-url') : '';
        const turmasUrl = cfgEl ? cfgEl.getAttribute('data-turmas-url') : '';
        const turmasBaseUrl = cfgEl ? cfgEl.getAttribute('data-turmas-base-url') : '';
        const turmasAllUrl = cfgEl ? cfgEl.getAttribute('data-turmas-all-url') : '';
        const canGroup = (cfgEl ? cfgEl.getAttribute('data-can-group') : '0') === '1';
        const groupCapabilities = JSON.parse(cfgEl ? (cfgEl.getAttribute('data-group-capabilities') || '{}') : '{}');
        const searchInput = document.getElementById('searchInput');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const tableContent = document.getElementById('table-content');
        let searchTimeout;

        // --- Functions ---

        // Toggle recipient selection
        window.toggleRecipient = function(id, name) {
            id = parseInt(id);
            if (selectedRecipients.has(id)) {
                selectedRecipients.delete(id);
            } else {
                selectedRecipients.set(id, name);
            }
            updateUI();
        };

        function addRecipientSilent(id, name) {
            id = parseInt(id);
            if (!selectedRecipients.has(id)) {
                selectedRecipients.set(id, name);
            }
        }

        // Update UI (Badges, Hidden Inputs, Checkboxes)
        function updateUI() {
            const badgesContainer = document.getElementById('selectedBadges');
            const hiddenInputs = document.getElementById('hiddenInputsContainer');
            const countSpan = document.getElementById('recipientCount');
            const noMsg = document.getElementById('noRecipientsMsg');
            const submitBtn = document.getElementById('submitBtn');

            // Clear containers
            badgesContainer.innerHTML = '';
            hiddenInputs.innerHTML = '';

            // Update Badges & Inputs
            if (selectedRecipients.size === 0) {
                if(noMsg) {
                    badgesContainer.innerHTML = `
                        <span class="text-muted small align-self-center w-100 text-center" id="noRecipientsMsg">
                            <i class="bi bi-person-plus fs-4 d-block mb-1 opacity-50"></i>
                            Clique nos nomes à esquerda para selecionar
                        </span>
                    `;
                }
                submitBtn.disabled = true;
            } else {
                submitBtn.disabled = false;
                selectedRecipients.forEach((name, id) => {
                    // Badge
                    const badge = document.createElement('div');
                    badge.className = 'badge bg-white text-dark border shadow-sm d-flex align-items-center gap-2 p-2 rounded-pill';
                    badge.innerHTML = `
                        <span class="fw-normal">${name}</span>
                        <i class="bi bi-x-circle-fill text-danger cursor-pointer" onclick="toggleRecipient(${id}, '${name.replace(/'/g, "\\'")}')"></i>
                    `;
                    badgesContainer.appendChild(badge);
                    
                    // Hidden Input
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'recipients[]';
                    input.value = id;
                    hiddenInputs.appendChild(input);
                });
            }
            
            countSpan.textContent = selectedRecipients.size;

            // Update Checkboxes in current view
            document.querySelectorAll('.recipient-checkbox').forEach(cb => {
                const id = parseInt(cb.value);
                cb.checked = selectedRecipients.has(id);
                
                // Highlight row
                const row = cb.closest('tr');
                if (row) {
                    if (cb.checked) {
                        row.classList.add('table-primary');
                    } else {
                        row.classList.remove('table-primary');
                    }
                }
            });
        }

        const clearSelectionBtn = document.getElementById('clearSelectionBtn');
        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function() {
                selectedRecipients.clear();
                groupSelectionSummary = [];
                const alertBox = document.getElementById('groupSelectionAlert');
                const alertText = document.getElementById('groupSelectionText');
                if (alertBox) alertBox.classList.add('d-none');
                if (alertText) alertText.textContent = '';
                updateUI();
            });
        }

        const selectAllPageBtn = document.getElementById('selectAllPageBtn');
        if (selectAllPageBtn) {
            selectAllPageBtn.addEventListener('click', function() {
                document.querySelectorAll('.recipient-checkbox').forEach(cb => {
                    const id = parseInt(cb.value);
                    const row = cb.closest('tr');
                    const nameCell = row ? row.querySelector('td:nth-child(2)') : null;
                    const name = nameCell ? nameCell.textContent.trim() : ('Registro #' + id);
                    addRecipientSilent(id, name);
                });
                updateUI();
            });
        }

        // Fetch registers via AJAX
        function fetchRegisters(url) {
            loadingOverlay.classList.remove('d-none');
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                tableContent.innerHTML = html;
                updateUI(); // Re-apply checks
                setupPaginationLinks(); // Re-attach event listeners
            })
            .catch(err => console.error('Error fetching registers:', err))
            .finally(() => {
                loadingOverlay.classList.add('d-none');
            });
        }

        // Setup Pagination Links Interception
        function setupPaginationLinks() {
            document.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (this.href) {
                        fetchRegisters(this.href);
                    }
                });
            });
        }

        // --- Event Listeners ---

        // Search Input
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = this.value;
                const url = new URL(indexUrl || window.location.href);
                if (query) {
                    url.searchParams.set('search', query);
                }
                fetchRegisters(url.toString());
            }, 500);
        });

        // Form Submit Validation
        document.getElementById('sendForm').addEventListener('submit', function(e) {
            if (selectedRecipients.size === 0) {
                e.preventDefault();
                alert('Por favor, selecione pelo menos um destinatário.');
            }
        });

        const groupModalEl = canGroup ? document.getElementById('groupSelectModal') : null;
        const confirmAllModalEl = canGroup ? document.getElementById('confirmAllTurmaModal') : null;
        const confirmAllModal = confirmAllModalEl ? new bootstrap.Modal(confirmAllModalEl) : null;
        const confirmAllCatechesesModalEl = canGroup ? document.getElementById('confirmAllCatechesesModal') : null;
        const confirmAllCatechesesModal = confirmAllCatechesesModalEl ? new bootstrap.Modal(confirmAllCatechesesModalEl) : null;
        let pendingTurmaAdd = null;
        let allTurmasEnabled = false;
        let allTurmasData = null;

        const turmasCache = { eucaristia: [], crisma: [], adultos: [] };

        function fillTurmaSelect(tipo) {
            const select = document.getElementById('turmaSelect-' + tipo);
            if (!select) return;
            select.innerHTML = '';
            const opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = 'Selecione...';
            select.appendChild(opt0);
            turmasCache[tipo].forEach(t => {
                const opt = document.createElement('option');
                opt.value = t.id;
                opt.textContent = t.nome;
                select.appendChild(opt);
            });
        }

        async function loadTurmas(tipo) {
            const url = new URL(turmasUrl || (window.location.origin + '/mass-communication/turmas'), window.location.origin);
            url.searchParams.set('tipo', tipo);
            const res = await fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const json = await res.json();
            turmasCache[tipo] = json.turmas || [];
            fillTurmaSelect(tipo);
        }

        if (groupModalEl) {
            groupModalEl.addEventListener('shown.bs.modal', function() {
                if (groupCapabilities.eucaristia) loadTurmas('eucaristia');
                if (groupCapabilities.crisma) loadTurmas('crisma');
                if (groupCapabilities.adultos) loadTurmas('adultos');
            });
        }

        const allTurmasSwitch = document.getElementById('allTurmasSwitch');
        const allTurmasHint = document.getElementById('allTurmasHint');
        const allTurmasLoading = document.getElementById('allTurmasLoading');
        const allTurmasSummary = document.getElementById('allTurmasSummary');
        const setGroupUiDisabled = (disabled) => {
            const tabs = document.getElementById('groupTabs');
            if (tabs) tabs.classList.toggle('opacity-50', disabled);
            if (tabs) tabs.querySelectorAll('button').forEach(b => b.disabled = disabled);
            ['eucaristia', 'crisma', 'adultos'].forEach(t => {
                const select = document.getElementById('turmaSelect-' + t);
                if (select) select.disabled = disabled;
            });
            document.querySelectorAll('#groupSelectModal [onclick^="addTurmaToSelection"]').forEach(btn => btn.disabled = disabled);
        };

        async function loadAllTurmasRecipients() {
            if (!turmasAllUrl) return null;
            if (allTurmasLoading) allTurmasLoading.classList.remove('d-none');
            if (allTurmasSummary) allTurmasSummary.classList.add('d-none');
            const res = await fetch(turmasAllUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) {
                if (allTurmasLoading) allTurmasLoading.classList.add('d-none');
                return null;
            }
            const json = await res.json();
            if (allTurmasLoading) allTurmasLoading.classList.add('d-none');
            return json;
        }

        if (allTurmasSwitch) {
            allTurmasSwitch.addEventListener('change', async function() {
                allTurmasEnabled = this.checked;
                if (allTurmasHint) allTurmasHint.classList.toggle('d-none', !allTurmasEnabled);
                setGroupUiDisabled(allTurmasEnabled);
                if (!allTurmasEnabled) {
                    allTurmasData = null;
                    if (allTurmasSummary) allTurmasSummary.classList.add('d-none');
                    return;
                }
                if (!allTurmasData) {
                    allTurmasData = await loadAllTurmasRecipients();
                }
                if (allTurmasData && allTurmasSummary) {
                    const c = allTurmasData.counts || {};
                    const t = (c.turmas || {});
                    allTurmasSummary.textContent = `Turmas: Eucaristia ${t.eucaristia || 0}, Crisma ${t.crisma || 0}, Adultos ${t.adultos || 0} · Destinatários (únicos): ${c.recipients || 0}`;
                    allTurmasSummary.classList.remove('d-none');
                }
            });
        }

        window.addTurmaToSelection = async function(tipo) {
            if (!canGroup) return;
            if (allTurmasEnabled) return;
            const select = document.getElementById('turmaSelect-' + tipo);
            const container = document.getElementById('turmasSelected-' + tipo);
            if (!select || !container) return;
            const turmaId = parseInt(select.value || '0', 10);
            if (!turmaId) return;

            const turma = turmasCache[tipo].find(t => t.id === turmaId);
            if (!turma) return;

            pendingTurmaAdd = { tipo, turmaId, turmaNome: turma.nome };
            const textEl = document.getElementById('confirmAllTurmaText');
            if (textEl) textEl.textContent = `Deseja selecionar todos os alunos da turma "${turma.nome}" por padrão? Você poderá desmarcar depois.`;
            if (confirmAllModal) confirmAllModal.show();
        };

        const confirmAllYesBtn = canGroup ? document.getElementById('confirmAllYesBtn') : null;
        const confirmAllNoBtn = canGroup ? document.getElementById('confirmAllNoBtn') : null;
        if (confirmAllYesBtn) {
            confirmAllYesBtn.addEventListener('click', function() {
                if (!pendingTurmaAdd) return;
                confirmAllModal.hide();
                fetchAndRenderTurma(pendingTurmaAdd.tipo, pendingTurmaAdd.turmaId, pendingTurmaAdd.turmaNome, true);
                pendingTurmaAdd = null;
            });
        }
        if (confirmAllNoBtn) {
            confirmAllNoBtn.addEventListener('click', function() {
                if (!pendingTurmaAdd) return;
                confirmAllModal.hide();
                fetchAndRenderTurma(pendingTurmaAdd.tipo, pendingTurmaAdd.turmaId, pendingTurmaAdd.turmaNome, false);
                pendingTurmaAdd = null;
            });
        }

        async function fetchAndRenderTurma(tipo, turmaId, turmaNome, selectAllDefault) {
            const container = document.getElementById('turmasSelected-' + tipo);
            if (!container) return;
            const existing = container.querySelector(`[data-turma-id="${turmaId}"]`);
            if (existing) return;

            const res = await fetch(`${turmasBaseUrl}/${tipo}/${turmaId}/recipients`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const json = await res.json();
            const recipients = json.recipients || [];

            const card = document.createElement('div');
            card.className = 'card border-0 shadow-sm rounded-4 mb-3';
            card.setAttribute('data-turma-id', turmaId);
            card.innerHTML = `
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-bold text-dark">${turmaNome}</div>
                            <div class="text-muted small">Alunos: <span class="fw-bold" data-total-count="1">${recipients.length}</span> · Selecionados: <span class="fw-bold" data-selected-count="1">0</span></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill" data-remove-turma="1">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" data-select-all="1" ${selectAllDefault ? 'checked' : ''}>
                        <label class="form-check-label small text-muted" data-select-all-label="1">${selectAllDefault ? 'Todos marcados' : 'Nenhum selecionado'}</label>
                    </div>
                    <div class="border rounded-4 p-2" style="max-height: 240px; overflow-y: auto;">
                        ${recipients.map(r => `
                            <div class="form-check d-flex align-items-center justify-content-between py-1">
                                <label class="form-check-label small text-dark" for="grp_${tipo}_${turmaId}_${r.id}">
                                    ${r.name}
                                </label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="text-muted small">${r.phone || ''}</div>
                                    <input class="form-check-input" type="checkbox" id="grp_${tipo}_${turmaId}_${r.id}" data-recipient-id="${r.id}" data-recipient-name="${encodeURIComponent(r.name)}" ${selectAllDefault ? 'checked' : ''}>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;

            card.querySelector('[data-remove-turma="1"]').addEventListener('click', function() {
                card.remove();
            });

            const selectAllCb = card.querySelector('[data-select-all="1"]');
            const selectAllLabel = card.querySelector('[data-select-all-label="1"]');
            const selectedCountEl = card.querySelector('[data-selected-count="1"]');
            const updateSelectedCount = () => {
                const all = Array.from(card.querySelectorAll('[data-recipient-id]'));
                const selected = all.filter(x => x.checked).length;
                if (selectedCountEl) selectedCountEl.textContent = String(selected);
                const total = all.length;
                if (selectAllLabel) {
                    if (selected === 0) selectAllLabel.textContent = 'Nenhum selecionado';
                    else if (selected === total) selectAllLabel.textContent = 'Todos marcados';
                    else selectAllLabel.textContent = 'Seleção parcial';
                }
            };
            selectAllCb.addEventListener('change', function() {
                const checked = this.checked;
                card.querySelectorAll('[data-recipient-id]').forEach(cb => cb.checked = checked);
                updateSelectedCount();
            });

            card.querySelectorAll('[data-recipient-id]').forEach(cb => {
                cb.addEventListener('change', function() {
                    const all = Array.from(card.querySelectorAll('[data-recipient-id]'));
                    const allChecked = all.every(x => x.checked);
                    selectAllCb.checked = allChecked;
                    updateSelectedCount();
                });
            });

            updateSelectedCount();
            container.appendChild(card);
        }

        const applyGroupBtn = document.getElementById('applyGroupBtn');
        if (applyGroupBtn) {
            applyGroupBtn.addEventListener('click', function() {
                if (allTurmasEnabled) {
                    if (!confirmAllCatechesesModal) return;
                    confirmAllCatechesesModal.show();
                    return;
                }
                const summaries = [];
                let totalSelected = 0;
                document.querySelectorAll('#groupSelectModal [data-turma-id]').forEach(card => {
                    const turmaId = card.getAttribute('data-turma-id');
                    const turmaNameEl = card.querySelector('.fw-bold');
                    const turmaName = turmaNameEl ? turmaNameEl.textContent.trim() : '';
                    const selected = Array.from(card.querySelectorAll('[data-recipient-id]')).filter(cb => cb.checked);
                    selected.forEach(cb => {
                        const id = cb.getAttribute('data-recipient-id');
                        const name = decodeURIComponent(cb.getAttribute('data-recipient-name') || '');
                        addRecipientSilent(id, name);
                        totalSelected++;
                    });
                    if (turmaName) summaries.push(turmaName);
                });

                groupSelectionSummary = summaries;
                const alertBox = document.getElementById('groupSelectionAlert');
                const alertText = document.getElementById('groupSelectionText');
                if (alertBox && alertText) {
                    if (summaries.length > 0) {
                        alertText.textContent = `Turmas selecionadas: ${summaries.join(', ')}.`;
                        alertBox.classList.remove('d-none');
                    } else {
                        alertText.textContent = '';
                        alertBox.classList.add('d-none');
                    }
                }

                updateUI();
                bootstrap.Modal.getOrCreateInstance(groupModalEl).hide();
            });
        }

        const confirmAllCatechesesConfirm = document.getElementById('confirmAllCatechesesConfirm');
        if (confirmAllCatechesesConfirm) {
            confirmAllCatechesesConfirm.addEventListener('click', async function() {
                const btn = this;
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Aplicando...';
                try {
                    if (!allTurmasData) {
                        allTurmasData = await loadAllTurmasRecipients();
                    }
                    if (!allTurmasData) {
                        alert('Não foi possível carregar as turmas. Tente novamente.');
                        return;
                    }
                    const recipients = allTurmasData.recipients || [];
                    recipients.forEach(r => addRecipientSilent(r.id, r.name));

                    const alertBox = document.getElementById('groupSelectionAlert');
                    const alertText = document.getElementById('groupSelectionText');
                    if (alertBox && alertText) {
                        alertText.textContent = 'Turmas selecionadas: Todas as turmas (Eucaristia, Crisma e Adultos).';
                        alertBox.classList.remove('d-none');
                    }

                    updateUI();
                    if (confirmAllCatechesesModal) confirmAllCatechesesModal.hide();
                    bootstrap.Modal.getOrCreateInstance(groupModalEl).hide();
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = original;
                }
            });
        }

        // Initial Setup
        updateUI();
        setupPaginationLinks();
    });
</script>
@endsection
