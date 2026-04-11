@extends('layouts.app')

@push('styles')
<!-- Dropzone CSS -->
<link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
<style>
    .dropzone {
        border: 2px dashed #0d6efd;
        border-radius: 1rem;
        background: #f8f9fa;
        padding: 40px;
        text-align: center;
        min-height: 250px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }
    .dropzone .dz-message {
        margin: 2em 0;
        width: 100%;
    }
    .dropzone .dz-preview.dz-image-preview {
        background: transparent;
    }
    /* Ajustando o layout de cada preview para centralizar elementos */
    .dropzone .dz-preview {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        margin: 16px;
        min-height: 200px;
    }
    
    .dropzone .dz-preview .dz-image {
        border-radius: 0.5rem;
        margin-bottom: 10px; /* Espaço entre imagem e mensagem de erro */
    }
    
    /* Customizando o botão de remover e a mensagem de erro */
    .dropzone .dz-preview .dz-remove {
        color: #dc3545;
        text-decoration: none;
        font-weight: bold;
        font-size: 0.85rem;
        margin-top: 10px;
        display: inline-block;
        padding: 4px 16px;
        border: 1px solid #dc3545;
        border-radius: 50rem;
        transition: all 0.2s;
        background: white;
        width: fit-content;
    }
    .dropzone .dz-preview .dz-remove:hover {
        background-color: #dc3545;
        color: white;
    }
    .dropzone .dz-preview .dz-remove.dz-remove-disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }

    /* Transformando a mensagem de erro (que era um tooltip hover) num bloco fixo abaixo da imagem */
    .dropzone .dz-preview .dz-error-message {
        display: none;
        position: relative;
        top: 0;
        left: 0;
        width: auto; /* Ajuste para não forçar 100% que quebrava o layout flex */
        max-width: 100%; /* Mas limita ao tamanho do contêiner */
        background: #dc3545;
        color: white;
        border-radius: 0.5rem;
        padding: 6px 10px;
        font-size: 0.75rem;
        opacity: 1;
        pointer-events: auto;
        text-align: center;
        word-wrap: break-word; /* Quebra palavras longas */
    }
    .dropzone .dz-preview.dz-error .dz-error-message {
        display: block;
    }
    .dropzone .dz-preview .dz-error-message:after {
        display: none; /* Remove a setinha do tooltip original */
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Nova Postagem</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pascom.postagens.index') }}" class="text-decoration-none">Postagens Pascom</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova Postagem</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 p-lg-5">
            <form id="postagemForm" action="{{ route('pascom.postagens.store') }}" method="POST">
                @csrf
                <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-info-circle me-2"></i> Informações da Postagem</h5>
                
                <div class="row g-4 mb-5">
                    <div class="col-md-3">
                        <label for="data" class="form-label fw-bold text-muted small">Data <span class="text-danger">*</span></label>
                        <input type="date" name="data" id="data" class="form-control rounded-pill px-4 @error('data') is-invalid @enderror" value="{{ old('data', date('Y-m-d')) }}" required>
                        @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-3">
                        <label for="horario" class="form-label fw-bold text-muted small">Horário <span class="text-danger">*</span></label>
                        <input type="time" name="horario" id="horario" class="form-control rounded-pill px-4 @error('horario') is-invalid @enderror" value="{{ old('horario', date('H:i')) }}" required>
                        @error('horario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="celebrante" class="form-label fw-bold text-muted small">Celebrante <span class="text-danger">*</span></label>
                        <input type="text" name="celebrante" id="celebrante" class="form-control rounded-pill px-4 @error('celebrante') is-invalid @enderror" value="{{ old('celebrante') }}" placeholder="Nome do celebrante" required>
                        @error('celebrante')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label for="comunidade_id" class="form-label fw-bold text-muted small">Comunidade <span class="text-danger">*</span></label>
                        <select name="comunidade_id" id="comunidade_id" class="form-select rounded-pill px-4 @error('comunidade_id') is-invalid @enderror" required>
                            <option value="">Selecione uma comunidade</option>
                            @foreach($comunidades as $comunidade)
                                <option value="{{ $comunidade->ent_id }}" {{ old('comunidade_id') == $comunidade->ent_id ? 'selected' : '' }}>
                                    {{ $comunidade->ent_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('comunidade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label for="descricao" class="form-label fw-bold text-muted small">Descrição <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea name="descricao" id="descricao" rows="4" class="form-control rounded-4 p-3 @error('descricao') is-invalid @enderror" placeholder="Detalhes sobre o evento, informações adicionais...">{{ old('descricao') }}</textarea>
                        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h5 class="fw-bold mb-4 text-primary"><i class="bi bi-images me-2"></i> Fotos e Vídeos (Até 120 arquivos)</h5>
                
                <div class="mb-4">
                    <div id="mediaDropzone" class="dropzone">
                        <div class="dz-message" data-dz-message>
                            <i class="bi bi-cloud-arrow-up display-1 text-primary mb-3"></i>
                            <h4 class="fw-bold">Arraste e solte arquivos aqui</h4>
                            <p class="text-muted">ou clique para selecionar do seu dispositivo</p>
                            <p class="small text-muted mt-2 mb-0">Suporta JPG, PNG, HEIC e vídeos (MP4, MOV). Até 120 arquivos.</p>
                        </div>
                    </div>
                    <div class="mt-2 text-danger small d-none" id="dropzoneError">Por favor, adicione pelo menos um arquivo.</div>
                </div>

                <!-- Inputs ocultos gerados pelo Dropzone serão anexados ao form -->
                <div id="hiddenInputsContainer"></div>

                <hr class="my-5">
                
                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('pascom.postagens.index') }}" class="btn btn-light border rounded-pill px-4 py-2 fw-medium">Cancelar</a>
                    <button type="submit" id="submitBtn" class="btn btn-primary rounded-pill px-5 py-2 fw-bold d-flex align-items-center">
                        <i class="bi bi-check-lg me-2"></i> Salvar Postagem
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script>
    Dropzone.autoDiscover = false;

    document.addEventListener("DOMContentLoaded", function() {
        let uploadedFiles = [];
        const form = document.getElementById('postagemForm');
        const submitBtn = document.getElementById('submitBtn');
        const hiddenInputsContainer = document.getElementById('hiddenInputsContainer');
        const dropzoneError = document.getElementById('dropzoneError');

        const showMinMediaMessage = () => {
            dropzoneError.textContent = 'A postagem deve ter no mínimo 1 mídia. Adicione outra para remover esta.';
            dropzoneError.classList.remove('d-none');
            setTimeout(() => {
                dropzoneError.classList.add('d-none');
                dropzoneError.textContent = 'Por favor, adicione pelo menos um arquivo.';
            }, 4000);
        };

        const myDropzone = new Dropzone("#mediaDropzone", {
            url: "{{ route('pascom.postagens.upload') }}",
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            paramName: "file",
            maxFilesize: 500, // MB
            maxFiles: 120,
            acceptedFiles: "image/*,video/mp4,video/quicktime,video/x-msvideo,.heic,.heif",
            addRemoveLinks: true,
            dictRemoveFile: "Remover",
            dictCancelUpload: "Cancelar",
            dictFileTooBig: "Arquivo muito grande (@{{filesize}}MB). Máx: @{{maxFilesize}}MB.",
            dictMaxFilesExceeded: "Você não pode enviar mais de 120 arquivos.",
            timeout: 600000,
            accept: function(file, done) {
                if (file && file.type && file.type.startsWith('video/')) {
                    const url = URL.createObjectURL(file);
                    const video = document.createElement('video');
                    video.preload = 'metadata';
                    video.onloadedmetadata = function() {
                        URL.revokeObjectURL(url);
                        if (video.duration > 300) {
                            done('Vídeo com mais de 5 minutos. Selecione um vídeo de até 5 minutos.');
                        } else {
                            done();
                        }
                    };
                    video.onerror = function() {
                        URL.revokeObjectURL(url);
                        // Se não for possível ler a duração, vamos aceitar e deixar o backend validar/tentar, 
                        // ou rejeitar com done('Não foi possível ler o arquivo de vídeo.')
                        done();
                    };
                    video.src = url;
                    return;
                }
                done();
            },
            
            init: function() {
                const refreshRemoveState = () => {
                    const successfulFiles = this.files.filter(f => f.serverData);
                    this.files.forEach(f => {
                        const removeLink = f.previewElement ? f.previewElement.querySelector('.dz-remove') : null;
                        if (!removeLink) return;
                        if (f.serverData && successfulFiles.length <= 1) {
                            removeLink.classList.add('dz-remove-disabled');
                            removeLink.setAttribute('aria-disabled', 'true');
                        } else {
                            removeLink.classList.remove('dz-remove-disabled');
                            removeLink.removeAttribute('aria-disabled');
                        }
                    });
                };

                this.on('addedfile', function(file) {
                    const removeLink = file.previewElement ? file.previewElement.querySelector('.dz-remove') : null;
                    if (removeLink) {
                        removeLink.addEventListener('click', function(ev) {
                            const successfulFiles = myDropzone.files.filter(f => f.serverData);
                            if (file.serverData && successfulFiles.length <= 1) {
                                ev.preventDefault();
                                ev.stopPropagation();
                                showMinMediaMessage();
                            }
                        });
                    }
                });

                this.on("sending", function(file, xhr, formData) {
                    // Desabilitar submit durante o envio
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processando...';
                });

                this.on("success", function(file, response) {
                    // Armazena a resposta do servidor no objeto file
                    file.serverData = response;
                    uploadedFiles.push(response);
                    
                    // Adiciona um input hidden para este arquivo
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'arquivos[]';
                    input.value = JSON.stringify(response);
                    input.id = 'file_' + file.upload.uuid;
                    hiddenInputsContainer.appendChild(input);
                    refreshRemoveState();
                });

                this.on("error", function(file, errorMessage, xhr) {
                    let message = 'Não foi possível enviar este arquivo.';
                    if (typeof errorMessage === 'string' && errorMessage.trim() !== '') {
                        message = errorMessage;
                    } else if (errorMessage && typeof errorMessage === 'object') {
                        message = errorMessage.error || errorMessage.message || message;
                    }

                    if (xhr && xhr.status) {
                        if (xhr.status === 413) {
                            message = 'Arquivo muito grande para o limite atual do servidor.';
                        } else if (xhr.status >= 500) {
                            message = 'Erro interno ao processar o arquivo. Tente novamente.';
                        }

                        const responseText = xhr.responseText;
                        if (responseText) {
                            try {
                                const json = JSON.parse(responseText);
                                if (json && (json.error || json.message)) {
                                    message = json.error || json.message;
                                }
                            } catch (e) {
                            }
                        }
                    }

                    if (file && file.previewElement) {
                        file.previewElement.querySelectorAll('[data-dz-errormessage]').forEach(el => {
                            el.textContent = message;
                        });
                    }
                });

                this.on("removedfile", function(file) {
                    if (file.serverData) {
                        // Remover do array
                        uploadedFiles = uploadedFiles.filter(item => item.path !== file.serverData.path);
                        // Remover o input hidden
                        const input = document.getElementById('file_' + file.upload.uuid);
                        if (input) input.remove();
                    }
                    refreshRemoveState();
                });

                this.on("queuecomplete", function() {
                    // Reabilitar botão quando a fila acabar
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i> Salvar Postagem';
                    refreshRemoveState();
                });
            }
        });

        form.addEventListener('submit', function(e) {
            if (uploadedFiles.length === 0) {
                e.preventDefault();
                dropzoneError.classList.remove('d-none');
                window.scrollTo({ top: document.getElementById('mediaDropzone').offsetTop - 100, behavior: 'smooth' });
            } else {
                dropzoneError.classList.add('d-none');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enviando conteúdo...';
            }
        });
    });
</script>
@endpush
