<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th class="border-0 rounded-start ps-4">Nome Completo</th>
                <th class="border-0">Turma</th>
                <th class="border-0">Status</th>
                <th class="border-0 rounded-end text-end pe-4">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $student)
                @php
                    $docs = $student->register->docsEucaristia;
                    // Count how many are checked
                    $checkedCount = 0;
                    if($docs) {
                        if($docs->rg) $checkedCount++;
                        if($docs->comprovante_residencia) $checkedCount++;
                        if($docs->certidao_batismo) $checkedCount++;
                    }
                    $totalDocs = 3; 
                    
                    // Logic: "Documentação Obrigatoria Entregue" if all 3 are present.
                    $status = ($checkedCount == $totalDocs) ? 'Documentação Obrigatória Entregue' : 'Documentação Pendente';
                    $statusColor = ($checkedCount == $totalDocs) ? 'success' : 'warning';
                @endphp
                <tr>
                    <td class="ps-4 fw-bold text-dark">{{ $student->register->name }}</td>
                    <td class="text-muted">{{ $student->turma->turma ?? 'Sem Turma' }}</td>
                    <td>
                        <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} rounded-pill px-3">
                            <i class="bi bi-circle-fill me-1 small"></i> {{ $status }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editDocsModal{{ $student->register_id }}">
                            <i class="bi bi-pencil-square me-1"></i> Editar
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">Nenhum catecando encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@foreach($students as $student)
    @php $docs = $student->register->docsEucaristia; @endphp
    <!-- Modal -->
    <div class="modal fade" id="editDocsModal{{ $student->register_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Documentação: {{ $student->register->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="{{ route('docs-eucaristia.update', $student->register_id) }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = '<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span>Salvando...';">
                        @csrf
                        @method('PUT')
                        
                        <h6 class="text-muted text-uppercase small fw-bold mb-3"><i class="bi bi-file-earmark-text me-1"></i> Documentos Obrigatórios</h6>
                        <div class="d-flex flex-column gap-3">
                            <label class="d-flex align-items-center p-3 border rounded-3 hover-bg-light cursor-pointer">
                                <input type="checkbox" name="rg" class="form-check-input me-3 fs-5" {{ ($docs->rg ?? false) ? 'checked' : '' }}>
                                <span class="fw-medium"><i class="bi bi-card-heading me-2 text-muted"></i> RG</span>
                            </label>
                            <label class="d-flex align-items-center p-3 border rounded-3 hover-bg-light cursor-pointer">
                                <input type="checkbox" name="comprovante_residencia" class="form-check-input me-3 fs-5" {{ ($docs->comprovante_residencia ?? false) ? 'checked' : '' }}>
                                <span class="fw-medium"><i class="bi bi-house me-2 text-muted"></i> Comprovante de residência</span>
                            </label>
                            <label class="d-flex align-items-center p-3 border rounded-3 hover-bg-light cursor-pointer">
                                <input type="checkbox" name="certidao_batismo" class="form-check-input me-3 fs-5" {{ ($docs->certidao_batismo ?? false) ? 'checked' : '' }}>
                                <span class="fw-medium"><i class="bi bi-droplet me-2 text-muted"></i> Certidão de batismo</span>
                            </label>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

<div class="d-flex justify-content-end mt-4">
    {{ $students->links('partials.pagination') }}
</div>
