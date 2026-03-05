@extends('layouts.site-tools')

@section('tool-content')
<div class="container-fluid px-0" style="max-width: 1400px;">
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Ferramentas do Site</h2>
            <p class="text-muted mb-0">Gerencie o conteúdo e a aparência do seu site paroquial.</p>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Card Galeria Paroquial -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all rounded-4 overflow-hidden">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="bg-primary-subtle text-primary rounded-circle p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bi bi-images fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Galeria Paroquial</h5>
                    <p class="text-muted small mb-4">Gerencie fotos, posters e imagens exibidas no site da paróquia. Suporta upload em lote.</p>
                    <a href="{{ route('site-tools.gallery') }}" class="btn btn-primary rounded-pill px-4 fw-bold mt-auto w-100">
                        Acessar Galeria
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Personalização (Placeholder) -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all rounded-4 overflow-hidden">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="bg-secondary-subtle text-secondary rounded-circle p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bi bi-palette fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Personalização</h5>
                    <p class="text-muted small mb-4">Ajuste cores, logotipos e rodapé do site para combinar com a identidade visual da paróquia.</p>
                    <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold mt-auto w-100" disabled>
                        Em Breve
                    </button>
                </div>
            </div>
        </div>

        <!-- Card Notícias (Placeholder) -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all rounded-4 overflow-hidden">
                <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                    <div class="bg-secondary-subtle text-secondary rounded-circle p-3 mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="bi bi-newspaper fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Notícias</h5>
                    <p class="text-muted small mb-4">Publique notícias e avisos importantes para a comunidade paroquial.</p>
                    <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold mt-auto w-100" disabled>
                        Em Breve
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
</style>
@endpush
@endsection
