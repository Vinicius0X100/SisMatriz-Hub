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
                    $docs = $student->register->docsCrisma;
                    // Count how many are checked (excluding certidao_casamento_padrinho)
                    $checkedCount = 0;
                    $hasCasamento = false;
                    
                    if($docs) {
                        if($docs->rg) $checkedCount++;
                        if($docs->comprovante_residencia) $checkedCount++;
                        if($docs->certidao_batismo) $checkedCount++;
                        if($docs->certidao_eucaristia) $checkedCount++;
                        if($docs->rg_padrinho) $checkedCount++;
                        if($docs->certidao_crisma_padrinho) $checkedCount++;
                        
                        if($docs->certidao_casamento_padrinho) $hasCasamento = true;
                    }
                    $totalRequired = 6; 
                    
                    // Logic refinement:
                    // 1. If 6 mandatory + Casamento checked -> "Documentação Entregue" (Green)
                    // 2. If 6 mandatory checked (Casamento optional) -> "Documentação Obrigatória Entregue" (Green)
                    // 3. Else -> "Documentação Pendente" (Warning)

                    if ($checkedCount == $totalRequired && $hasCasamento) {
                        $status = 'Documentação Entregue';
                        $statusColor = 'success';
                    } elseif ($checkedCount == $totalRequired) {
                        $status = 'Documentação Obrigatória Entregue';
                        $statusColor = 'success';
                    } else {
                        $status = 'Documentação Pendente';
                        $statusColor = 'warning';
                    }
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
                    <td colspan="4" class="text-center py-5 text-muted">Nenhum crismando encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@foreach($students as $student)
    @php $docs = $student->register->docsCrisma; @endphp
    <!-- Modal -->
    <div class="modal fade" id="editDocsModal{{ $student->register_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Documentação: {{ $student->register->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3 mb-4 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill text-primary me-2 fs-5"></i>
                        <span class="text-primary fw-semibold">Em azul, documentação do padrinho ou madrinha caso já entregues.</span>
                    </div>

                    <form action="{{ route('docs-crisma.update', $student->register_id) }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = '<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span>Salvando...';">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <!-- Coluna 1: Crismando -->
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small fw-bold mb-3"><i class="bi bi-person me-1"></i> Documentos do Crismando</h6>
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
                                    <label class="d-flex align-items-center p-3 border rounded-3 hover-bg-light cursor-pointer">
                                        <input type="checkbox" name="certidao_eucaristia" class="form-check-input me-3 fs-5" {{ ($docs->certidao_eucaristia ?? false) ? 'checked' : '' }}>
                                        <span class="fw-medium"><i class="bi bi-book me-2 text-muted"></i> Certidão de Primeira Eucaristia</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Coluna 2: Padrinho -->
                            <div class="col-md-6">
                                <h6 class="text-primary text-uppercase small fw-bold mb-3"><i class="bi bi-people me-1"></i> Documentos do Padrinho/Madrinha</h6>
                                <div class="d-flex flex-column gap-3">
                                    <label class="d-flex align-items-center p-3 border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-3 cursor-pointer">
                                        <input type="checkbox" name="rg_padrinho" class="form-check-input me-3 fs-5" {{ ($docs->rg_padrinho ?? false) ? 'checked' : '' }}>
                                        <span class="fw-medium text-primary"><i class="bi bi-card-heading me-2"></i> RG</span>
                                    </label>
                                    <label class="d-flex align-items-center p-3 border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-3 cursor-pointer">
                                        <input type="checkbox" name="certidao_casamento_padrinho" class="form-check-input me-3 fs-5" {{ ($docs->certidao_casamento_padrinho ?? false) ? 'checked' : '' }}>
                                        <span class="fw-medium text-primary"><i class="bi bi-heart me-2"></i> Certidão de casamento religioso (se casado)</span>
                                    </label>
                                    <label class="d-flex align-items-center p-3 border border-primary border-opacity-25 bg-primary bg-opacity-10 rounded-3 cursor-pointer">
                                        <input type="checkbox" name="certidao_crisma_padrinho" class="form-check-input me-3 fs-5" {{ ($docs->certidao_crisma_padrinho ?? false) ? 'checked' : '' }}>
                                        <span class="fw-medium text-primary"><i class="bi bi-check-circle me-2"></i> Certidão de Crisma</span>
                                    </label>
                                </div>
                            </div>
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
