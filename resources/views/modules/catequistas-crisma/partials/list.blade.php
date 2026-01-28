<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th class="border-0 rounded-start ps-4">ID</th>
                <th class="border-0">Nome</th>
                <th class="border-0">Comunidade</th>
                <th class="border-0">Status</th>
                <th class="border-0">Criado em</th>
                <th class="border-0 rounded-end text-end pe-4">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($catequistas as $catequista)
                <tr>
                    <td class="ps-4 fw-bold text-muted">#{{ $catequista->id }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                <i class="bi bi-person fw-bold"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $catequista->nome }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border fw-normal">{{ $catequista->entidade->ent_name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        @if($catequista->status == 1)
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Ativo</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Inativo</span>
                        @endif
                    </td>
                    <td class="text-muted small">
                        {{ $catequista->created_at->format('d/m/Y') }}
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('catequistas-crisma.edit', $catequista->id) }}" class="btn btn-sm btn-light text-primary shadow-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-light text-danger shadow-sm" onclick="confirmDelete({{ $catequista->id }})" title="Excluir">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                        Nenhum catequista encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginação -->
<div class="d-flex justify-content-center mt-4">
    {{ $catequistas->links() }}
</div>
