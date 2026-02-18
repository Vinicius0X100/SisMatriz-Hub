@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Novo bucket</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('buckets.index') }}" class="text-decoration-none">Buckets de mídia</a></li>
                <li class="breadcrumb-item active" aria-current="page">Novo</li>
            </ol>
        </nav>
    </div>

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
            <div class="row g-4">
                <div class="col-lg-7">
                    <form action="{{ route('buckets.store') }}" method="post" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted small">Nome do bucket</label>
                            <input type="text" name="name" class="form-control rounded-3" required maxlength="255" value="{{ old('name') }}" placeholder="Ex: Imagens da paróquia">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small">Região</label>
                            <select class="form-select rounded-3" disabled>
                                <option value="1" selected>Região 1 (padrão)</option>
                            </select>
                            <div class="form-text small text-muted">No futuro você poderá escolher outras regiões.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold text-muted small">Limite de armazenamento</label>
                            <input type="text" class="form-control rounded-3" value="1 GB" disabled>
                            <div class="form-text small text-muted">Cada bucket possui até 1 GB de espaço.</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('buckets.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2">
                                <i class="mdi mdi-cloud-plus-outline"></i>
                                <span>Criar bucket</span>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-5">
                    <div class="bg-light rounded-4 p-4 h-100 d-flex flex-column justify-content-center">
                        <h5 class="fw-bold mb-3">Como funcionam os buckets de mídia</h5>
                        <ul class="small text-muted mb-0">
                            <li>Cada bucket é um “cofre” separado para arquivos da sua paróquia.</li>
                            <li>Limite de até 1 GB por bucket.</li>
                            <li>Armazene imagens, vídeos, PDFs, documentos e outros tipos de arquivo.</li>
                            <li>Os arquivos ficam organizados por usuário e bucket no storage seguro do sistema.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

