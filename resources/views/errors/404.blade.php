@extends('layouts.app')

@section('title', 'Página não encontrada')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center min-vh-100 bg-light text-center">
    <div class="card border-0 shadow-sm p-5" style="max-width: 600px; border-radius: 1rem;">
        <div class="mb-4">
            <i class="bi bi-emoji-dizzy text-primary" style="font-size: 5rem;"></i>
        </div>
        <h1 class="fw-bold text-dark mb-2">404</h1>
        <h4 class="text-muted mb-4">Página não encontrada</h4>
        <p class="text-secondary mb-5">
            Ops! A página que você está procurando não existe, foi movida ou você não tem permissão para acessá-la.
        </p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm">
            <i class="bi bi-arrow-left me-2"></i> Voltar ao Início
        </a>
    </div>
</div>
@endsection
