@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Gestão de Protocolos</h2>
            <p class="text-muted small mb-0">Visualize e gerencie as solicitações recebidas.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.protocols.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Buscar por código, descrição ou usuário..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select rounded-pill bg-light border-0" onchange="this.form.submit()">
                        <option value="" {{ request('status') === null ? 'selected' : '' }}>Todos os Status</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Pendentes</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Concluídos</option>
                        <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Reprovados</option>
                        <option value="3" {{ request('status') == '3' ? 'selected' : '' }}>Cancelados</option>
                    </select>
                </div>
                <div class="col-md-5 text-end">
                    <a href="{{ route('admin.protocols.index') }}" class="btn btn-light rounded-pill border"><i class="bi bi-arrow-clockwise me-2"></i>Atualizar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 ps-4 border-0 text-secondary small text-uppercase">Código</th>
                        <th class="py-3 border-0 text-secondary small text-uppercase">Solicitante</th>
                        <th class="py-3 border-0 text-secondary small text-uppercase">Data</th>
                        <th class="py-3 border-0 text-secondary small text-uppercase">Status</th>
                        <th class="py-3 border-0 text-secondary small text-uppercase text-end pe-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($protocols as $protocol)
                        <tr>
                            <td class="ps-4 fw-bold font-monospace text-primary">{{ $protocol->code }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                                        <span class="fw-bold">{{ substr($protocol->user->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $protocol->user->name ?? 'Usuário Removido' }}</div>
                                        <div class="small text-muted text-truncate" style="max-width: 200px;">{{ $protocol->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted small">
                                <div><i class="bi bi-calendar me-1"></i> {{ $protocol->created_at->format('d/m/Y') }}</div>
                                <div><i class="bi bi-clock me-1"></i> {{ $protocol->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                @if($protocol->status == 0)
                                    <span class="badge bg-warning text-dark rounded-pill px-3"><i class="bi bi-clock me-1"></i> Pendente</span>
                                @elseif($protocol->status == 1)
                                    <span class="badge bg-success rounded-pill px-3"><i class="bi bi-check-circle me-1"></i> Concluído</span>
                                @elseif($protocol->status == 2)
                                    <span class="badge bg-danger rounded-pill px-3"><i class="bi bi-x-circle me-1"></i> Reprovado</span>
                                @elseif($protocol->status == 3)
                                    <span class="badge bg-secondary rounded-pill px-3"><i class="bi bi-slash-circle me-1"></i> Cancelado</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#manageModal{{ $protocol->id }}">
                                    Gerenciar
                                </button>
                            </td>
                        </tr>

                        <!-- Manage Modal -->
                        <div class="modal fade" id="manageModal{{ $protocol->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-0 bg-light">
                                        <h5 class="modal-title fw-bold">Gerenciar Protocolo {{ $protocol->code }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="row g-4">
                                            <div class="col-md-7">
                                                <label class="small fw-bold text-muted text-uppercase mb-2">Descrição</label>
                                                <div class="bg-light p-3 rounded-3 border mb-3">
                                                    {{ $protocol->description }}
                                                </div>

                                                <label class="small fw-bold text-muted text-uppercase mb-2">Arquivos Anexados</label>
                                                @if($protocol->files->count() > 0)
                                                    <div class="list-group">
                                                        @foreach($protocol->files as $file)
                                                            <a href="{{ asset('storage/uploads/protocols/' . $file->file_name) }}" target="_blank" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                                                <span class="text-truncate me-3"><i class="bi bi-file-earmark me-2"></i> {{ $file->file_name }}</span>
                                                                <i class="bi bi-download"></i>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small fst-italic">Nenhum arquivo anexado.</div>
                                                @endif
                                            </div>
                                            <div class="col-md-5">
                                                <div class="card border-0 bg-light h-100">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold mb-3">Atualizar Status</h6>
                                                        <form action="{{ route('admin.protocols.update', $protocol->id) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label small">Status</label>
                                                                <select name="status" class="form-select">
                                                                    <option value="0" {{ $protocol->status == 0 ? 'selected' : '' }}>Pendente</option>
                                                                    <option value="1" {{ $protocol->status == 1 ? 'selected' : '' }}>Concluído</option>
                                                                    <option value="2" {{ $protocol->status == 2 ? 'selected' : '' }}>Reprovado</option>
                                                                    <option value="3" {{ $protocol->status == 3 ? 'selected' : '' }}>Cancelado</option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label small">Parecer / Mensagem</label>
                                                                <textarea name="message" class="form-control" rows="4" placeholder="Digite uma mensagem para o solicitante...">{{ $protocol->message }}</textarea>
                                                            </div>
                                                            
                                                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Salvar Alterações</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Nenhum protocolo encontrado com os filtros atuais.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $protocols->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
