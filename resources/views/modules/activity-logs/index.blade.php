@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Cabeçalho e Breadcrumbs -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Logs de Atividades</h2>
            <p class="text-muted small mb-0">Trilha de auditoria e monitoramento de segurança do SisMatriz</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Logs de Atividades</li>
            </ol>
        </nav>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Total</div>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['total'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3 text-success">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Logins/Auth</div>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['auth'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3 text-info">
                        <i class="bi bi-pencil-square fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Operações CRUD</div>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['model'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3 text-secondary">
                        <i class="bi bi-compass fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Navegação</div>
                        <h4 class="mb-0 fw-bold">{{ number_format($stats['navigation'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3 text-danger">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold text-uppercase">Falhas</div>
                        <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['failures'] ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Painel de Filtros e Tabela de Logs -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Barra de Filtros -->
            <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3 mb-4">
                <!-- Pesquisa Livre -->
                <div class="col-lg-3 col-md-6">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisa Textual</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Ação, descrição, IP ou usuário..." value="{{ request('search') }}" style="height: 42px;">
                    </div>
                </div>

                <!-- Filtro de Usuário -->
                <div class="col-lg-2 col-md-6">
                    <label for="userFilter" class="form-label fw-bold text-muted small">Usuário</label>
                    <select name="user_id" id="userFilter" class="form-select rounded-pill" style="height: 42px;">
                        <option value="">Todos os Usuários</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->user }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro de Categoria -->
                <div class="col-lg-2 col-md-4 col-6">
                    <label for="categoryFilter" class="form-label fw-bold text-muted small">Categoria</label>
                    <select name="category" id="categoryFilter" class="form-select rounded-pill" style="height: 42px;">
                        <option value="">Todas as Categorias</option>
                        <option value="auth" {{ request('category') == 'auth' ? 'selected' : '' }}>Autenticação</option>
                        <option value="model" {{ request('category') == 'model' ? 'selected' : '' }}>Registros (CRUD)</option>
                        <option value="navigation" {{ request('category') == 'navigation' ? 'selected' : '' }}>Navegação</option>
                        <option value="action" {{ request('category') == 'action' ? 'selected' : '' }}>Ações de Interface</option>
                        <option value="export" {{ request('category') == 'export' ? 'selected' : '' }}>Exportação / PDF</option>
                    </select>
                </div>

                <!-- Filtro de Módulo -->
                <div class="col-lg-2 col-md-4 col-6">
                    <label for="moduleFilter" class="form-label fw-bold text-muted small">Módulo</label>
                    <select name="module" id="moduleFilter" class="form-select rounded-pill" style="height: 42px;">
                        <option value="">Todos os Módulos</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro de Status -->
                <div class="col-lg-1 col-md-4 col-6">
                    <label for="statusFilter" class="form-label fw-bold text-muted small">Status</label>
                    <select name="status" id="statusFilter" class="form-select rounded-pill" style="height: 42px;">
                        <option value="">Todos</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sucesso</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falha</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                    </select>
                </div>

                <!-- Período: Data Inicial -->
                <div class="col-lg-2 col-md-6 col-6">
                    <label for="startDate" class="form-label fw-bold text-muted small">Data Inicial</label>
                    <input type="date" name="start_date" id="startDate" class="form-control rounded-pill" value="{{ request('start_date') }}" style="height: 42px;">
                </div>

                <!-- Período: Data Final -->
                <div class="col-lg-2 col-md-6 col-6">
                    <label for="endDate" class="form-label fw-bold text-muted small">Data Final</label>
                    <input type="date" name="end_date" id="endDate" class="form-control rounded-pill" value="{{ request('end_date') }}" style="height: 42px;">
                </div>

                @if($isGlobalAdmin)
                <!-- Seletor de Paróquia (Exclusivo Superadmin 111) -->
                <div class="col-lg-3 col-md-6">
                    <label for="paroquiaFilter" class="form-label fw-bold text-muted small"><i class="bi bi-buildings me-1 text-primary"></i>Paróquia (Superadmin)</label>
                    <select name="paroquia_id" id="paroquiaFilter" class="form-select rounded-pill border-primary" style="height: 42px;">
                        <option value="all" {{ request('paroquia_id') == 'all' ? 'selected' : '' }}>🌐 Todas as Paróquias (Visão Global)</option>
                        @foreach($paroquias as $p)
                            <option value="{{ $p->id }}" {{ (request('paroquia_id') == $p->id || (!request('paroquia_id') && auth()->user()->paroquia_id == $p->id)) ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <!-- Botões de Filtrar e Limpar -->
                <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2 ms-auto">
                    <button type="submit" class="btn btn-primary rounded-pill px-3 w-100 d-flex align-items-center justify-content-center gap-1" style="height: 42px;" data-audit-action="filtro_avancado_aplicado" data-audit-module="Logs de Atividades" data-audit-desc="Aplicação de filtros avançados na listagem de logs">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 42px;" title="Limpar Filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </form>

            <!-- Tabela de Logs -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-3 py-3 border-0 text-secondary small text-uppercase fw-bold" style="width: 140px;">Data/Hora</th>
                            <th class="px-3 py-3 border-0 text-secondary small text-uppercase fw-bold" style="width: 220px;">Usuário</th>
                            <th class="px-3 py-3 border-0 text-secondary small text-uppercase fw-bold" style="width: 140px;">Módulo</th>
                            <th class="px-3 py-3 border-0 text-secondary small text-uppercase fw-bold" style="width: 110px;">Ação</th>
                            <th class="px-3 py-3 border-0 text-secondary small text-uppercase fw-bold">Descrição da Atividade</th>
                            <th class="px-3 py-3 border-0 text-secondary small text-uppercase fw-bold text-center" style="width: 100px;">Status</th>
                            <th class="px-3 py-3 border-0 text-secondary small text-uppercase fw-bold text-end" style="width: 80px;">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <!-- Data e Hora -->
                                <td class="px-3 py-3 text-nowrap">
                                    <div class="fw-semibold text-dark small">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '—' }}</div>
                                    <div class="text-muted" style="font-size: 0.78rem;">{{ $log->created_at ? $log->created_at->format('H:i:s') : '' }}</div>
                                </td>

                                <!-- Usuário -->
                                <td class="px-3 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light border text-primary fw-bold d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                            @if($log->user && $log->user->avatar && file_exists(public_path('storage/uploads/avatars/' . $log->user->avatar)))
                                                <img src="{{ asset('storage/uploads/avatars/' . $log->user->avatar) }}" class="rounded-circle w-100 h-100 object-fit-cover">
                                            @else
                                                {{ strtoupper(substr($log->user_name ?? ($log->user?->name ?? 'S'), 0, 1)) }}
                                            @endif
                                        </div>
                                        <div class="text-truncate" style="max-width: 160px;">
                                            <div class="fw-semibold text-dark text-truncate" title="{{ $log->user_name ?? ($log->user?->name ?? 'Sistema') }}">
                                                {{ $log->user_name ?? ($log->user?->name ?? 'Sistema') }}
                                            </div>
                                            <div class="text-muted small text-truncate" style="font-size: 0.75rem;">
                                                {{ $log->user?->user ?? ($log->ip_address ?? '—') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Módulo -->
                                <td class="px-3 py-3 text-nowrap">
                                    <span class="badge bg-light text-secondary border fw-medium px-2 py-1">
                                        {{ $log->module }}
                                    </span>
                                </td>

                                <!-- Ação -->
                                <td class="px-3 py-3 text-nowrap">
                                    <span class="badge {{ $log->action_badge_class }} px-2 py-1 small fw-medium">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <!-- Descrição -->
                                <td class="px-3 py-3">
                                    <div class="text-dark small text-break">{{ $log->description }}</div>
                                    @if($log->old_values || $log->new_values)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.7rem;">
                                            <i class="bi bi-diff me-1"></i>Alterações gravadas
                                        </span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="px-3 py-3 text-center text-nowrap">
                                    <span class="badge {{ $log->status_badge_class }} px-2 py-1 rounded-pill small">
                                        {{ $log->status_label }}
                                    </span>
                                </td>

                                <!-- Ações (Modal) -->
                                <td class="px-3 py-3 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle p-2" title="Visualizar Detalhes" onclick="openLogDetails({{ $log->id }})" data-audit-action="modal_aberto" data-audit-module="Logs de Atividades" data-audit-desc="Visualização detalhada e diff do log #{{ $log->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-shield-check fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        <div class="fw-semibold">Nenhum registro de atividade encontrado</div>
                                        <small>Tente ajustar os filtros de período, categoria ou pesquisa.</small>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($logs->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <div class="text-muted small">
                        Exibindo de <strong>{{ $logs->firstItem() }}</strong> a <strong>{{ $logs->lastItem() }}</strong> de <strong>{{ $logs->total() }}</strong> atividades
                    </div>
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Detalhes do Evento de Auditoria -->
<div class="modal fade" id="modalLogDetails" tabindex="-1" aria-labelledby="modalLogDetailsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="modalLogDetailsLabel">
                        <i class="bi bi-shield-shaded text-primary me-2"></i>Detalhes da Atividade #<span id="detailLogId"></span>
                    </h5>
                    <p class="text-muted small mb-0">Informações completas e dados de auditoria</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Spinner de Carregamento -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <div class="text-muted small mt-2">Carregando detalhes do log...</div>
                </div>

                <!-- Conteúdo do Log -->
                <div id="modalContent" class="d-none">
                    <!-- Resumo Geral -->
                    <div class="card bg-light border-0 rounded-3 p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small fw-bold text-uppercase">Usuário Responsável</div>
                                <div class="fw-bold text-dark fs-6" id="detailUserName"></div>
                                <div class="text-muted small" id="detailUserLogin"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-muted small fw-bold text-uppercase">Data e Horário</div>
                                <div class="fw-bold text-dark fs-6" id="detailCreatedAt"></div>
                                <div class="text-muted small">IP: <span id="detailIp"></span></div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small fw-bold text-uppercase">Módulo</div>
                                <span class="badge bg-secondary px-2 py-1 mt-1" id="detailModule"></span>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small fw-bold text-uppercase">Ação</div>
                                <span class="badge px-2 py-1 mt-1" id="detailActionBadge"></span>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small fw-bold text-uppercase">Status da Operação</div>
                                <span class="badge rounded-pill px-2 py-1 mt-1" id="detailStatusBadge"></span>
                            </div>
                            <div class="col-12 border-top pt-2 mt-2">
                                <div class="text-muted small fw-bold text-uppercase">Descrição</div>
                                <div class="text-dark" id="detailDescription"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Comparação de Alterações (Diff de Campos) -->
                    <div id="diffSection" class="mb-4 d-none">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-diff text-primary me-2"></i>Alterações Registradas nos Campos</h6>
                        <div class="table-responsive border rounded-3">
                            <table class="table table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;" class="small text-uppercase">Campo Auditado</th>
                                        <th style="width: 37.5%;" class="small text-uppercase text-danger"><i class="bi bi-dash-circle me-1"></i>Valor Anterior</th>
                                        <th style="width: 37.5%;" class="small text-uppercase text-success"><i class="bi bi-plus-circle me-1"></i>Novo Valor</th>
                                    </tr>
                                </thead>
                                <tbody id="diffTableBody">
                                    <!-- Inserido dinamicamente via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Metadados Técnicos e Rastreabilidade -->
                    <div class="accordion" id="accordionMeta">
                        <div class="accordion-item border rounded-3">
                            <h2 class="accordion-header" id="headingMeta">
                                <button class="accordion-button collapsed py-2 px-3 small fw-bold text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMeta" aria-expanded="false" aria-controls="collapseMeta">
                                    <i class="bi bi-info-circle me-2"></i>Metadados Técnicos de Auditoria
                                </button>
                            </h2>
                            <div id="collapseMeta" class="accordion-collapse collapse" aria-labelledby="headingMeta" data-bs-parent="#accordionMeta">
                                <div class="accordion-body small bg-light p-3">
                                    <div class="row g-2">
                                        <div class="col-md-6"><strong>Paróquia:</strong> <span id="detailParoquia"></span></div>
                                        <div class="col-md-6"><strong>Origem (Source):</strong> <span id="detailSource"></span></div>
                                        <div class="col-md-12"><strong>URL:</strong> <span id="detailUrl" class="text-break"></span></div>
                                        <div class="col-md-6"><strong>Método HTTP:</strong> <span id="detailMethod"></span></div>
                                        <div class="col-md-6"><strong>Entidade Afetada:</strong> <span id="detailAuditable"></span></div>
                                        <div class="col-md-12"><strong>Navegador / Dispositivo:</strong> <span id="detailUserAgent" class="text-break"></span></div>
                                        <div class="col-md-12"><strong>ID da Requisição (UUID):</strong> <code id="detailRequestId" class="user-select-all"></code></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openLogDetails(id) {
        const modal = new bootstrap.Modal(document.getElementById('modalLogDetails'));
        const loading = document.getElementById('modalLoading');
        const content = document.getElementById('modalContent');
        const diffSection = document.getElementById('diffSection');
        const diffTableBody = document.getElementById('diffTableBody');

        document.getElementById('detailLogId').textContent = id;
        loading.classList.remove('d-none');
        content.classList.add('d-none');
        diffSection.classList.add('d-none');
        diffTableBody.innerHTML = '';

        modal.show();

        fetch("{{ url('activity-logs') }}/" + id, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('Não foi possível carregar os detalhes.');
            return res.json();
        })
        .then(data => {
            document.getElementById('detailUserName').textContent = data.user_name || 'Sistema';
            document.getElementById('detailUserLogin').textContent = data.user_login ? 'Login: ' + data.user_login : '';
            document.getElementById('detailCreatedAt').textContent = data.created_at;
            document.getElementById('detailIp').textContent = data.ip_address || '—';
            document.getElementById('detailModule').textContent = data.module;
            document.getElementById('detailDescription').textContent = data.description;
            document.getElementById('detailParoquia').textContent = data.paroquia_name || '—';
            document.getElementById('detailSource').textContent = data.source || '—';
            document.getElementById('detailUrl').textContent = (data.method ? data.method + ' ' : '') + (data.url || '—');
            document.getElementById('detailMethod').textContent = data.method || '—';
            document.getElementById('detailAuditable').textContent = data.auditable_type ? data.auditable_type + ' #' + data.auditable_id : '—';
            document.getElementById('detailUserAgent').textContent = data.user_agent || '—';
            document.getElementById('detailRequestId').textContent = data.request_id || '—';

            // Badges
            const actionBadge = document.getElementById('detailActionBadge');
            actionBadge.className = 'badge px-2 py-1 mt-1 ' + (data.action_badge || 'bg-secondary');
            actionBadge.textContent = data.action;

            const statusBadge = document.getElementById('detailStatusBadge');
            statusBadge.className = 'badge rounded-pill px-2 py-1 mt-1 ' + (data.status_badge || 'bg-secondary');
            statusBadge.textContent = data.status_label || data.status;

            // Renderização do Diff de Campos
            const oldVals = data.old_values || {};
            const newVals = data.new_values || {};
            const allKeys = Array.from(new Set([...Object.keys(oldVals), ...Object.keys(newVals)]));

            if (allKeys.length > 0) {
                allKeys.forEach(key => {
                    const tr = document.createElement('tr');

                    const tdField = document.createElement('td');
                    tdField.className = 'fw-bold small text-secondary';
                    tdField.textContent = key;

                    const tdOld = document.createElement('td');
                    tdOld.className = 'small text-danger bg-danger bg-opacity-10';
                    tdOld.textContent = formatValue(oldVals[key]);

                    const tdNew = document.createElement('td');
                    tdNew.className = 'small text-success bg-success bg-opacity-10';
                    tdNew.textContent = formatValue(newVals[key]);

                    tr.appendChild(tdField);
                    tr.appendChild(tdOld);
                    tr.appendChild(tdNew);
                    diffTableBody.appendChild(tr);
                });
                diffSection.classList.remove('d-none');
            }

            loading.classList.add('d-none');
            content.classList.remove('d-none');
        })
        .catch(err => {
            loading.innerHTML = '<div class="alert alert-danger py-2">' + err.message + '</div>';
        });
    }

    function formatValue(val) {
        if (val === null || val === undefined) return '—';
        if (typeof val === 'boolean') return val ? 'Sim' : 'Não';
        if (typeof val === 'object') return JSON.stringify(val);
        return String(val);
    }
</script>
@endpush
@endsection
