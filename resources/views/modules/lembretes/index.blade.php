@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row g-4">
        <!-- Sidebar / Stats -->
        <div class="col-lg-3">
            <h2 class="fw-bold mb-4">Lembretes</h2>
            
            <div class="row g-2 mb-4">
                <div class="col-6">
                    <div class="card border-0 bg-white shadow-sm rounded-4 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-calendar-event text-white"></i>
                                </div>
                                <h3 class="fw-bold mb-0">{{ $active->count() }}</h3>
                            </div>
                            <span class="text-muted small fw-bold">Programados</span>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-0 bg-white shadow-sm rounded-4 h-100">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-check-lg text-white"></i>
                                </div>
                                <h3 class="fw-bold mb-0">{{ $completed->count() }}</h3>
                            </div>
                            <span class="text-muted small fw-bold">Concluídos</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button class="btn btn-primary rounded-pill py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#createLembreteModal">
                    <i class="bi bi-plus-lg me-2"></i>Novo Lembrete
                </button>
            </div>
        </div>

        <!-- Main List -->
        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 bg-white" style="min-height: 70vh;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-primary mb-3">Em Aberto</h5>
                    <div class="list-group list-group-flush mb-5" id="activeList">
                        @forelse($active as $lembrete)
                            <div class="list-group-item border-0 d-flex align-items-center gap-3 py-3 px-0" id="lembrete-{{ $lembrete->id }}">
                                <div class="form-check">
                                    <input class="form-check-input rounded-circle border-2 p-2" type="checkbox" 
                                           onchange="toggleStatus({{ $lembrete->id }}, this)" 
                                           style="cursor: pointer; width: 24px; height: 24px;">
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium text-dark">{{ $lembrete->descricao }}</div>
                                    <div class="small {{ $lembrete->data_hora->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                        {{ $lembrete->data_hora->format('d/m/Y H:i') }}
                                        @if($lembrete->repeat != 'none')
                                            <i class="bi bi-arrow-repeat ms-1"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    @if($lembrete->pref_email) <i class="bi bi-envelope text-muted" title="Notificação por E-mail"></i> @endif
                                    @if($lembrete->pref_sound) <i class="bi bi-volume-up text-muted" title="Notificação Sonora"></i> @endif
                                    
                                    <form action="{{ route('lembretes.destroy', $lembrete->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Excluir este lembrete?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-light rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                    
                                    <!-- Snooze Dropdown -->
                                    <div class="dropdown d-inline ms-1">
                                        <button class="btn btn-sm btn-light rounded-circle text-muted" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-clock-history"></i>
                                        </button>
                                        <ul class="dropdown-menu border-0 shadow-sm rounded-3">
                                            <li><h6 class="dropdown-header small">Adiar por...</h6></li>
                                            <li><a class="dropdown-item small" href="#" onclick="snooze({{ $lembrete->id }}, 60)">1 Hora</a></li>
                                            <li><a class="dropdown-item small" href="#" onclick="snooze({{ $lembrete->id }}, 180)">3 Horas</a></li>
                                            <li><a class="dropdown-item small" href="#" onclick="snooze({{ $lembrete->id }}, 1440)">Amanhã</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-check2-circle display-4 mb-3 d-block opacity-50"></i>
                                Nenhum lembrete pendente.
                            </div>
                        @endforelse
                    </div>

                    @if($completed->count() > 0)
                        <h5 class="fw-bold text-muted mb-3">Concluídos</h5>
                        <div class="list-group list-group-flush opacity-75" id="completedList">
                            @foreach($completed as $lembrete)
                                <div class="list-group-item border-0 d-flex align-items-center gap-3 py-3 px-0 text-decoration-line-through text-muted" id="lembrete-{{ $lembrete->id }}">
                                    <div class="form-check">
                                        <input class="form-check-input rounded-circle border-2 p-2" type="checkbox" 
                                               onchange="toggleStatus({{ $lembrete->id }}, this)" 
                                               checked
                                               style="cursor: pointer; width: 24px; height: 24px;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium">{{ $lembrete->descricao }}</div>
                                        <div class="small text-muted">
                                            {{ $lembrete->data_hora->format('d/m/Y H:i') }}
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 align-items-center">
                                        <form action="{{ route('lembretes.destroy', $lembrete->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Excluir este lembrete?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-light rounded-circle text-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="createLembreteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Novo Lembrete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('lembretes.store') }}" method="POST" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <textarea name="descricao" class="form-control form-control-lg border-0 bg-light rounded-3" placeholder="O que você precisa lembrar?" rows="3" required></textarea>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase mb-1">Data e Hora</label>
                            <input type="datetime-local" name="data_hora" class="form-control border-0 bg-light rounded-3" required value="{{ date('Y-m-d\TH:i') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase mb-1">Repetição</label>
                            <select name="repeat" class="form-select border-0 bg-light rounded-3">
                                <option value="none">Nunca</option>
                                <option value="daily">Diariamente</option>
                                <option value="weekly">Semanalmente</option>
                                <option value="monthly">Mensalmente</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pref_email" id="prefEmail" value="1">
                            <label class="form-check-label small" for="prefEmail">Receber E-mail</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pref_sound" id="prefSound" value="1" checked>
                            <label class="form-check-label small" for="prefSound">Tocar Som</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Criar Lembrete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sound Notification Check (Simple polling every minute for demonstration)
        // In a real app, you might use WebSockets (Reverb/Pusher) or a more robust service worker.
        setInterval(checkReminders, 60000);

        function checkReminders() {
            // This would ideally check against a local store or an endpoint
            // For now, we rely on the server sending emails, but for sound, we need client-side logic.
            // A simple implementation is to fetch "due" reminders via AJAX.
        }

        window.toggleStatus = function(id, el) {
            const isChecked = el.checked;
            const row = document.getElementById(`lembrete-${id}`);
            
            // Visual feedback
            if (isChecked) {
                row.classList.add('text-decoration-line-through', 'text-muted');
            } else {
                row.classList.remove('text-decoration-line-through', 'text-muted');
            }

            fetch(`{{ url('lembretes') }}/${id}?toggle_status=1`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    // Reload to reorganize lists (move from Active to Completed or vice versa)
                    // Or move DOM element manually for smoother experience
                    location.reload(); 
                }
            });
        };

        window.snooze = function(id, minutes) {
            fetch(`{{ url('lembretes') }}/${id}/snooze`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ minutes: minutes })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                }
            });
        };
    });
</script>
@endsection
