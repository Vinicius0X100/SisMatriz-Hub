@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Festas e Eventos</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Festas e Eventos</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-balloon-heart fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Festas/Eventos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('festas-eventos.index') }}" method="GET">
                <div class="row g-3 mb-4 align-items-end">
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" id="search" class="form-control ps-5 rounded-pill" placeholder="Título ou descrição..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="comunidade_id" class="form-label fw-bold text-muted small">Comunidade</label>
                        <select name="comunidade_id" id="comunidade_id" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}" {{ request('comunidade_id') == $entidade->ent_id ? 'selected' : '' }}>
                                    {{ $entidade->ent_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createFestaModal">
                            <i class="bi bi-plus-lg me-2"></i>Nova Festa/Evento
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start-3 ps-4">Título</th>
                            <th class="border-0">Comunidade</th>
                            <th class="border-0">Período</th>
                            <th class="border-0">Meta</th>
                            <th class="border-0 rounded-end-3 text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($festas as $festa)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $festa->titulo }}</td>
                            <td class="text-muted small">{{ $festa->comunidade->ent_name ?? '-' }}</td>
                            <td>
                                <span class="text-muted small">
                                    {{ $festa->data_inicio ? $festa->data_inicio->format('d/m/Y') : '-' }}
                                    @if($festa->data_fim)
                                        até {{ $festa->data_fim->format('d/m/Y') }}
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($festa->meta)
                                    <span class="badge bg-light text-success border">
                                        Meta: R$ {{ number_format($festa->meta, 2, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-muted small">Sem meta</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill btn-manage-festa" data-id="{{ $festa->id }}" data-bs-toggle="modal" data-bs-target="#manageFestaModal">
                                        <i class="bi bi-kanban"></i> Gerenciar
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editFestaModal{{ $festa->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('festas-eventos.destroy', $festa->id) }}" method="POST" class="form-delete-festa" data-festa-name="{{ $festa->titulo }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <div class="modal fade" id="editFestaModal{{ $festa->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('festas-eventos.update', $festa->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Editar Festa/Evento</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Título</label>
                                                <input type="text" name="titulo" class="form-control" value="{{ $festa->titulo }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Comunidade</label>
                                                <select name="comunidade_id" class="form-select" required>
                                                    @foreach($entidades as $entidade)
                                                        <option value="{{ $entidade->ent_id }}" {{ $festa->comunidade_id == $entidade->ent_id ? 'selected' : '' }}>
                                                            {{ $entidade->ent_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Data Início</label>
                                                    <input type="date" name="data_inicio" class="form-control" value="{{ $festa->data_inicio ? $festa->data_inicio->format('Y-m-d') : '' }}">
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Data Fim</label>
                                                    <input type="date" name="data_fim" class="form-control" value="{{ $festa->data_fim ? $festa->data_fim->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Meta de Arrecadação (opcional)</label>
                                                <input type="number" step="0.01" min="0" name="meta" class="form-control" value="{{ $festa->meta }}">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Descrição</label>
                                                <textarea name="descricao" class="form-control" rows="3">{{ $festa->descricao }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                    <p class="mb-0">Nenhuma festa/evento cadastrado.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $festas->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createFestaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('festas-eventos.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Nova Festa/Evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Título</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comunidade</label>
                        <select name="comunidade_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Meta de Arrecadação (opcional)</label>
                        <input type="number" step="0.01" min="0" name="meta" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Criar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="manageFestaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-center">
                <div class="me-3">
                    <h5 class="modal-title fw-bold" id="manageFestaTitle"></h5>
                    <p class="mb-0 text-muted small" id="manageFestaSubtitle"></p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a id="manageExportPdf" href="#" target="_blank" class="btn btn-outline-secondary rounded-pill">
                        <i class="bi bi-filetype-pdf"></i> Exportar PDF
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-arrow-down-circle text-success fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase mb-1">Entradas</div>
                                    <div class="h5 mb-0 fw-bold text-success" id="manageTotalEntradas">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-danger bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-arrow-up-circle text-danger fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase mb-1">Saídas</div>
                                    <div class="h5 mb-0 fw-bold text-danger" id="manageTotalSaidas">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-calculator text-primary fs-4"></i>
                                </div>
                                <div>
                                    <div class="text-muted small fw-bold text-uppercase mb-1">Saldo</div>
                                    <div class="h5 mb-0 fw-bold" id="manageSaldo">R$ 0,00</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <h6 class="text-muted small text-uppercase fw-bold mb-3">Fluxo Financeiro</h6>
                                <div class="chart-box">
                                    <canvas id="manageLineChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body">
                                <h6 class="text-muted small text-uppercase fw-bold mb-3">Entradas vs Saídas</h6>
                                <div class="chart-box">
                                    <canvas id="managePieChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <ul class="nav nav-pills nav-fill bg-light rounded-pill p-1" id="manageFestaTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill" id="tab-entradas-tab" data-bs-toggle="tab" data-bs-target="#tab-entradas" type="button" role="tab" aria-selected="true">Entradas (dinheiro)</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill" id="tab-saidas-tab" data-bs-toggle="tab" data-bs-target="#tab-saidas" type="button" role="tab" aria-selected="false">Saídas (dinheiro)</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill" id="tab-itens-entrada-tab" data-bs-toggle="tab" data-bs-target="#tab-itens-entrada" type="button" role="tab" aria-selected="false">Itens de entrada</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill" id="tab-itens-saida-tab" data-bs-toggle="tab" data-bs-target="#tab-itens-saida" type="button" role="tab" aria-selected="false">Itens de saída</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill" id="tab-combined-tab" data-bs-toggle="tab" data-bs-target="#tab-combined" type="button" role="tab" aria-selected="false">Movimentações (tudo)</button>
                            </li>
                        </ul>
                        <div class="tab-content pt-3">
                            <div class="tab-pane fade show active" id="tab-entradas" role="tabpanel">
                                <form id="formEntrada" class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <input type="date" name="data" class="form-control" id="entradaData" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="valor" class="form-control currency-input" placeholder="R$ 0,00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="descricao" class="form-control" placeholder="Descrição">
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary rounded-pill">Adicionar</button>
                                    </div>
                                </form>
                                <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                                    <table class="table table-sm table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Valor</th>
                                                <th>Descrição</th>
                                                <th class="text-end">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="entradasTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-saidas" role="tabpanel">
                                <form id="formSaida" class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <input type="date" name="data" class="form-control" id="saidaData" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="valor" class="form-control currency-input" placeholder="R$ 0,00" required>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="text" name="descricao" class="form-control" placeholder="Descrição">
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary rounded-pill">Adicionar</button>
                                    </div>
                                </form>
                                <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                                    <table class="table table-sm table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Valor</th>
                                                <th>Descrição</th>
                                                <th class="text-end">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="saidasTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-itens-entrada" role="tabpanel">
                                <form id="formItemEntrada" class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <input type="date" name="data" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="item" class="form-control" placeholder="Item" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="quantidade" min="1" class="form-control" placeholder="Qtde" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="observacao" class="form-control" placeholder="Observação">
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary rounded-pill">Adicionar</button>
                                    </div>
                                </form>
                                <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                                    <table class="table table-sm table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Item</th>
                                                <th>Quantidade</th>
                                                <th>Observação</th>
                                                <th class="text-end">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itensEntradaTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-itens-saida" role="tabpanel">
                                <form id="formItemSaida" class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <input type="date" name="data" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="text" name="item" class="form-control" placeholder="Item" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="quantidade" min="1" class="form-control" placeholder="Qtde" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="text" name="observacao" class="form-control" placeholder="Observação">
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary rounded-pill">Adicionar</button>
                                    </div>
                                </form>
                                <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                                    <table class="table table-sm table-striped table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Data</th>
                                                <th>Item</th>
                                                <th>Quantidade</th>
                                                <th>Observação</th>
                                                <th class="text-end">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itensSaidaTableBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab-combined" role="tabpanel">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body">
                                        <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                            <table class="table table-striped table-hover align-middle">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Tipo</th>
                                                        <th>Data</th>
                                                        <th>Valor/Qtde</th>
                                                        <th>Detalhe</th>
                                                        <th class="text-end">Ações</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="combinedTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteFestaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Confirmar exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="confirmDeleteFestaText"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill" id="confirmDeleteFestaButton">Excluir</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmDeleteMovModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Confirmar exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="confirmDeleteMovText"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill" id="confirmDeleteMovButton">Excluir</button>
            </div>
        </div>
    </div>
</div>

<style>
    .chart-box { height: 180px; }
    @media (min-width: 992px) { .chart-box { height: 200px; } }
    #manageFestaTabs .nav-link { font-size: .875rem; padding: .5rem .75rem; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var manageModal = document.getElementById('manageFestaModal');
        var manageButtons = document.querySelectorAll('.btn-manage-festa');
        var endpointBase = "{{ url('/festas-eventos') }}";
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        var titleEl = document.getElementById('manageFestaTitle');
        var subtitleEl = document.getElementById('manageFestaSubtitle');
        var totalEntradasEl = document.getElementById('manageTotalEntradas');
        var totalSaidasEl = document.getElementById('manageTotalSaidas');
        var saldoEl = document.getElementById('manageSaldo');
        var exportPdfBtn = document.getElementById('manageExportPdf');

        var entradasBody = document.getElementById('entradasTableBody');
        var saidasBody = document.getElementById('saidasTableBody');
        var itensEntradaBody = document.getElementById('itensEntradaTableBody');
        var itensSaidaBody = document.getElementById('itensSaidaTableBody');
        var combinedBody = document.getElementById('combinedTableBody');

        var formEntrada = document.getElementById('formEntrada');
        var formSaida = document.getElementById('formSaida');
        var formItemEntrada = document.getElementById('formItemEntrada');
        var formItemSaida = document.getElementById('formItemSaida');

        var lineChartInstance = null;
        var pieChartInstance = null;

        var confirmFestaModalEl = document.getElementById('confirmDeleteFestaModal');
        var confirmFestaModal = confirmFestaModalEl ? new bootstrap.Modal(confirmFestaModalEl) : null;
        var confirmFestaText = document.getElementById('confirmDeleteFestaText');
        var confirmFestaButton = document.getElementById('confirmDeleteFestaButton');
        var currentDeleteFestaForm = null;

        var confirmMovModalEl = document.getElementById('confirmDeleteMovModal');
        var confirmMovModal = confirmMovModalEl ? new bootstrap.Modal(confirmMovModalEl) : null;
        var confirmMovText = document.getElementById('confirmDeleteMovText');
        var confirmMovButton = document.getElementById('confirmDeleteMovButton');
        var currentDeleteMov = null;

        function formatCurrency(value) {
            var number = parseFloat(value || 0);
            if (isNaN(number)) {
                number = 0;
            }
            return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function maskCurrencyInput(el) {
            var raw = el.value || '';
            raw = raw.replace(/[^\d]/g, '');
            if (raw.length === 0) {
                el.value = '';
                return;
            }
            var number = parseFloat(raw) / 100;
            el.value = number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function unmaskCurrency(str) {
            if (!str) return '0.00';
            var s = String(str);
            s = s.replace(/\s/g, '').replace(/R\$/g, '');
            s = s.replace(/\./g, '').replace(/,/g, '.');
            var num = parseFloat(s);
            if (isNaN(num)) num = 0;
            return num.toFixed(2);
        }

        function initCurrencyMasks() {
            document.querySelectorAll('.currency-input').forEach(function(el) {
                el.addEventListener('input', function() {
                    maskCurrencyInput(el);
                });
                el.addEventListener('blur', function() {
                    maskCurrencyInput(el);
                });
                el.addEventListener('focus', function() {
                    if (!el.value) el.value = '';
                });
            });
        }

        function formatDate(value) {
            if (!value) {
                return '-';
            }
            var date = new Date(value);
            if (isNaN(date.getTime())) {
                return value;
            }
            return date.toLocaleDateString('pt-BR');
        }

        function clearTables() {
            entradasBody.innerHTML = '';
            saidasBody.innerHTML = '';
            itensEntradaBody.innerHTML = '';
            itensSaidaBody.innerHTML = '';
            combinedBody.innerHTML = '';
        }

        function loadFestaData(id) {
            clearTables();
            titleEl.textContent = 'Carregando...';
            subtitleEl.textContent = '';
            totalEntradasEl.textContent = 'R$ 0,00';
            totalSaidasEl.textContent = 'R$ 0,00';
            saldoEl.textContent = 'R$ 0,00';

            fetch(endpointBase + '/' + id)
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('Erro ao carregar dados');
                    }
                    return response.json();
                })
                .then(function(data) {
                    manageModal.setAttribute('data-festa-id', id);

                    var festa = data.festa || {};
                    var comunidade = festa.comunidade || festa.entidade || null;

                    titleEl.textContent = festa.titulo || 'Festa/Evento';

                    var periodParts = [];
                    if (festa.data_inicio) {
                        periodParts.push('Início: ' + formatDate(festa.data_inicio));
                    }
                    if (festa.data_fim) {
                        periodParts.push('Fim: ' + formatDate(festa.data_fim));
                    }

                    var subtitlePieces = [];
                    if (comunidade && comunidade.ent_name) {
                        subtitlePieces.push(comunidade.ent_name);
                    }
                    if (periodParts.length) {
                        subtitlePieces.push(periodParts.join(' | '));
                    }
                    subtitleEl.textContent = subtitlePieces.join(' • ');

                    if (data.totais) {
                        totalEntradasEl.textContent = formatCurrency(data.totais.entradas || 0);
                        totalSaidasEl.textContent = formatCurrency(data.totais.saidas || 0);
                        saldoEl.textContent = formatCurrency(data.totais.saldo_financeiro || 0);
                        if ((data.totais.saldo_financeiro || 0) < 0) {
                            saldoEl.classList.add('text-danger');
                            saldoEl.classList.remove('text-success');
                        } else {
                            saldoEl.classList.add('text-success');
                            saldoEl.classList.remove('text-danger');
                        }
                    }

                    (data.entradas || []).forEach(function(item) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + formatDate(item.data) + '</td>' +
                            '<td>' + formatCurrency(item.valor) + '</td>' +
                            '<td>' + (item.descricao || '') + '</td>' +
                            '<td class="text-end">' +
                                '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete-mov" data-type="entrada" data-id="' + item.id + '">' +
                                    '<i class="bi bi-trash"></i>' +
                                '</button>' +
                            '</td>';
                        entradasBody.appendChild(tr);
                    });
 
                    (data.saidas || []).forEach(function(item) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + formatDate(item.data) + '</td>' +
                            '<td>' + formatCurrency(item.valor) + '</td>' +
                            '<td>' + (item.descricao || '') + '</td>' +
                            '<td class="text-end">' +
                                '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete-mov" data-type="saida" data-id="' + item.id + '">' +
                                    '<i class="bi bi-trash"></i>' +
                                '</button>' +
                            '</td>';
                        saidasBody.appendChild(tr);
                    });
 
                    (data.itens_entrada || []).forEach(function(item) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + formatDate(item.data) + '</td>' +
                            '<td>' + (item.item || '') + '</td>' +
                            '<td>' + (item.quantidade || '') + '</td>' +
                            '<td>' + (item.observacao || '') + '</td>' +
                            '<td class="text-end">' +
                                '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete-mov" data-type="item_entrada" data-id="' + item.id + '">' +
                                    '<i class="bi bi-trash"></i>' +
                                '</button>' +
                            '</td>';
                        itensEntradaBody.appendChild(tr);
                    });
 
                    (data.itens_saida || []).forEach(function(item) {
                        var tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + formatDate(item.data) + '</td>' +
                            '<td>' + (item.item || '') + '</td>' +
                            '<td>' + (item.quantidade || '') + '</td>' +
                            '<td>' + (item.observacao || '') + '</td>' +
                            '<td class="text-end">' +
                                '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete-mov" data-type="item_saida" data-id="' + item.id + '">' +
                                    '<i class="bi bi-trash"></i>' +
                                '</button>' +
                            '</td>';
                        itensSaidaBody.appendChild(tr);
                    });

                    renderCombinedTable(data);
                    renderCharts(data);
                    if (exportPdfBtn) {
                        exportPdfBtn.setAttribute('href', endpointBase + '/' + id + '/report');
                    }
                })
                .catch(function() {
                    titleEl.textContent = 'Erro ao carregar dados';
                    subtitleEl.textContent = '';
                });
        }

        function renderCombinedTable(data) {
            var rows = [];
 
            (data.entradas || []).forEach(function(e) {
                rows.push({
                    tipo: 'Entrada',
                    source: 'entrada',
                    id: e.id,
                    data: e.data,
                    valor: e.valor,
                    detalhe: e.descricao || ''
                });
            });
            (data.saidas || []).forEach(function(s) {
                rows.push({
                    tipo: 'Saída',
                    source: 'saida',
                    id: s.id,
                    data: s.data,
                    valor: s.valor,
                    detalhe: s.descricao || ''
                });
            });
            (data.itens_entrada || []).forEach(function(ie) {
                rows.push({
                    tipo: 'Item Entrada',
                    source: 'item_entrada',
                    id: ie.id,
                    data: ie.data,
                    valor: ie.quantidade,
                    detalhe: (ie.item || '') + (ie.observacao ? ' • ' + ie.observacao : '')
                });
            });
            (data.itens_saida || []).forEach(function(is) {
                rows.push({
                    tipo: 'Item Saída',
                    source: 'item_saida',
                    id: is.id,
                    data: is.data,
                    valor: is.quantidade,
                    detalhe: (is.item || '') + (is.observacao ? ' • ' + is.observacao : '')
                });
            });

            rows.sort(function(a, b) {
                var da = a.data ? new Date(a.data).getTime() : 0;
                var db = b.data ? new Date(b.data).getTime() : 0;
                return db - da;
            });

            rows.forEach(function(r) {
                var tr = document.createElement('tr');
                var valorDisplay = (r.tipo === 'Entrada' || r.tipo === 'Saída') ? formatCurrency(r.valor) : (r.valor || '');
                var badgeClass = 'bg-secondary';
                if (r.tipo === 'Entrada') badgeClass = 'bg-success';
                else if (r.tipo === 'Saída') badgeClass = 'bg-danger';
                tr.innerHTML =
                    '<td><span class="badge ' + badgeClass + ' bg-opacity-10 text-dark border">' + r.tipo + '</span></td>' +
                    '<td>' + formatDate(r.data) + '</td>' +
                    '<td>' + valorDisplay + '</td>' +
                    '<td>' + r.detalhe + '</td>' +
                    '<td class="text-end">' +
                        '<button type="button" class="btn btn-sm btn-outline-danger rounded-pill btn-delete-mov" data-type="' + r.source + '" data-id="' + r.id + '">' +
                            '<i class="bi bi-trash"></i>' +
                        '</button>' +
                    '</td>';
                combinedBody.appendChild(tr);
            });
        }

        function renderCharts(data) {
            var lineCtx = document.getElementById('manageLineChart');
            var pieCtx = document.getElementById('managePieChart');

            var mapEntradas = {};
            var mapSaidas = {};
            var labelsSet = {};

            (data.entradas || []).forEach(function(e) {
                var key = e.data;
                labelsSet[key] = true;
                mapEntradas[key] = (mapEntradas[key] || 0) + parseFloat(e.valor || 0);
            });
            (data.saidas || []).forEach(function(s) {
                var key = s.data;
                labelsSet[key] = true;
                mapSaidas[key] = (mapSaidas[key] || 0) + parseFloat(s.valor || 0);
            });

            var labels = Object.keys(labelsSet).sort(function(a, b) {
                return new Date(a).getTime() - new Date(b).getTime();
            }).map(function(d) {
                return formatDate(d);
            });

            var entradasData = labels.map(function(label, idx) {
                var origDate = Object.keys(labelsSet).sort(function(a, b) {
                    return new Date(a).getTime() - new Date(b).getTime();
                })[idx];
                return mapEntradas[origDate] || 0;
            });
            var saidasData = labels.map(function(label, idx) {
                var origDate = Object.keys(labelsSet).sort(function(a, b) {
                    return new Date(a).getTime() - new Date(b).getTime();
                })[idx];
                return mapSaidas[origDate] || 0;
            });

            var totalEntradas = (data.totais && data.totais.entradas) ? data.totais.entradas : 0;
            var totalSaidas = (data.totais && data.totais.saidas) ? data.totais.saidas : 0;

            if (lineChartInstance) {
                lineChartInstance.destroy();
            }
            if (pieChartInstance) {
                pieChartInstance.destroy();
            }

            if (lineCtx) {
                lineChartInstance = new Chart(lineCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Entradas',
                                data: entradasData,
                                borderColor: '#198754',
                                backgroundColor: 'rgba(25, 135, 84, 0.12)',
                                tension: 0.3,
                                fill: true
                            },
                            {
                                label: 'Saídas',
                                data: saidasData,
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220, 53, 69, 0.12)',
                                tension: 0.3,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            if (pieCtx) {
                pieChartInstance = new Chart(pieCtx.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: ['Entradas', 'Saídas'],
                        datasets: [{
                            data: [totalEntradas, totalSaidas],
                            backgroundColor: ['#198754', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        }
                    }
                });
            }
        }

        manageButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var id = this.getAttribute('data-id');
                if (id) {
                    loadFestaData(id);
                    var entradaData = document.getElementById('entradaData');
                    var saidaData = document.getElementById('saidaData');
                    var today = new Date().toISOString().slice(0, 10);
                    if (entradaData && !entradaData.value) {
                        entradaData.value = today;
                    }
                    if (saidaData && !saidaData.value) {
                        saidaData.value = today;
                    }
                }
            });
        });

        var deleteFestaForms = document.querySelectorAll('.form-delete-festa');
        deleteFestaForms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                currentDeleteFestaForm = form;
                var name = form.getAttribute('data-festa-name') || '';
                if (confirmFestaText) {
                    confirmFestaText.textContent = name
                        ? 'Deseja realmente excluir a festa/evento "' + name + '" e todas as suas movimentações?'
                        : 'Deseja realmente excluir esta festa/evento e todas as suas movimentações?';
                }
                if (confirmFestaModal) {
                    confirmFestaModal.show();
                }
            });
        });

        if (confirmFestaButton) {
            confirmFestaButton.addEventListener('click', function() {
                if (currentDeleteFestaForm) {
                    currentDeleteFestaForm.submit();
                    currentDeleteFestaForm = null;
                }
            });
        }

        function postForm(form, path) {
            var festaId = manageModal.getAttribute('data-festa-id');
            if (!festaId) {
                return;
            }
            var formData = new FormData(form);
            var currencyInput = form.querySelector('.currency-input[name="valor"]');
            if (currencyInput) {
                var normalized = unmaskCurrency(currencyInput.value);
                formData.set('valor', normalized);
            }
            fetch(endpointBase + '/' + festaId + path, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
                .then(function(response) {
                    if (!response.ok) {
                        return response.json().then(function(err) {
                            var message = err.message || 'Erro ao salvar';
                            alert(message);
                            throw new Error(message);
                        }).catch(function() {
                            alert('Erro ao salvar');
                            throw new Error('Erro ao salvar');
                        });
                    }
                    return response.json();
                })
                .then(function() {
                    form.reset();
                    initCurrencyMasks();
                    loadFestaData(festaId);
                })
                .catch(function() {});
        }

        if (formEntrada) {
            formEntrada.addEventListener('submit', function(e) {
                e.preventDefault();
                postForm(formEntrada, '/entradas');
            });
        }

        if (formSaida) {
            formSaida.addEventListener('submit', function(e) {
                e.preventDefault();
                postForm(formSaida, '/saidas');
            });
        }

        if (formItemEntrada) {
            formItemEntrada.addEventListener('submit', function(e) {
                e.preventDefault();
                postForm(formItemEntrada, '/itens-entrada');
            });
        }

        if (formItemSaida) {
            formItemSaida.addEventListener('submit', function(e) {
                e.preventDefault();
                postForm(formItemSaida, '/itens-saida');
            });
        }
        initCurrencyMasks();

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-delete-mov');
            if (!btn) {
                return;
            }
            e.preventDefault();
            var type = btn.getAttribute('data-type');
            var id = btn.getAttribute('data-id');
            if (!type || !id) {
                return;
            }
            currentDeleteMov = { type: type, id: id };
            if (confirmMovText) {
                var label = '';
                if (type === 'entrada') label = 'entrada';
                else if (type === 'saida') label = 'saída';
                else if (type === 'item_entrada') label = 'item de entrada';
                else if (type === 'item_saida') label = 'item de saída';
                else label = 'registro';
                confirmMovText.textContent = 'Deseja realmente excluir esta ' + label + '?';
            }
            if (confirmMovModal) {
                confirmMovModal.show();
            }
        });

        if (confirmMovButton) {
            confirmMovButton.addEventListener('click', function() {
                if (!currentDeleteMov) {
                    return;
                }
                var festaId = manageModal.getAttribute('data-festa-id');
                var url = null;
                if (currentDeleteMov.type === 'entrada') {
                    url = "{{ url('festas-eventos/entradas') }}/" + currentDeleteMov.id;
                } else if (currentDeleteMov.type === 'saida') {
                    url = "{{ url('festas-eventos/saidas') }}/" + currentDeleteMov.id;
                } else if (currentDeleteMov.type === 'item_entrada') {
                    url = "{{ url('festas-eventos/itens-entrada') }}/" + currentDeleteMov.id;
                } else if (currentDeleteMov.type === 'item_saida') {
                    url = "{{ url('festas-eventos/itens-saida') }}/" + currentDeleteMov.id;
                }
                if (!url) {
                    return;
                }
                fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error('Erro ao excluir');
                        }
                        return response.json();
                    })
                    .then(function() {
                        if (confirmMovModal) {
                            confirmMovModal.hide();
                        }
                        currentDeleteMov = null;
                        if (festaId) {
                            loadFestaData(festaId);
                        }
                    })
                    .catch(function() {
                        alert('Erro ao excluir registro');
                    });
            });
        }
    });
</script>
@endsection
