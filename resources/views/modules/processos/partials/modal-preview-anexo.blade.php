<div class="modal fade" id="modalPreviewAnexo" tabindex="-1" aria-labelledby="modalPreviewAnexoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-light border-0 py-3 px-4">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalPreviewAnexoLabel">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span id="previewAnexoName" class="text-truncate" style="max-width: 400px;">Visualizar Anexo</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light d-flex flex-column align-items-center justify-content-center" style="min-height: 400px; max-height: 70vh; overflow: auto;">
                {{-- Container de Loading --}}
                <div id="previewLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="text-muted mt-2 small">Carregando visualização...</p>
                </div>

                {{-- Container para Imagens --}}
                <img id="previewImage" src="" alt="Preview do anexo" class="img-fluid d-none" style="max-height: 70vh; object-fit: contain;">

                {{-- Container para PDFs / Outros suportados via iframe --}}
                <iframe id="previewIframe" src="" class="d-none w-100" style="height: 70vh; border: none;"></iframe>

                {{-- Container para arquivos sem preview --}}
                <div id="previewUnsupported" class="text-center py-5 d-none w-100">
                    <div class="rounded-circle bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-file-earmark-x fs-1 text-secondary"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Visualização Indisponível</h6>
                    <p class="text-muted small mb-4">Este tipo de arquivo não pode ser visualizado diretamente no navegador.</p>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 py-3 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                <a href="#" id="previewDownloadBtn" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2" download target="_blank">
                    <i class="bi bi-download"></i> Baixar Arquivo
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalPreviewAnexo = document.getElementById('modalPreviewAnexo');
        if (!modalPreviewAnexo) return;
        
        const previewModalInstance = new bootstrap.Modal(modalPreviewAnexo);
        
        const previewName = document.getElementById('previewAnexoName');
        const previewDownloadBtn = document.getElementById('previewDownloadBtn');
        const previewLoading = document.getElementById('previewLoading');
        const previewImage = document.getElementById('previewImage');
        const previewIframe = document.getElementById('previewIframe');
        const previewUnsupported = document.getElementById('previewUnsupported');

        // Função para esconder todos os containers de visualização
        function resetPreviewContainers() {
            previewLoading.classList.add('d-none');
            previewImage.classList.add('d-none');
            previewIframe.classList.add('d-none');
            previewUnsupported.classList.add('d-none');
            previewImage.src = '';
            previewIframe.src = '';
        }

        // Listener para qualquer botão com a classe .btn-preview-anexo
        document.body.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-preview-anexo');
            if (btn) {
                e.preventDefault();
                
                const url = btn.dataset.url;
                const name = btn.dataset.name;
                const ext = btn.dataset.type ? btn.dataset.type.toLowerCase() : url.split('.').pop().toLowerCase();
                
                // Configurar Modal
                previewName.textContent = name;
                previewDownloadBtn.href = url;
                previewDownloadBtn.setAttribute('download', name);
                
                // Mostrar Modal
                previewModalInstance.show();
                resetPreviewContainers();
                previewLoading.classList.remove('d-none');

                // Lógica de tipo de arquivo
                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
                const iframeExtensions = ['pdf', 'txt'];

                // Pequeno delay para a animação do modal não engasgar
                setTimeout(() => {
                    previewLoading.classList.add('d-none');
                    if (imageExtensions.includes(ext)) {
                        previewImage.src = url;
                        previewImage.classList.remove('d-none');
                    } else if (iframeExtensions.includes(ext)) {
                        previewIframe.src = url;
                        previewIframe.classList.remove('d-none');
                    } else {
                        previewUnsupported.classList.remove('d-none');
                    }
                }, 300);
            }
        });
        
        // Limpar os elementos quando fechar o modal
        modalPreviewAnexo.addEventListener('hidden.bs.modal', function () {
            resetPreviewContainers();
        });
    });
</script>
