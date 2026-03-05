@extends('layouts.site-tools')

@section('tool-content')
<style>
    .hover-shadow:hover {
        transform: translateY(-3px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .hover-scale:hover {
        transform: scale(1.05);
    }
    .group-hover-scale {
        transition: transform 0.5s ease;
    }
    .group:hover .group-hover-scale {
        transform: scale(1.1);
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .cursor-not-allowed {
        cursor: not-allowed !important;
    }
    .ls-1 {
        letter-spacing: 0.5px;
    }
    .shadow-sm-hover:focus {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    .shadow-inner {
        box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
    }
    .focus-ring-none:focus {
        box-shadow: none;
    }
    [x-cloak] { display: none !important; }
</style>
<div class="container-fluid px-0" style="max-width: 1400px;" x-cloak>
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <!-- Mobile Menu Toggle Button (handled in layout, but kept here for structure if needed, though layout handles it) -->
            <div>
                <h2 class="fw-bold text-dark mb-1">Galeria Paroquial</h2>
                <p class="text-muted mb-0">Gerencie as imagens exibidas no site da paróquia.</p>
            </div>
        </div>
        <span class="badge bg-primary rounded-pill px-3 py-2 shadow-sm d-none d-sm-inline-block">Módulo Ativo</span>
    </div>
    
    <div class="card border-0 shadow-sm mb-5 rounded-4 overflow-hidden" x-data="galleryComponent({ uploadUrl: '{{ route('site-tools.gallery.upload') }}' })">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
            <h5 class="mb-0 fw-bold text-secondary"><i class="bi bi-cloud-upload me-2"></i>Nova Imagem</h5>
            
            <!-- Upload Type Toggle -->
            <div class="bg-light rounded-pill p-1 d-flex shadow-inner">
                <button type="button" @click="setMode('individual')" 
                        :class="{'bg-white shadow-sm text-primary fw-bold': uploadType === 'individual', 'text-muted': uploadType !== 'individual'}" 
                        class="btn btn-sm rounded-pill px-4 transition-all border-0">
                    Individual
                </button>
                <button type="button" @click="setMode('batch')" 
                        :class="{'bg-white shadow-sm text-primary fw-bold': uploadType === 'batch', 'text-muted': uploadType !== 'batch'}" 
                        class="btn btn-sm rounded-pill px-4 transition-all border-0">
                    Em Lote
                </button>
            </div>
        </div>

        <div class="card-body p-4 p-md-5">
            <!-- Form -->
            <form @submit.prevent="submitForm">
                
                <!-- Individual Mode -->
                <div x-show="uploadType === 'individual'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="row g-4 mb-4">
                        <!-- Título -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase ls-1">Título <span class="text-primary fw-bold small">(opcional)</span></label>
                            <input type="text" x-model="items[0].titulo" class="form-control rounded-pill bg-light border-0 px-4 py-2 shadow-sm-hover" placeholder="Ex: Festa do Padroeiro">
                        </div>

                        <!-- Tipo -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase ls-1">Tipo de Imagem <span class="text-danger fw-bold small">*</span></label>
                            <select x-model="items[0].tipo" class="form-select rounded-pill bg-light border-0 px-4 py-2 shadow-sm-hover" required>
                                <option value="1">Poster (Página Principal)</option>
                                <option value="2">Postagem (Galeria/Blog)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Descrição -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase ls-1">Descrição <span class="text-primary fw-bold small">(opcional)</span></label>
                        <textarea x-model="items[0].descricao" rows="3" class="form-control rounded-4 bg-light border-0 px-4 py-3 shadow-sm-hover" placeholder="Detalhes opcionais sobre a imagem..."></textarea>
                    </div>

                    <!-- Drag & Drop Area -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted small text-uppercase ls-1">Imagem <span class="text-danger fw-bold small">*</span></label>
                        
                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-5 text-center position-relative transition-all" 
                             @dragover.prevent="dragover = true" 
                             @dragleave.prevent="dragover = false"
                             @drop.prevent="handleDrop($event)"
                             :class="{'border-primary bg-primary-subtle': dragover, 'border-secondary-subtle': !dragover, 'border-danger bg-danger-subtle': error}">
                            
                            <input type="file" x-ref="fileInputIndividual" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer z-10" 
                                   accept="image/*, .heic, .heif"
                                   @change="handleFiles($event.target.files)">
                            
                            <div x-show="!items[0].file" class="pointer-events-none">
                                <div class="mb-3">
                                    <i class="bi bi-cloud-arrow-up text-primary" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Arraste e solte sua imagem aqui</h6>
                                <p class="text-muted small mb-0">ou clique para selecionar</p>
                            </div>

                            <div x-show="items[0].file" class="text-start position-relative z-20 pointer-events-none" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-secondary small">Arquivo selecionado:</span>
                                    <button type="button" @click.stop="clearFiles()" class="btn btn-sm btn-outline-danger pointer-events-auto py-1 px-3 rounded-pill transition-all hover-scale">
                                        <i class="bi bi-trash me-1"></i> Remover
                                    </button>
                                </div>
                                <div class="bg-white p-3 rounded-4 border d-flex align-items-center shadow-sm">
                                    <div class="bg-light rounded-3 p-2 me-3 text-secondary d-flex align-items-center justify-content-center border" style="width: 48px; height: 48px;">
                                        <i class="bi bi-file-earmark-image fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 text-truncate">
                                        <div class="fw-bold text-dark small text-truncate" x-text="items[0].file ? items[0].file.name : ''"></div>
                                        <div class="text-muted small" x-text="items[0].file ? formatSize(items[0].file.size) : ''"></div>
                                    </div>
                                    <div class="text-success ms-3">
                                        <i class="bi bi-check-circle-fill fs-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Batch Mode -->
                <div x-show="uploadType === 'batch'" style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase ls-1 mb-0">Itens para Upload</label>
                            <button type="button" @click="$refs.fileInputBatch.click()" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold transition-all hover-scale">
                                <i class="bi bi-plus-lg me-1"></i> Adicionar Imagens
                            </button>
                            <input type="file" x-ref="fileInputBatch" class="d-none" multiple accept="image/*, .heic, .heif" @change="handleFiles($event.target.files)">
                        </div>

                        <template x-if="items.length === 0">
                            <div class="text-center py-5 border-2 border-dashed rounded-4 bg-light transition-all"
                                 @dragover.prevent="dragover = true" 
                                 @dragleave.prevent="dragover = false"
                                 @drop.prevent="handleDrop($event)"
                                 :class="{'border-primary bg-primary-subtle': dragover, 'border-secondary-subtle': !dragover}">
                                <i class="bi bi-images display-4 text-muted opacity-50 mb-3"></i>
                                <h6 class="fw-bold text-secondary">Nenhuma imagem adicionada</h6>
                                <p class="text-muted small">Arraste arquivos ou clique em "Adicionar Imagens"</p>
                            </div>
                        </template>

                        <div class="row g-4">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                                        <div class="card-body p-0 d-flex flex-column flex-md-row">
                                            <!-- Image Preview/Info -->
                                            <div class="bg-light p-4 d-flex flex-column align-items-center justify-content-center text-center border-end-md" style="min-width: 200px;">
                                                <div class="mb-2">
                                                    <i class="bi bi-file-earmark-image fs-1 text-secondary opacity-50"></i>
                                                </div>
                                                <div class="fw-bold text-dark small text-break" x-text="item.file.name" style="max-width: 180px;"></div>
                                                <div class="text-muted x-small" x-text="formatSize(item.file.size)"></div>
                                            </div>

                                            <!-- Fields -->
                                            <div class="p-4 flex-grow-1">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-muted x-small text-uppercase">Título <span class="text-primary x-small">(opcional)</span></label>
                                                        <input type="text" x-model="item.titulo" class="form-control form-control-sm rounded-pill bg-light border-0 px-3 shadow-sm-hover" placeholder="Título da imagem">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold text-muted x-small text-uppercase">Tipo <span class="text-danger x-small">*</span></label>
                                                        <select x-model="item.tipo" class="form-select form-select-sm rounded-pill bg-light border-0 px-3 shadow-sm-hover" required>
                                                            <option value="1">Poster</option>
                                                            <option value="2">Postagem</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label fw-bold text-muted x-small text-uppercase">Descrição <span class="text-primary x-small">(opcional)</span></label>
                                                        <textarea x-model="item.descricao" rows="2" class="form-control form-control-sm rounded-4 bg-light border-0 px-3 py-2 shadow-sm-hover" placeholder="Descrição da imagem"></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="p-2 d-flex align-items-start justify-content-end bg-white">
                                                <button type="button" @click="removeFile(index)" class="btn btn-link text-danger p-2 hover-scale" title="Remover">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end pt-4 border-top mt-2">
                    <button type="submit" 
                            :disabled="!canSubmit"
                            class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-lg hover-shadow-lg transition-all d-flex align-items-center gap-2">
                        <span x-show="!loading">
                            <span class="d-flex align-items-center">
                                <i class="bi bi-send me-2"></i> Enviar Imagens
                            </span>
                        </span>
                        <span x-show="loading" style="display: none;">
                            <span class="d-flex align-items-center">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Enviando...
                            </span>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Gallery Preview -->
    <div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
            <h4 class="fw-bold text-dark mb-0">Galeria Atual</h4>
            
            <!-- Filters -->
            <form action="{{ route('site-tools.gallery') }}" method="GET" class="d-flex gap-2 flex-wrap justify-content-end w-100 w-md-auto">
                <div class="input-group rounded-pill overflow-hidden border bg-white shadow-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-0 ps-3"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 ps-1 shadow-none" placeholder="Pesquisar..." value="{{ request('search') }}">
                </div>

                <select name="tipo" class="form-select rounded-pill border shadow-sm bg-white cursor-pointer" style="width: auto; min-width: 140px;" onchange="this.form.submit()">
                    <option value="">Todos os Tipos</option>
                    <option value="1" {{ request('tipo') == '1' ? 'selected' : '' }}>Poster</option>
                    <option value="2" {{ request('tipo') == '2' ? 'selected' : '' }}>Postagem</option>
                </select>

                <select name="period" class="form-select rounded-pill border shadow-sm bg-white cursor-pointer" style="width: auto; min-width: 160px;" onchange="this.form.submit()">
                    <option value="">Todos os Períodos</option>
                    @if(isset($periods))
                        @foreach($periods as $period)
                            <option value="{{ $period->period }}" {{ request('period') == $period->period ? 'selected' : '' }}>
                                {{ $period->label }}
                            </option>
                        @endforeach
                    @endif
                </select>
                
                @if(request()->anyFilled(['search', 'tipo', 'period']))
                    <a href="{{ route('site-tools.gallery') }}" class="btn btn-light rounded-circle shadow-sm border d-flex align-items-center justify-content-center hover-scale" style="width: 38px; height: 38px;" title="Limpar Filtros">
                        <i class="bi bi-x-lg text-secondary small"></i>
                    </a>
                @endif
            </form>
        </div>

            <div id="gallery-wrapper">
                <div id="gallery-grid" class="row g-4 {{ (!isset($imagens) || $imagens->count() == 0) ? 'd-none' : '' }}">
                    @if(isset($imagens))
                        @foreach($imagens as $img)
                            <div class="col-sm-6 col-md-4 col-lg-3 gallery-item">
                                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all overflow-hidden group rounded-4">
                                    <div class="position-relative bg-light ratio ratio-16x9">
                                        @if($img->imagem && file_exists(storage_path('app/public/uploads/paroquias/' . $img->imagem)))
                                            <img src="{{ asset('storage/uploads/paroquias/' . $img->imagem) }}" 
                                                 alt="{{ $img->titulo }}" 
                                                 class="object-fit-cover w-100 h-100 transition-transform group-hover-scale"
                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;d-flex align-items-center justify-content-center w-100 h-100 bg-secondary bg-opacity-10 text-secondary&quot;><i class=&quot;bi bi-image fs-1&quot;></i></div>'">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-secondary bg-opacity-10 text-secondary">
                                                <i class="bi bi-image fs-1"></i>
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge {{ $img->tipo == 1 ? 'bg-primary' : 'bg-info' }} bg-opacity-75 backdrop-blur rounded-pill shadow-sm">
                                                {{ $img->tipo == 1 ? 'Poster' : 'Postagem' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <h6 class="card-title fw-bold text-dark text-truncate mb-1" title="{{ $img->titulo }}">{{ $img->titulo }}</h6>
                                        @if($img->descricao)
                                            <p class="card-text small text-muted text-truncate-2 mb-2" title="{{ $img->descricao }}">{{ $img->descricao }}</p>
                                        @endif
                                        <div class="d-flex align-items-center text-muted small mt-auto pt-2 border-top">
                                            <i class="bi bi-calendar-event me-1"></i> {{ $img->created_at->format('d/m/Y') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div id="gallery-empty" class="text-center py-5 bg-white rounded-4 shadow-sm border border-dashed {{ (!isset($imagens) || $imagens->count() == 0) ? '' : 'd-none' }}">
                    <div class="text-muted mb-3">
                        <i class="bi bi-images display-1 opacity-25"></i>
                    </div>
                    <h5 class="fw-bold text-secondary">Nenhuma imagem na galeria</h5>
                    <p class="text-muted">Faça o upload da primeira imagem usando o formulário acima.</p>
                </div>
            </div>
    </div>
</div>

<!-- Toast Notifications -->
@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div class="toast show align-items-center text-bg-success border-0 shadow-lg rounded-4" role="alert" aria-live="assertive" aria-atomic="true" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center py-3">
                <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                <div>
                    <h6 class="fw-bold mb-0">Sucesso!</h6>
                    <span class="small opacity-90">{{ session('success') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-3 m-auto" @click="show = false" aria-label="Close"></button>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div class="toast show align-items-center text-bg-danger border-0 shadow-lg rounded-4" role="alert" aria-live="assertive" aria-atomic="true" x-data="{ show: true }" x-show="show" x-transition>
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center py-3">
                <i class="bi bi-exclamation-circle-fill me-3 fs-5"></i>
                <div>
                    <h6 class="fw-bold mb-0">Erro!</h6>
                    <span class="small opacity-90">{{ session('error') }}</span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-3 m-auto" @click="show = false" aria-label="Close"></button>
        </div>
    </div>
</div>
@endif

<!-- JS Dynamic Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
    <div id="js-toast" class="toast align-items-center text-bg-primary border-0 shadow-lg rounded-4 hide" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center py-3">
                <i id="js-toast-icon" class="bi bi-info-circle-fill me-3 fs-5"></i>
                <div>
                    <h6 id="js-toast-title" class="fw-bold mb-0">Notificação</h6>
                    <span id="js-toast-message" class="small opacity-90"></span>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@endsection
