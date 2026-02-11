<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th scope="col" class="text-muted small text-uppercase">Nome</th>
                <th scope="col" class="text-muted small text-uppercase">Status</th>
                <th scope="col" class="text-muted small text-uppercase">Data Batismo</th>
                <th scope="col" class="text-muted small text-uppercase">Local</th>
                <th scope="col" class="text-muted small text-uppercase">Celebrante</th>
                <th scope="col" class="text-end text-muted small text-uppercase">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batismos as $batismo)
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm rounded-circle bg-light d-flex align-items-center justify-content-center text-primary me-3 fw-bold">
                            {{ substr($batismo->register->name ?? 'N', 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark">{{ $batismo->register->name ?? 'Sem Nome' }}</div>
                            <div class="small text-muted">{{ $batismo->register->cpf ?? 'Sem CPF' }}</div>
                        </div>
                    </div>
                </td>
                <td>
                    @if($batismo->is_batizado)
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">Batizado</span>
                    @else
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2">Não Batizado</span>
                    @endif
                </td>
                <td>
                    {{ $batismo->data_batismo ? \Carbon\Carbon::parse($batismo->data_batismo)->format('d/m/Y') : '-' }}
                </td>
                <td>{{ $batismo->local_batismo ?? '-' }}</td>
                <td>{{ $batismo->celebrante ?? '-' }}</td>
                <td class="text-end">
                    <a href="{{ route('batismos.edit', $batismo->id) }}" class="btn btn-light btn-sm rounded-pill px-3 text-primary fw-bold">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-muted">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-inbox fs-1 mb-2"></i>
                        <p class="mb-0">Nenhum registro encontrado.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginação -->
<div class="d-flex justify-content-between align-items-center mt-4 border-top pt-3">
    <div class="text-muted small">
        Mostrando <span class="fw-bold">{{ $batismos->firstItem() ?? 0 }}</span> a <span class="fw-bold">{{ $batismos->lastItem() ?? 0 }}</span> de <span class="fw-bold">{{ $batismos->total() }}</span> registros
    </div>
    <div>
        {{ $batismos->links() }}
    </div>
</div>