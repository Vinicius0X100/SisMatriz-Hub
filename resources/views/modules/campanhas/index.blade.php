@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Campanhas</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Campanhas</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-megaphone fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Campanhas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Ativas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-archive fs-3 text-secondary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Inativas/Concluídas</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['inactive'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form action="{{ route('campanhas.index') }}" method="GET">
                <div class="row g-3 mb-4 align-items-end">
                    <!-- Pesquisa -->
                    <div class="col-md-4">
                        <label for="search" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" id="search" class="form-control ps-5 rounded-pill" placeholder="Nome ou descrição..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </div>
                    
                    <!-- Filtro: Status -->
                    <div class="col-md-3">
                        <label for="status" class="form-label fw-bold text-muted small">Status</label>
                        <select name="status" id="status" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                            <option value="">Todos os Status</option>
                            <option value="ativa" {{ request('status') == 'ativa' ? 'selected' : '' }}>Ativa</option>
                            <option value="inativa" {{ request('status') == 'inativa' ? 'selected' : '' }}>Inativa</option>
                            <option value="concluida" {{ request('status') == 'concluida' ? 'selected' : '' }}>Concluída</option>
                        </select>
                    </div>

                    <!-- Filtro: Categoria -->
                    <div class="col-md-3">
                        <label for="categoria_id" class="form-label fw-bold text-muted small">Categoria</label>
                        <div class="input-group">
                            <select name="categoria_id" id="categoria_id" class="form-select rounded-start-pill" style="height: 45px;" onchange="this.form.submit()">
                                <option value="">Todas as Categorias</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-secondary rounded-end-pill" type="button" id="btnAddCategory" title="Gerenciar Categorias" style="height: 45px;">
                                <i class="bi bi-gear"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" data-bs-toggle="modal" data-bs-target="#createCampanhaModal">
                            <i class="bi bi-plus-lg fs-5"></i> <span class="d-none d-lg-inline">Nova</span>
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 rounded-start-3 ps-4">Nome</th>
                            <th class="border-0">Descrição</th>
                            <th class="border-0">Categoria</th>
                            <th class="border-0">Início</th>
                            <th class="border-0">Fim</th>
                            <th class="border-0">Status</th>
                            <th class="border-0 rounded-end-3 text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campanhas as $campanha)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $campanha->nome }}</td>
                            <td class="text-muted small">{{ Str::limit($campanha->descricao, 50) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $campanha->categoria->nome }}</span></td>
                            <td>{{ $campanha->data_inicio ? $campanha->data_inicio->format('d/m/Y') : '-' }}</td>
                            <td>{{ $campanha->data_fim ? $campanha->data_fim->format('d/m/Y') : '-' }}</td>
                            <td>
                                @if($campanha->status == 'ativa')
                                    <span class="badge bg-success bg-opacity-10 text-success">Ativa</span>
                                @elseif($campanha->status == 'concluida')
                                    <span class="badge bg-primary bg-opacity-10 text-primary">Concluída</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Inativa</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill btn-manage-campaign" data-id="{{ $campanha->id }}" title="Gerenciar">
                                        <i class="bi bi-kanban"></i> Gerenciar
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#editCampanhaModal{{ $campanha->id }}" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill" data-bs-toggle="modal" data-bs-target="#deleteCampanhaModal{{ $campanha->id }}" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editCampanhaModal{{ $campanha->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('campanhas.update', $campanha->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Editar Campanha</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nome</label>
                                                <input type="text" name="nome" class="form-control" value="{{ $campanha->nome }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Categoria</label>
                                                <select name="categoria_id" class="form-select" required>
                                                    @foreach($categorias as $cat)
                                                        <option value="{{ $cat->id }}" {{ $campanha->categoria_id == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Descrição</label>
                                                <textarea name="descricao" class="form-control" rows="3">{{ $campanha->descricao }}</textarea>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Início</label>
                                                    <input type="date" name="data_inicio" class="form-control" value="{{ $campanha->data_inicio ? $campanha->data_inicio->format('Y-m-d') : '' }}">
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label class="form-label">Fim</label>
                                                    <input type="date" name="data_fim" class="form-control" value="{{ $campanha->data_fim ? $campanha->data_fim->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="ativa" {{ $campanha->status == 'ativa' ? 'selected' : '' }}>Ativa</option>
                                                    <option value="inativa" {{ $campanha->status == 'inativa' ? 'selected' : '' }}>Inativa</option>
                                                    <option value="concluida" {{ $campanha->status == 'concluida' ? 'selected' : '' }}>Concluída</option>
                                                </select>
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

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteCampanhaModal{{ $campanha->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="{{ route('campanhas.destroy', $campanha->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold text-danger">Excluir Campanha</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Tem certeza que deseja excluir a campanha <strong>{{ $campanha->nome }}</strong>?</p>
                                            <p class="text-muted small">Isso excluirá permanentemente todas as movimentações (entradas e saídas) associadas a esta campanha. Esta ação não pode ser desfeita.</p>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-danger rounded-pill px-4">Excluir Tudo</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Nenhuma campanha encontrada.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $campanhas->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createCampanhaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('campanhas.store') }}" method="POST">
            @csrf
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Nova Campanha</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoria</label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Início</label>
                            <input type="date" name="data_inicio" class="form-control">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Fim</label>
                            <input type="date" name="data_fim" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Criar Campanha</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- React Component Root for Category Manager -->
<div id="campanhas-category-manager-root" data-paroquia-name="{{ optional(Auth::user()->paroquia)->name ?? 'Paróquia' }}"></div>
<!-- React Component Root for Campaign Dashboard Modal -->
<div id="campanhas-dashboard-modal-root" data-paroquia-name="{{ optional(Auth::user()->paroquia)->name ?? 'Paróquia' }}"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add spinner to forms on submit
        const forms = document.querySelectorAll('.modal form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    const originalText = btn.innerText;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processando...';
                }
            });
        });
    });
</script>

@vite('resources/js/campanhas.tsx')
@endsection
