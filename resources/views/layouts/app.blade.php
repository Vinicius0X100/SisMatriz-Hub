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
    @viteReactRefresh
    @vite(['resources/css/app.scss', 'resources/js/app.js', 'resources/js/echo-setup.js'])
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 bg-light" data-auth-id="{{ Auth::id() }}">
    
    @auth
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm fixed-top">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center gap-2">
                <button id="sidebarToggle" class="btn btn-light border-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;" title="Menu">
                    <i class="bi bi-layout-sidebar fs-5 text-dark"></i>
                </button>
                <a class="navbar-brand d-flex align-items-center gap-2 mb-0" href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="SisMatriz" height="38" class="rounded" onerror="this.style.display='none'">
                    <span class="fw-bold text-dark">SisMatriz</span>
                </a>
            </div>
            
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
                                
                            $processoNotifications = \App\Models\ProcessoNotificacao::where('user_id', Auth::id())
                                ->where('is_read', false)
                                ->orderBy('created_at', 'desc')
                                ->get();
                                
                            $totalNotifications = $reminders->count() + $messages->count() + $protocolNotifications->count() + $processoNotifications->count();
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
                            @php
                                $extraNavbarNotification = session('bucket_notification');
                            @endphp
                            @if($totalNotifications > 0)
                                <span class="badge bg-primary rounded-pill">{{ $totalNotifications }}</span>
                            @endif
                        </div>
                        <div class="list-group list-group-flush" id="notificationList">
                            @if(!empty($extraNavbarNotification))
                                <div class="list-group-item border-0 px-3 py-3 bg-light">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="position-relative">
                                            <i class="bi bi-cloud-arrow-up text-primary mt-1 fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark small">{{ $extraNavbarNotification['title'] ?? 'Bucket criado com sucesso' }}</div>
                                            <div class="text-muted small" style="font-size: 0.8rem;">{{ $extraNavbarNotification['message'] ?? '' }}</div>
                                            <div class="text-muted" style="font-size: 0.65rem;">agora mesmo</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
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
                            <!-- Processo Notifications -->
                            @foreach($processoNotifications as $notification)
                                <a href="{{ route('processos.notificacao.ler', $notification->id) }}" class="list-group-item list-group-item-action border-0 px-3 py-3 bg-light">
                                    <div class="d-flex align-items-start gap-2">
                                        <div class="position-relative">
                                            <i class="bi bi-diagram-3-fill text-warning mt-1 fs-5"></i>
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

    {{-- Sidebar --}}
    @auth
    <aside id="appSidebar">
        <div class="sidebar-inner">

            {{-- Fixed Top Links --}}
            <div class="sidebar-top-fixed">
                <div class="sidebar-top-row">
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" style="flex:1;">
                        <span class="sidebar-icon"><i class="bi bi-house-door-fill"></i></span>
                        <span class="sidebar-label">Início</span>
                    </a>
                    <button id="sidebarEditBtn" class="sidebar-edit-btn" title="Editar fixados">
                        <span id="sidebarEditBtnLabel">Editar</span>
                    </button>
                </div>
            </div>

            {{-- Search --}}
            <div class="sidebar-search-wrap">
                <div class="position-relative">
                    <i class="bi bi-search sidebar-search-icon"></i>
                    <input type="text" id="sidebarSearch" class="sidebar-search" placeholder="Pesquisar módulos...">
                </div>
            </div>

            {{-- Scrollable area: Pinned + All Modules --}}
            <nav class="sidebar-nav" id="sidebarNav">

                {{-- Pinned Modules (inside scroll) --}}
                @if(isset($globalPinnedModules) && $globalPinnedModules->count() > 0)
                <div class="sidebar-group sidebar-pinned-group" data-group="__pinned__">
                    <div class="sidebar-group-label">
                        <i class="bi bi-pin-fill me-1" style="font-size: 0.65rem;"></i> Fixados
                    </div>
                    @foreach($globalPinnedModules as $module)
                        <div class="sidebar-link-wrap"
                             data-slug="{{ Str::slug($module['name']) }}"
                             data-pinned="1">
                            <button class="sidebar-pin-badge sidebar-pin-badge--pinned" tabindex="-1" aria-label="Desafixar {{ $module['name'] }}">
                                <i class="bi bi-dash"></i>
                            </button>
                            <a href="{{ $module['url'] ?? '#' }}"
                               class="sidebar-link sidebar-module-link sidebar-pinned-link"
                               data-module-name="{{ strtolower($module['name']) }}">
                                <span class="sidebar-icon">
                                    <i class="bi bi-{{ $module['icon'] }}"></i>
                                </span>
                                <span class="sidebar-label">{{ $module['name'] }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- All modules grouped A-Z --}}
                @if(isset($globalGroupedModules))
                    @foreach($globalGroupedModules as $letter => $modules)
                        <div class="sidebar-group" data-group="{{ $letter }}">
                            <div class="sidebar-group-label">{{ $letter }}</div>
                            @foreach($modules as $module)
                                @php $slug = Str::slug($module['name']); @endphp
                                <div class="sidebar-link-wrap"
                                     data-slug="{{ $slug }}"
                                     data-pinned="{{ isset($globalPinnedModules) && $globalPinnedModules->firstWhere('slug', $slug) ? '1' : '0' }}">
                                    <button class="sidebar-pin-badge {{ isset($globalPinnedModules) && $globalPinnedModules->firstWhere('slug', $slug) ? 'sidebar-pin-badge--pinned' : 'sidebar-pin-badge--unpinned' }}"
                                            tabindex="-1"
                                            aria-label="{{ isset($globalPinnedModules) && $globalPinnedModules->firstWhere('slug', $slug) ? 'Desafixar' : 'Fixar' }} {{ $module['name'] }}">
                                        <i class="bi {{ isset($globalPinnedModules) && $globalPinnedModules->firstWhere('slug', $slug) ? 'bi-dash' : 'bi-plus' }}"></i>
                                    </button>
                                    <a href="{{ $module['url'] ?? '#' }}"
                                       class="sidebar-link sidebar-module-link"
                                       data-module-name="{{ strtolower($module['name']) }}">
                                        <span class="sidebar-icon">
                                            <i class="bi bi-{{ $module['icon'] }}"></i>
                                        </span>
                                        <span class="sidebar-label">{{ $module['name'] }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif

            </nav>

        </div>
    </aside>
    <div id="sidebarOverlay"></div>
    @endauth

    <main id="appMain" class="flex-shrink-0" style="padding-top: 70px;">
        @yield('content')
    </main>

    <footer id="appFooter" class="footer mt-auto py-3 bg-white border-top text-center">
        <div class="container">
            <span class="text-muted">
                &copy; {{ date('Y') }} <strong>Sacratech Softwares LTDA</strong>. Todos os direitos reservados.
                <br>
                <small>SisMatriz é um serviço da Sacratech Softwares.</small>
            </span>
        </div>
    </footer>

    <style>
        /* =============================================
           SIDEBAR
        ============================================= */
        :root {
            --sidebar-width: 260px;
            --navbar-height: 60px;
        }

        #appSidebar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--navbar-height));
            background: #ffffff;
            border-right: 1px solid #e9ecef;
            box-shadow: 2px 0 12px rgba(0,0,0,0.05);
            z-index: 1020;
            transform: translateX(-100%);
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        #appSidebar.sidebar-open {
            transform: translateX(0);
        }

        .sidebar-inner {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        /* Fixed top navigation (Início) */
        .sidebar-top-fixed {
            flex-shrink: 0;
            padding: 10px 0 6px;
        }

        .sidebar-top-row {
            display: flex;
            align-items: center;
            padding-right: 6px;
        }

        /* Editar button */
        .sidebar-edit-btn {
            flex-shrink: 0;
            background: none;
            border: none;
            font-size: 0.78rem;
            font-weight: 600;
            color: #0d6efd;
            padding: 4px 10px;
            border-radius: 20px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
        }

        .sidebar-edit-btn:hover {
            background: #e7f0ff;
        }

        .sidebar-edit-btn.edit-active {
            color: #dc3545;
        }

        /* Link wrapper for edit mode */
        .sidebar-link-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        /* Pin badge (hidden by default) */
        .sidebar-pin-badge {
            position: absolute;
            left: 6px;
            z-index: 10;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            opacity: 0;
            pointer-events: none;
            transform: scale(0.5);
            transition: opacity 0.18s, transform 0.18s;
            line-height: 1;
        }

        .sidebar-pin-badge--pinned {
            background: #dc3545;
            color: #fff;
        }

        .sidebar-pin-badge--unpinned {
            background: #198754;
            color: #fff;
        }

        /* Show badges in edit mode */
        #sidebarNav.edit-mode .sidebar-pin-badge {
            opacity: 1;
            pointer-events: auto;
            transform: scale(1);
        }

        /* Push link to the right to make room for badge */
        #sidebarNav.edit-mode .sidebar-link {
            padding-left: 36px;
        }

        /* Dimmed + no-pointer on link itself in edit mode */
        #sidebarNav.edit-mode .sidebar-link {
            pointer-events: none;
            user-select: none;
        }

        /* Pinned modules group (inside scroll) */
        .sidebar-pinned-group {
            border-bottom: 1px solid #f1f3f5;
            margin-bottom: 4px;
        }

        .sidebar-pinned-link .sidebar-icon {
            background: #fef3c7;
            color: #d97706;
        }

        .sidebar-pinned-link:hover .sidebar-icon {
            background: #fde68a;
            color: #b45309;
        }

        .sidebar-pinned-link.active .sidebar-icon {
            background: #dbeafe;
            color: #0d6efd;
        }

        /* Search */
        .sidebar-search-wrap {
            padding: 14px 14px 10px;
            border-bottom: 1px solid #f1f3f5;
            flex-shrink: 0;
        }

        .sidebar-search {
            width: 100%;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 7px 12px 7px 34px;
            font-size: 0.82rem;
            background: #f8f9fa;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            color: #495057;
        }

        .sidebar-search:focus {
            border-color: #86b7fe;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(13,110,253,.1);
        }

        .sidebar-search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 0.8rem;
            pointer-events: none;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 transparent;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 4px;
        }

        /* Group label */
        .sidebar-group-label {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            color: #adb5bd;
            text-transform: uppercase;
            padding: 10px 16px 4px;
        }

        /* Links */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 14px;
            margin: 1px 8px;
            border-radius: 8px;
            font-size: 0.84rem;
            font-weight: 500;
            color: #343a40;
            text-decoration: none;
            transition: background 0.15s, color 0.15s;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-link:hover {
            background: #f1f3f5;
            color: #0d6efd;
        }

        .sidebar-link.active {
            background: #e7f0ff;
            color: #0d6efd;
            font-weight: 600;
        }

        .sidebar-icon {
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: #f8f9fa;
            flex-shrink: 0;
            font-size: 0.85rem;
            color: #6c757d;
            transition: background 0.15s, color 0.15s;
        }

        .sidebar-link:hover .sidebar-icon,
        .sidebar-link.active .sidebar-icon {
            background: #dbeafe;
            color: #0d6efd;
        }

        /* Footer */
        .sidebar-footer {
            border-top: 1px solid #f1f3f5;
            padding: 8px 0;
            flex-shrink: 0;
        }

        /* Overlay (mobile) */
        #sidebarOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.35);
            z-index: 1019;
            backdrop-filter: blur(2px);
        }

        #sidebarOverlay.active {
            display: block;
        }

        /* Toggle button active state */
        #sidebarToggle.active {
            background: #e7f0ff !important;
            color: #0d6efd;
        }

        /* Push main content on large screens */
        @media (min-width: 992px) {
            #appMain, #appFooter {
                transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1);
            }

            #appMain.sidebar-pushed,
            #appFooter.sidebar-pushed {
                margin-left: var(--sidebar-width);
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sound Notification Check
            setInterval(checkReminders, 30000); // Check every 30 seconds

            function checkReminders() {
                fetch('/lembretes/check', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
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

            // Mark all notifications as read when dropdown is opened
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown) {
                notificationDropdown.addEventListener('show.bs.dropdown', function () {
                    // Remove red badges immediately for visual feedback
                    const outerBadge = notificationDropdown.querySelector('.bg-danger');
                    if (outerBadge) outerBadge.remove();
                    
                    const innerBadge = document.querySelector('.dropdown-menu[aria-labelledby="notificationDropdown"] .badge');
                    if (innerBadge) innerBadge.remove();

                    // Optional: remove red dots from list items
                    document.querySelectorAll('#notificationList .bg-danger').forEach(dot => dot.remove());

                    fetch('{{ route("notifications.markAllRead") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).catch(err => console.error('Error marking notifications as read:', err));
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

        // =============================================
        // SIDEBAR
        // =============================================
        (function() {
            const sidebar     = document.getElementById('appSidebar');
            const overlay     = document.getElementById('sidebarOverlay');
            const toggleBtn   = document.getElementById('sidebarToggle');
            const mainEl      = document.getElementById('appMain');
            const footerEl    = document.getElementById('appFooter');
            const searchInput = document.getElementById('sidebarSearch');

            if (!sidebar || !toggleBtn) return;

            const STORAGE_KEY = 'sismatriz_sidebar_open';
            const isLargeScreen = () => window.innerWidth >= 992;

            function openSidebar() {
                sidebar.classList.add('sidebar-open');
                toggleBtn.classList.add('active');
                if (isLargeScreen()) {
                    mainEl   && mainEl.classList.add('sidebar-pushed');
                    footerEl && footerEl.classList.add('sidebar-pushed');
                } else {
                    overlay && overlay.classList.add('active');
                }
                localStorage.setItem(STORAGE_KEY, '1');
            }

            function closeSidebar() {
                sidebar.classList.remove('sidebar-open');
                toggleBtn.classList.remove('active');
                mainEl   && mainEl.classList.remove('sidebar-pushed');
                footerEl && footerEl.classList.remove('sidebar-pushed');
                overlay  && overlay.classList.remove('active');
                localStorage.setItem(STORAGE_KEY, '0');
            }

            // Restore state from localStorage — default is open
            if (localStorage.getItem(STORAGE_KEY) !== '0') {
                openSidebar();
            }

            // Toggle button click
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
            });

            // Overlay click closes sidebar (mobile)
            overlay && overlay.addEventListener('click', closeSidebar);

            // Handle resize — push/overlay logic
            window.addEventListener('resize', function() {
                if (!isLargeScreen()) {
                    mainEl   && mainEl.classList.remove('sidebar-pushed');
                    footerEl && footerEl.classList.remove('sidebar-pushed');
                    if (sidebar.classList.contains('sidebar-open')) {
                        overlay && overlay.classList.add('active');
                    }
                } else {
                    overlay && overlay.classList.remove('active');
                    if (sidebar.classList.contains('sidebar-open')) {
                        mainEl   && mainEl.classList.add('sidebar-pushed');
                        footerEl && footerEl.classList.add('sidebar-pushed');
                    }
                }
            });

            // Highlight active link based on current URL
            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar-module-link').forEach(function(link) {
                try {
                    const linkPath = new URL(link.href).pathname;
                    if (linkPath !== '/' && currentPath.startsWith(linkPath)) {
                        link.classList.add('active');
                    }
                } catch(e) {}
            });

            // Search filter
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const term = this.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    document.querySelectorAll('.sidebar-group').forEach(function(group) {
                        let visible = 0;
                        group.querySelectorAll('.sidebar-module-link').forEach(function(link) {
                            const name = (link.dataset.moduleName || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                            const show = name.includes(term);
                            link.parentElement.style.display = show ? '' : 'none';
                            if (show) visible++;
                        });
                        group.style.display = visible > 0 ? '' : 'none';
                    });
                });
            }

            // =============================================
            // SIDEBAR EDIT MODE (Apple jiggle + pin/unpin)
            // =============================================
            const editBtn   = document.getElementById('sidebarEditBtn');
            const editLabel = document.getElementById('sidebarEditBtnLabel');
            const nav       = document.getElementById('sidebarNav');
            const CSRF      = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const TOGGLE_PIN_URL = '{{ route("dashboard.toggle-pin") }}';

            let editMode = false;

            function setEditMode(active) {
                editMode = active;
                if (active) {
                    nav.classList.add('edit-mode');
                    editBtn.classList.add('edit-active');
                    editLabel.textContent = 'Concluído';
                    // Enable badge buttons
                    nav.querySelectorAll('.sidebar-pin-badge').forEach(b => b.removeAttribute('tabindex'));
                } else {
                    nav.classList.remove('edit-mode');
                    editBtn.classList.remove('edit-active');
                    editLabel.textContent = 'Editar';
                    // Disable badge buttons again
                    nav.querySelectorAll('.sidebar-pin-badge').forEach(b => b.setAttribute('tabindex', '-1'));
                }
            }

            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    setEditMode(!editMode);
                });
            }

            // Close edit mode when clicking outside sidebar
            document.addEventListener('click', function(e) {
                if (editMode && !sidebar.contains(e.target)) {
                    setEditMode(false);
                }
            });

            // Pin badge click handler
            if (nav) {
                nav.addEventListener('click', function(e) {
                    const badge = e.target.closest('.sidebar-pin-badge');
                    if (!badge || !editMode) return;
                    e.preventDefault();
                    e.stopPropagation();

                    const wrap = badge.closest('.sidebar-link-wrap');
                    if (!wrap) return;

                    const slug    = wrap.dataset.slug;
                    const isPinned = wrap.dataset.pinned === '1';
                    const icon    = badge.querySelector('i');

                    // Optimistic UI
                    badge.disabled = true;
                    badge.style.opacity = '0.5';

                    fetch(TOGGLE_PIN_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: JSON.stringify({ module_slug: slug })
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        badge.disabled = false;
                        badge.style.opacity = '';

                        if (data.status === 'pinned') {
                            // Update state
                            wrap.dataset.pinned = '1';
                            badge.classList.remove('sidebar-pin-badge--unpinned');
                            badge.classList.add('sidebar-pin-badge--pinned');
                            if (icon) { icon.className = 'bi bi-dash'; }
                            badge.setAttribute('aria-label', badge.getAttribute('aria-label').replace('Fixar', 'Desafixar'));

                            // Move wrap to Fixados group (create if not exists)
                            let pinnedGroup = nav.querySelector('.sidebar-pinned-group');
                            if (!pinnedGroup) {
                                pinnedGroup = document.createElement('div');
                                pinnedGroup.className = 'sidebar-group sidebar-pinned-group';
                                pinnedGroup.dataset.group = '__pinned__';
                                pinnedGroup.innerHTML = '<div class="sidebar-group-label"><i class="bi bi-pin-fill me-1" style="font-size:0.65rem;"></i> Fixados</div>';
                                nav.prepend(pinnedGroup);
                            }
                            // Clone the wrap with pinned class
                            const link = wrap.querySelector('.sidebar-link');
                            if (link) link.classList.add('sidebar-pinned-link');
                            // Move to top of pinned group
                            pinnedGroup.appendChild(wrap);

                        } else {
                            // Unpinned
                            wrap.dataset.pinned = '0';
                            badge.classList.remove('sidebar-pin-badge--pinned');
                            badge.classList.add('sidebar-pin-badge--unpinned');
                            if (icon) { icon.className = 'bi bi-plus'; }
                            badge.setAttribute('aria-label', badge.getAttribute('aria-label').replace('Desafixar', 'Fixar'));

                            const link = wrap.querySelector('.sidebar-link');
                            if (link) link.classList.remove('sidebar-pinned-link');

                            // Move wrap back to correct letter group in A-Z section
                            const name = wrap.querySelector('.sidebar-module-link')?.dataset.moduleName || '';
                            const firstLetter = name.normalize('NFD').replace(/[\u0300-\u036f]/g,'')[0]?.toUpperCase();
                            let letterGroup = nav.querySelector('.sidebar-group[data-group="' + firstLetter + '"]');
                            if (letterGroup) {
                                letterGroup.appendChild(wrap);
                            } else {
                                nav.appendChild(wrap);
                            }

                            // Remove pinned group if empty
                            const pinnedGroup = nav.querySelector('.sidebar-pinned-group');
                            if (pinnedGroup && pinnedGroup.querySelectorAll('.sidebar-link-wrap').length === 0) {
                                pinnedGroup.remove();
                            }
                        }
                    })
                    .catch(function() {
                        badge.disabled = false;
                        badge.style.opacity = '';
                    });
                });
            }
        })();
    </script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
