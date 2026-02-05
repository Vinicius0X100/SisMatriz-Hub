@extends('layouts.app')

@section('title', 'Solicitações à Pascom')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Solicitações à Pascom</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Pascom</li>
            </ol>
        </nav>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <strong>Sucesso!</strong> {{ session('success') }}
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form action="{{ route('solicitacoes-pascom.index') }}" method="GET" id="searchForm" class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-6">
                    <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control ps-5 rounded-pill" placeholder="Nome, pastoral, telefone..." style="height: 45px;" oninput="debounceSearch()">
                    </div>
                </div>
                <!-- Data Início -->
                <div class="col-md-2">
                    <label for="data_inicio" class="form-label fw-bold text-muted small">De</label>
                    <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" class="form-control rounded-pill" style="height: 45px;" onchange="document.getElementById('searchForm').submit()">
                </div>
                <!-- Data Fim -->
                <div class="col-md-2">
                    <label for="data_fim" class="form-label fw-bold text-muted small">Até</label>
                    <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" class="form-control rounded-pill" style="height: 45px;" onchange="document.getElementById('searchForm').submit()">
                </div>
                <!-- Limpar -->
                <div class="col-md-2">
                    <a href="{{ route('solicitacoes-pascom.index') }}" class="btn btn-outline-secondary rounded-pill w-100 fw-bold" style="height: 45px; display: flex; align-items: center; justify-content: center;">
                        Limpar
                    </a>
                </div>
            </form>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start-pill py-3 ps-4">ID</th>
                            <th class="border-0 py-3">Nome</th>
                            <th class="border-0 py-3">Telefone</th>
                            <th class="border-0 py-3">Cargo</th>
                            <th class="border-0 py-3">Serviço</th>
                            <th class="border-0 py-3">Pastoral</th>
                            <th class="border-0 py-3">Descrição</th>
                            <th class="border-0 py-3">Data</th>
                            <th class="border-0 rounded-end-pill py-3 text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">#{{ $record->id }}</td>
                            <td class="fw-bold">{{ $record->nome }}</td>
                            <td>{{ $record->phone }}</td>
                            <td>
                                @if($record->cargo == 1)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill">Coordenador</span>
                                @elseif($record->cargo == 2)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill">Vice-Coordenador</span>
                                @else
                                    {{ $record->cargo }}
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-2">
                                    {{ $record->service }}
                                </span>
                            </td>
                            <td>{{ $record->pastoral }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $record->description }}">
                                    {{ $record->description }}
                                </span>
                            </td>
                            <td>{{ $record->created_at ? $record->created_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Detalhes" 
                                    data-record="{{ json_encode($record) }}"
                                    onclick="showDetails(this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" title="Remover" 
                                    onclick="confirmDelete('{{ route('solicitacoes-pascom.destroy', $record->id) }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center opacity-50">
                                    <i class="bi bi-inbox fs-1 mb-3"></i>
                                    <p class="mb-0 fw-bold">Nenhuma solicitação encontrada</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="mt-4">
                {{ $records->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalhes -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Detalhes da Solicitação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Nome</label>
                    <div class="fw-bold fs-5" id="detailNome"></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Telefone</label>
                        <div id="detailPhone"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Data</label>
                        <div id="detailDate"></div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Cargo</label>
                        <div id="detailCargo"></div>
                    </div>
                    <div class="col-6">
                        <label class="small text-muted text-uppercase fw-bold">Pastoral</label>
                        <div id="detailPastoral"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold">Serviço Solicitado</label>
                    <div><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-2" id="detailService"></span></div>
                </div>
                <div class="mb-0">
                    <label class="small text-muted text-uppercase fw-bold">Descrição Detalhada</label>
                    <div class="p-3 bg-light rounded-3 mt-1" id="detailDescription" style="white-space: pre-wrap;"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmação Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body p-4 text-center">
                <div class="mb-3 text-danger">
                    <i class="bi bi-exclamation-circle display-1"></i>
                </div>
                <h5 class="fw-bold mb-2">Tem certeza?</h5>
                <p class="text-muted mb-4">Esta ação não poderá ser desfeita.</p>
                <div class="d-grid gap-2">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger rounded-pill w-100 fw-bold">Sim, excluir</button>
                    </form>
                    <button type="button" class="btn btn-light rounded-pill w-100 fw-bold" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let timeout = null;
    function debounceSearch() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            document.getElementById('searchForm').submit();
        }, 500);
    }

    function showDetails(button) {
        const record = JSON.parse(button.getAttribute('data-record'));
        document.getElementById('detailNome').innerText = record.nome;
        document.getElementById('detailPhone').innerText = record.phone;
        
        // Format Date
        const date = new Date(record.created_at);
        document.getElementById('detailDate').innerText = date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', {hour: '2-digit', minute:'2-digit'});
        
        // Map Cargo
        let cargoText = record.cargo;
        if (record.cargo == 1) cargoText = 'Coordenador';
        else if (record.cargo == 2) cargoText = 'Vice-Coordenador';
        document.getElementById('detailCargo').innerText = cargoText;
        
        document.getElementById('detailPastoral').innerText = record.pastoral;
        document.getElementById('detailService').innerText = record.service;
        document.getElementById('detailDescription').innerText = record.description;
        
        new bootstrap.Modal(document.getElementById('detailsModal')).show();
    }

    function confirmDelete(url) {
        document.getElementById('deleteForm').action = url;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
@endsection
