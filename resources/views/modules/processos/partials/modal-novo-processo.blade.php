<!-- Modal Novo Processo (Interno) -->
<div class="modal fade" id="novoProcessoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="bi bi-file-earmark-plus"></i>
                    </div>
                    Novo Processo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('processos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-4">
                    <p class="text-muted small mb-4">Inicie um novo processo interno. O responsável será o grupo configurado para receber aquele assunto.</p>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Assunto <span class="text-danger">*</span></label>
                            <select name="assunto" class="form-select rounded-3" required>
                                <option value="">Selecione...</option>
                                <option value="pascom">PASCOM</option>
                                <option value="compra">Compra</option>
                                <option value="autorizacao">Autorização</option>
                                <option value="oficio">Ofício</option>
                                <option value="manutencao">Manutenção</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Prioridade <span class="text-danger">*</span></label>
                            <select name="prioridade" class="form-select rounded-3" required>
                                <option value="2" selected>Normal</option>
                                <option value="1">Baixa</option>
                                <option value="3">Alta</option>
                                <option value="4">Urgente</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Prazo</label>
                            <input type="date" name="data_limite" class="form-control rounded-3">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Descrição <span class="text-danger">*</span></label>
                            <textarea name="descricao" class="form-control rounded-3" rows="4" required
                                      placeholder="Descreva detalhadamente o processo..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">Anexos (opcional)</label>
                            <div class="p-4 border border-2 border-dashed rounded-4 bg-light text-center position-relative">
                                <i class="bi bi-cloud-arrow-up text-primary fs-1 mb-2 d-block"></i>
                                <span class="fw-bold text-dark d-block mb-1">Arraste ou clique para selecionar</span>
                                <span class="small text-muted d-block mb-3">PDF, Imagens, Word, Excel — sem vídeos (máx. 50 arquivos)</span>
                                <input class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                       style="cursor:pointer;" type="file"
                                       name="arquivos[]" multiple
                                       accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.csv,.ppt,.pptx,.txt,.zip,.rar,.7z">
                            </div>
                            <div id="novoArquivosList" class="mt-2 small text-muted"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i> Criar Processo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const novoInput = document.querySelector('#novoProcessoModal [name="arquivos[]"]');
    if (novoInput) {
        novoInput.addEventListener('change', function () {
            const list = document.getElementById('novoArquivosList');
            list.innerHTML = '';
            if (!this.files.length) return;
            const ul = document.createElement('ul');
            ul.className = 'list-unstyled mb-0';
            Array.from(this.files).forEach(f => {
                const li = document.createElement('li');
                li.className = 'd-flex align-items-center mb-1 text-start';
                li.innerHTML = `<i class="bi bi-check-circle-fill text-success me-2"></i>
                    ${f.name} <span class="text-muted ms-2">(${formatBytes(f.size)})</span>`;
                ul.appendChild(li);
            });
            list.appendChild(ul);
        });
    }

    function formatBytes(b) {
        if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
        if (b >= 1024)    return (b / 1024).toFixed(1) + ' KB';
        return b + ' B';
    }
});
</script>
