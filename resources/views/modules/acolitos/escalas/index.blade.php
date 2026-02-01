@extends('layouts.app')

@section('title', 'Escalas de Acólitos')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Escalas de Acólitos</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.index') }}" class="text-decoration-none">Acólitos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Escalas</li>
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

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <form action="{{ route('acolitos.escalas.index') }}" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-pill" placeholder="Pesquisar..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Filtrar</button>
                </form>
                
                <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createEscalaModal">
                    <i class="bi bi-plus-lg me-2"></i>Nova Escala
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="ps-4 rounded-start-pill text-muted small text-uppercase fw-bold">ID</th>
                            <th scope="col" class="text-muted small text-uppercase fw-bold">Mês/Ano</th>
                            <th scope="col" class="text-muted small text-uppercase fw-bold">Comunidade</th>
                            <th scope="col" class="text-muted small text-uppercase fw-bold">Data Envio</th>
                            <th scope="col" class="text-muted small text-uppercase fw-bold text-center">Qtd. Acólitos</th>
                            <th scope="col" class="text-muted small text-uppercase fw-bold text-center">Situação</th>
                            <th scope="col" class="pe-4 rounded-end-pill text-muted small text-uppercase fw-bold text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($escalas as $escala)
                        <tr>
                            <td class="ps-4 fw-bold text-muted">#{{ $escala->es_id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $escala->month }}</div>
                                <div class="small text-muted">{{ $escala->year }}</div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="bi bi-house-door text-primary"></i>
                                    </div>
                                    <span class="fw-medium">{{ $escala->church }}</span>
                                </div>
                            </td>
                            <td>{{ $escala->send_date ? $escala->send_date->format('d/m/Y') : '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">
                                    {{ $escala->qntd_acolitos ?? 0 }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($escala->situation == 1)
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">
                                        <i class="bi bi-check-circle me-1"></i> Concluída
                                    </span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">
                                        <i class="bi bi-hourglass-split me-1"></i> Em Progresso
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('acolitos.escalas.manage', $escala->es_id) }}" class="btn btn-sm btn-light text-primary rounded-pill px-3" title="Gerenciar">
                                        <i class="bi bi-gear me-1"></i> Gerenciar
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light text-secondary rounded-pill px-3" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editEscalaModal{{ $escala->es_id }}"
                                            title="Editar">
                                        <i class="bi bi-pencil me-1"></i> Editar
                                    </button>
                                    <form action="{{ route('acolitos.escalas.destroy', $escala->es_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta escala?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger rounded-pill px-3" title="Excluir">
                                            <i class="bi bi-trash me-1"></i> Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editEscalaModal{{ $escala->es_id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold">Editar Escala #{{ $escala->es_id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('acolitos.escalas.update', $escala->es_id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Mês</label>
                                                <select name="month" class="form-select rounded-3" required>
                                                    @foreach(['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $mes)
                                                        <option value="{{ $mes }}" {{ $escala->month == $mes ? 'selected' : '' }}>{{ $mes }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Comunidade</label>
                                                <select name="ent_id" class="form-select rounded-3" required>
                                                    @foreach($entidades as $entidade)
                                                        <option value="{{ $entidade->ent_id }}" {{ $escala->church == $entidade->ent_name ? 'selected' : '' }}>
                                                            {{ $entidade->ent_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Data de Envio</label>
                                                <input type="date" name="send_date" class="form-control rounded-3" value="{{ $escala->send_date ? $escala->send_date->format('Y-m-d') : '' }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-muted">Situação</label>
                                                <select name="situation" class="form-select rounded-3" required>
                                                    <option value="0" {{ $escala->situation == 0 ? 'selected' : '' }}>Em Progresso</option>
                                                    <option value="1" {{ $escala->situation == 1 ? 'selected' : '' }}>Concluída</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Alterações</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-3">
                                    <i class="bi bi-calendar-x fs-1 text-muted opacity-50"></i>
                                </div>
                                <h6 class="fw-bold">Nenhuma escala encontrada</h6>
                                <p class="small mb-0">Clique em "Nova Escala" para começar.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-end mt-4">
                {{ $escalas->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createEscalaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nova Escala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('acolitos.escalas.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Mês</label>
                        <select name="month" class="form-select rounded-3" required>
                            <option value="" disabled selected>Selecione o mês...</option>
                            @foreach(['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'] as $mes)
                                <option value="{{ $mes }}">{{ $mes }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Comunidade</label>
                        <select name="ent_id" class="form-select rounded-3" required>
                            <option value="" disabled selected>Selecione a comunidade...</option>
                            @foreach($entidades as $entidade)
                                <option value="{{ $entidade->ent_id }}">{{ $entidade->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Data de Envio</label>
                        <input type="date" name="send_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Criar Escala</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection