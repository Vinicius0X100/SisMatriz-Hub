@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Comunidades</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Comunidades</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-6">
                    <form action="{{ route('comunidades.index') }}" method="GET">
                        <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Nome da comunidade..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </form>
                </div>
                
                <!-- Botões de Ação -->
                <div class="col-md-6 text-end d-flex gap-2 justify-content-end align-items-end">
                     <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('comunidades.create') }}'">
                        <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Nova Comunidade</span>
                    </button>
                </div>
            </div>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="text-start">Nome</th>
                            <th scope="col">Endereço</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($comunidades as $comunidade)
                        <tr>
                            <td class="fw-bold">{{ $comunidade->ent_name }}</td>
                            <td>{{ $comunidade->address }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('comunidades.edit', $comunidade->ent_id) }}" class="btn btn-sm btn-light border rounded-pill" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill text-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $comunidade->ent_id }}" title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <!-- Modal Exclusão -->
                                <div class="modal fade" id="deleteModal{{ $comunidade->ent_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-body text-center p-5">
                                                <div class="text-danger mb-3">
                                                    <i class="bi bi-exclamation-circle display-1"></i>
                                                </div>
                                                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                                                <p class="text-muted mb-4">A comunidade <strong>{{ $comunidade->ent_name }}</strong> será removida permanentemente.</p>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('comunidades.destroy', $comunidade->ent_id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4">Sim, excluir</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-houses display-1 d-block mb-3 text-secondary opacity-25"></i>
                                <div>Nenhuma comunidade encontrada.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-end mt-4">
                {{ $comunidades->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
