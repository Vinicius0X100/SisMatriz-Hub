@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Fazer Chamada</h2>
            <p class="text-muted small mb-0">Registre presenças e faltas nas celebrações escaladas.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.index') }}" class="text-decoration-none">Acólitos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Fazer Chamada</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                <div><strong>Sucesso!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('acolitos.chamada') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Escala</label>
                    <select name="escala_id" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">Selecione...</option>
                        @foreach($escalas as $escala)
                            <option value="{{ $escala->es_id }}" {{ (int)$selectedEscalaId === (int)$escala->es_id ? 'selected' : '' }}>
                                {{ ucfirst($escala->month) }} / {{ $escala->year }} - {{ $escala->church }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Celebração/Data</label>
                    <select name="d_id" class="form-select bg-light" onchange="this.form.submit()" {{ $datas->isEmpty() ? 'disabled' : '' }}>
                        <option value="">{{ $datas->isEmpty() ? 'Selecione uma escala primeiro' : 'Selecione...' }}</option>
                        @foreach($datas as $data)
                            <option value="{{ $data->d_id }}" {{ (int)$selectedDId === (int)$data->d_id ? 'selected' : '' }}>
                                {{ str_pad($data->data, 2, '0', STR_PAD_LEFT) }}/{{ ucfirst($data->escala->month ?? '') }} {{ substr($data->hora, 0, 5) }} - {{ $data->celebration }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                        <i class="bi bi-search me-2"></i>Carregar Chamada
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($celebration && $escalados->count() > 0)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="mb-4">
                    <h5 class="fw-bold mb-1">{{ $celebration->celebration }}</h5>
                    <p class="text-muted small mb-0">
                        Data: {{ str_pad($celebration->data, 2, '0', STR_PAD_LEFT) }}/{{ $celebration->escala->month }} {{ $celebration->escala->year }}
                        • Hora: {{ substr($celebration->hora, 0, 5) }}
                        • Local: {{ $celebration->entidade->ent_name ?? '-' }}
                    </p>
                </div>

                <form action="{{ route('acolitos.attendance.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="d_id" value="{{ $celebration->d_id }}">
                    @php
                        $monthsMap = [
                            'janeiro' => 1,'fevereiro' => 2,'março' => 3,'marco' => 3,'abril' => 4,'maio' => 5,'junho' => 6,
                            'julho' => 7,'agosto' => 8,'setembro' => 9,'outubro' => 10,'novembro' => 11,'dezembro' => 12
                        ];
                        $monthName = mb_strtolower($celebration->escala->month, 'UTF-8');
                        $monthNum = $monthsMap[$monthName] ?? date('n');
                        $dateStr = sprintf('%04d-%02d-%02d', (int)$celebration->escala->year, (int)$monthNum, (int)$celebration->data);
                    @endphp
                    <input type="hidden" name="data" value="{{ $dateStr }}">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Acólito/Coroinha</th>
                                    <th>Função</th>
                                    <th>Status</th>
                                    <th>Gravidade</th>
                                    <th>Justificativa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($escalados as $index => $item)
                                    @php
                                        $existing = isset($existingByAcolito) ? $existingByAcolito->get($item->acolito_id) : null;
                                        $isAbsent = $existing ? !$existing->status : false;
                                        $justifyType = 'sem';
                                        if ($isAbsent) {
                                            $justifyType = ($existing->grave ?? false) ? 'sem' : (optional($existing->justificativa)->motivo ? 'com' : 'sem');
                                        }
                                        $justifyValue = $isAbsent ? (optional($existing->justificativa)->motivo ?? '') : '';
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $item->acolito->name }}</div>
                                        </td>
                                        <td>{{ $item->funcao->title ?? '-' }}</td>
                                        <td>
                                            <select name="registros[{{ $index }}][status]" class="form-select form-select-sm status-select">
                                                <option value="present" {{ $existing ? ($existing->status ? 'selected' : '') : '' }}>Presente</option>
                                                <option value="absent" {{ $existing ? (!$existing->status ? 'selected' : '') : '' }}>Falta</option>
                                            </select>
                                            <input type="hidden" name="registros[{{ $index }}][acolito_id]" value="{{ $item->acolito_id }}">
                                        </td>
                                        <td>
                                            <select name="registros[{{ $index }}][justify_type]" class="form-select form-select-sm justify-type-select">
                                                <option value="sem" {{ $justifyType === 'sem' ? 'selected' : '' }}>Sem justificativa (grave)</option>
                                                <option value="com" {{ $justifyType === 'com' ? 'selected' : '' }}>Com justificativa</option>
                                            </select>
                                        </td>
                                        <td>
                                            <textarea name="registros[{{ $index }}][motivo]" class="form-control form-control-sm justify-text" rows="1" style="display: {{ ($isAbsent && $justifyType === 'com') ? 'block' : 'none' }};" placeholder="Motivo da falta">{{ $justifyValue }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success rounded-pill px-4">
                            <i class="bi bi-check2-circle me-2"></i>Salvar Chamada
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @elseif($selectedEscalaId && $selectedDId)
        <div class="alert alert-warning border-0 rounded-4 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Nenhum acólito escalado</strong>
                    <p class="mb-0 small text-muted">Não há acólitos vinculados a esta celebração para chamada.</p>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('tr').forEach(function (row) {
            const statusSelect = row.querySelector('.status-select');
            const justifyTypeSelect = row.querySelector('.justify-type-select');
            const justifyText = row.querySelector('.justify-text');

            if (!statusSelect || !justifyTypeSelect || !justifyText) return;

            function updateVisibility() {
                const isAbsent = statusSelect.value === 'absent';
                const isWithJustify = justifyTypeSelect.value === 'com';
                justifyTypeSelect.disabled = !isAbsent;
                justifyText.style.display = isAbsent && isWithJustify ? 'block' : 'none';
            }

            statusSelect.addEventListener('change', updateVisibility);
            justifyTypeSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        });
    });
</script>
@endsection
