@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">{{ $bucket->name }}</h2>
            <p class="text-muted small mb-0">Bucket de mídia #{{ $bucket->rand }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('buckets.index') }}" class="text-decoration-none">Buckets de mídia</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $bucket->name }}</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div><strong>Sucesso!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div><strong>Erro!</strong> {{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1 fw-bold text-dark">Arquivos do bucket</h5>
                            <p class="text-muted small mb-0">Envie e gerencie arquivos de mídia para este bucket.</p>
                        </div>
                    </div>
                    <form action="{{ route('buckets.files.store', $bucket) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div id="bucketDropArea" class="file-drop-area rounded-4 border border-2 bg-light p-4 cursor-pointer mb-3">
                            <input type="file" name="files[]" id="bucketFilesInput" class="d-none" multiple>
                            <div id="bucketDropContent" class="text-center text-muted">
                                <i class="bi bi-cloud-arrow-up fs-1 d-block mb-2"></i>
                                <div class="fw-semibold">Arraste e solte arquivos aqui</div>
                                <div class="small">ou clique para selecionar</div>
                                <div class="small mt-2">Limite do bucket: 1 GB • Tipos: qualquer arquivo</div>
                            </div>
                            <div id="bucketPreviewArea" class="d-none">
                                <div class="small text-muted mb-2 fw-semibold">Arquivos selecionados</div>
                                <ul id="bucketFileList" class="list-unstyled mb-0 small"></ul>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-4 d-none" id="bucketClearSelection">Limpar seleção</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2">
                                <i class="bi bi-upload"></i>
                                <span>Enviar arquivos</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted small text-uppercase mb-3">Resumo do bucket</h6>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Uso de armazenamento</span>
                            <span class="small text-muted">
                                {{ number_format($used / (1024 * 1024), 2, ',', '.') }} MB / {{ number_format($max / (1024 * 1024 * 1024), 2, ',', '.') }} GB
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar @if($percent > 80) bg-danger @elseif($percent > 60) bg-warning @else bg-success @endif" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <ul class="small text-muted mb-0">
                        <li>Bucket vinculado ao usuário #{{ $bucket->user_id }} da sua paróquia.</li>
                        <li>Arquivos são salvos em <code>/uploads/buckets/{{ $bucket->user_id }}/{{ $bucket->rand }}</code>.</li>
                        <li>Ideal para centralizar arquivos de mídia usados em outros módulos.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="mb-1 fw-bold text-dark">Arquivos armazenados</h5>
                    <p class="text-muted small mb-0">Gerencie seus arquivos de forma semelhante a um painel de armazenamento em nuvem.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-3 d-flex align-items-center gap-2" id="btnSelectAllFiles">
                        <i class="bi bi-check2-square"></i>
                        <span>Selecionar tudo</span>
                    </button>
                    <form action="{{ route('buckets.files.bulk-destroy', $bucket) }}" method="post" id="bulkDeleteForm">
                        @csrf
                        <input type="hidden" name="files" id="bulkFilesInput">
                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 d-flex align-items-center gap-2" id="btnBulkDelete" disabled>
                            <i class="bi bi-trash"></i>
                            <span>Excluir selecionados</span>
                        </button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                            </th>
                            <th>Nome do arquivo</th>
                            <th class="text-nowrap">Tamanho</th>
                            <th class="text-nowrap">Enviado em</th>
                            <th class="text-end text-nowrap">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($files as $file)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input file-select-checkbox" value="{{ $file->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-file-earmark text-muted"></i>
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="text-decoration-none text-dark">
                                            <span class="fw-semibold">{{ $file->file_name }}</span>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ number_format($file->file_size / (1024 * 1024), 2, ',', '.') }} MB</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($file->upload_date)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn btn-light btn-sm" title="Visualizar">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ asset('storage/' . $file->file_path) }}" download class="btn btn-light btn-sm" title="Download">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form action="{{ route('buckets.files.destroy', $file) }}" method="post" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-light btn-sm text-danger shadow-sm btn-delete-file" title="Excluir arquivo">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    Nenhum arquivo enviado para este bucket ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $files->links() }}
            </div>
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
    const dropArea = document.getElementById('bucketDropArea');
    const fileInput = document.getElementById('bucketFilesInput');
    const dropContent = document.getElementById('bucketDropContent');
    const previewArea = document.getElementById('bucketPreviewArea');
    const fileList = document.getElementById('bucketFileList');
    const clearBtn = document.getElementById('bucketClearSelection');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const btnSelectAllFiles = document.getElementById('btnSelectAllFiles');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkFilesInput = document.getElementById('bulkFilesInput');
    const btnBulkDelete = document.getElementById('btnBulkDelete');

    if (dropArea && fileInput) {
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
    }

    function handleFiles(files) {
        if (!files || files.length === 0) {
            resetSelection();
            return;
        }

        fileList.innerHTML = '';
        Array.from(files).forEach(file => {
            const li = document.createElement('li');
            li.textContent = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
            fileList.appendChild(li);
        });

        dropContent.classList.add('d-none');
        previewArea.classList.remove('d-none');
        if (clearBtn) clearBtn.classList.remove('d-none');
    }

    function resetSelection() {
        fileInput.value = '';
        fileList.innerHTML = '';
        previewArea.classList.add('d-none');
        dropContent.classList.remove('d-none');
        if (clearBtn) clearBtn.classList.add('d-none');
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetSelection();
        });
    }

    document.querySelectorAll('.btn-delete-file').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            if (!confirm('Tem certeza que deseja excluir este arquivo do bucket?')) {
                e.preventDefault();
            }
        });
    });

    function updateBulkSelection() {
        const checkboxes = document.querySelectorAll('.file-select-checkbox');
        const selectedIds = Array.from(checkboxes).filter(cb => cb.checked).map(cb => cb.value);

        if (bulkFilesInput) {
            bulkFilesInput.value = selectedIds.join(',');
        }

        if (btnBulkDelete) {
            btnBulkDelete.disabled = selectedIds.length === 0;
        }

        if (selectAllCheckbox) {
            if (checkboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else {
                const allChecked = selectedIds.length === checkboxes.length;
                const noneChecked = selectedIds.length === 0;
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
            }
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.file-select-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = selectAllCheckbox.checked;
            });
            updateBulkSelection();
        });
    }

    if (btnSelectAllFiles) {
        btnSelectAllFiles.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.file-select-checkbox');
            const someUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => {
                cb.checked = someUnchecked;
            });
            updateBulkSelection();
        });
    }

    document.querySelectorAll('.file-select-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateBulkSelection();
        });
    });

    if (bulkDeleteForm) {
        bulkDeleteForm.addEventListener('submit', function(e) {
            const value = bulkFilesInput ? bulkFilesInput.value : '';
            if (!value) {
                e.preventDefault();
                return;
            }
            const ids = value.split(',').filter(Boolean);
            if (!ids.length) {
                e.preventDefault();
                return;
            }
            if (!confirm('Tem certeza que deseja excluir os arquivos selecionados do bucket?')) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endsection
