@extends('layouts.app')

@push('styles')
<style>
    .pascom-video-thumb {
        position: relative;
        width: 100%;
        height: 200px;
        background: #111827;
        border-radius: 1rem;
        overflow: hidden;
        cursor: pointer;
    }
    .pascom-video-thumb .pascom-video-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease-in-out;
        background: rgba(0, 0, 0, 0.35);
    }
    .pascom-video-thumb:hover .pascom-video-overlay {
        opacity: 1;
    }
    .pascom-video-thumb .pascom-video-label {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: 14px;
        color: rgba(255,255,255,0.9);
        font-size: 0.85rem;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .pascom-media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 16px;
    }
    .pascom-media-tile {
        position: relative;
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 1rem;
        overflow: hidden;
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
    }
    .pascom-media-tile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: filter 0.15s ease, transform 0.15s ease;
    }
    .pascom-media-tile-hover {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: rgba(0,0,0,0.4);
        opacity: 0;
        transition: opacity 0.2s ease;
        z-index: 10;
    }
    .pascom-media-tile:hover .pascom-media-tile-hover {
        opacity: 1;
    }
    .pascom-media-tile:hover img {
        filter: blur(3px);
        transform: scale(1.05);
    }
    .pascom-media-tile-actions {
        position: absolute;
        top: 8px;
        right: 8px;
        display: flex;
        gap: 6px;
        z-index: 20;
    }
    .pascom-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: rgba(255,255,255,0.95);
        color: #374151;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: none;
        transition: all 0.15s ease;
    }
    .pascom-action-btn:hover {
        background: #fff;
        color: #0d6efd;
        transform: scale(1.05);
    }
    .pascom-action-btn.text-danger:hover {
        color: #dc3545;
    }
    .pascom-expand-btn {
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 50px;
        padding: 6px 16px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #111827;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.15s ease, background 0.15s ease;
    }
    .pascom-expand-btn:hover {
        background: #fff;
        transform: scale(1.05);
    }
    .pascom-delete-disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Editar Postagem</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('pascom.postagens.index') }}" class="text-decoration-none">Postagens Pascom</a></li>
                <li class="breadcrumb-item active" aria-current="page">Editar</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 p-lg-5">
            <form action="{{ route('pascom.postagens.update', $postagem->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <label for="data" class="form-label fw-bold text-muted small">Data <span class="text-danger">*</span></label>
                        <input type="date" name="data" id="data" class="form-control rounded-pill px-4 @error('data') is-invalid @enderror" value="{{ old('data', $postagem->data?->format('Y-m-d')) }}" required>
                        @error('data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-3">
                        <label for="horario" class="form-label fw-bold text-muted small">Horário <span class="text-danger">*</span></label>
                        <input type="time" name="horario" id="horario" class="form-control rounded-pill px-4 @error('horario') is-invalid @enderror" value="{{ old('horario', \Carbon\Carbon::parse($postagem->horario)->format('H:i')) }}" required>
                        @error('horario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="celebrante" class="form-label fw-bold text-muted small">Celebrante <span class="text-danger">*</span></label>
                        <input type="text" name="celebrante" id="celebrante" class="form-control rounded-pill px-4 @error('celebrante') is-invalid @enderror" value="{{ old('celebrante', $postagem->celebrante) }}" required>
                        @error('celebrante')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label for="comunidade_id" class="form-label fw-bold text-muted small">Comunidade <span class="text-danger">*</span></label>
                        <select name="comunidade_id" id="comunidade_id" class="form-select rounded-pill px-4 @error('comunidade_id') is-invalid @enderror" required>
                            <option value="">Selecione uma comunidade</option>
                            @foreach($comunidades as $comunidade)
                                <option value="{{ $comunidade->ent_id }}" {{ (string)old('comunidade_id', $postagem->comunidade_id) === (string)$comunidade->ent_id ? 'selected' : '' }}>
                                    {{ $comunidade->ent_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('comunidade_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-12">
                        <label for="descricao" class="form-label fw-bold text-muted small">Descrição <span class="text-muted fw-normal">(Opcional)</span></label>
                        <textarea name="descricao" id="descricao" rows="4" class="form-control rounded-4 p-3 @error('descricao') is-invalid @enderror">{{ old('descricao', $postagem->descricao) }}</textarea>
                        @error('descricao')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="border-top pt-4">
                    <h6 class="fw-bold text-primary mb-3">Arquivos</h6>
                    
                    @php
                        $images = $postagem->arquivos->where('type', 'image')->values();
                        $videos = $postagem->arquivos->where('type', 'video')->values();
                    @endphp

                    @if($postagem->arquivos->count() === 0)
                        <div class="text-muted small">Nenhum arquivo anexado.</div>
                    @else
                        @if($images->count() > 0)
                            <div class="mb-4">
                                <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-image me-2"></i>Imagens</h6>
                                <div class="pascom-media-grid">
                                    @foreach($images as $img)
                                        <div class="pascom-media-tile" id="arquivo-{{ $img->id }}">
                                            <img src="{{ asset('storage/uploads/pascom/' . $img->filename) }}" alt="{{ $img->original_name }}">
                                            <div class="pascom-media-tile-hover">
                                                <button type="button"
                                                        class="pascom-expand-btn js-open-image"
                                                        data-image-url="{{ asset('storage/uploads/pascom/' . $img->filename) }}"
                                                        data-image-title="{{ $img->original_name }}"
                                                        data-post-id="{{ $postagem->id }}"
                                                        data-arquivo-id="{{ $img->id }}"
                                                        aria-label="Expandir">
                                                    <i class="bi bi-arrows-fullscreen"></i> Expandir
                                                </button>
                                            </div>
                                            <div class="pascom-media-tile-actions">
                                                <button type="button" class="pascom-action-btn text-danger" onclick="removerArquivo('{{ $img->id }}')" title="Remover">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($videos->count() > 0)
                            <div>
                                <h6 class="fw-bold text-muted small text-uppercase mb-3"><i class="bi bi-camera-reels me-2"></i>Vídeos</h6>
                                <div class="row g-3">
                                    @foreach($videos as $vid)
                                        <div class="col-md-6 col-lg-4" id="arquivo-{{ $vid->id }}">
                                            <div class="pascom-video-thumb js-open-video" data-video-url="{{ asset('storage/uploads/pascom/' . $vid->filename) }}" data-video-title="{{ $vid->original_name }}">
                                                <div class="pascom-video-overlay">
                                                    <i class="bi bi-play-circle-fill text-white" style="font-size: 3rem;"></i>
                                                </div>
                                                <div class="pascom-video-label">{{ $vid->original_name }}</div>
                                            </div>
                                            <div class="d-flex justify-content-end mt-2 gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="removerArquivo('{{ $vid->id }}')">
                                                    <i class="bi bi-trash me-1"></i> Remover
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <hr class="my-5">

                <div class="d-flex justify-content-end gap-3">
                    <a href="{{ route('pascom.postagens.index') }}" class="btn btn-light border rounded-pill px-4 py-2 fw-medium">Cancelar</a>
                    <button type="submit" id="submitBtn" class="btn btn-primary rounded-pill px-5 py-2 fw-bold d-flex align-items-center">
                        <i class="bi bi-check-lg me-2"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Visualizador de Imagem -->
<div class="modal fade" id="imageViewerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom-0 px-4 py-3">
                <h5 class="modal-title fw-bold mb-0" id="imageViewerTitle">Imagem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted small" id="imageViewerMeta"></div>
                    <div class="d-flex align-items-center gap-2">
                        <a id="imageViewerDownload" class="btn btn-sm btn-outline-primary rounded-pill" href="#" download>
                            <i class="bi bi-download me-1"></i> Baixar
                        </a>
                        <button type="button" id="imageViewerRemove" class="btn btn-sm btn-outline-danger rounded-pill">
                            <i class="bi bi-trash me-1"></i> Remover
                        </button>
                    </div>
                </div>
                <div class="rounded-4 overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="min-height: 60vh;">
                    <img id="imageViewerImg" src="" alt="" style="max-height: 75vh; width: 100%; object-fit: contain;">
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 px-4 py-3">
                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Player de Vídeo -->
<div class="modal fade" id="videoPlayerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-white border-bottom-0 px-4 py-3">
                <h5 class="modal-title fw-bold mb-0" id="videoPlayerTitle">Vídeo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="stopVideoPlayer()"></button>
            </div>
            <div class="modal-body p-4">
                <video id="videoPlayer" class="w-100 rounded-4" style="max-height: 70vh;" controls playsinline></video>
            </div>
            <div class="modal-footer bg-white border-top-0 px-4 py-3">
                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal" onclick="stopVideoPlayer()">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-triangle-fill display-4"></i>
                </div>
                <h5 class="fw-bold mb-3">Excluir Mídia?</h5>
                <p class="text-muted small mb-4">Esta ação não pode ser desfeita. A mídia será removida permanentemente desta postagem.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger rounded-pill px-4">
                        <i class="bi bi-trash me-1"></i> Excluir
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="minMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="text-warning mb-3">
                    <i class="bi bi-info-circle-fill display-4"></i>
                </div>
                <h5 class="fw-bold mb-3">Atenção</h5>
                <p class="text-muted small mb-0" id="minMediaMessage">A postagem deve ter no mínimo 1 mídia. Não é possível remover todas.</p>
            </div>
            <div class="modal-footer bg-white border-top-0 px-4 py-3 justify-content-center">
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-dismiss="modal">Entendi</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Configurar botão de Salvar com Loading
    document.querySelector('form').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Salvando...';
    });

    let arquivoIdParaExcluir = null;
    let deleteConfirmModal = null;
    let minMediaModal = null;

    document.addEventListener('DOMContentLoaded', function() {
        deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        minMediaModal = new bootstrap.Modal(document.getElementById('minMediaModal'));
        
        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (arquivoIdParaExcluir) {
                executarRemocao(arquivoIdParaExcluir);
            }
        });

        updateRemoveButtonsState();
    });

    function remainingMediaCount() {
        return document.querySelectorAll('[id^="arquivo-"]').length;
    }

    function showMinMediaModal(message) {
        const el = document.getElementById('minMediaMessage');
        if (el) {
            el.textContent = message || 'A postagem deve ter no mínimo 1 mídia. Não é possível remover todas.';
        }
        if (minMediaModal) {
            minMediaModal.show();
        }
    }

    function updateRemoveButtonsState() {
        const shouldBlock = remainingMediaCount() <= 1;
        document.querySelectorAll('[onclick^="removerArquivo("]').forEach(btn => {
            if (shouldBlock) {
                btn.classList.add('pascom-delete-disabled');
                btn.setAttribute('data-disabled', '1');
            } else {
                btn.classList.remove('pascom-delete-disabled');
                btn.removeAttribute('data-disabled');
            }
        });

        const viewerRemove = document.getElementById('imageViewerRemove');
        if (viewerRemove) {
            if (shouldBlock) {
                viewerRemove.classList.add('pascom-delete-disabled');
                viewerRemove.setAttribute('data-disabled', '1');
            } else {
                viewerRemove.classList.remove('pascom-delete-disabled');
                viewerRemove.removeAttribute('data-disabled');
            }
        }
    }

    // Função para abrir o modal de exclusão
    function removerArquivo(id) {
        if (remainingMediaCount() <= 1) {
            showMinMediaModal('A postagem deve ter no mínimo 1 mídia. Não é possível remover todas.');
            return;
        }
        arquivoIdParaExcluir = id;
        deleteConfirmModal.show();
    }

    // Executa a requisição DELETE
    function executarRemocao(id) {
        const btn = document.getElementById('confirmDeleteBtn');
        const btnOriginalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Excluindo...';

        const postagemId = '{{ $postagem->id }}';
        const url = '{{ url("/pascom/postagens") }}/' + postagemId + '/arquivos/' + id;

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            let json = null;
            try {
                json = await response.json();
            } catch (e) {
                json = null;
            }
            return { ok: response.ok, status: response.status, json };
        })
        .then(({ ok, status, json }) => {
            btn.disabled = false;
            btn.innerHTML = btnOriginalText;
            
            if(ok && json && json.success) {
                deleteConfirmModal.hide();
                
                // Remover o elemento do DOM
                const element = document.getElementById('arquivo-' + id);
                if (element) element.remove();
                
                // Fechar o modal de imagem se estiver aberto
                const imageModalEl = document.getElementById('imageViewerModal');
                if (imageModalEl && imageModalEl.classList.contains('show')) {
                    const bsModal = bootstrap.Modal.getInstance(imageModalEl);
                    if(bsModal) bsModal.hide();
                }
                updateRemoveButtonsState();
            } else {
                deleteConfirmModal.hide();
                if (status === 422 && json && json.error) {
                    showMinMediaModal(json.error);
                    updateRemoveButtonsState();
                    return;
                }
                alert((json && (json.error || json.message)) || 'Erro ao remover arquivo.');
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = btnOriginalText;
            deleteConfirmModal.hide();
            alert('Erro na requisição ao remover arquivo.');
        });
    }

    // Modal de Imagem
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-open-image');
        if (btn) {
            const url = btn.getAttribute('data-image-url');
            const title = btn.getAttribute('data-image-title');
            const id = btn.getAttribute('data-arquivo-id');

            document.getElementById('imageViewerImg').src = url;
            document.getElementById('imageViewerTitle').innerText = title;
            document.getElementById('imageViewerDownload').href = url;
            
            const removeBtn = document.getElementById('imageViewerRemove');
            if (removeBtn) {
                removeBtn.onclick = function() {
                    removerArquivo(id);
                };
            }

            const myModal = new bootstrap.Modal(document.getElementById('imageViewerModal'));
            myModal.show();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const imageModalEl = document.getElementById('imageViewerModal');
        if (imageModalEl) {
            imageModalEl.addEventListener('shown.bs.modal', function() {
                updateRemoveButtonsState();
            });
        }
    });

    // Modal de Vídeo
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-open-video');
        if (btn) {
            const url = btn.getAttribute('data-video-url');
            const title = btn.getAttribute('data-video-title');

            const videoEl = document.getElementById('videoPlayer');
            videoEl.src = url;
            document.getElementById('videoPlayerTitle').innerText = title;

            const myModal = new bootstrap.Modal(document.getElementById('videoPlayerModal'));
            myModal.show();
            videoEl.play();
        }
    });

    function stopVideoPlayer() {
        const videoEl = document.getElementById('videoPlayer');
        if (videoEl) {
            videoEl.pause();
            videoEl.src = '';
        }
    }
</script>
@endpush
