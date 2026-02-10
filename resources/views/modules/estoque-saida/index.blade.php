@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Saída de Estoque</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Saída de Estoque</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-box-arrow-right fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Saídas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 me-3">
                        <i class="bi bi-calendar-event fs-3 text-warning"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Este Mês</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['this_month'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-boxes fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Itens Distribuídos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['items_distributed'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form action="{{ route('estoque-saida.index') }}" method="GET">
                <div class="row g-3 mb-4 align-items-end">
                    <!-- Pesquisa -->
                    <div class="col-md-3">
                        <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="search" name="search" class="form-control ps-5 rounded-pill" placeholder="Item, Responsável..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </div>
                    
                    <!-- Filtro: Periodo -->
                    <div class="col-md-2">
                         <label for="start_date" class="form-label fw-bold text-muted small">De</label>
                         <input type="date" class="form-control rounded-pill" id="start_date" name="start_date" value="{{ request('start_date') }}" style="height: 45px;" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-2">
                         <label for="end_date" class="form-label fw-bold text-muted small">Até</label>
                         <input type="date" class="form-control rounded-pill" id="end_date" name="end_date" value="{{ request('end_date') }}" style="height: 45px;" onchange="this.form.submit()">
                    </div>

                    <!-- Filtro: Entidade -->
                    <div class="col-md-2">
                        <label for="ent_id" class="form-label fw-bold text-muted small">Comunidade</label>
                        <select id="ent_id" name="ent_id" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            @foreach($entidades as $ent)
                                <option value="{{ $ent->ent_id }}" {{ request('ent_id') == $ent->ent_id ? 'selected' : '' }}>{{ $ent->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro: Status -->
                    <div class="col-md-1">
                        <label for="status" class="form-label fw-bold text-muted small">Status</label>
                        <select id="status" name="status" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                         <button type="button" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('estoque-saida.create') }}'">
                            <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Nova</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" width="40" class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th scope="col">Item</th>
                            <th scope="col" class="text-center">Qtd.</th>
                            <th scope="col">Retirado Por</th>
                            <th scope="col">Entregue Por</th>
                            <th scope="col">Comunidade</th>
                            <th scope="col">Data Saída</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input row-checkbox" type="checkbox" value="{{ $item->id }}">
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded bg-light d-flex align-items-center justify-content-center text-secondary" style="width: 40px; height: 40px;">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->nome_item }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><span class="fw-bold">{{ $item->qntd_distribuida }}</span></td>
                                <td>{{ $item->retirado_por }}</td>
                                <td>{{ $item->entregue_por }}</td>
                                <td>{{ $item->comunidade->ent_name ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->data_saida)->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    @if($item->status == 1)
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Confirmado</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Cancelado</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex gap-1 justify-content-end">
                                        {{-- Actions can be added here if needed --}}
                                        <button class="btn btn-sm btn-light border rounded-circle" title="Detalhes">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="mb-2"><i class="bi bi-inbox fs-1"></i></div>
                                    <div>Nenhuma saída registrada.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
                <div class="text-muted small">
                    Mostrando {{ $items->firstItem() ?? 0 }} a {{ $items->lastItem() ?? 0 }} de {{ $items->total() }} registros
                </div>
                <div>
                    {{ $items->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    });
</script>
@endsection
