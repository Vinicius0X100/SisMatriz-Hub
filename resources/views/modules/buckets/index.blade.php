@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Buckets de mídia</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buckets de mídia</li>
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
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-4 border-0 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div><strong>Erro!</strong> {{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label fw-bold text-muted small">Pesquisar buckets</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control ps-5 rounded-pill" placeholder="Buscar por nome do bucket..." style="height: 45px;" form="bucketFilterForm">
                    </div>
                </div>
                <div class="col-md-6 d-flex justify-content-end gap-2">
                    <div class="text-end me-3 d-none d-md-block">
                        @php
                            $usedGb = $totalUsed / (1024 * 1024 * 1024);
                        @endphp
                        <div class="small text-muted">Uso total em buckets</div>
                        <div class="fw-bold">{{ number_format($usedGb, 2, ',', '.') }} GB</div>
                    </div>
                    <a href="{{ route('buckets.create') }}" class="btn btn-primary rounded-pill px-4 d-flex align-items-center gap-2" style="height: 45px;">
                        <i class="mdi mdi-plus"></i>
                        <span>Novo bucket</span>
                    </a>
                </div>
            </div>
            <form id="bucketFilterForm" action="{{ route('buckets.index') }}" method="GET" class="d-none">
                <input type="hidden" name="search" value="{{ request('search') }}">
            </form>
        </div>
    </div>

    <div class="row g-3">
        @forelse($buckets as $bucket)
            @php
                $used = (int) $bucket->tamanho;
                $max = (int) $bucket->tamanho_max;
                $percent = $max > 0 ? min(100, round($used / $max * 100)) : 0;
            @endphp
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <a href="{{ route('buckets.show', $bucket) }}" class="text-decoration-none text-dark flex-grow-1">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0 fw-bold text-dark">{{ $bucket->name }}</h5>
                                        <small class="text-muted d-block">Bucket #{{ $bucket->rand }}</small>
                                        <small class="text-muted">Região {{ $bucket->regiao ?? 1 }}</small>
                                    </div>
                                </div>
                            </a>
                            <button type="button" class="btn btn-light btn-sm rounded-circle text-danger ms-2" data-bs-toggle="modal" data-bs-target="#destroyBucketModal-{{ $bucket->id }}" title="Destruir bucket">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                        <a href="{{ route('buckets.show', $bucket) }}" class="text-decoration-none text-dark">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted">Uso</small>
                                <small class="text-muted">
                                    {{ number_format($used / (1024 * 1024), 2, ',', '.') }} MB / {{ number_format($max / (1024 * 1024 * 1024), 2, ',', '.') }} GB
                                </small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar @if($percent > 80) bg-danger @elseif($percent > 60) bg-warning @else bg-success @endif" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <small class="text-muted">Criado em {{ \Carbon\Carbon::parse($bucket->created_at)->format('d/m/Y H:i') }}</small>
                                <span class="text-primary small fw-semibold d-flex align-items-center gap-1">
                                    Gerenciar arquivos
                                    <i class="bi bi-arrow-right-short"></i>
                                </span>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="modal fade" id="destroyBucketModal-{{ $bucket->id }}" tabindex="-1" aria-labelledby="destroyBucketModalLabel-{{ $bucket->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold text-danger" id="destroyBucketModalLabel-{{ $bucket->id }}">Destruir bucket</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                            </div>
                            <div class="modal-body">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                    </div>
                                    <div>
                                        <p class="mb-2">Tem certeza que deseja destruir o bucket <strong>{{ $bucket->name }}</strong>?</p>
                                        <p class="mb-0 text-muted small">
                                            Esta ação é permanente e irá remover <strong>todos os arquivos armazenados</strong> dentro deste bucket. 
                                            Os arquivos não poderão ser recuperados após a destruição.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 d-flex justify-content-between">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                                <form action="{{ route('buckets.destroy', $bucket) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger rounded-pill px-4">
                                        Destruir bucket
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-5 text-center text-muted">
                        <i class="bi bi-cloud-slash fs-1 d-block mb-3"></i>
                        <p class="mb-2">Você ainda não possui buckets de mídia.</p>
                        <a href="{{ route('buckets.create') }}" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center gap-2">
                            <i class="mdi mdi-plus"></i>
                            <span>Criar primeiro bucket</span>
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $buckets->withQueryString()->links() }}
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('search');
        const form = document.getElementById('bucketFilterForm');

        if (searchInput && form) {
            let timeout = null;
            searchInput.addEventListener('input', function () {
                clearTimeout(timeout);
                timeout = setTimeout(function () {
                    form.querySelector('input[name="search"]').value = searchInput.value;
                    form.submit();
                }, 600);
            });
        }
    });
</script>
@endsection
