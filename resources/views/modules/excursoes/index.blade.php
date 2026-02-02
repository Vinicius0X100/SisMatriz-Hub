@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Excursões</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Excursões</li>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-secondary">Gerenciar Excursões</h5>
                <a href="{{ route('excursoes.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                    <i class="bi bi-plus-lg me-2"></i> Nova Excursão
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 ps-3 rounded-start-pill">Destino</th>
                            <th class="py-3">Tipo</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Criado em</th>
                            <th class="py-3 text-end rounded-end-pill pe-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($excursoes as $excursao)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">{{ $excursao->destino }}</td>
                            <td><span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">{{ ucfirst($excursao->tipo) }}</span></td>
                            <td>
                                @if($excursao->finalizada)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Finalizada</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Ativa</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $excursao->created_at->format('d/m/Y') }}</td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <a href="{{ route('excursoes.show', $excursao) }}" class="btn btn-sm btn-outline-info rounded-pill me-1" title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('excursoes.edit', $excursao) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('excursoes.destroy', $excursao) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta excursão?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-bus-front display-4 opacity-25 mb-3"></i>
                                <p class="mb-0">Nenhuma excursão encontrada.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $excursoes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
