@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Histórico de Presenças e Faltas</h2>
            <p class="text-muted small mb-0">Acólito/Coroinha: {{ $acolito->name }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.index') }}" class="text-decoration-none">Acólitos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Histórico</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('acolitos.attendance-history', $acolito->id) }}" method="GET" class="row g-3" id="filtersForm">
                <div class="col-md-3">
                    <label for="status" class="form-label text-muted small fw-bold">Status</label>
                    <select name="status" id="status" class="form-select bg-light rounded-pill" style="height:45px;">
                        <option value="">Todos</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Presenças</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Faltas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label text-muted small fw-bold">Data Inicial</label>
                    <input type="date" name="start_date" id="start_date" class="form-control bg-light rounded-pill" style="height:45px;" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label text-muted small fw-bold">Data Final</label>
                    <input type="date" name="end_date" id="end_date" class="form-control bg-light rounded-pill" style="height:45px;" value="{{ request('end_date') }}">
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Lançar Presença/Falta</h5>
            <form action="{{ route('acolitos.attendance.store') }}" method="POST" class="row g-3">
                @csrf
                <input type="hidden" name="acolito_id" value="{{ $acolito->id }}">
                <div class="col-md-3">
                    <label for="attendance_date" class="form-label text-muted small fw-bold">Data</label>
                    <input type="date" id="attendance_date" name="data" class="form-control bg-light" required>
                </div>
                <div class="col-md-3">
                    <label for="attendance_status" class="form-label text-muted small fw-bold">Status</label>
                    <select id="attendance_status" name="status" class="form-select bg-light" required>
                        <option value="present">Presença</option>
                        <option value="absent">Falta</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-bold">Justificativa</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="justify_type" id="justify_none" value="sem" checked>
                            <label class="form-check-label" for="justify_none">
                                Sem justificativa
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="justify_type" id="justify_with" value="com">
                            <label class="form-check-label" for="justify_with">
                                Com justificativa
                            </label>
                        </div>
                    </div>
                    <div class="mt-2" id="justify_text_container" style="display: none;">
                        <textarea class="form-control bg-light" name="motivo" rows="3" placeholder="Descreva a justificativa..."></textarea>
                    </div>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="border-0 rounded-start ps-4">Data / Celebração</th>
                            <th scope="col" class="border-0">Título</th>
                            <th scope="col" class="border-0">Justificativa</th>
                            <th scope="col" class="border-0">Grave</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end text-end pe-4">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $record)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-secondary">{{ \Carbon\Carbon::parse($record->data_aula)->format('d/m/Y') }}</div>
                                @if($record->escalaDataHora)
                                    <div class="text-muted small">
                                        {{ $record->escalaDataHora->celebration }} • {{ substr($record->escalaDataHora->hora ?? '', 0, 5) }}
                                    </div>
                                @else
                                    <div class="text-muted small">Registro manual</div>
                                @endif
                            </td>
                            <td>{{ $record->title }}</td>
                            <td class="text-muted small" style="max-width: 250px;">
                                @if($record->justificativa)
                                    {{ Str::limit($record->justificativa->motivo ?? '', 50) }}
                                    @if(isset($record->justificativa->motivo) && strlen($record->justificativa->motivo) > 50)
                                        <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="{{ $record->justificativa->motivo }}"></i>
                                    @endif
                                @else
                                    <span class="text-muted fst-italic">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record->grave)
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Falta grave</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">-</span>
                                @endif
                            </td>
                            <td>
                                @if($record->status)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> Presente
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">
                                        <i class="bi bi-x-circle-fill me-1"></i> Falta
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-pill" 
                                        data-id="{{ $record->id }}" 
                                        data-justify="{{ $record->justificativa->motivo ?? '' }}"
                                        onclick="openJustifyModal(this)">
                                    <i class="bi bi-pencil-square me-1"></i> Justificar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                Nenhum registro de presença ou falta encontrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Justificativa -->
<div class="modal fade" id="justifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Adicionar/Editar Justificativa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="justifyForm" action="{{ route('acolitos.attendance.justify') }}" method="POST">
                    @csrf
                    <input type="hidden" name="falta_id" id="modalFaltaId">
                    <div class="mb-3">
                        <label for="justifyText" class="form-label text-muted small fw-bold">Justificativa</label>
                        <textarea class="form-control bg-light border-0" id="justifyText" name="motivo" rows="4" placeholder="Digite o motivo da falta ou observação..." required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">Salvar Justificativa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
    function openJustifyModal(button) {
        const id = button.getAttribute('data-id');
        const currentJustify = button.getAttribute('data-justify');
        
        document.getElementById('modalFaltaId').value = id;
        document.getElementById('justifyText').value = currentJustify;
        new bootstrap.Modal(document.getElementById('justifyModal')).show();
    }
    
    const statusSelect = document.getElementById('attendance_status');
    const justifyNone = document.getElementById('justify_none');
    const justifyWith = document.getElementById('justify_with');
    const justifyContainer = document.getElementById('justify_text_container');
    
    function toggleJustify() {
        const isAbsent = statusSelect.value === 'absent';
        const showText = isAbsent && justifyWith.checked;
        justifyContainer.style.display = showText ? 'block' : 'none';
    }
    
    statusSelect.addEventListener('change', toggleJustify);
    justifyNone.addEventListener('change', toggleJustify);
    justifyWith.addEventListener('change', toggleJustify);
    toggleJustify();
    
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

    const form = document.getElementById('filtersForm');
    ['status','start_date','end_date'].forEach(function(id){
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function(){ form.submit(); });
        }
    });
</script>
@endsection
