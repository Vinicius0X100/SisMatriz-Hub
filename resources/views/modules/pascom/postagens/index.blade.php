@extends('layouts.app')

@php
    $canManage = Auth::user()->hasAnyRole(['1', '111', '9']);
@endphp

@push('styles')
<style>
    .pascom-video-thumb {
        position: relative;
        width: 100%;
        height: 320px;
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
    .pascom-media-strip-wrap {
        position: relative;
    }
    .pascom-media-strip {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        padding: 6px 44px;
        -webkit-overflow-scrolling: touch;
    }
    .pascom-media-strip::-webkit-scrollbar {
        height: 8px;
    }
    .pascom-media-strip::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.15);
        border-radius: 999px;
    }
    .pascom-media-tile {
        flex: 0 0 auto;
        width: 140px;
        height: 140px;
        border-radius: 18px;
        overflow: hidden;
        background: #f8f9fa;
        position: relative;
        scroll-snap-align: start;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.06);
    }
    .pascom-media-tile img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: filter 0.15s ease, transform 0.15s ease;
    }
    .pascom-media-tile:hover img {
        filter: blur(3px);
        transform: scale(1.03);
    }
    .pascom-media-tile-hover {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.15s ease;
        background: rgba(0,0,0,0.25);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        pointer-events: none;
    }
    .pascom-media-tile:hover .pascom-media-tile-hover {
        opacity: 1;
        pointer-events: auto;
    }
    .pascom-expand-btn {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.95);
        border: 1px solid rgba(0,0,0,0.15);
        color: #111827;
    }
    .pascom-media-tile-actions {
        position: absolute;
        top: 8px;
        left: 8px;
        right: 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        pointer-events: none;
    }
    .pascom-media-tile-actions .form-check-input {
        pointer-events: auto;
        background-color: rgba(255,255,255,0.95);
        border-color: rgba(0,0,0,0.2);
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.08);
        accent-color: #0d6efd;
    }
    .pascom-media-tile-actions .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .pascom-media-tile-actions .pascom-download-btn {
        pointer-events: auto;
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.95);
        border: 1px solid rgba(0,0,0,0.12);
        color: #0d6efd;
        text-decoration: none;
    }
    .pascom-media-strip-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border-radius: 999px;
        background: rgba(255,255,255,0.95);
        border: 1px solid rgba(0,0,0,0.12);
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.08);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }
    .pascom-media-strip-prev {
        left: 6px;
    }
    .pascom-media-strip-next {
        right: 6px;
    }
    .pascom-pagination .pagination .page-link {
        border-radius: 999px !important;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        margin: 0 4px;
        padding: 0;
    }
    .pascom-pagination .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        color: #fff;
    }
    .pascom-pagination .pagination .page-item.disabled .page-link {
        opacity: 0.55;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Postagens Pascom</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Postagens Pascom</li>
            </ol>
        </nav>
    </div>

    <!-- Cards Quantitativos -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-images fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Postagens</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-calendar-check fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Neste Mês</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['este_mes'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-geo-alt fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Comunidades Atendidas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['comunidades'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Barra de Filtros -->
            <form id="filtersForm" method="GET" action="{{ route('pascom.postagens.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" value="{{ request('search') }}" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Celebrante, descrição..." style="height: 45px;">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="comunidadeFilter" class="form-label fw-bold text-muted small">Comunidade</label>
                    <select name="comunidade" id="comunidadeFilter" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todas</option>
                        @foreach($comunidades as $comunidade)
                            <option value="{{ $comunidade->ent_id }}" {{ request('comunidade') == $comunidade->ent_id ? 'selected' : '' }}>
                                {{ $comunidade->ent_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="dataFilter" class="form-label fw-bold text-muted small">Data</label>
                    <input type="date" name="data" value="{{ request('data') }}" id="dataFilter" class="form-control rounded-pill" style="height: 45px;">
                </div>

                <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                    <a href="{{ route('pascom.postagens.index') }}" class="btn btn-light border rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" title="Limpar Filtros">
                        <i class="bi bi-x-lg fs-5"></i>
                    </a>
                    <a href="{{ route('pascom.postagens.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;">
                        <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Nova Postagem</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Grid de Postagens -->
    <div class="row g-4">
        @forelse($postagens as $postagem)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                    <!-- Preview Image/Video -->
                    <div class="position-relative" style="height: 200px; background-color: #f8f9fa;">
                        @php
                            $firstFile = $postagem->arquivos->first();
                        @endphp
                        @if($firstFile)
                            @if($firstFile->type === 'image')
                                <img src="{{ asset('storage/uploads/pascom/' . $firstFile->filename) }}" class="w-100 h-100 object-fit-cover" alt="Preview">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-dark text-white">
                                    <i class="bi bi-play-circle fs-1"></i>
                                </div>
                            @endif
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">
                                <i class="bi bi-image fs-1 opacity-50"></i>
                            </div>
                        @endif
                        
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge bg-dark bg-opacity-75 rounded-pill px-3 py-2">
                                <i class="bi bi-collection me-1"></i> <span id="postCount{{ $postagem->id }}">{{ $postagem->arquivos->count() }}</span> itens
                            </span>
                        </div>

                        @if($canManage)
                            <div class="position-absolute top-0 start-0 m-2 d-flex gap-1">
                                <a class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" href="{{ route('pascom.postagens.edit', $postagem->id) }}" title="Editar">
                                    <i class="bi bi-pencil text-warning"></i>
                                </a>
                                <form action="{{ route('pascom.postagens.destroy', $postagem->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta postagem?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Excluir">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                    
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                <i class="bi bi-calendar3 me-1"></i> {{ $postagem->data->format('d/m/Y') }} às {{ \Carbon\Carbon::parse($postagem->horario)->format('H:i') }}
                            </span>
                        </div>
                        
                        <h5 class="fw-bold mb-1 text-truncate" title="{{ $postagem->comunidade->ent_name ?? 'Comunidade não encontrada' }}">
                            {{ $postagem->comunidade->ent_name ?? 'Comunidade não encontrada' }}
                        </h5>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-person me-1"></i> {{ $postagem->celebrante }}
                        </p>
                        
                        <p class="text-muted small mb-0 line-clamp-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $postagem->descricao ?: 'Sem descrição' }}
                        </p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 p-4 pt-0">
                        <button class="btn btn-light border rounded-pill w-100 text-primary fw-medium" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $postagem->id }}">
                            Ver Detalhes
                        </button>
                    </div>
                </div>
            </div>

            @php
                $images = $postagem->arquivos->where('type', 'image')->values();
                $videos = $postagem->arquivos->where('type', 'video')->values();
            @endphp
            <div class="modal fade" id="detailsModal{{ $postagem->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="modal-header bg-white border-bottom-0 px-4 py-3">
                            <div>
                                <h5 class="modal-title fw-bold mb-0">Detalhes da Postagem</h5>
                                <div class="text-muted small">
                                    {{ $postagem->data->format('d/m/Y') }} às {{ \Carbon\Carbon::parse($postagem->horario)->format('H:i') }} · {{ $postagem->comunidade->ent_name ?? 'Comunidade não encontrada' }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Celebrante</label>
                                    <div class="fw-medium text-dark">{{ $postagem->celebrante }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Data</label>
                                    <div class="fw-medium text-dark">{{ $postagem->data->format('d/m/Y') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Horário</label>
                                    <div class="fw-medium text-dark">{{ \Carbon\Carbon::parse($postagem->horario)->format('H:i') }}</div>
                                </div>
                                <div class="col-12">
                                    <label class="text-muted small fw-bold text-uppercase d-block mb-1">Descrição</label>
                                    <div class="text-dark">{{ $postagem->descricao ?: 'Sem descrição' }}</div>
                                </div>
                            </div>

                            @if($images->count() > 0)
                                <div class="border-top pt-4 mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-image me-2"></i>Imagens</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="selectAllImages{{ $postagem->id }}" onclick="toggleSelectAll('{{ $postagem->id }}','image', this.checked)">
                                                <label class="form-check-label small text-muted" for="selectAllImages{{ $postagem->id }}">Selecionar todos</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="downloadSelected('{{ $postagem->id }}','image')">
                                                <i class="bi bi-download me-1"></i> Baixar selecionados
                                            </button>
                                        </div>
                                    </div>

                                    <div class="pascom-media-strip-wrap">
                                        <button type="button" class="pascom-media-strip-nav pascom-media-strip-prev" onclick="scrollMediaStrip('{{ $postagem->id }}', 'image', -1)" aria-label="Anterior">
                                            <i class="bi bi-chevron-left"></i>
                                        </button>
                                        <div id="imagesStrip{{ $postagem->id }}" class="pascom-media-strip">
                                            @foreach($images as $img)
                                                <div class="pascom-media-tile" data-post="{{ $postagem->id }}" data-arquivo="{{ $img->id }}">
                                                    <img src="{{ asset('storage/uploads/pascom/' . $img->filename) }}" alt="{{ $img->original_name }}">
                                                    <div class="pascom-media-tile-hover">
                                                        <button type="button"
                                                                class="pascom-expand-btn js-open-image"
                                                                data-image-url="{{ asset('storage/uploads/pascom/' . $img->filename) }}"
                                                                data-image-title="{{ $img->original_name }}"
                                                                data-post-id="{{ $postagem->id }}"
                                                                data-arquivo-id="{{ $img->id }}"
                                                                aria-label="Expandir">
                                                            <i class="bi bi-arrows-fullscreen"></i>
                                                        </button>
                                                    </div>
                                                    <div class="pascom-media-tile-actions">
                                                        <input class="form-check-input media-checkbox" type="checkbox" data-post="{{ $postagem->id }}" data-type="image" value="{{ asset('storage/uploads/pascom/' . $img->filename) }}">
                                                        <a class="pascom-download-btn" href="{{ asset('storage/uploads/pascom/' . $img->filename) }}" download aria-label="Baixar">
                                                            <i class="bi bi-download"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="pascom-media-strip-nav pascom-media-strip-next" onclick="scrollMediaStrip('{{ $postagem->id }}', 'image', 1)" aria-label="Próximo">
                                            <i class="bi bi-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if($videos->count() > 0)
                                <div class="border-top pt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-camera-reels me-2"></i>Vídeos</h6>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" id="selectAllVideos{{ $postagem->id }}" onclick="toggleSelectAll('{{ $postagem->id }}','video', this.checked)">
                                                <label class="form-check-label small text-muted" for="selectAllVideos{{ $postagem->id }}">Selecionar todos</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="downloadSelected('{{ $postagem->id }}','video')">
                                                <i class="bi bi-download me-1"></i> Baixar selecionados
                                            </button>
                                        </div>
                                    </div>

                                    <div id="videosCarousel{{ $postagem->id }}" class="carousel slide mb-3" data-bs-ride="carousel">
                                        <div class="carousel-inner rounded-4 overflow-hidden">
                                            @foreach($videos as $idx => $vid)
                                                <div class="carousel-item {{ $idx === 0 ? 'active' : '' }}">
                                                    <div class="pascom-video-thumb js-open-video" data-video-url="{{ asset('storage/uploads/pascom/' . $vid->filename) }}" data-video-title="{{ $vid->original_name }}">
                                                        <div class="pascom-video-overlay">
                                                            <i class="bi bi-play-circle-fill text-white" style="font-size: 4rem;"></i>
                                                        </div>
                                                        <div class="pascom-video-label">{{ $vid->original_name }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($videos->count() > 1)
                                            <button class="carousel-control-prev" type="button" data-bs-target="#videosCarousel{{ $postagem->id }}" data-bs-slide="prev">
                                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Anterior</span>
                                            </button>
                                            <button class="carousel-control-next" type="button" data-bs-target="#videosCarousel{{ $postagem->id }}" data-bs-slide="next">
                                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                <span class="visually-hidden">Próximo</span>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="list-group list-group-flush border rounded-4 overflow-hidden">
                                        @foreach($videos as $vid)
                                            <div class="list-group-item d-flex align-items-center justify-content-between">
                                                <div class="d-flex align-items-center gap-2">
                                                    <input class="form-check-input media-checkbox" type="checkbox" data-post="{{ $postagem->id }}" data-type="video" value="{{ asset('storage/uploads/pascom/' . $vid->filename) }}">
                                                    <i class="bi bi-camera-reels text-primary"></i>
                                                    <span class="small fw-semibold text-dark">{{ $vid->original_name }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill js-open-video" data-video-url="{{ asset('storage/uploads/pascom/' . $vid->filename) }}" data-video-title="{{ $vid->original_name }}">
                                                        <i class="bi bi-play-fill me-1"></i> Ver
                                                    </button>
                                                    <a class="btn btn-sm btn-outline-primary rounded-pill" href="{{ asset('storage/uploads/pascom/' . $vid->filename) }}" download>
                                                        <i class="bi bi-download me-1"></i> Baixar
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer bg-white border-top-0 px-4 py-3">
                            <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted bg-white rounded-4 shadow-sm">
                    <i class="bi bi-images fs-1 d-block mb-3 opacity-50"></i>
                    <h5>Nenhuma postagem encontrada.</h5>
                    <p class="mb-0">Tente ajustar os filtros ou crie uma nova postagem.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    <div class="pascom-pagination">
        {{ $postagens->appends(request()->query())->links('partials.pagination') }}
    </div>
</div>

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
                        @if($canManage)
                            <button type="button" id="imageViewerRemove" class="btn btn-sm btn-outline-danger rounded-pill">
                                <i class="bi bi-trash me-1"></i> Remover
                            </button>
                        @endif
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

@endsection

@push('scripts')
<script>
    (function() {
        const form = document.getElementById('filtersForm');
        const searchInput = document.getElementById('searchInput');
        const comunidadeFilter = document.getElementById('comunidadeFilter');
        const dataFilter = document.getElementById('dataFilter');

        if (!form) return;

        let searchDebounce = null;
        const submitForm = () => {
            form.submit();
        };

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(submitForm, 400);
            });
        }
        if (comunidadeFilter) {
            comunidadeFilter.addEventListener('change', submitForm);
        }
        if (dataFilter) {
            dataFilter.addEventListener('change', submitForm);
        }
    })();

    function toggleSelectAll(postId, type, checked) {
        document.querySelectorAll('.media-checkbox[data-post="' + postId + '"][data-type="' + type + '"]').forEach(cb => {
            cb.checked = checked;
        });
    }

    function downloadSelected(postId, type) {
        const urls = Array.from(document.querySelectorAll('.media-checkbox[data-post="' + postId + '"][data-type="' + type + '"]:checked'))
            .map(cb => cb.value);
        if (urls.length === 0) {
            return;
        }
        downloadFiles(urls);
    }

    function downloadFiles(urls) {
        const queue = urls.slice();
        const next = () => {
            const url = queue.shift();
            if (!url) return;
            const a = document.createElement('a');
            a.href = url;
            a.download = '';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            a.remove();
            setTimeout(next, 250);
        };
        next();
    }

    function openVideoPlayer(url, title) {
        const modalEl = document.getElementById('videoPlayerModal');
        const videoEl = document.getElementById('videoPlayer');
        const titleEl = document.getElementById('videoPlayerTitle');
        if (!modalEl || !videoEl) return;
        if (titleEl) titleEl.textContent = title || 'Vídeo';
        videoEl.src = url;
        videoEl.load();
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function scrollMediaStrip(postId, type, direction) {
        const el = document.getElementById((type === 'image' ? 'imagesStrip' : 'videosStrip') + postId);
        if (!el) return;
        const amount = Math.max(200, Math.floor(el.clientWidth * 0.8));
        el.scrollBy({ left: direction * amount, behavior: 'smooth' });
    }

    function stopVideoPlayer() {
        const videoEl = document.getElementById('videoPlayer');
        if (!videoEl) return;
        videoEl.pause();
        videoEl.removeAttribute('src');
        videoEl.load();
    }

    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.js-open-video');
        if (!trigger) return;
        const url = trigger.getAttribute('data-video-url');
        const title = trigger.getAttribute('data-video-title');
        if (!url) return;
        openVideoPlayer(url, title || 'Vídeo');
    });

    function openImageViewer(url, title, postId, arquivoId) {
        const modalEl = document.getElementById('imageViewerModal');
        const imgEl = document.getElementById('imageViewerImg');
        const titleEl = document.getElementById('imageViewerTitle');
        const metaEl = document.getElementById('imageViewerMeta');
        const downloadEl = document.getElementById('imageViewerDownload');
        const removeBtn = document.getElementById('imageViewerRemove');

        if (!modalEl || !imgEl || !downloadEl) return;
        imgEl.src = url;
        imgEl.alt = title || 'Imagem';
        if (titleEl) titleEl.textContent = title || 'Imagem';
        if (metaEl) metaEl.textContent = '';
        downloadEl.href = url;

        if (removeBtn) {
            removeBtn.setAttribute('data-post-id', postId || '');
            removeBtn.setAttribute('data-arquivo-id', arquivoId || '');
        }

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.js-open-image');
        if (!btn) return;
        const url = btn.getAttribute('data-image-url');
        const title = btn.getAttribute('data-image-title');
        const postId = btn.getAttribute('data-post-id');
        const arquivoId = btn.getAttribute('data-arquivo-id');
        if (!url) return;
        openImageViewer(url, title, postId, arquivoId);
    });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const imageRemoveBtn = document.getElementById('imageViewerRemove');
    if (imageRemoveBtn) {
        imageRemoveBtn.addEventListener('click', async function() {
            const postId = this.getAttribute('data-post-id');
            const arquivoId = this.getAttribute('data-arquivo-id');
            if (!postId || !arquivoId) return;
            if (!confirm('Remover esta imagem da postagem?')) return;

            try {
                const res = await fetch(`{{ url('pascom/postagens') }}/${postId}/arquivos/${arquivoId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });
                if (!res.ok) {
                    throw new Error('Falha ao remover a imagem.');
                }
                const tile = document.querySelector(`.pascom-media-tile[data-post="${postId}"][data-arquivo="${arquivoId}"]`);
                if (tile) tile.remove();
                const countEl = document.getElementById(`postCount${postId}`);
                if (countEl) {
                    const n = parseInt(countEl.textContent || '0', 10);
                    countEl.textContent = String(Math.max(0, n - 1));
                }
                const modalEl = document.getElementById('imageViewerModal');
                if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            } catch (err) {
                alert('Não foi possível remover a imagem. Tente novamente.');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('videoPlayerModal');
        if (!modalEl) return;
        modalEl.addEventListener('hidden.bs.modal', function() {
            stopVideoPlayer();
        });

        const imageModalEl = document.getElementById('imageViewerModal');
        if (imageModalEl) {
            imageModalEl.addEventListener('hidden.bs.modal', function() {
                const imgEl = document.getElementById('imageViewerImg');
                if (imgEl) {
                    imgEl.removeAttribute('src');
                    imgEl.alt = '';
                }
            });
        }
    });
</script>
@endpush
