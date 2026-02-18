@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Editar Aviso</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('avisos.index') }}" class="text-decoration-none">Avisos Paroquiais</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('avisos.update', $aviso) }}" method="post" enctype="multipart/form-data" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold text-muted small">Título</label>
                    <input type="text" name="title" class="form-control rounded-3" required maxlength="255" value="{{ old('title', $aviso->title) }}">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold text-muted small">Nível de importância</label>
                    <select name="level_importance" class="form-select rounded-3" required>
                        <option value="0" @selected(old('level_importance', $aviso->level_importance)==='0')>Normal</option>
                        <option value="1" @selected(old('level_importance', $aviso->level_importance)==='1')>Médio</option>
                        <option value="2" @selected(old('level_importance', $aviso->level_importance)==='2')>Alto</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold text-muted small">Descrição</label>
                    <textarea name="legend" class="form-control rounded-3" rows="4" required>{{ old('legend', $aviso->legend) }}</textarea>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold text-muted small">Anexo (imagem ou arquivo)</label>
                    <input type="file" name="anexo" class="form-control rounded-3">
                    <div class="form-text small text-muted">Arquivo será salvo em storage/uploads/feed.</div>
                    @php
                        $currentPath = $aviso->anexo ? storage_path('app/public/' . $aviso->anexo) : null;
                        $currentUrl = ($currentPath && file_exists($currentPath)) ? asset('storage/' . $aviso->anexo) : null;
                    @endphp
                    @if($currentUrl)
                        <div class="mt-2">
                            <a href="{{ $currentUrl }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill">
                                <i class="bi bi-paperclip me-2"></i> Abrir anexo atual
                            </a>
                        </div>
                    @endif
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('avisos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2">
                        <i class="bi bi-save"></i>
                        <span>Salvar alterações</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
