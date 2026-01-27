<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SisMatriz - Painel Administrativo</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Material Design Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (para compatibilidade com módulos existentes) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    
    @auth
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="SisMatriz" height="40" class="rounded" onerror="this.style.display='none'">
                <span class="fw-bold text-dark">SisMatriz</span>
            </a>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Mega Menu Button -->
                <div class="dropdown">
                    <button class="btn btn-light border-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;" type="button" id="megaMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Todos os Módulos">
                        <i class="mdi mdi-view-grid text-dark fs-5"></i>
                    </button>
                    <div class="dropdown-menu shadow-lg p-0 mt-2 border-0 rounded-4" aria-labelledby="megaMenuBtn" style="width: 350px; max-height: 80vh; overflow-y: auto;">
                        <div class="p-3 sticky-top bg-white border-bottom">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" id="megaMenuSearch" placeholder="Pesquisar módulos...">
                            </div>
                        </div>
                        <div class="p-2" id="megaMenuContent">
                            @if(isset($globalGroupedModules))
                                @foreach($globalGroupedModules as $letter => $modules)
                                    <div class="module-group-item mb-2">
                                        <h6 class="px-3 py-1 text-muted fw-bold small bg-light">{{ $letter }}</h6>
                                        <div class="list-group list-group-flush">
                                            @foreach($modules as $module)
                                                <a href="{{ $module['url'] ?? '#' }}" class="list-group-item list-group-item-action border-0 d-flex align-items-center gap-2 px-3 py-2 module-link">
                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded-circle text-primary" style="width: 32px; height: 32px;">
                                                        <i class="bi bi-{{ $module['icon'] }}"></i>
                                                    </div>
                                                    <span class="text-dark small fw-semibold module-name">{{ $module['name'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="p-3 text-center border-top bg-light rounded-bottom-4">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none small fw-bold">Ver todos no Dashboard</a>
                        </div>
                    </div>
                </div>

                <div class="text-end d-none d-md-block">
                    <div class="fw-bold text-dark small">{{ Auth::user()->name ?? Auth::user()->user }}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">{{ Auth::user()->paroquia->name ?? 'Administrador' }}</div>
                </div>
                <div class="dropdown">
                    <a href="#" class="d-block link-dark text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        @php
                            $userName = Auth::user()->name ?? Auth::user()->user;
                            $parts = explode(' ', trim($userName));
                            $initials = strtoupper(substr($parts[0], 0, 1));
                            if (count($parts) > 1) {
                                $initials .= strtoupper(substr(end($parts), 0, 1));
                            }
                        @endphp

                        @if(Auth::user()->avatar && file_exists(public_path('storage/uploads/avatars/' . Auth::user()->avatar)))
                            <img src="{{ asset('storage/uploads/avatars/' . Auth::user()->avatar) }}" 
                                 alt="{{ $userName }}" 
                                 width="40" height="40" 
                                 class="rounded-circle border shadow-sm" 
                                 style="object-fit: cover; object-position: center;">
                        @else
                            <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center shadow-sm" style="width: 40px; height: 40px;">
                                {{ $initials }}
                            </div>
                        @endif
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="#">Perfil</a></li>
                        <li><a class="dropdown-item" href="#">Configurações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Sair</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    @endauth

    <main class="flex-shrink-0" style="padding-top: 80px;">
        @yield('content')
    </main>

    <footer class="footer mt-auto py-3 bg-white border-top text-center">
        <div class="container">
            <span class="text-muted">
                &copy; {{ date('Y') }} <strong>Sacratech Softwares LTDA</strong>. Todos os direitos reservados.
                <br>
                <small>SisMatriz é um serviço da Sacratech Softwares.</small>
            </span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mega Menu Search
            const megaSearch = document.getElementById('megaMenuSearch');
            if (megaSearch) {
                megaSearch.addEventListener('input', function(e) {
                    const term = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    const groups = document.querySelectorAll('.module-group-item');
                    
                    groups.forEach(group => {
                        let hasVisible = false;
                        const links = group.querySelectorAll('.module-link');
                        
                        links.forEach(link => {
                            const name = link.querySelector('.module-name').textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                            if (name.includes(term)) {
                                link.style.display = '';
                                hasVisible = true;
                            } else {
                                link.style.display = 'none';
                            }
                        });
                        
                        if (hasVisible) {
                            group.style.display = '';
                        } else {
                            group.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
