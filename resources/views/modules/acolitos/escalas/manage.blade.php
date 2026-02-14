@extends('layouts.app')

@section('title', 'Gerenciar Escala - ' . $escala->month . '/' . $escala->year)

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Gerenciar Escala</h2>
            <p class="text-muted mb-0">{{ $escala->month }} de {{ $escala->year }} - {{ $escala->church }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.index') }}" class="text-decoration-none">Acólitos</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.escalas.index') }}" class="text-decoration-none">Escalas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gerenciar</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Calendar Wrapper -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            
            <!-- Legend -->
            <div class="d-flex gap-3 mb-3 justify-content-end">
                <div class="d-flex align-items-center">
                    <span class="d-inline-block rounded-circle bg-primary me-2" style="width: 10px; height: 10px;"></span>
                    <span class="small text-muted">Publicado</span>
                </div>
                <div class="d-flex align-items-center">
                    <span class="d-inline-block rounded-circle bg-warning me-2" style="width: 10px; height: 10px;"></span>
                    <span class="small text-muted">Rascunho</span>
                </div>
            </div>

            <!-- Calendar Grid -->
    <div class="calendar-grid">
        <!-- Weekday Headers -->
        @php
            $weekdays = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
        @endphp
        @foreach($weekdays as $day)
            <div class="text-center text-muted small fw-bold text-uppercase py-2">{{ $day }}</div>
        @endforeach

        <!-- Empty Cells for start of month -->
        @php
            $firstDayOfWeek = \Carbon\Carbon::createFromDate($year, $monthNum, 1)->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
            $emptyCells = $firstDayOfWeek - 1;
        @endphp
        @for($i = 0; $i < $emptyCells; $i++)
            <div class="calendar-day empty bg-light rounded-3"></div>
        @endfor

        <!-- Days of Month -->
        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateKey = $day;
                $dayCelebrations = $celebrationsByDay[$day] ?? collect();
                $isWeekend = \Carbon\Carbon::createFromDate($year, $monthNum, $day)->isWeekend();
                
                $amIServingToday = false;
                $myServingCelebrationId = null;
                if (!$canEdit && $myAcolitoId) {
                    foreach($dayCelebrations as $cel) {
                        if ($cel->type !== 'draft' && isset($cel->escalados)) {
                            foreach($cel->escalados as $escalado) {
                                if ($escalado->acolito_id == $myAcolitoId) {
                                    $amIServingToday = true;
                                    $myServingCelebrationId = $cel->d_id;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            @endphp
            <div class="calendar-day {{ $isWeekend ? 'bg-light' : '' }} border rounded-3 p-2 position-relative {{ $amIServingToday ? 'border-success bg-success bg-opacity-10' : '' }}" 
                 style="min-height: 120px;" 
                 @if($canEdit) onclick="openCreateModal({{ $day }})" @endif>
                
                <div class="d-flex justify-content-between align-items-start">
                    <span class="day-number fw-bold {{ $isWeekend ? 'text-primary' : 'text-secondary' }}">{{ $day }}</span>
                    @if($amIServingToday)
                        <span class="badge bg-success rounded-pill shadow-sm animate__animated animate__pulse animate__infinite" 
                              style="font-size: 0.65rem; cursor: pointer;"
                              onclick="event.stopPropagation(); openViewModal('{{ $myServingCelebrationId }}')">
                            <i class="bi bi-person-check-fill me-1"></i> Você servirá aqui
                        </span>
                    @endif
                </div>
                
                <!-- Celebrations List -->
                <div class="mt-2 d-flex flex-column gap-1">
                    @foreach($dayCelebrations as $cel)
                        @php
                            $isDraft = isset($cel->type) && $cel->type === 'draft';
                            $imInThisOne = false;
                            
                            if (!$canEdit && $myAcolitoId && !$isDraft && isset($cel->escalados)) {
                                foreach($cel->escalados as $escalado) {
                                    if ($escalado->acolito_id == $myAcolitoId) {
                                        $imInThisOne = true;
                                        break;
                                    }
                                }
                            }

                            // Show celebration only if I'm in it OR I can edit OR show all for transparency? 
                            // Request implies showing the badge on the day. Let's show all but highlight mine.
                            // If user can't edit, they shouldn't see drafts probably? Let's hide drafts for rule 8.
                            if (!$canEdit && $isDraft) continue;

                            $badgeClass = $isDraft 
                                ? 'bg-warning bg-opacity-10 text-warning-emphasis border border-warning-subtle' 
                                : ($imInThisOne ? 'bg-success text-white shadow-sm border border-success' : 'bg-primary bg-opacity-10 text-primary border border-primary-subtle');
                            
                            $iconClass = $isDraft ? 'bi-file-earmark-text' : 'bi-clock';
                        @endphp
                        <div class="celebration-badge p-1 px-2 rounded-2 {{ $badgeClass }} d-flex align-items-center justify-content-between" 
                             @if($canEdit) 
                                onclick="event.stopPropagation(); openEditModal('{{ $cel->d_id }}')"
                             @else
                                onclick="event.stopPropagation(); openViewModal('{{ $cel->d_id }}')"
                             @endif
                             >
                            <div class="text-truncate small fw-medium" style="max-width: 85%;">
                                <i class="bi {{ $iconClass }} me-1"></i>{{ \Carbon\Carbon::parse($cel->hora)->format('H:i') }} - {{ $cel->celebration }}
                            </div>
                            @if($canEdit)
                                @if(isset($cel->escalados) && $cel->escalados->count() > 0)
                                    <span class="badge {{ $isDraft ? 'bg-warning text-dark' : 'bg-primary' }} rounded-pill" style="font-size: 0.6rem;">{{ $cel->escalados->count() }}</span>
                                @elseif(isset($cel->payload['acolitos']) && count($cel->payload['acolitos']) > 0)
                                    <span class="badge {{ $isDraft ? 'bg-warning text-dark' : 'bg-primary' }} rounded-pill" style="font-size: 0.6rem;">{{ count($cel->payload['acolitos']) }}</span>
                                @else
                                    <i class="bi bi-exclamation-circle-fill {{ $isDraft ? 'text-dark' : 'text-warning' }}" style="font-size: 0.7rem;"></i>
                                @endif
                            @elseif($imInThisOne)
                                <i class="bi bi-eye-fill" style="font-size: 0.7rem;"></i>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                <!-- Add Button (Visible on Hover) -->
                @if($canEdit)
                <div class="add-indicator position-absolute top-50 start-50 translate-middle opacity-0 transition-opacity">
                    <i class="bi bi-plus-circle-fill fs-3 text-primary"></i>
                </div>
                @endif
            </div>
        @endfor
    </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Detalhes da Celebração</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-4">
                <div class="text-center mb-4">
                    <div class="display-1 text-primary mb-2"><i class="bi bi-calendar-check"></i></div>
                    <h4 class="fw-bold" id="viewCelebrationName"></h4>
                    <p class="text-muted mb-0" id="viewCelebrationTime"></p>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill mt-2" id="viewCelebrationEnt"></span>
                </div>
                
                <div class="card bg-light border-0 rounded-4 p-3">
                    <h6 class="fw-bold mb-3 text-secondary small text-uppercase">Sua Equipe</h6>
                    <div class="d-flex flex-column gap-2" id="viewTeamList">
                        <!-- Populated via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="celebrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Nova Celebração</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="celebrationForm">
                    @csrf
                    <input type="hidden" name="d_id" id="celebrationId">
                    <input type="hidden" name="data" id="celebrationData">
                    <input type="hidden" name="dia" id="celebrationDia">
                    
                    <!-- Top Info -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted">Data</label>
                            <input type="text" class="form-control rounded-3 bg-light" id="displayDate" readonly>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small text-muted">Horário</label>
                            <input type="time" name="hora" id="celebrationHora" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Celebração/Missa</label>
                            <input type="text" name="celebration" id="celebrationName" class="form-control rounded-3" placeholder="Ex: Missa Solene" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Comunidade</label>
                            <select name="ent_id" id="celebrationEnt" class="form-select rounded-3" required>
                                @foreach($entidades as $entidade)
                                    <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Dual List for Acolytes -->
                    <div class="row g-4 h-100">
                        <!-- Available Acolytes -->
                        <div class="col-md-6 d-flex flex-column" style="height: 500px;">
                            <div class="card h-100 border rounded-4 shadow-sm">
                                <div class="card-header bg-white border-bottom-0 pt-3 pb-2">
                                    <h6 class="fw-bold mb-2">Acólitos Disponíveis</h6>
                                    <input type="text" id="searchAcolyte" class="form-control form-control-sm rounded-pill" placeholder="Pesquisar por nome...">
                                </div>
                                <div class="card-body p-0 overflow-auto" id="availableList">
                                    <!-- Populated by JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Selected Acolytes -->
                        <div class="col-md-6 d-flex flex-column" style="height: 500px;">
                            <div class="card h-100 border rounded-4 shadow-sm border-primary">
                                <div class="card-header bg-primary bg-opacity-10 border-bottom-0 pt-3 pb-2">
                                    <h6 class="fw-bold mb-2 text-primary">Acólitos Escalados</h6>
                                    <div class="small text-muted">Defina a função para cada um</div>
                                </div>
                                <div class="card-body p-0 overflow-auto bg-primary bg-opacity-10" id="selectedList">
                                    <!-- Populated by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-danger rounded-pill px-4 me-auto d-none" id="btnDelete">
                    <i class="bi bi-trash me-2"></i>Excluir
                </button>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning rounded-pill px-4 me-2" id="btnDraft" onclick="saveCelebration('draft')">
                    <i class="bi bi-file-earmark-text me-2"></i>Rascunho
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnPublish" onclick="saveCelebration('published')">
                    <i class="bi bi-check-lg me-2"></i>Publicar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão de Celebração -->
<div class="modal fade" id="deleteCelebrationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center py-4">
                    <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-exclamation-triangle-fill fs-1 text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Tem certeza?</h5>
                    <p class="text-muted mb-0">Esta ação é <strong>irreversível</strong> e removerá esta celebração da escala.</p>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" onclick="deleteCelebration()">Sim, Excluir</button>
            </div>
        </div>
    </div>
</div>

<style>
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }
    .calendar-day {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .calendar-day:not(.empty):hover {
        background-color: #f8f9fa;
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .calendar-day:not(.empty):hover .add-indicator {
        opacity: 1;
    }
    .celebration-badge {
        cursor: pointer;
        transition: transform 0.1s;
    }
    .celebration-badge:hover {
        transform: scale(1.02);
        background-color: rgba(13, 110, 253, 0.2) !important;
    }
    /* Scrollbar for lists */
    .overflow-auto::-webkit-scrollbar {
        width: 6px;
    }
    .overflow-auto::-webkit-scrollbar-thumb {
        background-color: #dee2e6;
        border-radius: 10px;
    }
</style>

<script>
    // Data passed from backend
    const allAcolytes = @json($acolitos);
    const allFuncoes = @json($funcoes);
    const celebrations = @json($allCelebrations);
    const allEntities = @json($entidades);
    const myAcolitoId = @json($myAcolitoId);
    const defaultEntId = "{{ $defaultEntId }}";
    const monthNum = {{ $monthNum }};
    const year = {{ $year }};
    
    let selectedAcolytes = [];
    let currentMode = 'create'; // create or edit

    document.addEventListener('DOMContentLoaded', function() {
        // Search Filter
        const searchInput = document.getElementById('searchAcolyte');
        if(searchInput) {
            searchInput.addEventListener('input', function(e) {
                renderAvailableList(e.target.value);
            });
        }
        
        // Delete Button
        const deleteBtn = document.getElementById('btnDelete');
        if(deleteBtn) {
            deleteBtn.addEventListener('click', confirmDeleteCelebration);
        }
    });

    function confirmDeleteCelebration() {
        // Abre o modal de confirmação em vez de usar window.confirm
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteCelebrationModal'));
        deleteModal.show();
    }

    function openViewModal(celebrationId) {
        if (!celebrationId) return;

        console.log('Opening modal for:', celebrationId); // Debug
        const celebration = celebrations.find(c => c.d_id == celebrationId);
        
        if (!celebration) {
            console.error('Celebration not found:', celebrationId);
            return;
        }

        document.getElementById('viewCelebrationName').textContent = celebration.celebration;
        document.getElementById('viewCelebrationTime').textContent = celebration.hora.substring(0, 5); // HH:MM
        
        // Find entity name
        const entity = allEntities.find(e => e.ent_id == celebration.ent_id);
        document.getElementById('viewCelebrationEnt').textContent = entity ? entity.ent_name : 'Comunidade desconhecida';
        
        // Team list
        const teamList = document.getElementById('viewTeamList');
        teamList.innerHTML = '';

        let teamMembers = [];
        // Handle potential missing properties safely
        if (celebration.type === 'draft') {
             const acolitosPayload = celebration.payload && celebration.payload.acolitos ? celebration.payload.acolitos : [];
             teamMembers = acolitosPayload.map(ac => {
                 const originalAc = allAcolytes.find(a => a.id == ac.id);
                 const funcao = allFuncoes.find(f => f.f_id == ac.funcao_id);
                 return {
                    id: ac.id,
                    name: originalAc ? (originalAc.user_name || originalAc.name) : 'Desconhecido',
                    avatar: originalAc ? originalAc.user_avatar : null,
                    funcao_name: funcao ? funcao.title : 'Sem função'
                 };
             });
        } else {
            const escaladosList = celebration.escalados || [];
            teamMembers = escaladosList.map(esc => {
                const acId = esc.acolito ? esc.acolito.id : null;
                if (!acId) return null; // Skip invalid entries

                const originalAc = allAcolytes.find(a => a.id == acId);
                const funcao = allFuncoes.find(f => f.f_id == esc.funcao_id);
                return {
                    id: acId,
                    name: originalAc ? (originalAc.user_name || originalAc.name) : (esc.acolito.user?.name || esc.acolito.name),
                    avatar: originalAc ? originalAc.user_avatar : esc.acolito.user?.avatar,
                    funcao_name: funcao ? funcao.title : 'Sem função'
                };
            }).filter(m => m !== null);
        }

        if (teamMembers.length === 0) {
            teamList.innerHTML = '<div class="text-center text-muted small py-3">Nenhum acólito escalado.</div>';
        } else {
            teamMembers.forEach(member => {
                const isMe = myAcolitoId && member.id == myAcolitoId;
                const avatarUrl = member.avatar 
                    ? `/storage/uploads/avatars/${member.avatar}` 
                    : `https://ui-avatars.com/api/?name=${encodeURIComponent(member.name)}`;

                const div = document.createElement('div');
                div.className = `d-flex align-items-center justify-content-between p-2 rounded-3 ${isMe ? 'bg-primary bg-opacity-10 border border-primary-subtle' : 'bg-white border'}`;
                div.innerHTML = `
                    <div class="d-flex align-items-center gap-3">
                        <img src="${avatarUrl}" class="rounded-circle" width="32" height="32" style="object-fit: cover;" alt="${member.name}">
                        <div>
                            <div class="fw-bold small ${isMe ? 'text-primary' : 'text-dark'}">${member.name} ${isMe ? '(Você)' : ''}</div>
                            <div class="text-muted" style="font-size: 0.75rem;">${member.funcao_name}</div>
                        </div>
                    </div>
                `;
                teamList.appendChild(div);
            });
        }

        try {
            const modalEl = document.getElementById('viewModal');
            if(modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            } else {
                console.error('Modal element not found');
            }
        } catch(e) {
            console.error('Bootstrap modal error:', e);
        }
    }

    function openCreateModal(day) {
        currentMode = 'create';
        document.getElementById('modalTitle').textContent = 'Nova Celebração';
        document.getElementById('celebrationForm').reset();
        document.getElementById('celebrationId').value = '';
        document.getElementById('celebrationData').value = day;
        document.getElementById('displayDate').value = `${day}/${monthNum}/${year}`;
        document.getElementById('btnDelete').classList.add('d-none');
        
        // Calculate Day of Week (1-7)
        const date = new Date(year, monthNum - 1, day);
        let dayOfWeek = date.getDay(); // 0 (Sun) - 6 (Sat)
        dayOfWeek = dayOfWeek === 0 ? 7 : dayOfWeek; // Convert to 1 (Mon) - 7 (Sun)
        document.getElementById('celebrationDia').value = dayOfWeek;

        // Set default community
        if (defaultEntId) {
            document.getElementById('celebrationEnt').value = defaultEntId;
        }

        // Reset lists
        selectedAcolytes = [];
        renderAvailableList();
        renderSelectedList();

        new bootstrap.Modal(document.getElementById('celebrationModal')).show();
    }

    function openEditModal(celebrationId) {
        currentMode = 'edit';
        const celebration = celebrations.find(c => c.d_id == celebrationId);
        if (!celebration) return;

        document.getElementById('modalTitle').textContent = 'Editar Celebração';
        document.getElementById('celebrationId').value = celebration.d_id;
        document.getElementById('celebrationData').value = celebration.data;
        document.getElementById('celebrationDia').value = celebration.dia;
        document.getElementById('displayDate').value = `${celebration.data}/${monthNum}/${year}`;
        document.getElementById('celebrationHora').value = celebration.hora.substring(0, 5); // HH:MM
        document.getElementById('celebrationName').value = celebration.celebration;
        document.getElementById('celebrationEnt').value = celebration.ent_id;
        
        document.getElementById('btnDelete').classList.remove('d-none');

        // Populate selected acolytes
        if (celebration.type === 'draft') {
             selectedAcolytes = (celebration.payload.acolitos || []).map(ac => {
                 const originalAc = allAcolytes.find(a => a.id == ac.id);
                 return {
                    id: ac.id,
                    name: originalAc ? (originalAc.user_name || originalAc.name) : 'Desconhecido',
                    avatar: originalAc ? originalAc.user_avatar : null,
                    funcao_id: ac.funcao_id
                 };
             });
        } else {
            selectedAcolytes = celebration.escalados.map(esc => {
                // Find acolyte in full list to get avatar/name correctly
                const originalAc = allAcolytes.find(a => a.id === esc.acolito.id);
                return {
                    id: esc.acolito.id,
                    name: originalAc ? (originalAc.user_name || originalAc.name) : (esc.acolito.user?.name || esc.acolito.name),
                    avatar: originalAc ? originalAc.user_avatar : esc.acolito.user?.avatar,
                    funcao_id: esc.funcao_id
                };
            });
        }

        renderAvailableList();
        renderSelectedList();

        new bootstrap.Modal(document.getElementById('celebrationModal')).show();
    }

    function renderAvailableList(filter = '') {
        const container = document.getElementById('availableList');
        container.innerHTML = '';
        
        const filtered = allAcolytes.filter(ac => {
            const isSelected = selectedAcolytes.some(s => s.id === ac.id);
            const displayName = ac.user_name || ac.name || 'Sem Nome';
            const matchesSearch = displayName.toLowerCase().includes(filter.toLowerCase());
            return !isSelected && matchesSearch;
        });

        filtered.forEach(ac => {
            const name = ac.user_name || ac.name || 'Sem Nome';
            const avatar = ac.user_avatar ? `/storage/uploads/avatars/${ac.user_avatar}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name);
            
            const div = document.createElement('div');
            div.className = 'd-flex align-items-center justify-content-between p-3 border-bottom hover-bg-light';
            div.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${avatar}" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                    <div>
                        <div class="fw-bold text-dark">${name}</div>
                        <div class="small text-muted">Acólito/Coroinha</div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" onclick="addAcolyte(${ac.id})">
                    <i class="bi bi-plus-lg"></i>
                </button>
            `;
            container.appendChild(div);
        });
    }

    function renderSelectedList() {
        const container = document.getElementById('selectedList');
        container.innerHTML = '';

        selectedAcolytes.forEach((ac, index) => {
            // Try to find fresh data from allAcolytes if possible, fallback to stored object
            const originalAc = allAcolytes.find(a => a.id === ac.id);
            const name = originalAc ? (originalAc.user_name || originalAc.name) : ac.name;
            const avatar = originalAc?.user_avatar ? `/storage/uploads/avatars/${originalAc.user_avatar}` : (ac.avatar ? `/storage/uploads/avatars/${ac.avatar}` : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name));

            const div = document.createElement('div');
            div.className = 'p-3 border-bottom bg-white m-2 rounded-3 shadow-sm';
            
            let optionsHtml = '<option value="">Sem função definida</option>';
            allFuncoes.forEach(f => {
                const selected = ac.funcao_id == f.f_id ? 'selected' : '';
                optionsHtml += `<option value="${f.f_id}" ${selected}>${f.title}</option>`;
            });

            div.innerHTML = `
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center">
                        <img src="${avatar}" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                        <div class="fw-bold text-dark">${name}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-light text-danger rounded-circle" onclick="removeAcolyte(${index})">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <select class="form-select form-select-sm rounded-3 bg-light border-0" onchange="updateRole(${index}, this.value)">
                    ${optionsHtml}
                </select>
            `;
            container.appendChild(div);
        });
    }

    function addAcolyte(id) {
        const ac = allAcolytes.find(a => a.id === id);
        if (ac) {
            selectedAcolytes.push({
                id: ac.id,
                name: ac.user_name || ac.name,
                avatar: ac.user_avatar,
                funcao_id: null
            });
            renderAvailableList(document.getElementById('searchAcolyte').value);
            renderSelectedList();
        }
    }

    function removeAcolyte(index) {
        selectedAcolytes.splice(index, 1);
        renderAvailableList(document.getElementById('searchAcolyte').value);
        renderSelectedList();
    }

    window.updateRole = function(index, value) {
        selectedAcolytes[index].funcao_id = value;
    };
    
    // Make functions globally available for inline onclicks
    window.addAcolyte = addAcolyte;
    window.removeAcolyte = removeAcolyte;

    function saveCelebration(status = 'published') {
        const form = document.getElementById('celebrationForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = status === 'draft' ? document.getElementById('btnDraft') : document.getElementById('btnPublish');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>${status === 'draft' ? 'Salvando...' : 'Adicionando...'}`;

        const formData = {
            data: document.getElementById('celebrationData').value,
            dia: document.getElementById('celebrationDia').value,
            hora: document.getElementById('celebrationHora').value,
            celebration: document.getElementById('celebrationName').value,
            ent_id: document.getElementById('celebrationEnt').value,
            acolitos: selectedAcolytes,
            status: status,
            _token: document.querySelector('input[name="_token"]').value
        };

        const id = document.getElementById('celebrationId').value;
        const url = currentMode === 'create' 
            ? `{{ route('acolitos.escalas.celebrations.store', $escala->es_id) }}`
            : `{{ url('acolitos/escalas/' . $escala->es_id . '/celebrations') }}/${id}`;
        
        const method = currentMode === 'create' ? 'POST' : 'PUT';

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh logic - ideally reload page to see changes correctly
                location.reload(); 
            } else {
                alert(data.message);
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao processar requisição');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    function deleteCelebration() {
        const id = document.getElementById('celebrationId').value;
        const url = `{{ url('acolitos/escalas/' . $escala->es_id . '/celebrations') }}/${id}`;

        // Fechar o modal de confirmação
        const deleteModalEl = document.getElementById('deleteCelebrationModal');
        const deleteModal = bootstrap.Modal.getInstance(deleteModalEl);
        if (deleteModal) {
            deleteModal.hide();
        }

        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => {
            if (!response.ok) {
                 return response.text().then(text => { throw new Error(text) });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro na exclusão:', error);
            // Tenta extrair mensagem JSON se possível, senão mostra erro genérico
            try {
                const errorObj = JSON.parse(error.message);
                alert('Erro ao excluir: ' + (errorObj.message || 'Erro desconhecido'));
            } catch(e) {
                alert('Erro ao processar exclusão. Verifique o console para mais detalhes.');
            }
        });
    }
</script>
@endsection
