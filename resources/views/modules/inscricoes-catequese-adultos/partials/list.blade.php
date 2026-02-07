<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th scope="col" width="50" class="text-center">
                    <div class="form-check d-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" id="select-all-checkbox" onclick="handleSelectAll(this)">
                    </div>
                </th>
                <th scope="col">Situação</th>
                <th scope="col">Nome</th>
                <th scope="col">Sexo</th>
                <th scope="col">Cert. Batismo</th>
                <th scope="col">Cert. 1ª Comunhão</th>
                <th scope="col">Cert. Matrimônio</th>
                <th scope="col">Nascimento</th>
                <th scope="col">Inscrito em</th>
                <th scope="col" class="text-end pe-4">Ações</th>
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
                    $hasMatrimonio = !empty($record->certidao_matrimonio);
                @endphp
                <tr>
                    <td class="text-center">
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $record->id }}" onchange="handleRowCheckbox(this)">
                        </div>
                    </td>
                    <td>
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
                    <td>
                        @if($hasMatrimonio)
                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2">Sim/Anexado</span>
                        @else
                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2">Não Anexado</span>
                        @endif
                    </td>
                    <td class="text-muted">{{ $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '-' }}</td>
                    <td class="text-muted">{{ $record->data_inscricao ? \Carbon\Carbon::parse($record->data_inscricao)->format('d/m/Y H:i') : '-' }}</td>
                    
                    <td class="text-end pe-4">
                        <a href="{{ route('inscricoes-catequese-adultos.print-single', $record->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-2" target="_blank" title="Imprimir Ficha Individual">
                            <i class="bi bi-printer me-1"></i> PDF
                        </a>
                        <button class="btn btn-sm btn-outline-info rounded-pill px-3 me-2" data-bs-toggle="modal" data-bs-target="#detailsModal{{ $record->id }}">
                            <i class="bi bi-eye me-1"></i> Abrir ficha
                        </button>
                        <button class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="openDeleteModal('{{ route('inscricoes-catequese-adultos.destroy', $record->id) }}')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center py-5 text-muted">Nenhuma inscrição encontrada.</td>
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
                    <!-- Status Management -->
                    <div class="mb-4">
                         <!-- Hint Card -->
                         <div class="alert alert-info border-0 bg-info bg-opacity-10 rounded-3 mb-4" role="alert">
                            <div class="d-flex align-items-start">
                                <i class="bi bi-info-circle-fill text-info fs-5 me-2 mt-1"></i>
                                <div>
                                    <h6 class="alert-heading fw-bold mb-2">Alteração de situação da inscrição</h6>
                                    <ul class="list-unstyled mb-2 small text-dark">
                                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> <strong>Aprovar:</strong> Envia para registro geral.</li>
                                        <li class="mb-1"><i class="bi bi-x-circle-fill text-danger me-1"></i> <strong>Reprovar:</strong> Notifica via WhatsApp.</li>
                                        <li class="mb-1"><i class="bi bi-exclamation-circle-fill text-warning me-1"></i> <strong>Pendente:</strong> Aguarda decisão.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center">
                            <form action="{{ route('inscricoes-catequese-adultos.update-status', $record->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                @csrf
                                @method('PUT')
                                <select name="status" class="form-select rounded-pill" style="min-width: 150px;">
                                    <option value="0" {{ $record->status == 0 ? 'selected' : '' }}>Pendente</option>
                                    <option value="1" {{ $record->status == 1 ? 'selected' : '' }}>Aprovado</option>
                                    <option value="2" {{ $record->status == 2 ? 'selected' : '' }}>Reprovado</option>
                                </select>
                                <button type="submit" class="btn btn-primary rounded-pill px-4">
                                    <i class="bi bi-save me-1"></i> Salvar
                                </button>
                            </form>
                        </div>
                    </div>

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
                                <div class="col-md-4">
                                    <label class="text-muted small fw-bold text-uppercase">Estado Civil</label>
                                    <div class="fw-medium text-dark">{{ $record->estado_civil ?? '-' }}</div>
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
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Possui Batismo:</label>
                                @if(!empty($record->certidao_batismo))
                                    <span class="badge bg-success rounded-pill px-3">Sim</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Não/Não Anexado</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Possui Primeira Comunhão:</label>
                                @if(!empty($record->certidao_primeira_comunhao))
                                    <span class="badge bg-success rounded-pill px-3">Sim</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Não/Não Anexado</span>
                                @endif
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase d-block mb-1">Possui Matrimônio:</label>
                                @if(!empty($record->certidao_matrimonio))
                                    <span class="badge bg-success rounded-pill px-3">Sim</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Não/Não Anexado</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
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
                                    'title' => 'Certidão de 1ª Comunhão',
                                    'file' => $record->certidao_primeira_comunhao,
                                    'path' => 'storage/uploads/certidoes/',
                                    'icon' => 'bi-book',
                                    'color' => 'success'
                                ],
                                [
                                    'title' => 'Certidão de Matrimônio',
                                    'file' => $record->certidao_matrimonio,
                                    'path' => 'storage/uploads/certidoes/',
                                    'icon' => 'bi-heart',
                                    'color' => 'danger'
                                ],
                                [
                                    'title' => 'Comprovante de Pagamento',
                                    'file' => $record->comprovante_pagamento,
                                    'path' => 'storage/uploads/comprovantes/',
                                    'icon' => 'bi-cash-coin',
                                    'color' => 'warning'
                                ]
                            ];
                        @endphp

                        @foreach($attachments as $attach)
                            @if(!empty($attach['file']))
                                @php
                                    // Handle full path in database vs relative path logic
                                    // User said: columns already have prefix like catequese_adultos/
                                    // And we should go to /certidoes/
                                    // So full URL: asset('storage/uploads/certidoes/' . $attach['file'])
                                    // Or 'storage/uploads/comprovantes/' . $attach['file']
                                    
                                    // Let's ensure the path is correct based on what user said.
                                    // "na table na coluna certidao_batismo ja tem o catequese_adultos/"
                                    // "ele só precia ir ate /certidoes/ igual nos demais modulos"
                                    
                                    // So if DB has "catequese_adultos/file.pdf", and we use 'storage/uploads/certidoes/', result is 'storage/uploads/certidoes/catequese_adultos/file.pdf'.
                                    // This matches the requirement "as pastas desse modulo ficam em uploads/certidoes/catequese_adultos".
                                    
                                    $fileUrl = asset($attach['path'] . $attach['file']);
                                    $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $attach['file']);
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden group-hover-effect">
                                        <div class="card-body p-3 d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-{{ $attach['color'] }} bg-opacity-10 p-3 text-{{ $attach['color'] }}">
                                                <i class="bi {{ $attach['icon'] }} fs-4"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="card-title fw-bold mb-1 text-truncate" title="{{ $attach['title'] }}">{{ $attach['title'] }}</h6>
                                                <div class="d-flex gap-2 mt-2">
                                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                        <i class="bi bi-eye me-1"></i> Visualizar
                                                    </a>
                                                    <a href="{{ $fileUrl }}" download class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        
                        @if(empty($record->certidao_batismo) && empty($record->certidao_primeira_comunhao) && empty($record->certidao_matrimonio) && empty($record->comprovante_pagamento))
                            <div class="col-12 text-center py-4 text-muted">
                                <i class="bi bi-folder-x fs-3 d-block mb-2 opacity-50"></i>
                                Nenhum documento anexado.
                            </div>
                        @endif
                     </div>
                </div>
            </div>
        </div>
    </div>
@endforeach
