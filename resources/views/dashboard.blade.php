@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <!-- Main Content (Modules) -->
        <div class="col-md-9 col-lg-10 position-relative">
            <!-- Shimmer Loading Overlay -->
            <div id="dashboardShimmer" aria-hidden="true">
            <!-- Header shimmer -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <div class="shimmer-block" style="width:120px;height:22px;border-radius:6px;margin-bottom:6px;"></div>
                    <div class="shimmer-block" style="width:180px;height:13px;border-radius:6px;"></div>
                </div>
                <div class="d-flex gap-3">
                    <div class="shimmer-block" style="width:90px;height:15px;border-radius:6px;"></div>
                    <div class="shimmer-block" style="width:70px;height:15px;border-radius:6px;"></div>
                    <div class="shimmer-block" style="width:80px;height:15px;border-radius:6px;"></div>
                </div>
            </div>

            <!-- Stat cards: 2 linhas de 5 -->
            <div class="row g-3 mb-4 row-cols-2 row-cols-sm-3 row-cols-md-5">
                @for($i = 0; $i < 10; $i++)
                <div class="col">
                    <div class="shimmer-card p-3 rounded-4">
                        <div class="shimmer-block" style="width:40px;height:40px;border-radius:10px;margin-bottom:10px;"></div>
                        <div class="shimmer-block" style="width:55px;height:26px;border-radius:6px;margin-bottom:6px;"></div>
                        <div class="shimmer-block" style="width:85%;height:11px;border-radius:6px;"></div>
                    </div>
                </div>
                @endfor
            </div>

            <!-- Filter bar + charts -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="shimmer-block" style="width:140px;height:18px;border-radius:6px;"></div>
                <div class="shimmer-block" style="width:230px;height:36px;border-radius:50px;"></div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-lg-7">
                    <div class="shimmer-card p-4 rounded-4" style="height:330px;">
                        <div class="shimmer-block" style="width:160px;height:17px;border-radius:6px;margin-bottom:7px;"></div>
                        <div class="shimmer-block" style="width:110px;height:12px;border-radius:6px;margin-bottom:20px;"></div>
                        <div class="shimmer-block" style="width:100%;height:240px;border-radius:12px;"></div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="shimmer-card p-4 rounded-4" style="height:330px;">
                        <div class="shimmer-block" style="width:140px;height:17px;border-radius:6px;margin-bottom:7px;"></div>
                        <div class="shimmer-block" style="width:100px;height:12px;border-radius:6px;margin-bottom:20px;"></div>
                        <div class="shimmer-block" style="width:100%;height:240px;border-radius:12px;"></div>
                    </div>
                </div>
            </div>

            <!-- Search bar -->
            <div class="mb-4">
                <div class="shimmer-block" style="width:100%;height:50px;border-radius:50px;"></div>
            </div>

            <!-- Pinned section header -->
            <div class="d-flex align-items-center mb-3">
                <div class="shimmer-block" style="width:4px;height:22px;border-radius:2px;margin-right:10px;flex-shrink:0;"></div>
                <div class="shimmer-block" style="width:140px;height:17px;border-radius:6px;"></div>
            </div>

            <!-- Module cards grid -->
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">
                @for($i = 0; $i < 12; $i++)
                <div class="col">
                    <div class="shimmer-card p-3 rounded-4 d-flex flex-column align-items-center gap-2" style="min-height:105px;">
                        <div class="shimmer-block" style="width:62px;height:62px;border-radius:16px;"></div>
                        <div class="shimmer-block" style="width:75%;height:11px;border-radius:6px;"></div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
        <!-- /Shimmer -->

        <!-- Conteúdo real do dashboard -->
        <div id="dashboardContent" style="opacity:0;transition:opacity 0.35s ease;">

            <!-- Header Info (Simples) -->

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Dashboard</h4>
                    <p class="text-muted small mb-0">Visão geral da paróquia</p>
                </div>
                <div class="d-flex gap-4">
                    <div class="d-flex align-items-center text-muted">
                        <i class="bi bi-calendar-event me-2"></i>
                        <span id="clockDate" class="fw-medium">--/--/----</span>
                    </div>
                    <div class="d-flex align-items-center text-muted">
                        <i class="bi bi-clock me-2"></i>
                        <span id="clockTime" class="fw-medium">--:--</span>
                    </div>
                    <div class="d-flex align-items-center text-muted">
                        <i id="weatherIcon" class="bi bi-cloud me-2"></i>
                        <span id="weatherTemp" class="fw-medium">--°C</span>
                    </div>
                </div>
            </div>

            @if(isset($todayEvents) && $todayEvents->count() > 0)
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold text-primary mb-0">HOJE NA SUA PARÓQUIA</h5>
                    <a href="{{ url('/eventos') }}" class="btn btn-light rounded-pill">Ver todos</a>
                </div>
                <div class="position-relative">
                    <button class="btn btn-light rounded-circle position-absolute top-50 start-0 translate-middle-y shadow-sm" id="todayPrevBtn" style="z-index: 2;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="d-flex overflow-auto gap-2 px-2" id="todayCarousel" style="scroll-behavior: smooth;">
                        @foreach($todayEvents as $evento)
                            <div class="event-thumb rounded-3 shadow-sm position-relative"
                                 data-title="{{ $evento->title }}"
                                 data-date="{{ $evento->date }}"
                                 data-time="{{ $evento->time }}"
                                 data-address="{{ $evento->address }}"
                                 data-photo-url="{{ $evento->photo_url }}"
                                 role="button"
                                 tabindex="0"
                                 aria-label="Evento: {{ $evento->title }}">
                                @if($evento->photo_url)
                                    <img src="{{ $evento->photo_url }}" alt="{{ $evento->title }}" class="w-100 h-100">
                                @else
                                    <div class="w-100 h-100 thumb-fallback">
                                        <div class="thumb-gradient"></div>
                                        <div class="thumb-title text-white fw-bold">{{ $evento->title }}</div>
                                    </div>
                                @endif
                                    <div class="thumb-overlay d-flex align-items-center justify-content-center">
                                        <i class="bi bi-mouse me-2 text-white"></i>
                                        <span class="text-white small fw-bold overlay-text">Clique para saber mais</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-light rounded-circle position-absolute top-50 end-0 translate-middle-y shadow-sm" id="todayNextBtn" style="z-index: 2;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
            @endif

            @if(isset($upcomingEvents) && $upcomingEvents->count() > 0)
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold text-dark mb-0">Próximos eventos</h5>
                    <a href="{{ url('/eventos') }}" class="btn btn-light rounded-pill">Ver todos</a>
                </div>
                <div class="position-relative">
                    <button class="btn btn-light rounded-circle position-absolute top-50 start-0 translate-middle-y shadow-sm" id="upcomingPrevBtn" style="z-index: 2;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="d-flex overflow-auto gap-2 px-2" id="upcomingCarousel" style="scroll-behavior: smooth;">
                        @foreach($upcomingEvents as $evento)
                            <div class="event-thumb rounded-3 shadow-sm position-relative"
                                 data-title="{{ $evento->title }}"
                                 data-date="{{ $evento->date }}"
                                 data-time="{{ $evento->time }}"
                                 data-address="{{ $evento->address }}"
                                 data-photo-url="{{ $evento->photo_url }}"
                                 role="button"
                                 tabindex="0"
                                 aria-label="Evento: {{ $evento->title }}">
                                @if($evento->photo_url)
                                    <img src="{{ $evento->photo_url }}" alt="{{ $evento->title }}" class="w-100 h-100">
                                @else
                                    <div class="w-100 h-100 thumb-fallback">
                                        <div class="thumb-gradient"></div>
                                        <div class="thumb-title text-white fw-bold">{{ $evento->title }}</div>
                                    </div>
                                @endif
                                    <div class="thumb-overlay d-flex align-items-center justify-content-center">
                                        <i class="bi bi-mouse me-2 text-white"></i>
                                        <span class="text-white small fw-bold overlay-text">Clique para saber mais</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-light rounded-circle position-absolute top-50 end-0 translate-middle-y shadow-sm" id="upcomingNextBtn" style="z-index: 2;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
            @endif

            <!-- Quantitative Cards -->
            @if(isset($stats))
            <div class="row g-3 mb-4 row-cols-2 row-cols-sm-3 row-cols-md-5">
                <!-- Registros -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #0d6efd;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(13,110,253,0.1); color: #0d6efd;">
                                <i class="bi bi-people"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['registers'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Registros</p>
                        </div>
                    </div>
                </div>
                <!-- Usuários -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #0dcaf0;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(13,202,240,0.1); color: #0dcaf0;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['users'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Usuários</p>
                        </div>
                    </div>
                </div>
                <!-- Turmas -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #198754;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(25,135,84,0.1); color: #198754;">
                                <i class="bi bi-easel"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['classes'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Turmas</p>
                        </div>
                    </div>
                </div>
                <!-- Inscrições -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #ffc107;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(255,193,7,0.1); color: #ffc107;">
                                <i class="bi bi-card-checklist"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['enrollments'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Inscrições</p>
                        </div>
                    </div>
                </div>
                <!-- Categorias -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #6c757d;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(108,117,125,0.1); color: #6c757d;">
                                <i class="bi bi-tags"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['categories'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Categorias</p>
                        </div>
                    </div>
                </div>
                <!-- Comunidades -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #dc3545;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                                <i class="bi bi-building"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['communities'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Comunidades</p>
                        </div>
                    </div>
                </div>
                <!-- Protocolos -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #0d6efd;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(13,110,253,0.1); color: #0d6efd;">
                                <i class="bi bi-file-text"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['protocols'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Protocolos</p>
                        </div>
                    </div>
                </div>
                <!-- Vicentinos -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #dc3545;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(220,53,69,0.1); color: #dc3545;">
                                <i class="bi bi-heart"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['vicentinos'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Vicentinos</p>
                        </div>
                    </div>
                </div>
                <!-- Docs Pendentes -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #ffc107;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(255,193,7,0.1); color: #ffc107;">
                                <i class="bi bi-file-earmark-x"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['docs_pending'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Docs Pendentes</p>
                        </div>
                    </div>
                </div>
                <!-- Docs Entregues -->
                <div class="col">
                    <div class="card border-0 shadow-sm h-100 rounded-4 stat-card" style="--accent: #198754;">
                        <div class="card-body p-3">
                            <div class="stat-icon-wrap" style="background: rgba(25,135,84,0.1); color: #198754;">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-0 mt-2">{{ $stats['docs_delivered'] }}</h2>
                            <p class="text-muted small text-uppercase fw-semibold mb-0">Docs Entregues</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Charts & Filters Section -->
            @if(isset($chartData) || isset($accessChartData))

            {{-- Barra de filtro de datas --}}
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-line text-primary"></i>
                    <span class="fw-semibold text-dark">Análise de Dados</span>
                </div>
                <form action="{{ route('dashboard') }}" method="GET" class="d-flex align-items-center gap-2">
                    <div class="chart-filter-bar">
                        <i class="bi bi-calendar3 text-muted"></i>
                        <input type="date" name="start_date" class="chart-filter-input" value="{{ request('start_date') }}" title="Data Início">
                        <span class="text-muted px-1">—</span>
                        <input type="date" name="end_date" class="chart-filter-input" value="{{ request('end_date') }}" title="Data Fim">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 ms-1">
                            <i class="bi bi-funnel-fill"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Cards de gráficos --}}
            <div class="row g-3 mb-4">
                @if(isset($chartData))
                <div class="{{ isset($accessChartData) ? 'col-lg-7' : 'col-12' }}">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon-wrap me-3" style="background: rgba(13,110,253,0.1); color: #0d6efd; width:36px; height:36px; font-size:1rem;">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Análise Financeira</h6>
                                    <p class="text-muted small mb-0">Ofertas, Dízimos e Notas Fiscais</p>
                                </div>
                            </div>
                            <div style="height: 280px;">
                                <canvas id="financialChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if(isset($accessChartData))
                <div class="{{ isset($chartData) ? 'col-lg-5' : 'col-12' }}">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stat-icon-wrap me-3" style="background: rgba(25,135,84,0.1); color: #198754; width:36px; height:36px; font-size:1rem;">
                                    <i class="bi bi-phone"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Acessos por Dispositivo</h6>
                                    <p class="text-muted small mb-0">Web, Android e iOS</p>
                                </div>
                            </div>
                            <div style="height: 280px;">
                                <canvas id="accessChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <!-- Barra de Pesquisa de Módulos -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="module-search-wrap">
                        <i class="bi bi-search module-search-icon"></i>
                        <input type="text" id="moduleSearch" class="module-search-input" placeholder="Pesquisar módulos...">
                        <span class="module-search-hint">Pressione / para focar</span>
                    </div>
                </div>
            </div>

            <!-- Seção de Módulos Fixados -->
            @if($pinnedModules->count() > 0)
            <div class="mb-5" id="pinnedSection">
                <div class="d-flex align-items-center mb-3">
                    <span class="pinned-section-accent"></span>
                    <h5 class="fw-bold text-dark mb-0 me-2">
                        <i class="bi bi-pin-angle-fill text-primary me-2" style="font-size:1rem;"></i>Meus Fixados
                    </h5>
                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary" style="font-size:0.75rem;">{{ $pinnedModules->count() }}</span>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">
                    @foreach($pinnedModules as $module)
                    <div class="col module-item" data-slug="{{ $module['slug'] }}">
                        <div class="card h-100 border-0 shadow-sm card-module card-module-pinned text-center position-relative" style="background-color: {{ $module['bg_color'] ?? '#fff' }};">

                            <!-- Star indicator -->
                            <span class="module-pinned-star">★</span>

                            <!-- Options Dropdown -->
                            <div class="dropdown position-absolute top-0 end-0 p-1" style="z-index: 20;">
                                <button class="btn btn-sm btn-link text-muted p-0 module-options-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                                <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2 py-3 px-2">
                                    <div class="module-icon-wrap" style="background: rgba(0,0,0,0.04);">
                                        <i class="bi bi-{{ $module['icon'] }}" style="font-size: 1.9rem; color: inherit;"></i>
                                    </div>
                                    <span class="module-name fw-semibold text-truncate w-100">{{ $module['name'] }}</span>
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Separador com label -->
            <div class="modules-section-divider">
                <span class="modules-section-label">
                    <i class="bi bi-grid-3x3-gap me-1"></i> Todos os Módulos
                </span>
            </div>
            @endif

            <!-- Lista de Todos os Módulos (Agrupados A-Z) -->
            <div id="allModulesSection">
                @foreach($groupedModules as $letter => $modules)
                <div class="mb-4 module-group" data-letter="{{ $letter }}">
                    <div class="module-letter-chip">{{ $letter }}</div>
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3">
                        @foreach($modules as $module)
                        <div class="col module-item">
                            <div class="card h-100 border-0 shadow-sm card-module text-center position-relative">
                                <!-- Pin Button (aparece no hover) -->
                                <button class="btn pin-btn pin-action-btn" data-slug="{{ $module['slug'] }}" title="{{ $module['is_pinned'] ? 'Desafixar' : 'Fixar' }}">
                                    <i class="bi {{ $module['is_pinned'] ? 'bi-pin-fill text-primary' : 'bi-pin' }}"></i>
                                </button>

                                <a href="{{ $module['url'] ?? '#' }}" class="text-decoration-none text-dark d-block h-100">
                                    <div class="card-body d-flex flex-column align-items-center justify-content-center gap-2 py-3 px-2">
                                        <div class="module-icon-wrap">
                                            <i class="bi bi-{{ $module['icon'] }} text-primary" style="font-size: 1.9rem;"></i>
                                        </div>
                                        <span class="module-name fw-semibold text-truncate w-100">{{ $module['name'] }}</span>
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
        <!-- /dashboardContent -->
        </div>

        <!-- Sidebar (Online Users) -->
        <div class="col-lg-2 border-start ps-3">
            <div class="sidebar-online-wrap">
                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-1">
                        <span class="online-pulse-dot"></span>
                        <h5 class="fw-bold mb-0" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #374151;">Agora no sistema</h5>
                    </div>
                    <span class="badge rounded-pill bg-success text-white" id="onlineCountBadge" style="font-size:0.65rem; padding: 3px 7px;">0</span>
                </div>

                <!-- Lista de usuários -->
                <div id="onlineUsersList">
                    <!-- Skeleton -->
                    <div class="online-skeleton">
                        <div class="skeleton-avatar"></div>
                        <div class="skeleton-lines">
                            <div class="skeleton-line w-75"></div>
                            <div class="skeleton-line w-50"></div>
                        </div>
                    </div>
                    <div class="online-skeleton">
                        <div class="skeleton-avatar"></div>
                        <div class="skeleton-lines">
                            <div class="skeleton-line w-75"></div>
                            <div class="skeleton-line w-50"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhe do Evento -->
<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="eventDetailTitle">Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="eventDetailImageWrapper" style="height: 180px;">
                    <img id="eventDetailImage" src="#" alt="" class="w-100 h-100 rounded-3" style="object-fit: cover; display: none;">
                    <div id="eventDetailFallback" class="w-100 h-100 rounded-3 position-relative" style="display: none; background: linear-gradient(135deg, #0d6efd, #6c757d);">
                        <div class="position-absolute bottom-0 start-0 p-3 text-white fw-bold" id="eventDetailFallbackTitle"></div>
                    </div>
                </div>
                <div class="text-muted mb-2"><i class="bi bi-calendar-event me-1"></i><span id="eventDetailDate"></span></div>
                <div class="text-muted mb-2"><i class="bi bi-clock me-1"></i><span id="eventDetailTime"></span></div>
                <div class="text-muted"><i class="bi bi-geo-alt me-1"></i><span id="eventDetailAddress"></span></div>
            </div>
        </div>
    </div>
    </div>


<style>
    /* =============================================
       SHIMMER LOADING OVERLAY
    ============================================= */
    .shimmer-card {
        background-color: #fff;
        border: 1px solid #f1f5f9;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }
    .shimmer-block {
        background: #f6f7f8;
        background-image: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-repeat: no-repeat;
        background-size: 1000px 100%;
        animation: shimmer-animation 1.5s infinite linear;
    }
    @keyframes shimmer-animation {
        0% { background-position: -1000px 0; }
        100% { background-position: 1000px 0; }
    }

    /* =============================================
       STAT CARDS
    ============================================= */
    .stat-card {
        position: relative;
        overflow: hidden;
        transition: transform 0.2s ease, box-shadow 0.25s ease;
    }
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent, #0d6efd);
        border-radius: 16px 16px 0 0;
        opacity: 0.8;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.1) !important;
    }
    .stat-icon-wrap {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .stat-card h2 {
        font-size: 1.6rem;
        line-height: 1;
    }
    .stat-card p {
        font-size: 0.65rem;
        letter-spacing: 0.04em;
    }

    /* =============================================
       CHART FILTER BAR
    ============================================= */
    .chart-filter-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        background: #f8f9fa;
        border-radius: 50px;
        padding: 6px 14px;
        border: 1px solid #e9ecef;
    }
    .chart-filter-input {
        border: none;
        background: transparent;
        font-size: 0.85rem;
        color: #495057;
        outline: none;
        width: 130px;
    }
    .chart-filter-input:focus {
        outline: none;
        box-shadow: none;
    }

    /* =============================================
       MODULE SEARCH
    ============================================= */
    .module-search-wrap {
        position: relative;
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 50px;
        padding: 10px 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border: 1.5px solid transparent;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .module-search-wrap:focus-within {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13,110,253,0.1);
    }
    .module-search-icon {
        color: #adb5bd;
        font-size: 1.1rem;
        margin-right: 10px;
        flex-shrink: 0;
    }
    .module-search-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 1rem;
        background: transparent;
        color: #212529;
    }
    .module-search-input::placeholder {
        color: #adb5bd;
    }
    .module-search-hint {
        font-size: 0.7rem;
        color: #ced4da;
        white-space: nowrap;
        background: #f8f9fa;
        border-radius: 4px;
        padding: 2px 6px;
        border: 1px solid #e9ecef;
    }
    @media (max-width: 576px) {
        .module-search-hint { display: none; }
    }

    /* =============================================
       PINNED SECTION ACCENT
    ============================================= */
    .pinned-section-accent {
        display: inline-block;
        width: 4px;
        height: 20px;
        background: #0d6efd;
        border-radius: 2px;
        margin-right: 10px;
        flex-shrink: 0;
    }

    /* =============================================
       SIDEBAR ONLINE — SOCIAL STYLE
    ============================================= */
    .sidebar-online-wrap {
        position: sticky;
        top: 80px;
        max-height: calc(100vh - 100px);
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-width: thin;
        scrollbar-color: #e5e7eb transparent;
    }
    /* Dot pulsante no header */
    .online-pulse-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        flex-shrink: 0;
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0%, 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0.5); }
        50%       { box-shadow: 0 0 0 5px rgba(34,197,94,0); }
    }
    /* Skeleton loader */
    .online-skeleton {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
    }
    .skeleton-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.4s infinite;
        flex-shrink: 0;
    }
    .skeleton-lines { flex: 1; display: flex; flex-direction: column; gap: 6px; }
    .skeleton-line {
        height: 10px;
        border-radius: 5px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.4s infinite;
    }
    @keyframes skeleton-shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    /* Label de secao Online / Recentes */
    .online-section-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #22c55e;
        display: flex;
        align-items: center;
        gap: 5px;
        margin: 4px 0 6px;
    }
    .online-dot-sm {
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #22c55e;
        animation: pulse-green 2s infinite;
    }
    /* Card de usuario */
    .online-user-card {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 6px 4px;
        border-radius: 10px;
        transition: background 0.15s ease;
        cursor: default;
    }
    .online-user-card:hover { background: #f3f4f6; }
    /* Wrapper do avatar com borda de status */
    .online-avatar-wrap {
        position: relative;
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        padding: 2px;
    }
    .online-avatar-wrap.status-online {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 2px 8px rgba(34,197,94,0.35);
    }
    .online-avatar-wrap.status-offline { background: #e5e7eb; }
    .online-avatar-img,
    .online-avatar-fallback {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .online-avatar-fallback {
        font-size: 0.9rem;
        font-weight: 700;
        color: #fff;
    }
    /* Info do usuario */
    .online-user-info { flex: 1; min-width: 0; }
    .online-user-name {
        font-size: 0.78rem;
        font-weight: 600;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }
    .online-user-meta {
        display: flex;
        align-items: center;
        gap: 3px;
        margin-top: 1px;
    }
    .online-user-status { font-size: 0.67rem; color: #6b7280; }
    .online-user-role { font-size: 0.62rem; color: #9ca3af; margin-top: 1px; line-height: 1.2; }
    .device-badge { font-size: 0.62rem; color: #9ca3af; }
    /* Tag Voce */
    .me-tag {
        display: inline-block;
        font-size: 0.58rem;
        font-weight: 700;
        color: #0d6efd;
        background: rgba(13,110,253,0.1);
        border-radius: 4px;
        padding: 0 4px;
        vertical-align: middle;
        margin-left: 2px;
    }
    /* Estado vazio */
    .online-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 24px 0;
        color: #d1d5db;
        font-size: 0.75rem;
    }
    .online-empty i { font-size: 1.8rem; }

    /* =============================================
       MODULE CARDS — APP LAUNCHER STYLE
    ============================================= */
    .card-module {
        transition: transform 0.22s ease, box-shadow 0.22s ease;
        border-radius: 1rem;
        background: #fff;
        cursor: pointer;
    }
    .card-module:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(13, 110, 253, 0.12) !important;
        background-color: #fff;
        z-index: 10;
    }
    /* Ícone do módulo */
    .module-icon-wrap {
        width: 62px;
        height: 62px;
        border-radius: 16px;
        background: #eef2ff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease, background 0.2s ease;
        flex-shrink: 0;
    }
    .card-module:hover .module-icon-wrap {
        transform: scale(1.08);
        background: #dbeafe;
    }
    /* Nome do módulo */
    .module-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #374151;
        text-align: center;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
    }
    /* Cards fixados: ligeiramente maiores */
    .card-module-pinned .module-icon-wrap {
        width: 68px;
        height: 68px;
        border-radius: 18px;
    }
    /* Badge estrela dos fixados */
    .module-pinned-star {
        position: absolute;
        top: 8px;
        left: 10px;
        font-size: 0.7rem;
        color: #fbbf24;
        z-index: 10;
        line-height: 1;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    /* Botão de opções dos fixados */
    .module-options-btn {
        opacity: 0;
        transition: opacity 0.18s ease;
        color: #6b7280 !important;
        font-size: 0.85rem;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    .card-module:hover .module-options-btn {
        opacity: 1;
    }
    .module-options-btn:hover {
        background: rgba(0,0,0,0.06) !important;
    }
    /* Pin button — só aparece no hover */
    .pin-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        z-index: 20;
        opacity: 0;
        transition: opacity 0.18s ease, transform 0.18s ease, background 0.18s ease;
        color: #9ca3af;
        font-size: 0.9rem;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(4px);
        border: none;
        box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        padding: 0;
    }
    .card-module:hover .pin-btn {
        opacity: 1;
    }
    /* Módulo já fixado: pin sempre visível e azul */
    .pin-btn:has(.bi-pin-fill) {
        opacity: 1;
        color: #0d6efd;
    }
    .pin-btn:hover {
        background: #eff6ff !important;
        color: #0d6efd !important;
        transform: scale(1.1);
    }
    .pin-btn i.text-primary,
    .pin-btn .bi-pin-fill {
        color: #0d6efd !important;
    }
    /* Chip de letra A-Z */
    .module-letter-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        background: #0d6efd;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        margin-bottom: 10px;
    }
    /* Separador entre seções */
    .modules-section-divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
    }
    .modules-section-divider::before,
    .modules-section-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    .modules-section-label {
        padding: 4px 14px;
        font-size: 0.72rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 50px;
        margin: 0 12px;
        white-space: nowrap;
    }

    /* =============================================
       EVENT THUMBS
    ============================================= */
    .event-thumb {
        width: 180px;
        height: 110px;
        background-color: #f8f9fa;
        overflow: hidden;
        cursor: pointer;
        position: relative;
    }
    @media (max-width: 576px) {
        .event-thumb { width: 160px; height: 100px; }
    }
    @media (min-width: 992px) {
        .event-thumb { width: 200px; height: 120px; }
    }
    .event-thumb img {
        object-fit: contain;
        object-position: center center;
        background-color: #000;
        display: block;
    }
    .event-thumb .thumb-fallback {
        position: relative;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: flex-end;
    }
    .event-thumb .thumb-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.35) 70%, rgba(0,0,0,0.6) 100%);
    }
    .event-thumb .thumb-title {
        position: relative;
        padding: 8px;
        font-size: 0.8rem;
        text-shadow: 0 1px 2px rgba(0,0,0,0.35);
    }
    .event-thumb .thumb-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.0);
        opacity: 0;
        transition: opacity 0.2s ease, background 0.2s ease;
        text-align: center;
    }
    .event-thumb:hover .thumb-overlay {
        opacity: 1;
        background: rgba(0,0,0,0.35);
    }
    .event-thumb:focus {
        outline: 2px solid #0d6efd;
        outline-offset: 2px;
    }
    .event-thumb:focus .thumb-overlay {
        opacity: 1;
        background: rgba(0,0,0,0.35);
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Shimmer Fade Out
        setTimeout(() => {
            const shimmer = document.getElementById('dashboardShimmer');
            const content = document.getElementById('dashboardContent');
            if(shimmer && content) {
                shimmer.style.transition = 'opacity 0.4s ease';
                shimmer.style.opacity = '0';
                setTimeout(() => {
                    shimmer.style.display = 'none';
                    content.style.opacity = '1';
                }, 400); // aguarda a animação do shimmer sumir para exibir o conteudo
            }
        }, 150); // delay minimo para evitar flash rapido demais
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Financial Chart
        const chartCanvas = document.getElementById('financialChart');
        if (chartCanvas) {
            const ctx = chartCanvas.getContext('2d');
            const chartData = @json($chartData);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Ofertas',
                            data: chartData.ofertas,
                            borderColor: '#0d6efd', // Primary
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Dízimos',
                            data: chartData.dizimos,
                            borderColor: '#198754', // Success
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Notas Fiscais',
                            data: chartData.notas,
                            borderColor: '#ffc107', // Warning
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumSignificantDigits: 3 }).format(value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // Access Chart
        @if(isset($accessChartData))
        const ctxAccess = document.getElementById('accessChart');
        if (ctxAccess) {
            new Chart(ctxAccess, {
                type: 'line',
                data: {
                    labels: @json($accessChartData['labels']),
                    datasets: [
                        {
                            label: 'Web (Portal)',
                            data: @json($accessChartData['web']),
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Android',
                            data: @json($accessChartData['android']),
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'iOS',
                            data: @json($accessChartData['ios']),
                            borderColor: '#212529',
                            backgroundColor: 'rgba(33, 37, 41, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
        @endif

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

        // Atalho "/" para focar na busca
        document.addEventListener('keydown', function(e) {
            if (e.key === '/' && document.activeElement !== searchInput && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
            }
            if (e.key === 'Escape' && document.activeElement === searchInput) {
                searchInput.blur();
            }
        });

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

        // =============================================
        // PING — registra presença do usuário web
        // =============================================
        function pingPresence() {
            fetch('{{ route("dashboard.ping") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            }).catch(() => {}); // silencia erros de rede
        }
        pingPresence(); // ping imediato ao carregar
        setInterval(pingPresence, 60000); // ping a cada 60s

        // =============================================
        // ONLINE USERS — polling e renderização
        // =============================================
        function buildAvatar(user) {
            if (user.avatar_url) {
                return `<img src="${user.avatar_url}" class="online-avatar-img" alt="${user.name}"
                    onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(user.initials)}&background=0d6efd&color=fff&size=96';">`;
            }
            // Gera cor de fundo baseada nas iniciais (determinística)
            const colors = ['#0d6efd','#198754','#dc3545','#ffc107','#0dcaf0','#6c757d','#6610f2','#fd7e14'];
            const idx = (user.initials.charCodeAt(0) || 0) % colors.length;
            return `<div class="online-avatar-fallback" style="background: ${colors[idx]}">${user.initials}</div>`;
        }

        function buildDeviceBadge(type) {
            if (type == 1) return `<span class="device-badge" title="Web"><i class="bi bi-globe2"></i></span>`;
            if (type == 2) return `<span class="device-badge text-success" title="Android"><i class="bi bi-android2"></i></span>`;
            if (type == 3) return `<span class="device-badge" title="iOS"><i class="bi bi-apple"></i></span>`;
            return '';
        }

        function renderOnlineUsers(data) {
            const container = document.getElementById('onlineUsersList');
            const badge     = document.getElementById('onlineCountBadge');

            const onlineUsers  = data.filter(u => u.is_online);
            const recentUsers  = data.filter(u => !u.is_online);

            if (badge) badge.textContent = onlineUsers.length;

            if (data.length === 0) {
                container.innerHTML = `
                    <div class="online-empty">
                        <i class="bi bi-wifi-off"></i>
                        <span>Nenhum acesso recente</span>
                    </div>`;
                return;
            }

            let html = '';

            // Seção Online
            if (onlineUsers.length > 0) {
                html += `<p class="online-section-label"><span class="online-dot-sm"></span> Online</p>`;
                onlineUsers.forEach(user => {
                    html += buildUserCard(user);
                });
            }

            // Seção Recentes
            if (recentUsers.length > 0) {
                html += `<p class="online-section-label mt-2" style="color:#9ca3af;"><i class="bi bi-clock me-1"></i>Recentes</p>`;
                recentUsers.forEach(user => {
                    html += buildUserCard(user);
                });
            }

            container.innerHTML = html;
        }

        function buildUserCard(user) {
            const statusClass = user.is_online ? 'status-online' : 'status-offline';
            const meTag       = user.is_me ? `<span class="me-tag">Você</span>` : '';
            return `
                <div class="online-user-card">
                    <div class="online-avatar-wrap ${statusClass}">
                        ${buildAvatar(user)}
                    </div>
                    <div class="online-user-info">
                        <div class="online-user-name" title="${user.name}">${user.name} ${meTag}</div>
                        <div class="online-user-meta">
                            ${buildDeviceBadge(user.device_type)}
                            <span class="online-user-status">${user.status_text}</span>
                        </div>
                        ${user.role_label ? `<div class="online-user-role text-truncate">${user.role_label}</div>` : ''}
                    </div>
                </div>`;
        }

        function fetchOnlineUsers() {
            fetch('{{ route("dashboard.online-users") }}')
                .then(r => r.json())
                .then(data => renderOnlineUsers(data))
                .catch(() => {});
        }

        // Carrega imediatamente e depois a cada 30s
        fetchOnlineUsers();
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
                    
                    // Weather (Open-Meteo - Free, No Key)
                    fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,weather_code&timezone=auto`)
                        .then(res => res.json())
                        .then(data => {
                            const temp = Math.round(data.current.temperature_2m);
                            const tempEl = document.getElementById('weatherTemp');
                            if(tempEl) tempEl.textContent = `${temp}°C`;
                            
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
                            
                            const iconEl = document.getElementById('weatherIcon');
                            if(iconEl) iconEl.className = `bi ${icon} me-2`;
                        })
                        .catch(err => console.error('Weather error:', err));

                }, error => {
                    console.log('Geolocation denied or failed:', error);
                    // No UI update needed for error in simple mode, or maybe specific placeholder
                });
            } else {
                console.log('Navegador incompatível');
            }
        }
        fetchWeather(); // Call once on load

        // Eventos carousels scroll
        function bindCarouselControls(containerId, prevBtnId, nextBtnId) {
            const container = document.getElementById(containerId);
            const prevBtn = document.getElementById(prevBtnId);
            const nextBtn = document.getElementById(nextBtnId);
            if (!container) return;
            const touch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
            const scrollAmount = () => Math.max(280, Math.floor(container.clientWidth * 0.8));
            if (prevBtn) prevBtn.addEventListener('click', () => container.scrollBy({ left: -scrollAmount(), behavior: 'smooth' }));
            if (nextBtn) nextBtn.addEventListener('click', () => container.scrollBy({ left: scrollAmount(), behavior: 'smooth' }));
            function updateArrows() {
                if (touch) {
                    if (prevBtn) prevBtn.style.display = 'none';
                    if (nextBtn) nextBtn.style.display = 'none';
                    return;
                }
                const canScroll = container.scrollWidth > container.clientWidth + 4;
                if (prevBtn) prevBtn.style.display = canScroll && container.scrollLeft > 0 ? 'inline-flex' : (canScroll ? 'inline-flex' : 'none');
                if (nextBtn) nextBtn.style.display = canScroll && (container.scrollLeft + container.clientWidth < container.scrollWidth - 1) ? 'inline-flex' : (canScroll ? 'inline-flex' : 'none');
                if (!canScroll) {
                    if (prevBtn) prevBtn.style.display = 'none';
                    if (nextBtn) nextBtn.style.display = 'none';
                }
            }
            updateArrows();
            container.addEventListener('scroll', updateArrows);
            window.addEventListener('resize', updateArrows);
        }
        bindCarouselControls('todayCarousel', 'todayPrevBtn', 'todayNextBtn');
        bindCarouselControls('upcomingCarousel', 'upcomingPrevBtn', 'upcomingNextBtn');

        // Bind event detail modal
        const modalEl = document.getElementById('eventDetailModal');
        const eventModal = modalEl ? new bootstrap.Modal(modalEl) : null;
        function formatDate(dateStr) {
            try { return new Date(dateStr).toLocaleDateString('pt-BR'); } catch (e) { return dateStr; }
        }
        function hhmm(timeStr) {
            return (timeStr || '').substring(0,5);
        }
        function openEventModal(data) {
            document.getElementById('eventDetailTitle').textContent = data.title || 'Evento';
            document.getElementById('eventDetailDate').textContent = formatDate(data.date || '');
            document.getElementById('eventDetailTime').textContent = hhmm(data.time || '');
            document.getElementById('eventDetailAddress').textContent = data.address || '';
            const imgEl = document.getElementById('eventDetailImage');
            const wrap = document.getElementById('eventDetailImageWrapper');
            const fb = document.getElementById('eventDetailFallback');
            const fbTitle = document.getElementById('eventDetailFallbackTitle');
            if (data.photoUrl) {
                imgEl.src = data.photoUrl;
                imgEl.style.display = 'block';
                fb.style.display = 'none';
            } else {
                fb.style.display = 'block';
                fbTitle.textContent = data.title || '';
                imgEl.style.display = 'none';
            }
            if (eventModal) eventModal.show();
        }
        document.querySelectorAll('.event-thumb').forEach(el => {
            function trigger() {
                openEventModal({
                    title: el.getAttribute('data-title'),
                    date: el.getAttribute('data-date'),
                    time: el.getAttribute('data-time'),
                    address: el.getAttribute('data-address'),
                    photoUrl: el.getAttribute('data-photo-url'),
                });
            }
            el.addEventListener('click', trigger);
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    trigger();
                }
            });
        });

        const isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
        document.querySelectorAll('.thumb-overlay').forEach(el => {
            const icon = el.querySelector('i');
            const text = el.querySelector('.overlay-text');
            if (isTouch) {
                if (icon) icon.className = 'bi bi-hand-index-thumb me-2 text-white';
                if (text) text.textContent = 'Toque para saber mais';
            } else {
                if (icon) icon.className = 'bi bi-mouse me-2 text-white';
                if (text) text.textContent = 'Clique para saber mais';
            }
        });
    });
</script>
@endsection
