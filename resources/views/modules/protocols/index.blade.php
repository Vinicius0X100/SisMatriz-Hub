@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Meus Protocolos</h2>
            <p class="text-muted small mb-0">Gerencie e acompanhe seus protocolos.</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newProtocolModal">
            <i class="bi bi-plus-lg me-2"></i> Novo Protocolo
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($protocols->isEmpty())
        <div class="text-center py-5">
            <div class="mb-3">
                <i class="bi bi-folder2-open text-muted" style="font-size: 4rem;"></i>
            </div>
            <h4 class="text-muted">Nenhum protocolo encontrado</h4>
            <p class="text-muted">Clique em "Novo Protocolo" para começar.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($protocols as $protocol)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                        <!-- Status Header Strip -->
                        @php
                            $headerColor = 'bg-light';
                            $statusBadge = 'bg-secondary';
                            $statusText = 'Desconhecido';
                            
                            if ($protocol->status == 0) {
                                $headerColor = 'bg-warning bg-opacity-10';
                                $statusBadge = 'bg-warning text-dark';
                                $statusText = 'Em Análise';
                            } elseif ($protocol->status == 1) {
                                $headerColor = 'bg-success bg-opacity-10';
                                $statusBadge = 'bg-success';
                                $statusText = 'Concluído';
                            } elseif ($protocol->status == 2) {
                                $headerColor = 'bg-danger bg-opacity-10';
                                $statusBadge = 'bg-danger';
                                $statusText = 'Reprovado';
                            } elseif ($protocol->status == 3) {
                                $headerColor = 'bg-secondary bg-opacity-10';
                                $statusBadge = 'bg-secondary';
                                $statusText = 'Cancelado';
                            }
                        @endphp
                        
                        <div class="card-header border-0 {{ $headerColor }} py-3 px-4 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark font-monospace">{{ $protocol->code }}</span>
                            <span class="badge {{ $statusBadge }} rounded-pill">{{ $statusText }}</span>
                        </div>

                        <div class="card-body p-4 d-flex flex-column">
                            <div class="mb-3">
                                <p class="text-muted small mb-1"><i class="bi bi-calendar-event me-1"></i> Criado em {{ $protocol->created_at->format('d/m/Y \à\s H:i') }}</p>
                                <h5 class="fw-bold text-dark mb-0 text-truncate" title="{{ $protocol->description }}">{{ Str::limit($protocol->description, 60) }}</h5>
                            </div>

                            <div class="mt-auto">
                                <p class="small fw-bold text-muted mb-3 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Progresso</p>
                                
                                <div class="position-relative d-flex justify-content-between align-items-start">
                                    <!-- Line Background -->
                                    <div class="position-absolute top-0 start-0 w-100 bg-light rounded-pill" style="height: 4px; top: 18px !important; z-index: 0;"></div>
                                    
                                    <!-- Active Line -->
                                    @php
                                        $progressWidth = '0%';
                                        $lineClass = 'bg-success';
                                        
                                        if($protocol->status == 0) { // Pending
                                            $progressWidth = '50%';
                                        } elseif($protocol->status == 1) { // Concluded
                                            $progressWidth = '100%';
                                        } elseif($protocol->status == 2) { // Rejected
                                            $progressWidth = '100%';
                                            $lineClass = 'bg-danger';
                                        } elseif($protocol->status == 3) { // Cancelled
                                            $progressWidth = '100%';
                                            $lineClass = 'bg-secondary';
                                        }
                                    @endphp
                                    <div class="position-absolute top-0 start-0 rounded-pill {{ $lineClass }}" style="width: {{ $progressWidth }}; height: 4px; top: 18px !important; z-index: 0; transition: width 0.5s ease-in-out;"></div>

                                    <!-- Step 1 -->
                                    <div class="text-center position-relative" style="z-index: 1; width: 80px;">
                                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm mx-auto border border-2 border-white" style="width: 40px; height: 40px;">
                                            <i class="bi bi-check-lg"></i>
                                        </div>
                                        <div class="mt-2 small fw-bold text-success">Envio</div>
                                    </div>

                                    <!-- Step 2 -->
                                    <div class="text-center position-relative" style="z-index: 1; width: 80px;">
                                        @php
                                            $step2Class = 'bg-light text-muted border';
                                            $step2Icon = 'bi-circle';
                                            $step2LabelClass = 'text-muted';
                                            
                                            if($protocol->status == 0) {
                                                $step2Class = 'bg-warning text-white';
                                                $step2Icon = 'bi-clock-history';
                                                $step2LabelClass = 'text-warning';
                                            } elseif($protocol->status == 1) {
                                                $step2Class = 'bg-success text-white';
                                                $step2Icon = 'bi-check-lg';
                                                $step2LabelClass = 'text-success';
                                            } elseif($protocol->status == 2) {
                                                $step2Class = 'bg-danger text-white';
                                                $step2Icon = 'bi-x-lg';
                                                $step2LabelClass = 'text-danger';
                                            } elseif($protocol->status == 3) {
                                                $step2Class = 'bg-secondary text-white';
                                                $step2Icon = 'bi-slash-circle';
                                                $step2LabelClass = 'text-secondary';
                                            }
                                        @endphp
                                        <div class="rounded-circle {{ $step2Class }} d-flex align-items-center justify-content-center shadow-sm mx-auto border border-2 border-white" style="width: 40px; height: 40px;">
                                            <i class="bi {{ $step2Icon }}"></i>
                                        </div>
                                        <div class="mt-2 small fw-bold {{ $step2LabelClass }}">Análise</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white border-top-0 p-4 pt-0">
                            <button class="btn btn-outline-primary w-100 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#viewProtocolModal{{ $protocol->id }}">
                                <i class="bi bi-eye me-2"></i> Ver Detalhes
                            </button>
                        </div>
                    </div>
                </div>

                <!-- View Modal -->
                <div class="modal fade" id="viewProtocolModal{{ $protocol->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header border-0 bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 me-3">
                                        <i class="bi bi-shield-check fs-4"></i>
                                    </div>
                                    <div>
                                        <h5 class="modal-title fw-bold">Protocolo {{ $protocol->code }}</h5>
                                        <p class="mb-0 small text-muted">Criado em {{ $protocol->created_at->format('d/m/Y \à\s H:i') }}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="mb-4">
                                    <label class="small fw-bold text-muted text-uppercase mb-2">Descrição da Solicitação</label>
                                    <div class="bg-light p-3 rounded-3 border">
                                        {{ $protocol->description }}
                                    </div>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <label class="small fw-bold text-muted text-uppercase mb-2">Status Atual</label>
                                        <div>
                                            @if($protocol->status == 0) 
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-clock me-2"></i> Pendente / Em Análise</span>
                                            @elseif($protocol->status == 1) 
                                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-2"></i> Concluído</span>
                                            @elseif($protocol->status == 2) 
                                                <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-2"></i> Reprovado</span>
                                            @elseif($protocol->status == 3) 
                                                <span class="badge bg-secondary px-3 py-2 rounded-pill"><i class="bi bi-slash-circle me-2"></i> Cancelado</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($protocol->message)
                                        <div class="col-md-6">
                                            <label class="small fw-bold text-muted text-uppercase mb-2">Parecer Administrativo</label>
                                            <div class="bg-info bg-opacity-10 border border-info text-info p-3 rounded-3">
                                                {{ $protocol->message }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if($protocol->files->count() > 0)
                                    <div class="border-top pt-4">
                                        <label class="small fw-bold text-muted text-uppercase mb-3 d-flex align-items-center">
                                            <i class="bi bi-paperclip me-2"></i> Arquivos Anexados ({{ $protocol->files->count() }})
                                        </label>
                                        
                                        <div class="row g-3">
                                            @foreach($protocol->files as $file)
                                                <div class="col-12">
                                                    @php
                                                        $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                                        $filePath = asset('storage/uploads/protocols/' . $file->file_name);
                                                    @endphp

                                                    <div class="card border rounded-3 overflow-hidden shadow-sm">
                                                        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                                            <span class="small fw-bold text-truncate" style="max-width: 80%;">{{ $file->file_name }}</span>
                                                            <a href="{{ $filePath }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill" download>
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                        </div>
                                                        <div class="card-body p-0 text-center bg-light bg-opacity-50">
                                                            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                                                                <div class="p-3">
                                                                    <img src="{{ $filePath }}" class="img-fluid rounded border shadow-sm" style="max-height: 300px; width: auto;" alt="Preview">
                                                                </div>
                                                            @elseif($extension === 'pdf')
                                                                <div style="height: 400px;">
                                                                    <iframe src="{{ $filePath }}" class="w-100 h-100 border-0"></iframe>
                                                                </div>
                                                            @else
                                                                <div class="p-5">
                                                                    @php
                                                                        $icon = 'bi-file-earmark';
                                                                        $color = 'text-secondary';
                                                                        if(in_array($extension, ['doc', 'docx'])) { $icon = 'bi-file-earmark-word'; $color = 'text-primary'; }
                                                                        elseif(in_array($extension, ['xls', 'xlsx', 'csv'])) { $icon = 'bi-file-earmark-excel'; $color = 'text-success'; }
                                                                        elseif(in_array($extension, ['ppt', 'pptx'])) { $icon = 'bi-file-earmark-slides'; $color = 'text-warning'; }
                                                                        elseif(in_array($extension, ['zip', 'rar', '7z'])) { $icon = 'bi-file-earmark-zip'; $color = 'text-danger'; }
                                                                        elseif(in_array($extension, ['txt'])) { $icon = 'bi-file-earmark-text'; $color = 'text-dark'; }
                                                                    @endphp
                                                                    <i class="bi {{ $icon }} {{ $color }}" style="font-size: 4rem;"></i>
                                                                    <p class="mt-3 mb-0 text-muted small">Visualização não disponível para este formato.</p>
                                                                    <a href="{{ $filePath }}" target="_blank" class="btn btn-primary rounded-pill btn-sm mt-3 px-4">
                                                                        Baixar Arquivo
                                                                    </a>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="mt-4">
            {{ $protocols->links() }}
        </div>
    @endif
</div>

<!-- New Protocol Modal -->
<div class="modal fade" id="newProtocolModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 bg-primary text-white">
                <div class="d-flex align-items-center">
                    <i class="bi bi-plus-circle-fill fs-4 me-2"></i>
                    <h5 class="modal-title fw-bold">Novo Protocolo</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('protocols.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-light border-primary border-start border-4 shadow-sm mb-4">
                        <div class="d-flex">
                            <i class="bi bi-info-circle-fill text-primary fs-5 me-3"></i>
                            <div>
                                <h6 class="fw-bold text-primary mb-1">Informações Importantes</h6>
                                <p class="small text-muted mb-0">Descreva detalhadamente sua solicitação e anexe todos os documentos necessários para agilizar a análise.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold text-dark">Descrição da Solicitação <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3 border-secondary border-opacity-25 p-3" id="description" name="description" rows="5" required placeholder="Digite aqui os detalhes do seu protocolo..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="files" class="form-label fw-bold text-dark">Anexar Arquivos</label>
                        <div class="p-4 border border-2 border-dashed rounded-4 bg-light text-center position-relative hover-shadow transition-all">
                            <i class="bi bi-cloud-arrow-up text-primary fs-1 mb-2 d-block"></i>
                            <span class="fw-bold text-dark d-block mb-1">Arraste arquivos ou clique para selecionar</span>
                            <span class="small text-muted d-block mb-3">Suporta PDF, Imagens, Word, Excel (Máx 10 arquivos)</span>
                            <input class="form-control position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" type="file" id="files" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" style="cursor: pointer;">
                        </div>
                        <div id="fileList" class="mt-3 small text-muted"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-send-fill me-2"></i> Enviar Protocolo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('files').addEventListener('change', function(e) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '';
    
    if (this.files.length > 0) {
        const list = document.createElement('ul');
        list.className = 'list-unstyled mb-0';
        
        Array.from(this.files).forEach(file => {
            const li = document.createElement('li');
            li.className = 'd-flex align-items-center mb-1';
            li.innerHTML = `<i class="bi bi-check-circle-fill text-success me-2"></i> ${file.name} <span class="text-muted ms-2">(${formatBytes(file.size)})</span>`;
            list.appendChild(li);
        });
        
        fileList.appendChild(list);
    }
});

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}
</script>

<style>
.hover-shadow:hover {
    background-color: #f8f9fa !important;
    border-color: var(--bs-primary) !important;
}
</style>
@endsection
