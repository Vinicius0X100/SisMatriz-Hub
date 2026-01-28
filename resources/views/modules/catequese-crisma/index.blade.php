@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Catequese de Crisma</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Catequese de Crisma</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5 text-center">
            <div class="mb-3">
                <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 rounded-circle" style="width: 80px; height: 80px;">
                    <i class="bi bi-fire fs-1 text-danger"></i>
                </div>
            </div>
            <h4 class="fw-bold">Módulo em Desenvolvimento</h4>
            <p class="text-muted">As funcionalidades deste módulo serão implementadas em breve.</p>
        </div>
    </div>
</div>
@endsection
