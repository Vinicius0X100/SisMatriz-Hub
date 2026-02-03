@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Salas e Espaços</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Salas e Espaços</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <div class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-6">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <form action="{{ route('reservas-locais.index') }}" method="GET">
                        <div class="position-relative">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" name="search" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Nome da sala ou espaço..." value="{{ request('search') }}" style="height: 45px;">
                        </div>
                    </form>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-6 text-end d-flex gap-2 justify-content-end">
                     <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2" style="height: 45px;" onclick="window.location.href='{{ route('reservas-locais.create') }}'">
                        <i class="mdi mdi-plus fs-5"></i> <span class="d-none d-lg-inline">Novo</span>
                    </button>
                </div>
            </div>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" width="100">Foto</th>
                            <th scope="col">Nome</th>
                            <th scope="col" class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locais as $local)
                        <tr>
                            <td>
                                @if($local->foto)
                                    <img src="{{ asset('storage/' . $local->foto) }}" alt="{{ $local->name }}" class="rounded-3" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                        <i class="bi bi-image fs-4"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $local->name }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('reservas-locais.edit', $local->id) }}" class="btn btn-sm btn-light border rounded-pill" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light border rounded-pill text-danger" title="Excluir" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $local->id }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>

                                <!-- Modal Exclusão -->
                                <div class="modal fade" id="deleteModal{{ $local->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content rounded-4 border-0 shadow">
                                            <div class="modal-body text-center p-5">
                                                <div class="text-danger mb-3">
                                                    <i class="bi bi-exclamation-circle display-1"></i>
                                                </div>
                                                <h4 class="fw-bold mb-3">Tem certeza?</h4>
                                                <p class="text-muted mb-4">Você está prestes a excluir <strong>{{ $local->name }}</strong>. Esta ação não poderá ser desfeita.</p>
                                                <form action="{{ route('reservas-locais.destroy', $local->id) }}" method="POST" class="d-flex justify-content-center gap-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger rounded-pill px-4">Sim, excluir</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                <div>Nenhuma sala ou espaço encontrado.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="d-flex justify-content-end mt-4">
                {{ $locais->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
