<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="bg-light">
            <tr>
                <th width="50" class="ps-4">
                    <i class="bi bi-check-lg text-muted"></i>
                </th>
                <th>Nome</th>
                <th class="text-end pe-4">Telefone</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registers as $register)
                <tr class="cursor-pointer register-row" onclick="toggleRecipient({{ $register->id }}, '{{ addslashes($register->name) }}')">
                    <td class="ps-4">
                        <div class="form-check">
                            <input class="form-check-input recipient-checkbox" type="checkbox" value="{{ $register->id }}" id="check_{{ $register->id }}" onclick="event.stopPropagation(); toggleRecipient({{ $register->id }}, '{{ addslashes($register->name) }}')">
                        </div>
                    </td>
                    <td class="fw-medium text-dark">{{ $register->name }}</td>
                    <td class="text-end pe-4 text-muted small">{{ $register->phone }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                        <div class="mb-2"><i class="bi bi-person-x fs-1 opacity-25"></i></div>
                        Nenhum registro encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center p-3 border-top">
    {{ $registers->links() }}
</div>
