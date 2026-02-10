@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Editar Item de Estoque</h2>
            <p class="text-muted small mb-0">Atualize os dados do item.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('estoque.index') }}" class="text-decoration-none">Estoque</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            
            <form action="{{ route('estoque.update', $item->s_id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="description" class="form-label fw-bold small text-muted">Descrição do Item <span class="text-danger fw-bold small">*</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="description" name="description" value="{{ $item->description }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label fw-bold small text-muted">Tipo/Unidade <span class="text-danger fw-bold small">*</span></label>
                        <input type="text" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="type" name="type" value="{{ $item->type }}" required>
                    </div>
                    <div class="col-md-3">
                        <label for="qntd_destributed" class="form-label fw-bold small text-muted">Quantidade <span class="text-danger fw-bold small">*</span></label>
                        <input type="number" class="form-control rounded-pill bg-light border-0 px-4 py-2" id="qntd_destributed" name="qntd_destributed" value="{{ $item->qntd_destributed }}" required min="0">
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <label for="category" class="form-label fw-bold small text-muted">Categoria <span class="text-danger fw-bold small">*</span></label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="category" name="category" required>
                            <option value="">Selecione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ $item->category == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="ent_id" class="form-label fw-bold small text-muted">Comunidade (Opcional)</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="ent_id" name="ent_id">
                            <option value="">Selecione...</option>
                            @foreach($entidades as $ent)
                                <option value="{{ $ent->ent_id }}" {{ $item->ent_id == $ent->ent_id ? 'selected' : '' }}>{{ $ent->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="sala_id" class="form-label fw-bold small text-muted">Local/Sala (Opcional)</label>
                        <select class="form-select rounded-pill bg-light border-0 px-4 py-2" id="sala_id" name="sala_id">
                            <option value="">Selecione...</option>
                            @foreach($locais as $local)
                                <option value="{{ $local->id }}" {{ $item->sala_id == $local->id ? 'selected' : '' }}>{{ $local->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <div class="col-12">
                        <label class="form-label fw-bold small text-muted">Imagens do Item</label>
                        
                        <!-- Imagens Existentes -->
                        @if($item->images->count() > 0)
                            <div class="d-flex gap-3 mb-3 flex-wrap">
                                @foreach($item->images as $img)
                                    <div class="position-relative">
                                        <img src="{{ asset('storage/uploads/estoque/' . $img->filename) }}" class="rounded shadow-sm border" style="width: 100px; height: 100px; object-fit: cover;">
                                        <button type="button" class="btn btn-danger btn-sm rounded-circle position-absolute top-0 end-0 m-1 p-0 d-flex align-items-center justify-content-center" style="width: 20px; height: 20px;" onclick="if(confirm('Excluir imagem?')) document.getElementById('delete-img-{{ $img->id }}').submit();">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-5 text-center position-relative" style="min-height: 250px;">
                            <input type="file" name="images[]" id="images" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" multiple accept="image/*" style="z-index: 10;">
                            <div class="d-flex flex-column align-items-center justify-content-center h-100" id="uploadPlaceholder">
                                <i class="bi bi-cloud-arrow-up display-4 text-primary mb-3"></i>
                                <h6 class="fw-bold text-dark">Adicionar novas imagens</h6>
                                <p class="text-muted small mb-0">Arraste e solte ou clique para selecionar (Max 2MB Total)</p>
                            </div>
                            <div id="previewContainer" class="d-none row g-3 justify-content-center align-items-center pt-3 position-relative" style="z-index: 1;">
                                <!-- Previews injetados via JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center border-top pt-4">
                    <button type="button" class="btn btn-outline-danger rounded-pill px-4" onclick="if(confirm('Tem certeza que deseja excluir este item?')) document.getElementById('deleteForm').submit();">
                        <i class="bi bi-trash me-2"></i> Excluir Item
                    </button>
                    <div class="d-flex gap-2">
                        <a href="{{ route('estoque.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">Salvar Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form de Exclusão -->
<form id="deleteForm" action="{{ route('estoque.destroy', $item->s_id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Forms para Excluir Imagens -->
@foreach($item->images as $img)
    <form id="delete-img-{{ $img->id }}" action="{{ route('estoque.image.delete', $img->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<style>
    .file-drop-area {
        transition: all 0.2s ease;
        border-color: #dee2e6;
    }
    .file-drop-area:hover {
        border-color: #0d6efd;
        background-color: #f1f8ff !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('images');
        const previewContainer = document.getElementById('previewContainer');
        const uploadPlaceholder = document.getElementById('uploadPlaceholder');
        const maxTotalSize = 2 * 1024 * 1024; // 2MB

        fileInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            let totalSize = 0;
            let valid = true;

            // Reset UI
            previewContainer.innerHTML = '';
            
            if (files.length === 0) {
                uploadPlaceholder.classList.remove('d-none');
                previewContainer.classList.add('d-none');
                return;
            }

            // Validar
            files.forEach(file => {
                if (!file.type.startsWith('image/')) {
                    alert(`O arquivo "${file.name}" não é uma imagem válida.`);
                    valid = false;
                }
                totalSize += file.size;
            });

            if (!valid) {
                this.value = ''; // Limpa tudo
                return;
            }

            if (totalSize > maxTotalSize) {
                alert(`O tamanho total das imagens (${(totalSize / 1024 / 1024).toFixed(2)}MB) excede o limite de 2MB. Por favor, escolha menos imagens ou arquivos menores.`);
                this.value = ''; // Limpa tudo
                return;
            }

            // Exibir Previews
            uploadPlaceholder.classList.add('d-none');
            previewContainer.classList.remove('d-none');

            files.forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-auto';
                    col.innerHTML = `
                        <div class="card shadow-sm border-0">
                            <img src="${e.target.result}" class="card-img-top object-fit-cover rounded-top" style="height: 100px; width: 100px;" alt="${file.name}">
                            <div class="card-body p-1 text-center bg-white rounded-bottom">
                                <small class="d-block text-truncate text-muted" style="max-width: 90px; font-size: 10px;">${file.name}</small>
                                <small class="fw-bold text-primary" style="font-size: 10px;">${(file.size/1024).toFixed(0)}KB</small>
                            </div>
                        </div>
                    `;
                    previewContainer.appendChild(col);
                }
                reader.readAsDataURL(file);
            });
        });
    });
</script>
@endsection
