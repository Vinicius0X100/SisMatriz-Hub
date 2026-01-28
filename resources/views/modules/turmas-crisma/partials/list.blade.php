<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th scope="col" width="40" class="border-0 rounded-start ps-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                    </div>
                </th>
                <th scope="col" class="border-0 sortable cursor-pointer" data-sort="turma">
                    Turma <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                </th>
                <th scope="col" class="border-0 sortable cursor-pointer" data-sort="tutor">
                    Catequista <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                </th>
                <th scope="col" class="border-0 sortable cursor-pointer" data-sort="inicio">
                    Início <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                </th>
                <th scope="col" class="border-0 sortable cursor-pointer" data-sort="termino">
                    Término <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                </th>
                <th scope="col" class="border-0 sortable cursor-pointer" data-sort="alunos_qntd">
                    Qtd. Alunos <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                </th>
                <th scope="col" class="border-0 sortable cursor-pointer" data-sort="status">
                    Status <i class="bi bi-arrow-down-up small text-muted ms-1"></i>
                </th>
                <th scope="col" class="border-0 rounded-end text-end pe-4">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($turmas as $turma)
            <tr>
                <td class="ps-4">
                    <div class="form-check">
                        <input class="form-check-input row-checkbox" type="checkbox" value="{{ $turma->id }}">
                    </div>
                </td>
                <td class="fw-medium">{{ $turma->turma }}</td>
                <td>{{ $turma->catequista->nome ?? 'N/A' }}</td>
                <td>{{ $turma->inicio ? $turma->inicio->format('d/m/Y') : '-' }}</td>
                <td>{{ $turma->termino ? $turma->termino->format('d/m/Y') : '-' }}</td>
                <td>{{ $turma->alunos_qntd }}</td>
                <td>
                    @php
                        $statusColors = [
                            1 => 'bg-secondary', // Não Iniciada
                            2 => 'bg-success',   // Concluida
                            3 => 'bg-primary',   // Em catequese
                            4 => 'bg-danger',    // Cancelada
                        ];
                        $statusLabels = [
                            1 => 'Não Iniciada',
                            2 => 'Concluída',
                            3 => 'Em Catequese',
                            4 => 'Cancelada',
                        ];
                    @endphp
                    <span class="badge rounded-pill {{ $statusColors[$turma->status] ?? 'bg-secondary' }}">
                        {{ $statusLabels[$turma->status] ?? 'Desconhecido' }}
                    </span>
                </td>
                <td class="pe-4">
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-sm btn-light text-dark shadow-sm rounded-circle" onclick="openManageModal('{{ $turma->id }}')" title="Gerenciar" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-gear"></i>
                        </button>
                        <a href="{{ route('turmas-crisma.edit', $turma->id) }}" class="btn btn-sm btn-light text-primary shadow-sm rounded-circle" title="Editar" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button class="btn btn-sm btn-light text-danger shadow-sm rounded-circle" onclick="confirmDelete({{ $turma->id }})" title="Excluir" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    Nenhuma turma encontrada.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $turmas->appends(request()->query())->links() }}
</div>
