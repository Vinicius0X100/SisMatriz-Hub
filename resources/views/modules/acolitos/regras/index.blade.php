@extends('layouts.app')

@section('title', 'Regras de Escala Automática')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Regras de Escala Automática</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.index') }}" class="text-decoration-none">Acólitos</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acolitos.escalas.index') }}" class="text-decoration-none">Escalas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Regras</li>
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
            <form action="{{ route('acolitos.escalas.regras.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="ent_id" class="form-label fw-bold">Selecione a Comunidade</label>
                    <select name="ent_id" id="ent_id" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Selecione...</option>
                        @foreach($entidades as $entidade)
                            <option value="{{ $entidade->ent_id }}" {{ $selected_ent_id == $entidade->ent_id ? 'selected' : '' }}>
                                {{ $entidade->ent_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($selected_ent_id)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4">Definir Quantidades e Funções por Dia da Semana</h5>
            
            <form action="{{ route('acolitos.escalas.regras.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ent_id" value="{{ $selected_ent_id }}">

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" rowspan="2" style="width: 15%">Dia da Semana</th>
                                <th class="text-center" colspan="2">Acólitos</th>
                                <th class="text-center" colspan="2">Coroinhas</th>
                                <th class="text-center" rowspan="2" style="width: 20%">Função Padrão (Coroinhas)</th>
                                <th class="text-center" rowspan="2" style="width: 15%">Máx. Missas/Mês (Teto Global)</th>
                            </tr>
                            <tr>
                                <th class="text-center">Mínimo</th>
                                <th class="text-center">Máximo</th>
                                <th class="text-center">Mínimo</th>
                                <th class="text-center">Máximo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dias_semana as $id_dia => $nome_dia)
                                @php
                                    $regra = $regras->get($id_dia);
                                @endphp
                                <tr>
                                    <td class="text-center fw-bold">{{ $nome_dia }}</td>
                                    <td>
                                        <input type="number" name="regras[{{ $id_dia }}][min_acolitos]" class="form-control text-center" value="{{ $regra->min_acolitos ?? 0 }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="regras[{{ $id_dia }}][max_acolitos]" class="form-control text-center" value="{{ $regra->max_acolitos ?? 0 }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="regras[{{ $id_dia }}][min_coroinhas]" class="form-control text-center" value="{{ $regra->min_coroinhas ?? 0 }}" min="0" required>
                                    </td>
                                    <td>
                                        <input type="number" name="regras[{{ $id_dia }}][max_coroinhas]" class="form-control text-center" value="{{ $regra->max_coroinhas ?? 0 }}" min="0" required>
                                    </td>
                                    <td>
                                        <select name="regras[{{ $id_dia }}][coroinha_funcao_id]" class="form-select">
                                            <option value="">Nenhuma (Aleatória)</option>
                                            @foreach($funcoes as $funcao)
                                                <option value="{{ $funcao->f_id }}" {{ ($regra->coroinha_funcao_id ?? '') == $funcao->f_id ? 'selected' : '' }}>
                                                    {{ $funcao->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="regras[{{ $id_dia }}][max_serves_per_month]" class="form-control text-center" value="{{ $regra->max_serves_per_month ?? 4 }}" min="0" required title="0 para sem limite">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">
                        <i class="bi bi-save me-2"></i> Salvar Regras
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
