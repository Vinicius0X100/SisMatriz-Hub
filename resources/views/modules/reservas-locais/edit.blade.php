@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Sala/Espaço</h2>
            <p class="text-muted small mb-0">Atualize os dados do local.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reservas-locais.index') }}" class="text-decoration-none">Salas e Espaços</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            
            <form action="{{ route('reservas-locais.update', $local->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold small text-muted">Nome do Local <span class="text-danger fw-bold small">(obrigatório)</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="name" name="name" required value="{{ old('name', $local->name) }}">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted">Foto do Local (Opcional)</label>
                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-4 text-center position-relative" id="dropArea">
                            <input type="file" name="foto" id="foto" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/*">
                            <div class="d-flex flex-column align-items-center justify-content-center {{ $local->foto ? 'd-none' : '' }}" id="dropContent">
                                <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i>
                                <h6 class="fw-bold text-dark">Clique ou arraste a foto aqui</h6>
                                <p class="text-muted small">Para substituir a atual</p>
                            </div>
                            <div id="previewArea" class="{{ $local->foto ? '' : 'd-none' }} mt-3">
                                <img id="imgPreview" src="{{ $local->foto ? asset('storage/' . $local->foto) : '#' }}" alt="Preview" class="rounded-4 shadow-sm border" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                <p id="fileName" class="small text-muted mt-2 mb-0">{{ $local->foto ? 'Foto atual' : '' }}</p>
                                <button type="button" class="btn btn-sm btn-light rounded-pill border mt-2" id="removeFile">Remover / Alterar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 align-items-center mt-5">
                    <a href="{{ route('reservas-locais.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .file-drop-area {
        transition: all 0.2s ease;
        border-color: #dee2e6;
    }
    .file-drop-area:hover, .file-drop-area.dragover {
        border-color: #0d6efd;
        background-color: #f1f8ff !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropArea = document.getElementById('dropArea');
    const fileInput = document.getElementById('foto');
    const dropContent = document.getElementById('dropContent');
    const previewArea = document.getElementById('previewArea');
    const imgPreview = document.getElementById('imgPreview');
    const fileName = document.getElementById('fileName');
    const removeFileBtn = document.getElementById('removeFile');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropArea.classList.add('dragover');
    }

    function unhighlight(e) {
        dropArea.classList.remove('dragover');
    }

    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imgPreview.src = e.target.result;
                    fileName.textContent = file.name;
                    dropContent.classList.add('d-none');
                    previewArea.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
                
                if (fileInput.files !== files) {
                     const dataTransfer = new DataTransfer();
                     dataTransfer.items.add(file);
                     fileInput.files = dataTransfer.files;
                }
            }
        }
    }

    removeFileBtn.addEventListener('click', function(e) {
        e.preventDefault();
        fileInput.value = '';
        dropContent.classList.remove('d-none');
        previewArea.classList.add('d-none');
        imgPreview.src = '#';
        fileName.textContent = '';
    });
    
     removeFileBtn.addEventListener('click', function(e) {
         e.stopPropagation();
    });
});
</script>
@endsection
