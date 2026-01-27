@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <!-- Main Content (Modules) -->
        <div class="col-lg-9">
            <!-- Barra de Pesquisa de Módulos -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="moduleSearch" class="form-control form-control-lg ps-5 border-0 shadow-sm" placeholder="Pesquisar módulos..." style="width: 100%; border-radius: 50px;">
                    </div>
                </div>
            </div>

            <!-- Seção de Módulos Fixados -->
            @if($pinnedModules->count() > 0)
            <div class="mb-5" id="pinnedSection">
                <h5 class="fw-bold text-dark mb-3 ps-2 border-start border-4 border-primary">Meus Fixados</h5>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">
                    @foreach($pinnedModules as $module)
                    <div class="col module-item">
                        <div class="card h-100 border-0 shadow-sm card-module text-center p-2 position-relative">
                            <!-- Pin Button -->
                            <button class="btn position-absolute top-0 end-0 p-1 pin-btn" data-slug="{{ $module['slug'] }}" title="Desafixar">
                                <i class="mdi mdi-pin text-primary"></i>
                            </button>

                            <a href="{{ $module['url'] ?? '#' }}" class="text-decoration-none text-dark d-block h-100">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center p-2">
                                    <h6 class="card-title fw-bold mb-2 text-truncate w-100" style="font-size: 0.8rem;">{{ $module['name'] }}</h6>
                                    <div class="icon-container mb-0">
                                        <i class="bi bi-{{ $module['icon'] }} text-primary" style="font-size: 1.8rem;"></i>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <hr class="my-4 text-muted">
            @endif

            <!-- Lista de Todos os Módulos (Agrupados A-Z) -->
            <div id="allModulesSection">
                @foreach($groupedModules as $letter => $modules)
                <div class="mb-4 module-group" data-letter="{{ $letter }}">
                    <h6 class="text-muted fw-bold mb-3 ps-2">{{ $letter }}</h6>
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">
                        @foreach($modules as $module)
                        <div class="col module-item">
                            <div class="card h-100 border-0 shadow-sm card-module text-center p-2 position-relative">
                                <!-- Pin Button -->
                                <button class="btn position-absolute top-0 end-0 p-1 pin-btn" data-slug="{{ $module['slug'] }}" title="{{ $module['is_pinned'] ? 'Desafixar' : 'Fixar' }}">
                                    <i class="mdi {{ $module['is_pinned'] ? 'mdi-pin text-primary' : 'mdi-pin-outline' }}"></i>
                                </button>

                                <a href="{{ $module['url'] ?? '#' }}" class="text-decoration-none text-dark d-block h-100">
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center p-2">
                                        <h6 class="card-title fw-bold mb-2 text-truncate w-100" style="font-size: 0.8rem;">{{ $module['name'] }}</h6>
                                        <div class="icon-container mb-0">
                                            <i class="bi bi-{{ $module['icon'] }} text-primary" style="font-size: 1.8rem;"></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Sidebar (Online Users) -->
        <div class="col-lg-3 border-start ps-4">
            <h5 class="fw-bold mb-3">Online Agora</h5>
            <div id="onlineUsersList" class="d-flex flex-column gap-3">
                <!-- Skeleton Loading / Placeholder -->
                <div class="text-muted small"><i class="bi bi-arrow-repeat spin"></i> Carregando...</div>
            </div>
        </div>
    </div>
</div>


<style>
    .card-module {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 0.8rem;
        background: #fff;
    }
    .card-module:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
        background-color: #f8f9fa;
        z-index: 10;
    }
    .icon-container {
        width: 50px;
        height: 50px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .card-module:hover .icon-container {
        background: #e2e8f0;
    }
    .pin-btn {
        z-index: 20;
        opacity: 1;
        transition: all 0.2s ease;
        color: #cbd5e1; /* Cor padrão clara para não poluir (slate-300) */
        font-size: 1.2rem;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .card-module:hover .pin-btn {
        color: #94a3b8; /* Mais visível no hover do card (slate-400) */
    }
    .pin-btn:hover {
        background-color: #e2e8f0;
        color: #0d6efd !important;
        transform: scale(1.1);
    }
    /* Estilo específico para quando o ícone já tem a classe text-primary (pinned) */
    .pin-btn i.text-primary {
        color: #0d6efd !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search Functionality
        const searchInput = document.getElementById('moduleSearch');
        const moduleGroups = document.querySelectorAll('.module-group');
        const allModuleItems = document.querySelectorAll('.module-item');

        searchInput.addEventListener('input', function(e) {
            // Normaliza o termo de pesquisa: remove acentos, lowercase, e divide em palavras
            const rawTerm = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            const searchTerms = rawTerm.split(" ").filter(term => term.length > 0);

            // Filter items
            allModuleItems.forEach(function(item) {
                const title = item.querySelector('.card-title').textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                
                // Verifica se TODAS as palavras digitadas estão presentes no título (Lógica AND)
                // Isso permite encontrar "crisma inscricao" em "Inscrições Crisma"
                const matches = searchTerms.every(term => title.includes(term));

                if (matches) {
                    item.style.display = ''; // Show
                } else {
                    item.style.display = 'none'; // Hide
                }
            });

            // Hide empty groups
            moduleGroups.forEach(function(group) {
                const items = group.querySelectorAll('.module-item');
                let hasVisible = false;
                items.forEach(i => {
                    if (i.style.display !== 'none') hasVisible = true;
                });
                
                if (hasVisible) {
                    group.style.display = '';
                } else {
                    group.style.display = 'none';
                }
            });
        });

        // Pin Functionality
        const pinButtons = document.querySelectorAll('.pin-btn');
        
        pinButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const slug = this.getAttribute('data-slug');
                const icon = this.querySelector('i');
                
                // Optimistic UI Update (optional, but better to wait for server or reload)
                // For simplicity and correctness with sorting, we'll reload or fetch updated partial.
                // Given the request "ele tem que salvar", let's do API call then reload to reflect new order/grouping easily.
                
                fetch('{{ route("dashboard.toggle-pin") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ module_slug: slug })
                })
                .then(response => response.json())
                .then(data => {
                    // Reload to update the view (easiest way to handle moving items between sections and re-sorting)
                    window.location.reload();
                })
                .catch(error => console.error('Error:', error));
            });
        });

        // Online Users Polling
        function fetchOnlineUsers() {
            fetch('{{ route("dashboard.online-users") }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('onlineUsersList');
                    container.innerHTML = ''; // Clear current
                    
                    // Switch to Grid Layout for "Name below Avatar"
                    container.className = 'd-flex flex-wrap gap-2 justify-content-start';

                    if (data.length === 0) {
                        container.innerHTML = '<div class="text-muted small w-100 text-center">Nenhum usuário online.</div>';
                        return;
                    }

                    data.forEach(user => {
                        let avatarHtml = '';
                        
                        if (user.avatar_url) {
                            avatarHtml = `<img src="${user.avatar_url}" class="rounded-circle border shadow-sm" width="45" height="45" style="object-fit: cover;" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=${user.initials}&background=random&color=fff';">`;
                        } else {
                            avatarHtml = `
                                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center shadow-sm" style="width: 45px; height: 45px; font-size: 1rem;">
                                    ${user.initials}
                                </div>`;
                        }

                        let statusDotClass = user.is_online ? 'bg-success' : 'bg-secondary';
                        
                        let deviceIcon = '';
                        if (user.device_type == 1) deviceIcon = '<i class="bi bi-globe text-primary"></i>'; // Site
                        else if (user.device_type == 2) deviceIcon = '<i class="bi bi-android text-success"></i>'; // Android
                        else if (user.device_type == 3) deviceIcon = '<i class="bi bi-apple text-dark"></i>'; // iOS
                        else deviceIcon = '<i class="bi bi-question-circle text-muted"></i>';

                        const html = `
                            <div class="user-item fade-in text-center p-1" style="width: 80px;">
                                <div class="position-relative d-inline-block">
                                    ${avatarHtml}
                                    
                                    <!-- Status Dot (Bottom Right) -->
                                    <span class="position-absolute bottom-0 end-0 p-1 ${statusDotClass} border border-white rounded-circle" style="width: 12px; height: 12px; display: block;" title="${user.is_online ? 'Online' : 'Offline'}"></span>
                                    
                                    <!-- Device Icon (Bottom Left Overlay) -->
                                    <span class="position-absolute bottom-0 start-0 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center border" style="width: 20px; height: 20px; font-size: 0.75rem; transform: translate(-15%, 15%); z-index: 5;">
                                        ${deviceIcon}
                                    </span>
                                </div>
                                
                                <div class="mt-2">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate w-100" style="font-size: 0.75rem;" title="${user.name}">${user.name}</h6>
                                    <small class="text-muted d-block" style="font-size: 0.65rem; line-height: 1.1;">
                                        ${user.status_text}
                                    </small>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', html);
                    });
                })
                .catch(error => console.error('Error fetching online users:', error));
        }

        // Initial fetch
        fetchOnlineUsers();
        // Poll every 30 seconds
        setInterval(fetchOnlineUsers, 30000);
    });
</script>
@endsection
