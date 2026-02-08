<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SisMatriz - Painel Administrativo</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Material Design Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (para compatibilidade com módulos existentes) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>
<body class="d-flex flex-column min-vh-100 bg-light" data-auth-id="{{ Auth::id() }}">
    
    @auth
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('dashboard') }}">
                <img src="{{ asset('images/logo.png') }}" alt="SisMatriz" height="40" class="rounded" onerror="this.style.display='none'">
                <span class="fw-bold text-dark">SisMatriz</span>
            </a>
            
            <div class="d-flex align-items-center gap-3">
                <!-- Notifications Button -->
                <div class="dropdown">
                    <button class="btn btn-light border-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm position-relative" style="width: 40px; height: 40px;" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notificações">
                        <i class="bi bi-bell text-dark fs-5"></i>
                        @php
                            $reminders = \App\Models\Lembrete::where('usuario_id', Auth::id())
                                ->where('status', 'ativo')
                                ->where('data_hora', '<=', now())
                                ->orderBy('data_hora', 'desc')
                                ->get();

                            $messages = \App\Models\Message::where('receiver_id', Auth::id())
                                ->where('is_read', false)
                                ->whereHas('sender')
                                ->with('sender')
                                ->orderBy('created_at', 'desc')
                                ->get();

                            $protocolNotifications = \App\Models\ProtocolStatusNotification::where('user_id', Auth::id())
                                ->where('is_read', false)
                                ->with('protocol')
                                ->orderBy('created_at', 'desc')
                                ->get();
                                
                            $totalNotifications = $reminders->count() + $messages->count() + $protocolNotifications->count();
                        @endphp
                        @if($totalNotifications > 0)
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                                <span class="visually-hidden">New alerts</span>
                            </span>
                        @endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-0 mt-2" aria-labelledby="notificationDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-white sticky-top">
                            <h6 class="mb-0 fw-bold">Notificações</h6>
                            @if($totalNotifications > 0)
                                <span class="badge bg-primary rounded-pill">{{ $totalNotifications }}</span>
                            @endif
                        </div>
                        <div class="list-group list-group-flush" id="notificationList">
                            <!-- Protocol Notifications -->
                            @foreach($protocolNotifications as $notification)
                                <a href="{{ route('protocols.notification.read', $notification->id) }}" class="list-group-item list-group-item-action border-0 px-3 py-3 bg-light">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="position-relative">
                                            <i class="bi bi-file-earmark-text text-primary mt-1 fs-5"></i>
                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 10px; height: 10px;"></span>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $notification->title }}</div>
                                            <div class="text-muted small" style="font-size: 0.8rem;">{{ $notification->message }}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;">{{ $notification->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach

                            <!-- Messages -->
                            @php
                                $groupedMessages = $messages->groupBy('sender_id');
                            @endphp
                            @foreach($groupedMessages as $senderId => $senderMessages)
                                @php
                                    $sender = $senderMessages->first()->sender;
                                    $senderName = $sender ? ($sender->hide_name ? 'Usuário' : ($sender->name ?? $sender->user)) : 'Usuário Desconhecido';
                                @endphp
                                
                                @if($senderMessages->count() > 2)
                                    <a href="{{ route('chat.index', ['user_id' => $senderId]) }}" class="list-group-item list-group-item-action border-0 px-3 py-3 bg-light">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="position-relative">
                                                <i class="bi bi-chat-dots-fill text-success mt-1 fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small">{{ $senderName }}</div>
                                                <div class="text-muted small">{{ $senderMessages->count() }} novas mensagens</div>
                                            </div>
                                        </div>
                                    </a>
                                @else
                                    @foreach($senderMessages as $msg)
                                        <a href="{{ route('chat.index', ['user_id' => $senderId]) }}" class="list-group-item list-group-item-action border-0 px-3 py-3 bg-light">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-chat-left-text text-success mt-1"></i>
                                                <div>
                                                    <div class="fw-bold text-dark small">{{ $senderName }}</div>
                                                    <div class="text-muted text-truncate" style="max-width: 200px; font-size: 0.8rem;">{{ $msg->message }}</div>
                                                    <div class="text-muted" style="font-size: 0.65rem;">{{ $msg->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @endif
                            @endforeach

                            <!-- Reminders -->
                            @foreach($reminders as $reminder)
                                <a href="{{ route('lembretes.index') }}" class="list-group-item list-group-item-action border-0 px-3 py-3">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="bi bi-calendar-event text-primary mt-1"></i>
                                        <div>
                                            <div class="fw-medium text-dark small">{{ $reminder->descricao }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $reminder->data_hora->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                            
                            @if($totalNotifications == 0)
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
                                    Nenhuma notificação nova
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Chat Button -->
                <a href="{{ url('/chat') }}" class="btn btn-light border-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;" title="Chat">
                    <i class="bi bi-chat-dots text-dark fs-5"></i>
                </a>

                <!-- Mega Menu Button -->
                <div class="dropdown">
                    <button class="btn btn-light border-0 rounded-pill d-flex align-items-center gap-2 shadow-sm px-3" style="height: 40px;" type="button" id="megaMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" title="Todos os Módulos">
                        <i class="mdi mdi-view-grid text-dark fs-5"></i>
                        <span class="fw-bold text-dark small">Opções</span>
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
                        <li><a class="dropdown-item" href="{{ route('profile') }}">Perfil</a></li>
                        <li><a class="dropdown-item" href="{{ route('settings.index') }}">Configurações</a></li>
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
            // Sound Notification Check
            setInterval(checkReminders, 30000); // Check every 30 seconds

            function checkReminders() {
                fetch('/lembretes/check', { credentials: 'same-origin' })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.due) {
                            // Play sound
                            const audio = new Audio('{{ asset("sounds/notification.wav") }}');
                            audio.play().catch(error => console.log('Audio play failed (user interaction needed):', error));
                            
                            // Optional: Refresh notification dropdown or show toast
                            // ...
                        }
                    })
                    .catch(error => {
                        // Silently ignore errors during page navigation/unload
                        if (error.name === 'TypeError' && (error.message === 'Load failed' || error.message === 'NetworkError when attempting to fetch resource.')) {
                            return;
                        }
                        console.error('Error checking reminders:', error);
                    });
            }

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
    @yield('scripts')
</body>
</html>
