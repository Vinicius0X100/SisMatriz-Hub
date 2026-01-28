@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <!-- Main Content (Modules) -->
        <div class="col-md-9 col-lg-10">
            <!-- Widgets de Tempo e Clima -->
            <div class="row mb-4 g-3">
                <!-- Horário -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-primary text-white h-100 rounded-4">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Horário Atual</h6>
                                <h2 class="fw-bold mb-0 display-6" id="clockTime">--:--</h2>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-clock fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Data -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-success text-white h-100 rounded-4">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <h6 class="text-white-50 mb-1 small text-uppercase fw-bold">Data de Hoje</h6>
                                <h5 class="fw-bold mb-0" id="clockDate">--/--/----</h5>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-calendar-event fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Clima -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm bg-info text-white h-100 rounded-4">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <h6 class="text-white-50 mb-1 small text-uppercase fw-bold" id="locationName">Localizando...</h6>
                                <div class="d-flex align-items-center">
                                    <h2 class="fw-bold mb-0 display-6 me-2" id="weatherTemp">--°C</h2>
                                </div>
                            </div>
                            <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i id="weatherIcon" class="bi bi-cloud fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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
                    <div class="col module-item" data-slug="{{ $module['slug'] }}">
                        <div class="card h-100 border-0 shadow-sm card-module text-center p-2 position-relative" style="background-color: {{ $module['bg_color'] ?? '#fff' }};">
                            
                            <!-- Options Dropdown -->
                            <div class="dropdown position-absolute top-0 end-0 p-1" style="z-index: 20;">
                                <button class="btn btn-sm btn-link text-muted p-0 opacity-50 hover-opacity-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-3" style="min-width: 200px;">
                                    <li><h6 class="dropdown-header px-0 text-uppercase small fw-bold">Cor de Fundo</h6></li>
                                    <li class="mb-3">
                                        <div class="d-flex gap-1 flex-wrap justify-content-center">
                                            @foreach(['#ffffff', '#f8f9fa', '#e9ecef', '#dee2e6', '#cfe2ff', '#e0cffc', '#f1aeb5', '#ffe69c', '#d1e7dd', '#cff4fc'] as $color)
                                                <button class="btn btn-sm border rounded-circle color-btn shadow-sm" 
                                                        style="width: 24px; height: 24px; background-color: {{ $color }};" 
                                                        data-color="{{ $color }}" 
                                                        data-slug="{{ $module['slug'] }}"
                                                        title="Fundo"></button>
                                            @endforeach
                                        </div>
                                    </li>
                                    <li><h6 class="dropdown-header px-0 text-uppercase small fw-bold">Cor do Texto</h6></li>
                                    <li class="mb-3">
                                        <div class="d-flex gap-1 flex-wrap justify-content-center">
                                            @foreach(['#212529', '#6c757d', '#0d6efd', '#198754', '#dc3545', '#ffc107', '#0dcaf0', '#ffffff'] as $color)
                                                <button class="btn btn-sm border rounded-circle text-color-btn shadow-sm" 
                                                        style="width: 24px; height: 24px; background-color: {{ $color }};" 
                                                        data-color="{{ $color }}" 
                                                        data-slug="{{ $module['slug'] }}"
                                                        title="Texto"></button>
                                            @endforeach
                                        </div>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger small rounded pin-action-btn" href="#" data-slug="{{ $module['slug'] }}">
                                            <i class="bi bi-pin-angle-fill me-2"></i> Desafixar
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <a href="{{ $module['url'] ?? '#' }}" class="text-decoration-none d-block h-100" style="color: {{ $module['text_color'] ?? '#212529' }};">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center p-2 pt-4">
                                    <h6 class="card-title fw-bold mb-2 text-truncate w-100" style="font-size: 0.8rem;">{{ $module['name'] }}</h6>
                                    <div class="icon-container mb-0" style="background-color: rgba(0,0,0,0.03);">
                                        <i class="bi bi-{{ $module['icon'] }}" style="font-size: 1.8rem; color: inherit;"></i>
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
                                <button class="btn position-absolute top-0 end-0 p-1 pin-btn pin-action-btn" data-slug="{{ $module['slug'] }}" title="{{ $module['is_pinned'] ? 'Desafixar' : 'Fixar' }}">
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
        <div class="col-lg-2 border-start ps-3">
            <h5 class="fw-bold mb-3 small text-uppercase text-muted">Online Agora</h5>
            <div id="onlineUsersList" class="d-flex flex-column gap-2">
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

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Drag and Drop (Sortable)
        const pinnedContainer = document.querySelector('#pinnedSection .row');
        if (pinnedContainer) {
            Sortable.create(pinnedContainer, {
                animation: 150,
                ghostClass: 'bg-light',
                onEnd: function (evt) {
                    var order = [];
                    document.querySelectorAll('#pinnedSection .module-item').forEach((item, index) => {
                        order.push({
                            slug: item.getAttribute('data-slug'),
                            index: index
                        });
                    });

                    fetch('{{ route("dashboard.reorder-pins") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ order: order })
                    });
                }
            });
        }

        // Color Customization
        document.querySelectorAll('.color-btn, .text-color-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const slug = this.getAttribute('data-slug');
                const color = this.getAttribute('data-color');
                const isText = this.classList.contains('text-color-btn');
                const card = this.closest('.card-module');
                const link = card.querySelector('a');
                
                // Optimistic UI
                if (isText) {
                    link.style.color = color;
                    // Also update icon color if needed? The icon uses "inherit" now in my update.
                } else {
                    card.style.backgroundColor = color;
                }

                const data = { module_slug: slug };
                if (isText) data.text_color = color;
                else data.bg_color = color;

                fetch('{{ route("dashboard.update-pin-style") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                });
            });
        });

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
        const pinButtons = document.querySelectorAll('.pin-action-btn');
        
        pinButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const slug = this.getAttribute('data-slug');
                
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
                    
                    // Vertical List Layout (Compact)
                    container.className = 'd-flex flex-column gap-2';

                    if (data.length === 0) {
                        container.innerHTML = '<div class="text-muted small w-100 text-center">Nenhum usuário online.</div>';
                        return;
                    }

                    data.forEach(user => {
                        let avatarHtml = '';
                        
                        if (user.avatar_url) {
                            avatarHtml = `<img src="${user.avatar_url}" class="rounded-circle border shadow-sm" width="32" height="32" style="object-fit: cover;" onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=${user.initials}&background=random&color=fff';">`;
                        } else {
                            avatarHtml = `
                                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center shadow-sm" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    ${user.initials}
                                </div>`;
                        }

                        let statusDotClass = user.is_online ? 'bg-success' : 'bg-secondary';
                        
                        let deviceIcon = '';
                        if (user.device_type == 1) deviceIcon = '<i class="bi bi-globe text-primary small" style="font-size: 0.7rem;"></i>'; // Site
                        else if (user.device_type == 2) deviceIcon = '<i class="bi bi-android text-success small" style="font-size: 0.7rem;"></i>'; // Android
                        else if (user.device_type == 3) deviceIcon = '<i class="bi bi-apple text-dark small" style="font-size: 0.7rem;"></i>'; // iOS
                        else deviceIcon = '<i class="bi bi-question-circle text-muted small" style="font-size: 0.7rem;"></i>';

                        const html = `
                            <div class="user-item fade-in d-flex align-items-center p-1 rounded hover-bg-light">
                                <div class="position-relative flex-shrink-0">
                                    ${avatarHtml}
                                    <span class="position-absolute bottom-0 end-0 p-0 bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 10px; height: 10px;">
                                        <span class="${statusDotClass} rounded-circle" style="width: 8px; height: 8px; display: block;" title="${user.is_online ? 'Online' : 'Offline'}"></span>
                                    </span>
                                </div>
                                
                                <div class="ms-2 flex-grow-1 overflow-hidden" style="line-height: 1.2;">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.85rem;" title="${user.name}">${user.name}</h6>
                                    <div class="d-flex align-items-center gap-1">
                                        ${deviceIcon}
                                        <small class="text-muted" style="font-size: 0.7rem;">${user.status_text}</small>
                                    </div>
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

        // Clock & Weather Functionality
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('clockTime').textContent = `${hours}:${minutes}`;

            const options = { weekday: 'short', day: '2-digit', month: '2-digit', year: 'numeric' };
            document.getElementById('clockDate').textContent = now.toLocaleDateString('pt-BR', options);
        }
        setInterval(updateTime, 1000);
        updateTime();

        // Weather with Geolocation
        function fetchWeather() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(position => {
                    const lat = position.coords.latitude;
                    const lon = position.coords.longitude;
                    
                    // Reverse Geocoding (Nominatim OpenStreetMap - Free, No Key)
                    // Note: Use responsibly. For high traffic, cache results or use a paid service.
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
                        .then(res => res.json())
                        .then(data => {
                            let city = data.address.city || data.address.town || data.address.village || data.address.municipality || 'Local desconhecido';
                            // Shorten city name if too long
                            if (city.length > 15) city = city.substring(0, 15) + '...';
                            document.getElementById('locationName').textContent = city;
                        })
                        .catch(err => {
                            console.error('Geo error:', err);
                            document.getElementById('locationName').textContent = 'Brasil';
                        });

                    // Weather (Open-Meteo - Free, No Key)
                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code&timezone=auto`)
                        .then(res => res.json())
                        .then(data => {
                            const temp = Math.round(data.current.temperature_2m);
                            document.getElementById('weatherTemp').textContent = `${temp}°C`;
                            
                            // Map WMO codes to Bootstrap Icons
                            const code = data.current.weather_code;
                            let icon = 'bi-cloud';
                            if (code === 0) icon = 'bi-sun'; // Clear sky
                            else if (code >= 1 && code <= 3) icon = 'bi-cloud-sun'; // Partly cloudy
                            else if (code >= 45 && code <= 48) icon = 'bi-cloud-fog'; // Fog
                            else if (code >= 51 && code <= 67) icon = 'bi-cloud-rain'; // Drizzle/Rain
                            else if (code >= 71 && code <= 77) icon = 'bi-cloud-snow'; // Snow
                            else if (code >= 80 && code <= 82) icon = 'bi-cloud-rain-heavy'; // Showers
                            else if (code >= 95 && code <= 99) icon = 'bi-cloud-lightning-rain'; // Thunderstorm
                            
                            // Adjust for night time? (Simple check: 6pm to 6am)
                            const hour = new Date().getHours();
                            if ((hour >= 18 || hour < 6) && code === 0) icon = 'bi-moon-stars';
                            
                            document.getElementById('weatherIcon').className = `bi ${icon}`;
                        })
                        .catch(err => console.error('Weather error:', err));

                }, error => {
                    console.log('Geolocation denied or failed:', error);
                    document.getElementById('locationName').textContent = 'Brasil';
                    document.getElementById('weatherTemp').textContent = '--';
                });
            } else {
                document.getElementById('locationName').textContent = 'Navegador incompatível';
            }
        }
        fetchWeather(); // Call once on load
    });
</script>
@endsection
