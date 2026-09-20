<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="bg-light">
            <tr>
                <th scope="col" width="40" class="text-center">
                    <div class="form-check d-flex justify-content-center">
                        <input class="form-check-input" type="checkbox" id="selectAll">
                    </div>
                </th>
                <th class="text-nowrap">Título</th>
                <th class="text-nowrap">Importância</th>
                <th class="text-nowrap">Origem</th>
                <th class="text-nowrap">Enviado em</th>
                <th class="text-nowrap">Visualizações</th>
                <th class="text-end text-nowrap">Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
                @php
                    $importanceMap = [0 => ['Normal', 'secondary'], 1 => ['Médio', 'warning'], 2 => ['Alto', 'danger']];
                    $importance = $importanceMap[$post->level_importance] ?? ['Indefinido', 'secondary'];
                    $deviceIcon = match($post->device) {
                        2 => 'bi-android',
                        3 => 'bi-apple',
                        default => 'bi-globe',
                    };
                    $deviceLabel = match($post->device) {
                        1 => 'Web',
                        2 => 'Android',
                        3 => 'iOS',
                        default => 'Indefinido',
                    };
                    $thumbUrl = null;
                    $attachmentUrl = '';
                    if ($post->anexo) {
                        $fullPath = storage_path('app/public/' . $post->anexo);
                        if (file_exists($fullPath)) {
                            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                            $attachmentUrl = asset('storage/' . $post->anexo);
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                                $thumbUrl = asset('storage/' . $post->anexo);
                            }
                        }
                    }
                @endphp
                <tr
                    data-id="{{ $post->id }}"
                    data-title="{{ $post->title }}"
                    data-legend="{{ $post->legend }}"
                    data-level="{{ (int) $post->level_importance }}"
                    data-importance-label="{{ $importance[0] }}"
                    data-importance-badge="{{ $importance[1] }}"
                    data-device-label="{{ $deviceLabel }}"
                    data-device-icon="{{ $deviceIcon }}"
                    data-send-at="{{ optional($post->send_at)->format('d/m/Y H:i') }}"
                    data-image-url="{{ $thumbUrl }}"
                    data-attachment-url="{{ $attachmentUrl }}"
                >
                    <td class="text-center" width="40">
                        <div class="form-check d-flex justify-content-center">
                            <input class="form-check-input row-checkbox" type="checkbox" value="{{ $post->id }}">
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; overflow: hidden;">
                                @if($thumbUrl)
                                    <img src="{{ $thumbUrl }}" alt="{{ $post->title }}" class="w-100 h-100" style="object-fit: cover; object-position: center;">
                                @else
                                    <i class="bi bi-image text-muted"></i>
                                @endif
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $post->title }}</div>
                                <div class="text-muted small text-truncate" style="max-width: 260px;">{{ $post->legend }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $importance[1] }}">{{ $importance[0] }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi {{ $deviceIcon }}"></i>
                            <span class="text-muted small">{{ $deviceLabel }}</span>
                        </div>
                    </td>
                    <td>{{ optional($post->send_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $post->views ?? 0 }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-light rounded-pill px-3 btn-view-aviso" title="Ver aviso">
                                <i class="bi bi-eye"></i>
                            </button>
                            <a href="{{ route('avisos.edit', $post) }}" class="btn btn-light rounded-pill px-3" title="Editar aviso">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-light rounded-pill px-3 text-danger btn-delete-aviso" title="Excluir aviso"
                                    data-id="{{ $post->id }}"
                                    data-title="{{ $post->title }}"
                                    data-url="{{ route('avisos.destroy', $post) }}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Nenhum aviso cadastrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $posts->appends(request()->query())->links() }}
</div>
