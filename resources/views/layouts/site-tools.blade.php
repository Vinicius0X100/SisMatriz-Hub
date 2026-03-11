@extends('layouts.app')

@section('content')
<div class="d-flex flex-column flex-lg-row h-100" style="min-height: calc(100vh - 80px);" x-data="{ openDropdown: false, mobileMenuOpen: false }">
    <!-- Sidebar -->
    <div class="bg-white border-end d-lg-block" :class="mobileMenuOpen ? '' : 'd-none'" style="width: 280px; flex-shrink: 0;">
        <div class="p-4 border-bottom">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                <i class="bi bi-tools text-primary me-2 fs-4"></i>
                Ferramentas
            </h5>
        </div>
        
        <nav class="list-group list-group-flush pt-2">
            <!-- Dropdown Personalização -->
            <div class="list-group-item border-0 p-0">
                <button @click="openDropdown = !openDropdown" class="d-flex align-items-center justify-content-between w-100 p-3 bg-transparent border-0 text-secondary list-group-item-action focus-ring-none">
                    <span class="d-flex align-items-center fw-medium">
                        <i class="bi bi-palette me-3 fs-5"></i> Personalização
                    </span>
                    <i :class="{'bi-chevron-up': openDropdown, 'bi-chevron-down': !openDropdown}" class="bi small transition-transform"></i>
                </button>
                <div x-show="openDropdown" 
                     x-transition
                     class="bg-light border-start border-3 border-primary ms-4 me-3 mb-2 rounded overflow-hidden" style="display: none;">
                    <a href="#" class="d-block p-2 px-3 text-decoration-none text-secondary small hover:bg-white hover:text-primary transition-colors">Cores do Tema</a>
                    <a href="#" class="d-block p-2 px-3 text-decoration-none text-secondary small hover:bg-white hover:text-primary transition-colors">Logotipo e Favicon</a>
                    <a href="#" class="d-block p-2 px-3 text-decoration-none text-secondary small hover:bg-white hover:text-primary transition-colors">Rodapé</a>
                </div>
            </div>

            <!-- Galeria Paroquial -->
            <a href="{{ route('site-tools.gallery') }}" 
               class="list-group-item list-group-item-action border-0 p-3 d-flex align-items-center transition-all {{ request()->routeIs('site-tools.gallery') ? 'bg-primary-subtle text-primary border-end border-4 border-primary' : 'text-secondary' }}">
                <i class="bi bi-images me-3 fs-5"></i> 
                <span class="fw-bold">Galeria Paroquial</span>
            </a>

            <a href="{{ route('site-tools.paroquia-ajustes') }}"
               class="list-group-item list-group-item-action border-0 p-3 d-flex align-items-center transition-all {{ request()->routeIs('site-tools.paroquia-ajustes') ? 'bg-primary-subtle text-primary border-end border-4 border-primary' : 'text-secondary' }}">
                <i class="bi bi-gear me-3 fs-5"></i>
                <span class="fw-bold">Ajustes da Paróquia</span>
            </a>
            
            <!-- Outras Opções (Placeholders) -->
            <a href="#" class="list-group-item list-group-item-action border-0 p-3 d-flex align-items-center text-muted opacity-50 cursor-not-allowed">
                <i class="bi bi-newspaper me-3 fs-5"></i> 
                <span class="fw-medium">Notícias (Em breve)</span>
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 bg-light p-4 overflow-auto">
        <!-- Mobile Menu Toggle Button (visible only on small screens) -->
        <button class="btn btn-light d-lg-none mb-3 shadow-sm rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" @click="mobileMenuOpen = !mobileMenuOpen">
            <i class="bi fs-5" :class="mobileMenuOpen ? 'bi-x-lg' : 'bi-list'"></i>
        </button>

        @yield('tool-content')
    </div>
</div>
@endsection
