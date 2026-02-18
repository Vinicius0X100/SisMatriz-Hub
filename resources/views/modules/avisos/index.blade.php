@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Avisos Paroquiais</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Avisos Paroquiais</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div>
                    <strong>Sucesso!</strong> {{ session('success') }}
                </div>
            </div>
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

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Avisos</h6>
                <p class="text-muted mb-0">Cadastre e gerencie os avisos da sua paróquia.</p>
            </div>
            <a href="{{ route('avisos.create') }}" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2">
                <i class="mdi mdi-plus"></i>
                <span>Novo aviso</span>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control ps-5 rounded-pill" placeholder="Título ou descrição..." style="height: 45px;" disabled>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted small">Filtrar por importância</label>
                    <select class="form-select rounded-pill" style="height: 45px;" disabled>
                        <option value="">Todos</option>
                        <option value="0">Normal</option>
                        <option value="1">Médio</option>
                        <option value="2">Alto</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-nowrap">Título</th>
                            <th class="text-nowrap">Importância</th>
                            <th class="text-nowrap">Origem</th>
                            <th class="text-nowrap">Enviado em</th>
                            <th class="text-nowrap">Visualizações</th>
                            <th class="text-end text-nowrap">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($posts as $post)
                            @php
                                $importanceMap = [0 => ['Normal', 'secondary'], 1 => ['Médio', 'warning'], 2 => ['Alto', 'danger']];
                                $importance = $importanceMap[$post->level_importance] ?? ['Indefinido', 'secondary'];
                                $deviceIcon = match($post->device) {
                                    2 => 'bi-android',
                                    3 => 'bi-apple',
                                    default => 'bi-globe',
                                };
                                $deviceLabel = match($post->device) {
                                    1 => 'Web',
                                    2 => 'Android',
                                    3 => 'iOS',
                                    default => 'Indefinido',
                                };
                                $thumbUrl = null;
                                $attachmentUrl = '';
                                if ($post->anexo) {
                                    $fullPath = storage_path('app/public/' . $post->anexo);
                                    if (file_exists($fullPath)) {
                                        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                                        $attachmentUrl = asset('storage/' . $post->anexo);
                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                                            $thumbUrl = asset('storage/' . $post->anexo);
                                        }
                                    }
                                }
                            @endphp
                            <tr
                                data-id="{{ $post->id }}"
                                data-title="{{ $post->title }}"
                                data-legend="{{ $post->legend }}"
                                data-level="{{ (int) $post->level_importance }}"
                                data-importance-label="{{ $importance[0] }}"
                                data-importance-badge="{{ $importance[1] }}"
                                data-device-label="{{ $deviceLabel }}"
                                data-device-icon="{{ $deviceIcon }}"
                                data-send-at="{{ optional($post->send_at)->format('d/m/Y H:i') }}"
                                data-image-url="{{ $thumbUrl }}"
                                data-attachment-url="{{ $attachmentUrl }}"
                            >
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; overflow: hidden;">
                                            @if($thumbUrl)
                                                <img src="{{ $thumbUrl }}" alt="{{ $post->title }}" class="w-100 h-100" style="object-fit: cover; object-position: center;">
                                            @else
                                                <i class="bi bi-image text-muted"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $post->title }}</div>
                                            <div class="text-muted small text-truncate" style="max-width: 260px;">{{ $post->legend }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $importance[1] }}">{{ $importance[0] }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi {{ $deviceIcon }}"></i>
                                        <span class="text-muted small">{{ $deviceLabel }}</span>
                                    </div>
                                </td>
                                <td>{{ optional($post->send_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $post->views ?? 0 }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-light rounded-pill px-3 btn-view-aviso" title="Ver aviso">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <a href="{{ route('avisos.edit', $post) }}" class="btn btn-light rounded-pill px-3" title="Editar aviso">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-light rounded-pill px-3 text-danger btn-delete-aviso" title="Excluir aviso"
                                                data-id="{{ $post->id }}"
                                                data-title="{{ $post->title }}"
                                                data-url="{{ route('avisos.destroy', $post) }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nenhum aviso cadastrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewAvisoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="viewAvisoTitle">Aviso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" id="viewAvisoImportanceBadge">Normal</span>
                        <div class="d-flex align-items-center gap-2 text-muted small" id="viewAvisoDeviceWrapper">
                            <i class="bi" id="viewAvisoDeviceIcon"></i>
                            <span id="viewAvisoDeviceLabel"></span>
                        </div>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>
                        <span id="viewAvisoSendAt"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="rounded-4 bg-light d-flex align-items-center justify-content-center mb-3" style="width: 100%; max-height: 260px; overflow: hidden;">
                        <img src="" alt="" id="viewAvisoImage" class="img-fluid d-none" style="width: 100%; object-fit: cover; object-position: center;">
                        <div id="viewAvisoImagePlaceholder" class="text-muted d-flex flex-column align-items-center justify-content-center py-5 w-100">
                            <i class="bi bi-image fs-1 mb-2"></i>
                            <span class="small">Nenhuma imagem disponível para este aviso.</span>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <h6 class="fw-bold text-muted small text-uppercase mb-1">Descrição</h6>
                    <p class="mb-0" id="viewAvisoLegend"></p>
                </div>
            </div>
            <div class="modal-footer border-top-0 d-flex justify-content-between">
                <a href="#" target="_blank" class="btn btn-outline-secondary rounded-pill px-4 d-none" id="viewAvisoAttachmentBtn">
                    <i class="bi bi-paperclip me-2"></i>
                    Abrir anexo
                </a>
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAvisoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body text-center p-5">
                <div class="text-danger mb-3">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita. O aviso será removido permanentemente do sistema.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <form id="deleteAvisoForm" action="#" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill px-4">Sim, excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('viewAvisoModal');
        const deleteModalEl = document.getElementById('deleteAvisoModal');
        const deleteForm = document.getElementById('deleteAvisoForm');

        function getBootstrapModal() {
            if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                return null;
            }
            return bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        function getDeleteModal() {
            if (!deleteModalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                return null;
            }
            return bootstrap.Modal.getOrCreateInstance(deleteModalEl);
        }

        function populateViewModal(row) {
            const title = row.dataset.title || '';
            const legend = row.dataset.legend || '';
            const importanceLabel = row.dataset.importanceLabel || '';
            const importanceBadge = row.dataset.importanceBadge || 'secondary';
            const deviceLabel = row.dataset.deviceLabel || '';
            const deviceIcon = row.dataset.deviceIcon || 'bi-globe';
            const sendAt = row.dataset.sendAt || '';
            const imageUrl = row.dataset.imageUrl || '';
            const attachmentUrl = row.dataset.attachmentUrl || '';

            const titleEl = document.getElementById('viewAvisoTitle');
            const legendEl = document.getElementById('viewAvisoLegend');
            const importanceEl = document.getElementById('viewAvisoImportanceBadge');
            const deviceWrapper = document.getElementById('viewAvisoDeviceWrapper');
            const deviceIconEl = document.getElementById('viewAvisoDeviceIcon');
            const deviceLabelEl = document.getElementById('viewAvisoDeviceLabel');
            const sendAtEl = document.getElementById('viewAvisoSendAt');
            const imageEl = document.getElementById('viewAvisoImage');
            const imagePlaceholderEl = document.getElementById('viewAvisoImagePlaceholder');
            const attachmentBtn = document.getElementById('viewAvisoAttachmentBtn');

            if (titleEl) titleEl.textContent = title;
            if (legendEl) legendEl.textContent = legend;
            if (importanceEl) {
                importanceEl.textContent = importanceLabel || 'Normal';
                importanceEl.className = 'badge bg-' + (importanceBadge || 'secondary');
            }

            if (deviceWrapper) {
                if (deviceLabel) {
                    deviceWrapper.classList.remove('d-none');
                    if (deviceIconEl) deviceIconEl.className = 'bi ' + deviceIcon;
                    if (deviceLabelEl) deviceLabelEl.textContent = deviceLabel;
                } else {
                    deviceWrapper.classList.add('d-none');
                }
            }

            if (sendAtEl) sendAtEl.textContent = sendAt;

            if (imageEl && imagePlaceholderEl) {
                if (imageUrl) {
                    imageEl.src = imageUrl;
                    imageEl.classList.remove('d-none');
                    imagePlaceholderEl.classList.add('d-none');
                } else {
                    imageEl.src = '';
                    imageEl.classList.add('d-none');
                    imagePlaceholderEl.classList.remove('d-none');
                }
            }

            if (attachmentBtn) {
                if (attachmentUrl) {
                    attachmentBtn.href = attachmentUrl;
                    attachmentBtn.classList.remove('d-none');
                } else {
                    attachmentBtn.href = '#';
                    attachmentBtn.classList.add('d-none');
                }
            }
        }

        document.querySelectorAll('.btn-view-aviso').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = this.closest('tr');
                if (!row) return;

                const modal = getBootstrapModal();
                if (!modal) return;

                populateViewModal(row);
                modal.show();
            });
        });

        document.querySelectorAll('.btn-delete-aviso').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                const url = this.dataset.url;
                const modal = getDeleteModal();
                if (!modal || !deleteForm || !url) return;
                deleteForm.action = url;
                modal.show();
            });
        });
    });
</script>
@endsection
