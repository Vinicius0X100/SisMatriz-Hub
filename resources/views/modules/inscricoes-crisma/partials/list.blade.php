<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th class="border-0 rounded-start ps-4">Situação</th>
                <th class="border-0">Nome</th>
                <th class="border-0">Sexo</th>
                <th class="border-0">Cert. Batismo</th>
                <th class="border-0">Cert. 1ª Comunhão</th>
                <th class="border-0">Nascimento</th>
                <th class="border-0">Inscrito em</th>
                <th class="border-0">Pagamento</th>
                <th class="border-0 rounded-end text-end pe-4">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                @php
                    // Status Badge Logic
                    $statusLabel = 'Pendente';
                    $statusColor = 'warning';
                    if ($record->status == 1) {
                        $statusLabel = 'Aprovado';
                        $statusColor = 'success';
                    } elseif ($record->status == 2) {
                        $statusLabel = 'Reprovado';
                        $statusColor = 'danger';
                    }

                    // Payment Status Logic
                    $paymentLabel = 'Pendente';
                    $paymentColor = 'warning';
                    if ($record->taxaPaga) {
                        $paymentLabel = 'Pago';
                        $paymentColor = 'success';
                    }

                    // Attachments Logic
                    $hasBatismo = !empty($record->certidao_batismo);
                    $hasEucaristia = !empty($record->certidao_primeira_comunhao);
                @endphp
                <tr>
                    <td class="ps-4">
                        <span class="badge bg-{{ $statusColor }} bg-opacity-10 text-{{ $statusColor }} rounded-pill px-3">
                            <i class="bi bi-circle-fill me-1 small"></i> {{ $statusLabel }}
                        </span>
                    </td>
                    <td class="fw-bold text-dark">{{ $record->nome }}</td>
                    <td class="text-muted">{{ $record->sexo }}</td>
                    <td>
                        @if($hasBatismo)
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">Sim/Anexado</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2">Não Anexado</span>
                        @endif
                    </td>
                    <td>
                        @if($hasEucaristia)
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">Sim/Anexado</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2">Não Anexado</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '-' }}</td>
                    <td class="text-muted">{{ $record->criado_em ? \Carbon\Carbon::parse($record->criado_em)->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <span class="badge bg-{{ $paymentColor }} bg-opacity-10 text-{{ $paymentColor }} rounded-pill px-2">
                            {{ $paymentLabel }}
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-info rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $record->id }}">
                            <i class="bi bi-eye me-1"></i> Detalhes
                        </button>
                        <form action="{{ route('inscricoes-crisma.destroy', $record->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir esta inscrição?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">Nenhuma inscrição encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-end mt-4">
    {{ $records->appends(request()->query())->links('partials.pagination') }}
</div>

@foreach($records as $record)
    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-person fs-4"></i>
                        <h5 class="modal-title fw-bold">Detalhes da Inscrição</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 bg-light bg-opacity-10">
                    <!-- Section: Personal Info -->
                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase">Nome completo</label>
                                    <div class="fw-medium text-dark">{{ $record->nome }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase">Sexo</label>
                                    <div class="fw-medium text-dark">{{ $record->sexo }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase">Data de Nascimento</label>
                                    <div class="fw-medium text-dark">{{ $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '-' }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase">Nacionalidade</label>
                                    <div class="fw-medium text-dark">{{ $record->nacionalidade ?? '-' }}</div>
                                </div>
                                <div class="col-md-2">
                                    <label class="text-muted small fw-bold text-uppercase">Estado</label>
                                    <div class="fw-medium text-dark">{{ $record->estado ?? '-' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small fw-bold text-uppercase">CPF</label>
                                    <div class="fw-medium text-dark">{{ $record->cpf ?? 'Não informado' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small fw-bold text-uppercase">CEP</label>
                                    <div class="fw-medium text-dark">{{ $record->cep ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Address -->
                    <div class="row g-4 mb-4 border-top pt-4">
                         <div class="col-12">
                            <div class="row g-3">
                                <div class="col-md-9">
                                    <label class="text-muted small fw-bold text-uppercase">Endereço residencial</label>
                                    <div class="fw-medium text-dark">{{ $record->endereco ?? '-' }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small fw-bold text-uppercase">Número da residência</label>
                                    <div class="fw-medium text-dark">{{ $record->numero ?? '-' }}</div>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Section: Contact -->
                    <div class="row g-4 mb-4 border-top pt-4">
                         <div class="col-12">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="text-muted small fw-bold text-uppercase">Telefone principal</label>
                                    <div class="fw-medium text-dark">{{ $record->telefone1 ?? '-' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="text-muted small fw-bold text-uppercase">Telefone secundário</label>
                                    <div class="fw-medium text-dark">{{ $record->telefone2 ?? '-' }}</div>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Section: Parents -->
                    <div class="row g-4 mb-4 border-top pt-4">
                         <div class="col-12">
                            <label class="text-muted small fw-bold text-uppercase">Filiação (Pai e Mãe)</label>
                            <div class="fw-medium text-dark">
                                <div>{{ $record->filiacao ?? '-' }}</div>
                            </div>
                         </div>
                    </div>

                    <!-- Section: Religious Info & Status -->
                    <div class="row g-4 mb-4 border-top pt-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Possui Batismo:</label>
                                @if(!empty($record->certidao_batismo))
                                    <span class="badge bg-success rounded-pill px-3">Sim</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Não/Não Anexado</span>
                                @endif
                            </div>
                            <div>
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Situação:</label>
                                <span class="badge bg-{{ $statusColor }} text-{{ $statusColor }} bg-opacity-10 rounded-pill px-3 border border-{{ $statusColor }}">
                                    <i class="bi bi-circle-fill me-1 small"></i> {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Possui Primeira Comunhão:</label>
                                @if(!empty($record->certidao_primeira_comunhao))
                                    <span class="badge bg-success rounded-pill px-3">Sim</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Não/Não Anexado</span>
                                @endif
                            </div>
                            <div>
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Taxa escolhida:</label>
                                @if($record->taxa)
                                    <div class="mb-2 fw-medium text-dark">{{ $record->taxa->nome }} - R$ {{ number_format($record->taxa->valor, 2, ',', '.') }}</div>
                                    <div>
                                        @if($record->taxaPaga)
                                            @if(empty($record->comprovante_pagamento))
                                                <div class="alert alert-warning d-flex align-items-center mb-0 p-2 small" role="alert">
                                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                    <div>
                                                        Consta como pago mas não foi anexado, validar o pagamento falando com a pessoa que se inscreveu.
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 border border-success">Pago</span>
                                            @endif
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2 border border-warning">Pendente</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-muted">Nenhuma taxa informada</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                     <div class="row g-3 border-top pt-4">
                        <div class="col-12 mb-2">
                             <h6 class="fw-bold text-primary"><i class="bi bi-paperclip me-1"></i> Anexos e Comprovantes</h6>
                        </div>

                        @php
                            // Helper logic for attachments
                            $attachments = [
                                [
                                    'title' => 'Certidão de Batismo',
                                    'file' => $record->certidao_batismo,
                                    'path' => 'storage/uploads/certidoes/',
                                    'icon' => 'bi-droplet',
                                    'color' => 'primary'
                                ],
                                [
                                    'title' => 'Certidão 1ª Eucaristia',
                                    'file' => $record->certidao_primeira_comunhao,
                                    'path' => 'storage/uploads/certidoes/',
                                    'icon' => 'bi-book',
                                    'color' => 'info'
                                ],
                                [
                                    'title' => 'Comprovante Pagamento',
                                    'file' => $record->comprovante_pagamento,
                                    'path' => 'storage/uploads/comprovantes/',
                                    'icon' => 'bi-receipt',
                                    'color' => 'success'
                                ]
                            ];
                        @endphp

                        @foreach($attachments as $att)
                            @php
                                $url = $att['file'] ? asset($att['path'] . $att['file']) : null;
                                $ext = $att['file'] ? strtolower(pathinfo($att['file'], PATHINFO_EXTENSION)) : '';
                                $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                $isPdf = $ext === 'pdf';
                            @endphp
                            <div class="col-md-4">
                                <div class="card h-100 border bg-white rounded-3 overflow-hidden">
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center position-relative" style="height: 200px;">
                                        @if($url)
                                            @if($isImage)
                                                <img src="{{ $url }}" class="w-100 h-100" style="object-fit: contain;" alt="{{ $att['title'] }}">
                                            @elseif($isPdf)
                                                <embed src="{{ $url }}" type="application/pdf" width="100%" height="100%">
                                            @else
                                                <i class="bi {{ $att['icon'] }} fs-1 text-{{ $att['color'] }}"></i>
                                            @endif
                                            
                                            <a href="{{ $url }}" target="_blank" class="btn btn-sm btn-light position-absolute top-0 end-0 m-2 shadow-sm rounded-circle" title="Abrir em nova aba">
                                                <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        @else
                                            <div class="text-center text-muted">
                                                <i class="bi {{ $att['icon'] }} fs-1 opacity-25 mb-2 d-block"></i>
                                                <span class="small">Sem anexo</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-body text-center p-3 border-top">
                                        <h6 class="card-title small fw-bold mb-0 text-truncate" title="{{ $att['title'] }}">{{ $att['title'] }}</h6>
                                        @if($url)
                                            <a href="{{ $url }}" download class="btn btn-sm btn-outline-{{ $att['color'] }} rounded-pill w-100 mt-2">
                                                <i class="bi bi-download me-1"></i> Baixar
                                            </a>
                                        @else
                                            <button class="btn btn-sm btn-light text-muted border w-100 mt-2" disabled>
                                                Não anexado
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 bg-light bg-opacity-10">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
