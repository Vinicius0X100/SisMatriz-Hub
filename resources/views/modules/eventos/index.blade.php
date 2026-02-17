@extends('layouts.app')

@section('title', 'Eventos Paroquiais')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Eventos Paroquiais</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Eventos Paroquiais</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Sucesso!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-md-6">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <form action="{{ route('eventos.index') }}" method="GET" class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Título ou endereço do evento..." style="height: 45px;" value="{{ request('search') }}">
                    </form>
                </div>
                <div class="col-md-6 text-end d-flex justify-content-end gap-2">
                    <button class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2" style="height: 45px;" data-bs-toggle="modal" data-bs-target="#createEventoModal">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline">Novo Evento</span>
                    </button>
                </div>
            </div>

            <div class="row g-4">
                @forelse($events as $evento)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="position-relative rounded-top-4 overflow-hidden" style="height: 200px; background-color: #f8f9fa;">
                                @if($evento->photo_url)
                                    <img src="{{ $evento->photo_url }}" alt="{{ $evento->title }}" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted">
                                        <i class="bi bi-image fs-1 mb-2"></i>
                                        <span class="fw-bold">SEM IMAGEM</span>
                                    </div>
                                @endif
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="fw-bold mb-1 text-truncate">{{ $evento->title }}</h5>
                                @php
                                    $dateLabel = null;
                                    if ($evento->date) {
                                        try {
                                            $dateLabel = \Carbon\Carbon::parse($evento->date)->format('d/m/Y');
                                        } catch (\Exception $e) {
                                            $dateLabel = $evento->date;
                                        }
                                    }
                                    $timeLabel = $evento->time ? substr($evento->time, 0, 5) : null;
                                @endphp
                                @if($dateLabel || $timeLabel)
                                    <div class="text-muted small mb-2">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $dateLabel }}
                                        @if($timeLabel)
                                            às {{ $timeLabel }}
                                        @endif
                                    </div>
                                @endif
                                @if($evento->address)
                                    <div class="text-muted small mb-3 d-flex">
                                        <i class="bi bi-geo-alt me-1 mt-1"></i>
                                        <span class="text-truncate" title="{{ $evento->address }}">{{ $evento->address }}</span>
                                    </div>
                                @endif
                                <div class="mt-auto d-flex justify-content-center align-items-center">
                                    <div class="segmented rounded-pill shadow-sm">
                                        <button type="button"
                                            class="btn {{ $evento->has_lembrete ? 'btn-outline-success' : 'btn-outline-primary' }} btn-sm seg-btn d-flex align-items-center gap-1 btn-add-lembrete"
                                            data-id="{{ $evento->id }}"
                                            data-title="{{ $evento->title }}"
                                            data-has="{{ $evento->has_lembrete ? '1' : '0' }}">
                                            @if($evento->has_lembrete)
                                                <i class="bi bi-check2-circle"></i>
                                                <span>Com lembrete</span>
                                            @else
                                                <i class="bi bi-bell-plus"></i>
                                                <span>Lembrete</span>
                                            @endif
                                        </button>
    
                                        <button type="button"
                                            class="btn btn-outline-secondary btn-sm seg-btn d-flex align-items-center gap-1 btn-edit-evento"
                                            data-id="{{ $evento->id }}"
                                            data-title="{{ $evento->title }}"
                                            data-date="{{ $evento->date }}"
                                            data-time="{{ $evento->time }}"
                                            data-address="{{ $evento->address }}"
                                            data-photo-url="{{ $evento->photo_url }}">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </button>
    
                                        <form action="{{ route('eventos.destroy', $evento->id) }}" method="POST" class="d-inline form-delete-evento seg-form" data-evento-title="{{ $evento->title }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm seg-btn d-flex align-items-center gap-1">
                                                <i class="bi bi-trash"></i>
                                                <span>Excluir</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-event display-4 mb-3"></i>
                            <p class="mb-1">Nenhum evento cadastrado ainda.</p>
                            <button class="btn btn-primary rounded-pill mt-2" data-bs-toggle="modal" data-bs-target="#createEventoModal">
                                <i class="bi bi-plus-lg me-1"></i> Adicionar primeiro evento
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Evento -->
<div class="modal fade" id="editEventoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Editar Evento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="#" method="POST" enctype="multipart/form-data" id="editEventoForm">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Título do Evento <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="editTitle" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Data <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="editDate" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Horário <span class="text-danger">*</span></label>
                            <input type="time" name="time" id="editTime" class="form-control rounded-pill" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Endereço <span class="text-danger">*</span></label>
                        <input type="text" name="address" id="editAddress" class="form-control rounded-pill" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Imagem de Capa</label>
                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-4 text-center position-relative" id="eventoEditDropArea">
                            <input type="file" name="photo" id="eventoEditPhoto" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/*,.webp">
                            <div class="d-flex flex-column align-items-center justify-content-center" id="eventoEditDropContent">
                                <i class="bi bi-cloud-arrow-up display-5 text-primary mb-2"></i>
                                <h6 class="fw-bold text-dark mb-1">Arraste e solte a imagem aqui</h6>
                                <p class="text-muted small mb-0">ou clique para selecionar (JPG, PNG, GIF, WEBP)</p>
                            </div>
                            <div id="eventoEditPreviewArea" class="d-none mt-3">
                                <img id="eventoEditImgPreview" src="#" alt="Preview" class="rounded-4 shadow-sm border" style="max-width: 220px; max-height: 220px; object-fit: cover;">
                                <p id="eventoEditFileName" class="small text-muted mt-2 mb-0"></p>
                                <button type="button" class="btn btn-sm btn-light rounded-pill border mt-2" id="eventoEditRemoveFile">Remover</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

<!-- Modal Criar Evento -->
<div class="modal fade" id="createEventoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Novo Evento Paroquial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('eventos.store') }}" method="POST" enctype="multipart/form-data" id="createEventoForm">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Título do Evento <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Data <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-muted">Horário <span class="text-danger">*</span></label>
                            <input type="time" name="time" class="form-control rounded-pill" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Endereço <span class="text-danger">*</span></label>
                        <input type="text" name="address" class="form-control rounded-pill" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Imagem de Capa</label>
                        <div class="file-drop-area rounded-4 border-2 border-dashed bg-light p-4 text-center position-relative" id="eventoDropArea">
                            <input type="file" name="photo" id="eventoPhoto" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" accept="image/*,.webp">
                            <div class="d-flex flex-column align-items-center justify-content-center" id="eventoDropContent">
                                <i class="bi bi-cloud-arrow-up display-5 text-primary mb-2"></i>
                                <h6 class="fw-bold text-dark mb-1">Arraste e solte a imagem aqui</h6>
                                <p class="text-muted small mb-0">ou clique para selecionar (JPG, PNG, GIF, WEBP)</p>
                            </div>
                            <div id="eventoPreviewArea" class="d-none mt-3">
                                <img id="eventoImgPreview" src="#" alt="Preview" class="rounded-4 shadow-sm border" style="max-width: 220px; max-height: 220px; object-fit: cover;">
                                <p id="eventoFileName" class="small text-muted mt-2 mb-0"></p>
                                <button type="button" class="btn btn-sm btn-light rounded-pill border mt-2" id="eventoRemoveFile">Remover</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Evento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exclusão -->
<div class="modal fade" id="deleteEventoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Excluir evento?</h4>
                <p class="text-muted mb-4" id="deleteEventoText">Esta ação não poderá ser desfeita.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="confirmDeleteEventoBtn">Sim, excluir</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .file-drop-area {
        transition: all 0.2s ease;
        border-color: #dee2e6;
    }
    .file-drop-area:hover,
    .file-drop-area.dragover {
        border-color: #0d6efd;
        background-color: #f1f8ff !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .segmented {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        background-color: #f5f5f7;
        border: 1px solid #e5e7eb;
        padding: 4px;
    }
    .seg-btn {
        border: 0 !important;
        background: transparent;
        border-radius: 999px;
        padding: 6px 12px;
    }
    .seg-btn:hover {
        background-color: #ffffff;
    }
    .segmented .btn-outline-primary:hover,
    .segmented .btn-outline-primary:focus {
        color: #0d6efd !important;
        background-color: #ffffff !important;
    }
    .segmented .btn-outline-success:hover,
    .segmented .btn-outline-success:focus {
        color: #198754 !important;
        background-color: #ffffff !important;
    }
    .segmented .btn-outline-secondary:hover,
    .segmented .btn-outline-secondary:focus {
        color: #6c757d !important;
        background-color: #ffffff !important;
    }
    .segmented .btn-outline-danger:hover,
    .segmented .btn-outline-danger:focus {
        color: #dc3545 !important;
        background-color: #ffffff !important;
    }
    .seg-form {
        display: inline-block;
        margin: 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    var dropArea = document.getElementById('eventoDropArea');
    var fileInput = document.getElementById('eventoPhoto');
    var imgPreview = document.getElementById('eventoImgPreview');
    var previewArea = document.getElementById('eventoPreviewArea');
    var dropContent = document.getElementById('eventoDropContent');
    var removeFileBtn = document.getElementById('eventoRemoveFile');
    var fileNameDisplay = document.getElementById('eventoFileName');

    if (dropArea && fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (eventName) {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            dropArea.addEventListener(eventName, function () {
                dropArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            dropArea.addEventListener(eventName, function () {
                dropArea.classList.remove('dragover');
            }, false);
        });

        dropArea.addEventListener('drop', handleDrop, false);
        fileInput.addEventListener('change', function () {
            handleFiles(this.files);
        });
    }

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    function handleDrop(e) {
        var dt = e.dataTransfer;
        var files = dt.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        if (!files || files.length === 0) return;
        var file = files[0];
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione apenas arquivos de imagem.');
            return;
        }
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function () {
            imgPreview.src = reader.result;
            fileNameDisplay.textContent = file.name;
            dropContent.classList.add('d-none');
            previewArea.classList.remove('d-none');

            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
        };
    }

    if (removeFileBtn) {
        removeFileBtn.addEventListener('click', function () {
            fileInput.value = '';
            imgPreview.src = '#';
            previewArea.classList.add('d-none');
            dropContent.classList.remove('d-none');
        });
    }

    var currentDeleteForm = null;
    var deleteModalEl = document.getElementById('deleteEventoModal');
    var deleteModal = deleteModalEl ? new bootstrap.Modal(deleteModalEl) : null;
    var deleteText = document.getElementById('deleteEventoText');

    document.querySelectorAll('.form-delete-evento').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            currentDeleteForm = form;
            if (deleteText) {
                var title = form.getAttribute('data-evento-title') || '';
                deleteText.textContent = title
                    ? 'Deseja realmente excluir o evento "' + title + '"?'
                    : 'Deseja realmente excluir este evento?';
            }
            if (deleteModal) {
                deleteModal.show();
            }
        });
    });

    var confirmDeleteBtn = document.getElementById('confirmDeleteEventoBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function () {
            if (currentDeleteForm) {
                currentDeleteForm.submit();
                currentDeleteForm = null;
            }
        });
    }

    var editModalEl = document.getElementById('editEventoModal');
    var editModal = editModalEl ? new bootstrap.Modal(editModalEl) : null;
    var editForm = document.getElementById('editEventoForm');
    var editTitle = document.getElementById('editTitle');
    var editDate = document.getElementById('editDate');
    var editTime = document.getElementById('editTime');
    var editAddress = document.getElementById('editAddress');
    var editDropArea = document.getElementById('eventoEditDropArea');
    var editFileInput = document.getElementById('eventoEditPhoto');
    var editImgPreview = document.getElementById('eventoEditImgPreview');
    var editPreviewArea = document.getElementById('eventoEditPreviewArea');
    var editDropContent = document.getElementById('eventoEditDropContent');
    var editRemoveFileBtn = document.getElementById('eventoEditRemoveFile');
    var editFileNameDisplay = document.getElementById('eventoEditFileName');

    if (editDropArea && editFileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (eventName) {
            editDropArea.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            editDropArea.addEventListener(eventName, function () {
                editDropArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(function (eventName) {
            editDropArea.addEventListener(eventName, function () {
                editDropArea.classList.remove('dragover');
            }, false);
        });

        editDropArea.addEventListener('drop', function(e) {
            var dt = e.dataTransfer;
            var files = dt.files;
            handleEditFiles(files);
        }, false);
        editFileInput.addEventListener('change', function () {
            handleEditFiles(this.files);
        });
    }

    function handleEditFiles(files) {
        if (!files || files.length === 0) return;
        var file = files[0];
        if (!file.type.startsWith('image/')) {
            alert('Por favor, selecione apenas arquivos de imagem.');
            return;
        }
        var reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function () {
            editImgPreview.src = reader.result;
            editFileNameDisplay.textContent = file.name;
            editDropContent.classList.add('d-none');
            editPreviewArea.classList.remove('d-none');

            var dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            editFileInput.files = dataTransfer.files;
        };
    }

    if (editRemoveFileBtn) {
        editRemoveFileBtn.addEventListener('click', function () {
            editFileInput.value = '';
            editImgPreview.src = '#';
            editPreviewArea.classList.add('d-none');
            editDropContent.classList.remove('d-none');
            editFileNameDisplay.textContent = '';
        });
    }

    document.querySelectorAll('.btn-edit-evento').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var title = this.getAttribute('data-title') || '';
            var date = this.getAttribute('data-date') || '';
            var time = this.getAttribute('data-time') || '';
            var address = this.getAttribute('data-address') || '';
            var photoUrl = this.getAttribute('data-photo-url') || '';

            if (editForm) {
                editForm.setAttribute('action', "{{ url('/eventos') }}/" + id);
            }
            if (editTitle) editTitle.value = title;
            if (editDate) editDate.value = date;
            if (editTime) editTime.value = time ? time.substring(0,5) : '';
            if (editAddress) editAddress.value = address;

            // Show current photo preview if exists
            if (photoUrl) {
                editImgPreview.src = photoUrl;
                editFileNameDisplay.textContent = 'Capa atual';
                editDropContent.classList.add('d-none');
                editPreviewArea.classList.remove('d-none');
            } else {
                editFileInput.value = '';
                editImgPreview.src = '#';
                editPreviewArea.classList.add('d-none');
                editDropContent.classList.remove('d-none');
                editFileNameDisplay.textContent = '';
            }

            if (editModal) editModal.show();
        });
    });

    document.querySelectorAll('.btn-add-lembrete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            if (!id) return;

            var originalHtml = this.innerHTML;
            this.disabled = true;
            var has = this.getAttribute('data-has') === '1';
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + (has ? 'Removendo...' : 'Adicionando...');

            fetch("{{ url('/eventos') }}/" + id + "/lembrete", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Erro ao adicionar lembrete');
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.success) {
                        if (data.action === 'added') {
                            btn.classList.remove('btn-outline-primary');
                            btn.classList.add('btn-outline-success');
                            btn.setAttribute('data-has', '1');
                            btn.innerHTML = '<i class="bi bi-check2-circle"></i><span>Com lembrete</span>';
                        } else if (data.action === 'removed') {
                            btn.classList.remove('btn-outline-success');
                            btn.classList.add('btn-outline-primary');
                            btn.setAttribute('data-has', '0');
                            btn.innerHTML = '<i class="bi bi-bell-plus"></i><span>Lembrete</span>';
                        }
                    } else {
                        alert(data.message || 'Não foi possível concluir a ação.');
                    }
                })
                .catch(function () {
                    alert('Não foi possível adicionar o lembrete.');
                })
                .finally(() => {
                    btn.disabled = false;
                });
        });
    });
});
</script>
@endsection
