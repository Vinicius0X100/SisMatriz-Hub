@extends('layouts.app')

@section('title', 'Gerenciar Fila — ' . $fila->data->format('d/m/Y'))

@section('content')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Fila de Atendimento</h2>
            <p class="text-muted mb-0">{{ $fila->data->format('d \d\e F \d\e Y') }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('atendimento-fila.index') }}" class="text-decoration-none">Fila de Atendimento</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $fila->data->format('d/m/Y') }}</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div><strong>Sucesso!</strong> {{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>{{ session('warning') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Controles da fila -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body d-flex flex-wrap align-items-center gap-3">
            <!-- Status badge -->
            @php
                $statusClass = match($fila->status) {
                    0 => 'secondary',
                    1 => 'success',
                    2 => 'dark',
                    default => 'secondary',
                };
            @endphp
            <div>
                <span class="badge bg-{{ $statusClass }} fs-6 px-3 py-2">
                    <i class="bi bi-circle-fill me-2" style="font-size:8px"></i>{{ $fila->status_label }}
                </span>
            </div>

            <div class="ms-auto d-flex gap-2 flex-wrap">
                <!-- Botão adicionar na fila -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionarItem">
                    <i class="bi bi-person-plus me-2"></i>+ Adicionar na fila
                </button>

                <!-- Controles de status da fila -->
                <a href="{{ route('atendimento-fila.painel.fila', $fila->id) }}" class="btn btn-outline-success" target="_blank">
                    <i class="bi bi-display me-2"></i>Abrir Painel do Padre
                </a>

                @if($fila->status === \App\Models\AtendimentoFila::STATUS_AGUARDANDO)
                <form action="{{ route('atendimento-fila.status', $fila->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="1">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-play-circle me-2"></i>Ativar Fila
                    </button>
                </form>
                @elseif($fila->status === \App\Models\AtendimentoFila::STATUS_ATIVA)
                <form id="formEncerrarFila" action="{{ route('atendimento-fila.status', $fila->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="status" value="2">
                    <button type="button" class="btn btn-outline-dark" onclick="abrirConfirmacaoGenerica('formEncerrarFila', 'Encerrar Fila', 'Tem certeza que deseja encerrar a fila de hoje?', 'dark')">
                        <i class="bi bi-stop-circle me-2"></i>Encerrar Fila
                    </button>
                </form>
                @endif

                <!-- Excluir -->
                <form id="formExcluirFila" action="{{ route('atendimento-fila.destroy', $fila->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-danger" title="Excluir fila" onclick="abrirConfirmacaoGenerica('formExcluirFila', 'Excluir Fila', 'Tem certeza que deseja excluir esta fila e todos os registros de hoje?', 'danger')">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Agendados -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-clock me-2 text-primary"></i>Com hora marcada
                        <span class="badge bg-primary rounded-pill ms-2">{{ $agendados->count() }}</span>
                    </h5>
                </div>
                <div class="card-body px-4">
                    @if($agendados->isEmpty())
                        <p class="text-muted text-center py-3">Nenhum agendamento ainda.</p>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($agendados as $idx => $item)
                        <div class="list-group-item px-0 py-3 border-0 border-bottom">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-2 rounded-3" style="min-width:38px;text-align:center">
                                        {{ $item->hora_agendada ? \Carbon\Carbon::parse($item->hora_agendada)->format('H:i') : '—' }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $item->nome }}</div>
                                        @if($item->assunto)
                                        <small class="text-muted">{{ $item->assunto }}</small>
                                        @endif
                                        @if($item->whatsapp_enviado)
                                        <div><span class="badge bg-success bg-opacity-10 text-success" style="font-size:10px"><i class="bi bi-whatsapp"></i> Notificado</span></div>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    @php
                                        $statusBadge = match($item->status) {
                                            0 => ['secondary', 'Aguardando'],
                                            1 => ['warning', 'Atendendo'],
                                            2 => ['success', 'Atendido'],
                                            3 => ['danger', 'Ausente'],
                                            default => ['secondary', '—'],
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge[0] }} me-2">{{ $statusBadge[1] }}</span>

                                    @if($item->status === \App\Models\AtendimentoFilaItem::STATUS_AGUARDANDO)
                                    <form action="{{ route('atendimento-fila.itens.status', [$fila->id, $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="3">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Marcar como ausente">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <form id="formRemoverItem{{$item->id}}" action="{{ route('atendimento-fila.itens.destroy', [$fila->id, $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remover da fila" onclick="abrirConfirmacaoGenerica('formRemoverItem{{$item->id}}', 'Remover da Fila', 'Tem certeza que deseja remover <b>{{ addslashes($item->nome) }}</b> da fila?', 'danger')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Walk-ins -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-person-walking me-2 text-warning"></i>Sem hora marcada (Walk-in)
                        <span class="badge bg-warning text-dark rounded-pill ms-2">{{ $walkins->count() }}</span>
                    </h5>
                </div>
                <div class="card-body px-4">
                    @if($walkins->isEmpty())
                        <p class="text-muted text-center py-3">Nenhuma pessoa na fila sem hora marcada.</p>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($walkins as $idx => $item)
                        <div class="list-group-item px-0 py-3 border-0 border-bottom">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="d-flex align-items-start gap-3">
                                    <span class="badge bg-warning bg-opacity-20 text-warning fw-bold px-2 py-2 rounded-3" style="min-width:38px;text-align:center">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold">{{ $item->nome }}</div>
                                        @if($item->assunto)
                                        <small class="text-muted">{{ $item->assunto }}</small>
                                        @endif
                                        <div><small class="text-muted">Chegou às {{ $item->created_at->format('H:i') }}</small></div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    @php
                                        $statusBadge = match($item->status) {
                                            0 => ['secondary', 'Aguardando'],
                                            1 => ['warning', 'Atendendo'],
                                            2 => ['success', 'Atendido'],
                                            3 => ['danger', 'Ausente'],
                                            default => ['secondary', '—'],
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge[0] }} me-2">{{ $statusBadge[1] }}</span>

                                    @if($item->status === \App\Models\AtendimentoFilaItem::STATUS_AGUARDANDO)
                                    <form action="{{ route('atendimento-fila.itens.status', [$fila->id, $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="3">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Marcar como ausente">
                                            <i class="bi bi-person-dash"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <form id="formRemoverWalkin{{$item->id}}" action="{{ route('atendimento-fila.itens.destroy', [$fila->id, $item->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Remover da fila" onclick="abrirConfirmacaoGenerica('formRemoverWalkin{{$item->id}}', 'Remover da Fila', 'Tem certeza que deseja remover <b>{{ addslashes($item->nome) }}</b> da fila?', 'danger')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
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

<!-- Modal Adicionar na fila -->
<div class="modal fade" id="modalAdicionarItem" tabindex="-1" aria-labelledby="modalAdicionarItemLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalAdicionarItemLabel">Adicionar na fila</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('atendimento-fila.itens.store', $fila->id) }}" method="POST" id="formAdicionarItem">
                @csrf
                <div class="modal-body">

                    <!-- Tipo -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo de atendimento</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipoWalkin" value="0" checked>
                                <label class="form-check-label" for="tipoWalkin">
                                    <i class="bi bi-person-walking me-1 text-warning"></i>Chegou agora (walk-in)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipoAgendado" value="1">
                                <label class="form-check-label" for="tipoAgendado">
                                    <i class="bi bi-clock me-1 text-primary"></i>Com hora marcada
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Busca por Nome ou CPF (só para agendado) -->
                    <div class="mb-3 d-none" id="campoCpf">
                        <label for="cpfBusca" class="form-label fw-semibold">Buscar Pessoa <small class="text-muted">(auto-preenche nome e telefone)</small></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cpfBusca" placeholder="Nome completo ou CPF" maxlength="100">
                            <button type="button" class="btn btn-outline-primary" id="btnBuscarCpf">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <div id="cpfFeedback" class="form-text"></div>
                        <input type="hidden" name="register_id" id="registerId">
                    </div>

                    <!-- Nome -->
                    <div class="mb-3">
                        <label for="modalNome" class="form-label fw-semibold">Nome <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modalNome" name="nome" required placeholder="Nome completo">
                    </div>

                    <!-- Hora agendada (só para agendado) -->
                    <div class="mb-3 d-none" id="campoHora">
                        <label for="modalHora" class="form-label fw-semibold">Hora agendada <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="modalHora" name="hora_agendada">
                    </div>

                    <!-- Assunto (opcional) -->
                    <div class="mb-3">
                        <label for="modalAssunto" class="form-label fw-semibold">Assunto <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" id="modalAssunto" name="assunto" placeholder="Ex: Pedido de bênção, casamento, confissão...">
                    </div>

                    <!-- Telefone (para agendados — WhatsApp) -->
                    <div class="mb-2 d-none" id="campoTelefone">
                        <label for="modalTelefone" class="form-label fw-semibold">Telefone / WhatsApp <small class="text-muted">(para notificação)</small></label>
                        <input type="text" class="form-control" id="modalTelefone" name="telefone" placeholder="(XX) XXXXX-XXXX">
                    </div>

                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-plus me-2"></i>Adicionar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmacao Generica -->
<div class="modal fade" id="modalConfirmacaoGenerica" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 justify-content-center position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <div id="confirmGenericIcon" class="mt-3 mb-2 text-danger">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                </div>
            </div>
            <div class="modal-body pt-0 text-center px-4">
                <h5 class="fw-bold text-dark mb-3" id="confirmGenericTitle">Confirmar</h5>
                <p class="mb-4 text-muted" id="confirmGenericMessage">Tem certeza?</p>
                
                <div class="d-flex flex-column gap-2">
                    <button type="button" id="confirmGenericBtn" class="btn btn-danger w-100 rounded-pill py-2">Sim</button>
                    <button type="button" class="btn btn-light w-100 rounded-pill py-2 text-muted fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Resultado Busca -->
<div class="modal fade" id="modalResultadoBusca" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold text-dark mb-0">Confirmar Pessoa Encontrada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 py-4">
                <div class="list-group list-group-flush" id="listaPessoasEncontradas">
                    <!-- Preenchido via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Container para Notificações Flutuantes (Toasts) -->
<div id="toastContainerFila" class="toast-container position-fixed top-0 end-0 p-3 mt-5" style="z-index: 9999;"></div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoRadios    = document.querySelectorAll('input[name="tipo"]');
    const campoCpf      = document.getElementById('campoCpf');
    const campoHora     = document.getElementById('campoHora');
    const campoTelefone = document.getElementById('campoTelefone');
    const modalHora     = document.getElementById('modalHora');
    const btnBuscarCpf  = document.getElementById('btnBuscarCpf');
    const cpfBusca      = document.getElementById('cpfBusca');
    const cpfFeedback   = document.getElementById('cpfFeedback');
    const registerId    = document.getElementById('registerId');
    const modalNome     = document.getElementById('modalNome');
    const modalTelefone = document.getElementById('modalTelefone');

    tipoRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            const isAgendado = this.value === '1';
            campoCpf.classList.toggle('d-none', !isAgendado);
            campoHora.classList.toggle('d-none', !isAgendado);
            campoTelefone.classList.toggle('d-none', !isAgendado);
            modalHora.required = isAgendado;
        });
    });

    // Busca por Nome ou CPF
    btnBuscarCpf.addEventListener('click', function () {
        const query = document.getElementById('cpfBusca').value.trim();
        const cpfFeedback = document.getElementById('cpfFeedback');
        const modalNome = document.getElementById('modalNome');
        const modalTelefone = document.getElementById('modalTelefone');
        const registerId = document.getElementById('registerId');

        if (query.length < 3) {
            cpfFeedback.textContent = 'Digite pelo menos 3 caracteres para buscar.';
            cpfFeedback.className = 'form-text text-danger';
            return;
        }

        cpfFeedback.textContent = 'Buscando...';
        cpfFeedback.className = 'form-text text-muted';
        
        btnBuscarCpf.disabled = true;
        btnBuscarCpf.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

        fetch(`{{ route('atendimento-fila.buscar-pessoa') }}?q=${encodeURIComponent(query)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(r => r.json())
        .then(data => {
            btnBuscarCpf.disabled = false;
            btnBuscarCpf.innerHTML = '<i class="bi bi-search"></i>';

            if (data.found && data.registers.length > 0) {
                const lista = document.getElementById('listaPessoasEncontradas');
                lista.innerHTML = '';
                
                data.registers.forEach(p => {
                    lista.innerHTML += `
                        <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-bottom">
                            <div>
                                <h6 class="mb-1 fw-bold text-primary">${p.nome}</h6>
                                <p class="mb-0 text-muted small mt-2">
                                    <i class="bi bi-person-vcard me-1"></i> CPF: <b>${p.cpf || 'Não informado'}</b> <br>
                                    <i class="bi bi-envelope me-1"></i> E-mail: <b>${p.email || 'Não informado'}</b> <br>
                                    <i class="bi bi-calendar me-1"></i> Nasc: <b>${p.nascimento}</b>
                                </p>
                            </div>
                            <button type="button" class="btn btn-primary rounded-pill px-4" onclick="selecionarPessoa(${p.id}, '${p.nome.replace(/'/g, "\\'")}', '${p.telefone || ''}')">Confirmar</button>
                        </div>
                    `;
                });
                
                new bootstrap.Modal(document.getElementById('modalResultadoBusca')).show();
                cpfFeedback.textContent = 'Escolha a pessoa na lista para confirmar.';
                cpfFeedback.className = 'form-text text-primary';
            } else {
                modalNome.value = '';
                modalTelefone.value = '';
                registerId.value = '';
                cpfFeedback.textContent = data.message || 'Pessoa não encontrada.';
                cpfFeedback.className = 'form-text text-danger';
            }
        })
        .catch(() => {
            btnBuscarCpf.disabled = false;
            btnBuscarCpf.innerHTML = '<i class="bi bi-search"></i>';
            cpfFeedback.textContent = 'Erro ao buscar. Tente novamente.';
            cpfFeedback.className = 'form-text text-danger';
        });
    });

        // Limpa o modal ao fechar
    document.getElementById('modalAdicionarItem').addEventListener('hidden.bs.modal', function () {
        document.getElementById('formAdicionarItem').reset();
        campoCpf.classList.add('d-none');
        campoHora.classList.add('d-none');
        campoTelefone.classList.add('d-none');
        cpfFeedback.textContent = '';
        registerId.value = '';
        modalHora.required = false;
    });

    let currentFormIdToSubmit = null;
    
    window.abrirConfirmacaoGenerica = function(formId, titulo, mensagem, corBtn) {
        currentFormIdToSubmit = formId;
        
        document.getElementById('confirmGenericTitle').textContent = titulo;
        document.getElementById('confirmGenericMessage').innerHTML = mensagem;
        
        const btn = document.getElementById('confirmGenericBtn');
        btn.className = `btn btn-${corBtn} w-100 rounded-pill py-2`;
        
        const icon = document.getElementById('confirmGenericIcon');
        icon.className = `mt-3 mb-2 text-${corBtn}`;
        
        new bootstrap.Modal(document.getElementById('modalConfirmacaoGenerica')).show();
    };

    document.getElementById('confirmGenericBtn').addEventListener('click', function() {
        if (currentFormIdToSubmit) {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Aguarde...';
            document.getElementById(currentFormIdToSubmit).submit();
        }
    });

    window.selecionarPessoa = function(id, nome, telefone) {
        document.getElementById('registerId').value = id;
        document.getElementById('modalNome').value = nome;
        document.getElementById('modalTelefone').value = telefone;
        
        bootstrap.Modal.getInstance(document.getElementById('modalResultadoBusca')).hide();
        const cpfFeedback = document.getElementById('cpfFeedback');
        cpfFeedback.textContent = '✓ Pessoa confirmada e selecionada.';
        cpfFeedback.className = 'form-text text-success fw-bold';
    };

    // Spinner Global em Form Submit
    document.addEventListener('submit', function(e) {
        const form = e.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.disabled) {
            submitBtn.disabled = true;
            const originalText = submitBtn.innerHTML;
            submitBtn.setAttribute('data-original-text', originalText);
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Aguarde...';
        }
    });
});
</script>

<script>
    const pusherKey = "{{ env('PUSHER_APP_KEY', '') }}";
    if (pusherKey && typeof window.Echo !== 'undefined') {
        const echo = new window.Echo({
            broadcaster: 'pusher',
            key: pusherKey,
            cluster: "{{ env('PUSHER_APP_CLUSTER', 'mt1') }}",
            forceTLS: true,
        });
        echo.channel(`paroquia.{{ $fila->paroquia_id }}.fila`)
            .listen('.fila.atualizada', (e) => {
                if (e.fila_id === {{ $fila->id }}) {
                    const modal = document.getElementById('modalAdicionarItem');
                    const isModalOpen = modal && modal.classList.contains('show');

                    if (e.acao === 'proximo_chamado' && e.mensagem) {
                        // 1. Toca som de notificação (beep)
                        const audio = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg');
                        audio.play().catch(err => console.warn('Autoplay sound blocked:', err));

                        // 2. Mostra o balão Toast na tela da secretária
                        const toastContainer = document.getElementById('toastContainerFila');
                        if (toastContainer) {
                            const toastId = 'toast-' + Date.now();
                            const toastHtml = `
                                <div id="${toastId}" class="toast align-items-center text-bg-success border-0 mb-3 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
                                    <div class="d-flex px-2 py-1">
                                        <div class="toast-body fw-bold fs-5 d-flex align-items-center gap-2">
                                            <i class="bi bi-megaphone-fill fs-3"></i> 
                                            <span>${e.mensagem}</span>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                </div>
                            `;
                            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                            const toastElement = document.getElementById(toastId);
                            const toast = new bootstrap.Toast(toastElement);
                            toast.show();
                        }

                        // 3. Aguarda 4.5 segundos para a pessoa ler e fecha/recarrega a tela (para atualizar a navbar nativamente)
                        setTimeout(() => {
                            if (!isModalOpen) window.location.reload();
                        }, 4500);

                    } else {
                        // Atualização comum de status (ex: fila foi fechada, alguem adicionado)
                        if (!isModalOpen) window.location.reload();
                    }
                }
            });
    }
</script>
@endpush
