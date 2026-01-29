@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Histórico de Presenças</h2>
            <p class="text-muted small mb-0">Catecando(a): {{ $student->name }} | Turma: {{ $turma->turma }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('turmas-eucaristia.index') }}" class="text-decoration-none">Turmas de Eucaristia</a></li>
                <li class="breadcrumb-item"><a href="{{ route('turmas-eucaristia.attendance-analysis', $turma->id) }}" class="text-decoration-none">Apuração</a></li>
                <li class="breadcrumb-item active" aria-current="page">Histórico</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="border-0 rounded-start ps-4">Data</th>
                            <th scope="col" class="border-0">Tema/Título</th>
                            <th scope="col" class="border-0">Justificativa</th>
                            <th scope="col" class="border-0">Status</th>
                            <th scope="col" class="border-0 rounded-end text-end pe-4">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $record)
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">{{ $record->data_aula->format('d/m/Y') }}</td>
                            <td>{{ $record->title }}</td>
                            <td class="text-muted small" style="max-width: 250px;">
                                @if($record->justificativa)
                                    {{ Str::limit($record->justificativa->justify, 50) }}
                                    @if(strlen($record->justificativa->justify) > 50)
                                        <i class="bi bi-info-circle text-primary" data-bs-toggle="tooltip" title="{{ $record->justificativa->justify }}"></i>
                                    @endif
                                @else
                                    <span class="text-muted fst-italic">-</span>
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
                                        data-justify="{{ $record->justificativa->justify ?? '' }}"
                                        onclick="openJustifyModal(this)">
                                    <i class="bi bi-pencil-square me-1"></i> Justificar
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
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
                <form id="justifyForm" action="{{ route('turmas-eucaristia.attendance.justify') }}" method="POST">
                    @csrf
                    <input type="hidden" name="falta_id" id="modalFaltaId">
                    <div class="mb-3">
                        <label for="justifyText" class="form-label text-muted small fw-bold">Justificativa</label>
                        <textarea class="form-control bg-light border-0" id="justifyText" name="justify" rows="4" placeholder="Digite o motivo da falta ou observação..." required></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill py-2">Salvar Justificativa</button>
                    </div>
                </form>
            </div>
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
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endsection
