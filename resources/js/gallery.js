export default (config = {}) => ({
    uploadType: 'individual',
    dragover: false,
    items: [{
        file: null,
        titulo: '',
        descricao: '',
        tipo: '1'
    }],
    error: false,
    errorMessage: '',
    loading: false,
    uploadUrl: config.uploadUrl || '',

    init() {
        // Initialize if needed
    },

    get canSubmit() {
        if (this.loading) return false;
        
        if (this.uploadType === 'individual') {
            // Check if the single item has a file
            return this.items.length > 0 && this.items[0].file !== null;
        } else {
            // Check if there are any items
            return this.items.length > 0;
        }
    },

    setMode(mode) {
        // Prevent unnecessary reset
        if (this.uploadType === mode) return;
        
        this.uploadType = mode;
        this.clearFiles();
    },

    handleFiles(fileList) {
        this.error = false;
        if (!fileList || fileList.length === 0) return;

        const newFiles = Array.from(fileList);
        
        // Validate file types
        const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif', 'svg'];
        
        const invalidFiles = newFiles.filter(file => {
            // Check MIME type first
            if (file.type.startsWith('image/')) return false;
            
            // If MIME type is empty or not image/, check extension
            const extension = file.name.split('.').pop().toLowerCase();
            return !allowedExtensions.includes(extension);
        });

        if (invalidFiles.length > 0) {
            this.error = true;
            this.errorMessage = 'Apenas arquivos de imagem são permitidos (JPG, PNG, GIF, WEBP, HEIC, SVG).';
            return;
        }

        if (this.uploadType === 'individual') {
            if (newFiles.length > 1) {
                this.error = true;
                this.errorMessage = 'No modo individual, selecione apenas 1 imagem.';
                return;
            }
            // Update the single item
            this.items[0].file = newFiles[0];
            // Reset input value to allow re-selecting same file if cleared
            if(this.$refs.fileInputIndividual) this.$refs.fileInputIndividual.value = '';
        } else {
            // Batch mode: create new items for each file
            const newItems = newFiles.map(file => ({
                file: file,
                titulo: '',
                descricao: '',
                tipo: '1' // Default type
            }));
            this.items = [...this.items, ...newItems];
            if(this.$refs.fileInputBatch) this.$refs.fileInputBatch.value = '';
        }
    },

    handleDrop(event) {
        this.dragover = false;
        this.handleFiles(event.dataTransfer.files);
    },

    removeFile(index) {
        if (this.uploadType === 'individual') {
            this.items[0].file = null;
            if(this.$refs.fileInputIndividual) this.$refs.fileInputIndividual.value = '';
        } else {
            this.items.splice(index, 1);
        }
    },

    clearFiles() {
        if (this.uploadType === 'individual') {
            this.items = [{
                file: null,
                titulo: '',
                descricao: '',
                tipo: '1'
            }];
            try {
                if(this.$refs.fileInputIndividual) this.$refs.fileInputIndividual.value = '';
            } catch(e) {}
        } else {
            this.items = [];
            try {
                if(this.$refs.fileInputBatch) this.$refs.fileInputBatch.value = '';
            } catch(e) {}
        }
        this.error = false;
        this.errorMessage = '';
    },

    formatSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    submitForm() {
        if (!this.canSubmit) return;

        const validItems = this.items.filter(item => item.file !== null);

        if (validItems.length === 0) {
            this.error = true;
            this.errorMessage = 'Selecione pelo menos uma imagem.';
            return;
        }

        this.loading = true;
        const formData = new FormData();
        
        validItems.forEach((item, index) => {
            formData.append(`items[${index}][titulo]`, item.titulo || '');
            formData.append(`items[${index}][descricao]`, item.descricao || '');
            formData.append(`items[${index}][tipo]`, item.tipo);
            formData.append(`items[${index}][file]`, item.file);
        });

        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        if (!csrfToken) {
            this.error = true;
            this.errorMessage = 'Erro de segurança: Token CSRF não encontrado. Recarregue a página.';
            this.loading = false;
            return;
        }

        // Add timeout to fetch
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 60000); // 60s timeout

        if (!this.uploadUrl) {
            this.error = true;
            this.errorMessage = 'URL de upload não configurada.';
            this.loading = false;
            return;
        }

        fetch(this.uploadUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        const json = JSON.parse(text);
                        throw new Error(json.message || 'Erro no servidor (' + response.status + ')');
                    } catch (e) {
                        throw new Error('Erro no servidor (' + response.status + ')');
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show Success Toast
                const toastEl = document.getElementById('js-toast');
                const toastTitle = document.getElementById('js-toast-title');
                const toastMessage = document.getElementById('js-toast-message');
                const toastIcon = document.getElementById('js-toast-icon');

                if (toastEl && toastTitle && toastMessage && toastIcon) {
                    toastEl.classList.remove('text-bg-danger', 'text-bg-primary');
                    toastEl.classList.add('text-bg-success');
                    toastTitle.textContent = 'Sucesso!';
                    toastMessage.textContent = data.message;
                    toastIcon.className = 'bi bi-check-circle-fill me-3 fs-5';
                    const toast = new window.bootstrap.Toast(toastEl);
                    toast.show();
                }

                this.clearFiles();

                // Add images to grid dynamically
                if (data.images && data.images.length > 0) {
                    this.addImagesToGallery(data.images);
                }
            } else {
                throw new Error(data.message || 'Erro desconhecido');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            let msg = error.message;
            if (error.name === 'AbortError') {
                msg = 'O envio demorou muito e foi cancelado. Verifique sua conexão ou tente enviar menos imagens.';
            }
            
            // Show Error Toast
            const toastEl = document.getElementById('js-toast');
            const toastTitle = document.getElementById('js-toast-title');
            const toastMessage = document.getElementById('js-toast-message');
            const toastIcon = document.getElementById('js-toast-icon');

            if (toastEl && toastTitle && toastMessage && toastIcon) {
                toastEl.classList.remove('text-bg-success', 'text-bg-primary');
                toastEl.classList.add('text-bg-danger');
                toastTitle.textContent = 'Erro!';
                toastMessage.textContent = msg || 'Erro ao enviar imagens.';
                toastIcon.className = 'bi bi-exclamation-circle-fill me-3 fs-5';
                const toast = new window.bootstrap.Toast(toastEl);
                toast.show();
            }

            this.error = true;
            this.errorMessage = msg || 'Erro ao enviar imagens. Tente novamente.';
        })
        .finally(() => {
            this.loading = false;
        });
    },

    addImagesToGallery(images) {
        const grid = document.getElementById('gallery-grid');
        const emptyState = document.getElementById('gallery-empty');
        
        if (grid) {
            grid.classList.remove('d-none');
            grid.style.display = 'flex'; // Ensure flex/row display
        }
        if (emptyState) {
            emptyState.classList.add('d-none');
            emptyState.style.display = 'none'; // Hide empty state
        }

        images.forEach(img => {
            const col = document.createElement('div');
            col.className = 'col-sm-6 col-md-4 col-lg-3 gallery-item';
            // Simple fade in effect
            col.style.opacity = '0';
            col.style.transition = 'opacity 0.5s ease-in-out';
            
            const dateStr = img.created_at ? new Date(img.created_at).toLocaleDateString('pt-BR') : new Date().toLocaleDateString('pt-BR');
            const typeLabel = img.tipo == 1 ? 'Poster' : 'Postagem';
            const typeBadgeClass = img.tipo == 1 ? 'bg-primary' : 'bg-info';

            col.innerHTML = `
                <div class="card h-100 border-0 shadow-sm hover-shadow transition-all overflow-hidden group rounded-4">
                    <div class="position-relative bg-light ratio ratio-16x9">
                        <img src="${img.url}" 
                             alt="${img.titulo}" 
                             class="object-fit-cover w-100 h-100 transition-transform group-hover-scale"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;d-flex align-items-center justify-content-center w-100 h-100 bg-secondary bg-opacity-10 text-secondary&quot;><i class=&quot;bi bi-image fs-1&quot;></i></div>'">
                        <div class="position-absolute top-0 end-0 m-2">
                            <span class="badge ${typeBadgeClass} bg-opacity-75 backdrop-blur rounded-pill shadow-sm">
                                ${typeLabel}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <h6 class="card-title fw-bold text-dark text-truncate mb-1" title="${img.titulo}">${img.titulo}</h6>
                        ${img.descricao ? `<p class="card-text small text-muted text-truncate-2 mb-2" title="${img.descricao}">${img.descricao}</p>` : ''}
                        <div class="d-flex align-items-center text-muted small mt-auto pt-2 border-top">
                            <i class="bi bi-calendar-event me-1"></i> ${dateStr}
                        </div>
                    </div>
                </div>
            `;
            
            if (grid) {
                grid.prepend(col);
                // Trigger reflow for transition
                void col.offsetWidth;
                col.style.opacity = '1';
            }
        });
    }
});
