@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Novo Aviso</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('avisos.index') }}" class="text-decoration-none">Avisos Paroquiais</a></li>
                <li class="breadcrumb-item active" aria-current="page">Novo</li>
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
            <form action="{{ route('avisos.store') }}" method="post" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold text-muted small">Título</label>
                    <input type="text" name="title" class="form-control rounded-3" required maxlength="255" value="{{ old('title') }}">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold text-muted small">Nível de importância</label>
                    <select name="level_importance" class="form-select rounded-3" required>
                        <option value="0" @selected(old('level_importance')==='0')>Normal</option>
                        <option value="1" @selected(old('level_importance')==='1')>Médio</option>
                        <option value="2" @selected(old('level_importance')==='2')>Alto</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold text-muted small">Descrição</label>
                    <textarea name="legend" class="form-control rounded-3" rows="4" required>{{ old('legend') }}</textarea>
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-bold text-muted small">Imagem do aviso</label>
                    <input type="file" name="anexo" id="avisoAnexo" class="d-none" accept="image/*">
                    <div id="avisoDropArea" class="file-drop-area rounded-4 border border-2 bg-light p-4 cursor-pointer">
                        <div id="avisoDropContent" class="text-center text-muted">
                            <i class="bi bi-cloud-arrow-up fs-1 d-block mb-2"></i>
                            <div class="fw-semibold">Arraste e solte a imagem aqui</div>
                            <div class="small">ou clique para selecionar</div>
                            <div class="small mt-2">Formatos: JPG, PNG, GIF, WEBP • até 5MB</div>
                        </div>
                        <div id="avisoPreviewArea" class="d-none">
                            <div class="d-flex align-items-center gap-3">
                                <img id="avisoImgPreview" src="#" alt="Preview" class="rounded-4 shadow-sm border" style="width: 120px; height: 120px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <p id="avisoFileName" class="small text-muted mb-2"></p>
                                    <button type="button" class="btn btn-sm btn-light rounded-pill border" id="avisoRemoveFile">Remover / Alterar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('avisos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2">
                        <i class="mdi mdi-send"></i>
                        <span>Publicar aviso</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .file-drop-area {
        transition: all 0.2s ease;
        border-style: dashed !important;
        border-color: #dee2e6;
    }
    .file-drop-area:hover, .file-drop-area.dragover {
        border-color: #0d6efd;
        background-color: #f1f8ff !important;
    }
    .cursor-pointer { cursor: pointer; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropArea = document.getElementById('avisoDropArea');
    const fileInput = document.getElementById('avisoAnexo');
    const dropContent = document.getElementById('avisoDropContent');
    const previewArea = document.getElementById('avisoPreviewArea');
    const imgPreview = document.getElementById('avisoImgPreview');
    const fileName = document.getElementById('avisoFileName');
    const removeFileBtn = document.getElementById('avisoRemoveFile');

    if (!dropArea || !fileInput) return;

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });
    function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.add('dragover'), false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, () => dropArea.classList.remove('dragover'), false);
    });

    dropArea.addEventListener('click', function() {
        fileInput.click();
    });

    dropArea.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }, false);

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (!files || files.length === 0) return;
        const file = files[0];
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione apenas arquivos de imagem.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            imgPreview.src = e.target.result;
            fileName.textContent = file.name;
            dropContent.classList.add('d-none');
            previewArea.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
    }

    removeFileBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fileInput.value = '';
        dropContent.classList.remove('d-none');
        previewArea.classList.add('d-none');
        imgPreview.src = '#';
        fileName.textContent = '';
    });
});
</script>
@endsection
