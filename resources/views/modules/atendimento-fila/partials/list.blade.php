<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th class="border-0 rounded-start ps-4">Data</th>
                <th class="border-0">Status</th>
                <th class="border-0">Pessoas na fila</th>
                <th class="border-0 rounded-end text-end pe-4">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($filas as $fila)
                <tr>
                    <td class="ps-4 fw-semibold">
                        {{ $fila->data->format('d/m/Y') }}
                        @if($fila->data->isToday())
                            <span class="badge bg-primary ms-2">Hoje</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $statusClass = match($fila->status) {
                                0 => 'secondary',
                                1 => 'success',
                                2 => 'dark',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $statusClass }}">{{ $fila->status_label }}</span>
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $fila->itens_count }}</span>
                        <span class="text-muted">pessoa(s)</span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('atendimento-fila.show', $fila->id) }}" class="btn btn-sm btn-light text-primary shadow-sm" title="Gerenciar fila">
                                <i class="bi bi-list-ul"></i>
                            </a>
                            <a href="{{ route('atendimento-fila.painel.fila', $fila->id) }}" class="btn btn-sm btn-light text-success shadow-sm" title="Abrir painel do padre" target="_blank">
                                <i class="bi bi-display"></i>
                            </a>
                            <form id="formExcluirFila{{$fila->id}}" action="{{ route('atendimento-fila.destroy', $fila->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                            </form>
                            <button type="button" class="btn btn-sm btn-light text-danger shadow-sm" title="Excluir fila" onclick="abrirConfirmacaoGenerica('formExcluirFila{{$fila->id}}', 'Excluir Fila', 'Tem certeza que deseja excluir a fila do dia <b>{{ $fila->data->format('d/m/Y') }}</b> e todos os seus registros? Isso não pode ser desfeito.', 'danger')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                        Nenhuma fila criada ainda.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginação -->
<div class="d-flex justify-content-center mt-4">
    {{ $filas->links() }}
</div>
