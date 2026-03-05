export default (config = {}) => ({
    uploadType: 'individual',
    dragover: false,
    items: [{
        file: null,
        titulo: '',
        descricao: '',
        tipo: '1'
    }],
    batchTitulo: '',
    batchDescricao: '',
    batchTipo: '1',
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

    showToast(type, title, message) {
        const toastEl = document.getElementById('js-toast');
        const toastTitle = document.getElementById('js-toast-title');
        const toastMessage = document.getElementById('js-toast-message');
        const toastIcon = document.getElementById('js-toast-icon');

        if (toastEl && toastTitle && toastMessage && toastIcon) {
            toastEl.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-primary');
            toastEl.classList.add(type === 'success' ? 'text-bg-success' : (type === 'error' ? 'text-bg-danger' : 'text-bg-primary'));
            
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            
            if (type === 'success') {
                toastIcon.className = 'bi bi-check-circle-fill me-3 fs-5';
            } else if (type === 'error') {
                toastIcon.className = 'bi bi-exclamation-circle-fill me-3 fs-5';
            } else {
                toastIcon.className = 'bi bi-info-circle-fill me-3 fs-5';
            }
            
            const toast = new window.bootstrap.Toast(toastEl);
            toast.show();
        }
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
            this.showToast('error', 'Arquivo Inválido', this.errorMessage);
            return;
        }

        const createItem = (file) => ({
            file: file,
            titulo: this.batchTitulo || '',
            descricao: this.batchDescricao || '',
            tipo: this.batchTipo || '1', // Default type or batch type
            previewUrl: URL.createObjectURL(file)
        });

        if (this.uploadType === 'individual') {
            if (newFiles.length > 1) {
                this.error = true;
                this.errorMessage = 'No modo individual, selecione apenas 1 imagem.';
                this.showToast('error', 'Atenção', this.errorMessage);
                return;
            }
            // Revoke old preview if exists
            if (this.items[0].file && this.items[0].previewUrl) {
                URL.revokeObjectURL(this.items[0].previewUrl);
            }
            // Update the single item
            this.items[0] = createItem(newFiles[0]);
            
            // Reset input value to allow re-selecting same file if cleared
            if(this.$refs.fileInputIndividual) this.$refs.fileInputIndividual.value = '';
        } else {
            // Batch mode: create new items for each file
            const newItems = newFiles.map(createItem);
            this.items = [...this.items, ...newItems];
            if(this.$refs.fileInputBatch) this.$refs.fileInputBatch.value = '';
        }
    },

    handleDrop(event) {
        this.dragover = false;
        this.handleFiles(event.dataTransfer.files);
    },

    removeFile(index) {
        if (this.items[index] && this.items[index].previewUrl) {
            URL.revokeObjectURL(this.items[index].previewUrl);
        }
        
        if (this.uploadType === 'individual') {
            this.items[0] = {
                file: null,
                titulo: '',
                descricao: '',
                tipo: '1',
                previewUrl: null
            };
            if(this.$refs.fileInputIndividual) this.$refs.fileInputIndividual.value = '';
        } else {
            this.items.splice(index, 1);
        }
    },

    clearFiles() {
        this.items.forEach(item => {
            if (item && item.previewUrl) URL.revokeObjectURL(item.previewUrl);
        });

        if (this.uploadType === 'individual') {
            this.items = [{
                file: null,
                titulo: '',
                descricao: '',
                tipo: '1',
                previewUrl: null
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

    applyBatchSettings() {
        if (this.items.length === 0) {
            this.showToast('info', 'Aviso', 'Adicione imagens primeiro para aplicar as configurações.');
            return;
        }

        let updatedCount = 0;
        this.items.forEach(item => {
            if (item.file) {
                // Always apply type as it has a default
                item.tipo = this.batchTipo;
                
                // Apply title/desc only if they have content to avoid accidental clearing
                // or if the user explicitly wants to set them. 
                // Given the request "define at once", I'll apply if not empty.
                if (this.batchTitulo.trim() !== '') item.titulo = this.batchTitulo;
                if (this.batchDescricao.trim() !== '') item.descricao = this.batchDescricao;
                
                updatedCount++;
            }
        });
        
        this.showToast('success', 'Aplicado', `Configurações aplicadas a ${updatedCount} imagens.`);
    },

    formatSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },

    async compressImage(file, maxWidth = 2560, quality = 0.8) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;

                    // Resize if larger than maxWidth
                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error('Falha na compressão da imagem'));
                            return;
                        }

                        // Create a new File object with the compressed blob
                        const newFile = new File([blob], file.name, {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                        });
                        resolve(newFile);
                    }, 'image/jpeg', quality);
                };
                img.onerror = (error) => reject(error);
            };
            reader.onerror = (error) => reject(error);
        });
    },

    async submitForm() {
        if (!this.canSubmit) return;

        const validItems = this.items.filter(item => item.file !== null);

        if (validItems.length === 0) {
            this.error = true;
            this.errorMessage = 'Selecione pelo menos uma imagem.';
            this.showToast('error', 'Atenção', this.errorMessage);
            return;
        }

        this.loading = true;
        this.error = false;
        
        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';

        if (!csrfToken) {
            this.error = true;
            this.errorMessage = 'Erro de segurança: Token CSRF não encontrado. Recarregue a página.';
            this.showToast('error', 'Erro', this.errorMessage);
            this.loading = false;
            return;
        }

        if (!this.uploadUrl) {
            this.error = true;
            this.errorMessage = 'URL de upload não configurada.';
            this.showToast('error', 'Erro', this.errorMessage);
            this.loading = false;
            return;
        }

        let successCount = 0;
        let errors = [];
        let newImages = [];

        // Sequential Upload to avoid 413 Payload Too Large
        for (let i = 0; i < validItems.length; i++) {
            const item = validItems[i];
            
            // Compress image before upload
            let fileToUpload = item.file;
            try {
                // Compress if larger than 1MB
                if (item.file.size > 1024 * 1024) {
                   fileToUpload = await this.compressImage(item.file);
                }
            } catch (e) {
                console.warn('Falha ao comprimir imagem, tentando envio original', e);
            }

            // Final check for size before sending
            if (fileToUpload.size > 400 * 1024 * 1024) {
                errors.push(`${item.file.name}: Arquivo muito grande (> 400MB).`);
                continue; // Skip this file
            }

            const formData = new FormData();
            
            // Map single item to items[0] structure expected by backend
            // Only append title/description if they have content, allowing backend defaults to work
            if (item.titulo) formData.append('items[0][titulo]', item.titulo);
            if (item.descricao) formData.append('items[0][descricao]', item.descricao);
            formData.append('items[0][tipo]', item.tipo);
            formData.append('items[0][file]', fileToUpload);

            try {
                const response = await fetch(this.uploadUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    if (response.status === 413) {
                        throw new Error('Arquivo muito grande para o servidor.');
                    }
                    const text = await response.text();
                    let errorMsg = `Erro ${response.status}`;
                    try {
                        const json = JSON.parse(text);
                        errorMsg = json.message || errorMsg;
                    } catch (e) {}
                    throw new Error(errorMsg);
                }

                const data = await response.json();
                if (data.success) {
                    successCount++;
                    if (data.images && data.images.length > 0) {
                        newImages.push(...data.images);
                    }
                    // Optional: Update progress toast here
                    this.showToast('info', 'Enviando...', `Enviado ${successCount} de ${validItems.length}...`);
                } else {
                    errors.push(`${item.file.name}: ${data.message}`);
                }
            } catch (error) {
                console.error('Erro no upload:', error);
                errors.push(`${item.file.name}: ${error.message}`);
            }
        }

        this.loading = false;

        if (successCount > 0) {
            this.showToast('success', 'Sucesso!', `Enviado ${successCount} de ${validItems.length} imagens.`);

            // Add images to grid dynamically
            if (newImages.length > 0) {
                this.addImagesToGallery(newImages);
            }

            // Clear files if all successful
            if (errors.length === 0) {
                this.clearFiles();
            } else {
                // Show errors for failed ones
                this.error = true;
                this.errorMessage = `Alguns arquivos falharam: ${errors.join(', ')}`;
                this.showToast('warning', 'Parcialmente Enviado', this.errorMessage);
            }
        } else {
            // All failed
            this.error = true;
            this.errorMessage = errors.length > 0 ? errors.join(', ') : 'Erro ao enviar imagens.';
            this.showToast('error', 'Erro!', this.errorMessage);
        }
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
