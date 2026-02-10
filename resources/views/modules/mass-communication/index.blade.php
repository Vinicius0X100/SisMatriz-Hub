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

                        <div class="d-flex justify-content-end">
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedRecipients = new Map(); // ID -> Name
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
                const url = new URL("{{ route('mass-communication.index') }}");
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

        // Initial Setup
        updateUI();
        setupPaginationLinks();
    });
</script>
@endsection
