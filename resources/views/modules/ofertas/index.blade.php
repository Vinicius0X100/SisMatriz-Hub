@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Ofertas e Dízimos</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ofertas e Dízimos</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas / Filtros -->
            <form action="{{ route('ofertas.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                
                <!-- Filtro: Comunidade -->
                <div class="col-md-3">
                    <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                    <select name="ent_id" id="ent_id" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todas as Comunidades</option>
                        @foreach($entidades as $entidade)
                            <option value="{{ $entidade->ent_id }}" {{ request('ent_id') == $entidade->ent_id ? 'selected' : '' }}>
                                {{ $entidade->ent_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro: Tipo de Lançamento -->
                <div class="col-md-3">
                    <label for="kind" class="form-label fw-bold text-muted small">Tipo de Lançamento</label>
                    <select name="kind" id="kind" class="form-select rounded-pill" style="height: 45px;">
                        <option value="">Todos os Tipos</option>
                        <option value="1" {{ request('kind') == '1' ? 'selected' : '' }}>Dízimo</option>
                        <option value="2" {{ request('kind') == '2' ? 'selected' : '' }}>Oferta</option>
                        <option value="3" {{ request('kind') == '3' ? 'selected' : '' }}>Moedas</option>
                        <option value="4" {{ request('kind') == '4' ? 'selected' : '' }}>Doação em Cofre</option>
                        <option value="5" {{ request('kind') == '5' ? 'selected' : '' }}>Bazares</option>
                        <option value="6" {{ request('kind') == '6' ? 'selected' : '' }}>Vendas (Esporádicos)</option>
                    </select>
                </div>

                <!-- Filtro: Período (De - Até) -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">Período</label>
                    <div class="input-group">
                        <input type="date" name="data_inicio" class="form-control rounded-pill-start" value="{{ request('data_inicio') }}" placeholder="De" style="height: 45px;">
                        <span class="input-group-text bg-light border-start-0 border-end-0">a</span>
                        <input type="date" name="data_fim" class="form-control rounded-pill-end" value="{{ request('data_fim') }}" placeholder="Até" style="height: 45px;">
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-light border rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" title="Filtrar">
                        <i class="bi bi-funnel fs-5"></i>
                    </button>
                    <a href="{{ route('ofertas.index') }}" class="btn btn-light border rounded-pill px-3 d-flex align-items-center justify-content-center" style="height: 45px;" title="Limpar Filtros">
                        <i class="bi bi-x-lg"></i>
                    </a>
                    <a href="{{ route('ofertas.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;">
                        <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Novo</span>
                    </a>
                </div>
            </form>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">ID</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Data</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Horário</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Valor Arrecadado</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Celebração</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Tipo de Lançamento</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Comunidade</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Observações</th>
                            <th scope="col" class="text-muted small fw-bold text-uppercase">Registrado Em</th>
                            <th scope="col" class="text-end text-muted small fw-bold text-uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ofertas as $oferta)
                            <tr>
                                <td>#{{ $oferta->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($oferta->data)->format('d/m/Y') }}</td>
                                <td>{{ $oferta->horario ? \Carbon\Carbon::parse($oferta->horario)->format('H:i') : '-' }}</td>
                                <td class="fw-bold text-success">R$ {{ number_format($oferta->valor_total, 2, ',', '.') }}</td>
                                <td>{{ $oferta->tipo ?? '-' }}</td>
                                <td>
                                    @switch($oferta->kind)
                                        @case(1) <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Dízimo</span> @break
                                        @case(2) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Oferta</span> @break
                                        @case(3) <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Moedas</span> @break
                                        @case(4) <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">Doação em Cofre</span> @break
                                        @case(5) <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Bazares</span> @break
                                        @case(6) <span class="badge bg-dark bg-opacity-10 text-dark rounded-pill px-3">Vendas</span> @break
                                        @default <span class="badge bg-light text-muted border rounded-pill px-3">Outro</span>
                                    @endswitch
                                </td>
                                <td>{{ $oferta->entidade->ent_name ?? 'N/A' }}</td>
                                <td class="text-truncate" style="max-width: 150px;" title="{{ $oferta->observacoes }}">{{ $oferta->observacoes ?? '-' }}</td>
                                <td class="small text-muted">{{ $oferta->criado_em ? \Carbon\Carbon::parse($oferta->criado_em)->format('d/m/Y H:i') : '-' }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('ofertas.edit', $oferta->id) }}" class="btn btn-sm btn-light border rounded-circle" title="Editar" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('ofertas.destroy', $oferta->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este registro?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger rounded-circle" title="Excluir" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-inbox fs-1 text-muted opacity-25"></i></div>
                                    <div>Nenhum lançamento encontrado.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                <div class="text-muted small">
                    Mostrando <span class="fw-bold">{{ $ofertas->firstItem() ?? 0 }}</span> a <span class="fw-bold">{{ $ofertas->lastItem() ?? 0 }}</span> de <span class="fw-bold">{{ $ofertas->total() }}</span> registros
                </div>
                <div>
                    {{ $ofertas->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
