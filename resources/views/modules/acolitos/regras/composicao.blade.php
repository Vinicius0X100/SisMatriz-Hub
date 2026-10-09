@extends('layouts.app')

@section('title', 'Regras de Composição de Equipe')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Regras de Composição de Equipe</h2>
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

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link" href="{{ route('acolitos.escalas.regras.index', ['ent_id' => $selected_ent_id]) }}">Regras da Comunidade</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active fw-bold" href="#">Regras de Composição Individual</a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('acolitos.escalas.regras.composicao') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
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
                
                @if($selected_ent_id)
                <div class="col-md-6">
                    <label for="acolito_id" class="form-label fw-bold">Selecione o Membro</label>
                    <select name="acolito_id" id="acolito_id" class="form-select rounded-pill" onchange="this.form.submit()">
                        <option value="">Selecione...</option>
                        @foreach($acolitos as $acolito)
                            <option value="{{ $acolito->id }}" {{ $selected_acolito_id == $acolito->id ? 'selected' : '' }}>
                                {{ $acolito->name }} ({{ $acolito->type == 0 ? 'Acólito' : 'Coroinha' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
            </form>
        </div>
    </div>

    @if($selected_acolito_id)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4">Definir Regras de Composição para o Membro</h5>
            
            <form action="{{ route('acolitos.escalas.regras.composicao.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ent_id" value="{{ $selected_ent_id }}">
                <input type="hidden" name="acolito_id" value="{{ $selected_acolito_id }}">

                @php
                    $regraIncompatibilidade = $regras_composicao->get('incompatibilidade');
                    $idsIncompativeis = $regraIncompatibilidade ? ($regraIncompatibilidade->configuracao['acolitos_incompativeis'] ?? []) : [];

                    $regraObrigatoria = $regras_composicao->get('companhia_obrigatoria');
                    $regraMinimo = $regras_composicao->get('acompanhamento_minimo');
                    $regraUnificada = $regras_composicao->get('acompanhamento');

                    $idsObrigatorios = [];
                    $modoObrigatorio = 'qualquer';
                    $minimoAcompanhantes = 0;
                    $categoria = 'qualquer';

                    if ($regraUnificada) {
                        $idsObrigatorios = $regraUnificada->configuracao['acompanhantes_especificos'] ?? [];
                        $modoObrigatorio = $regraUnificada->configuracao['modo'] ?? 'qualquer';
                        $minimoAcompanhantes = $regraUnificada->configuracao['minimo_acompanhantes'] ?? 0;
                        $categoria = $regraUnificada->configuracao['categoria'] ?? 'qualquer';
                    } else {
                        if ($regraObrigatoria) {
                            $idsObrigatorios = $regraObrigatoria->configuracao['acolitos_obrigatorios'] ?? [];
                            $modoObrigatorio = $regraObrigatoria->configuracao['modo'] ?? 'qualquer';
                        }
                        if ($regraMinimo) {
                            $minimoAcompanhantes = $regraMinimo->configuracao['minimo_acompanhantes'] ?? 0;
                            $catLegacy = $regraMinimo->configuracao['categorias'] ?? [];
                            // cast to int array if necessary
                            $catLegacy = array_map('intval', $catLegacy);
                            if (in_array(0, $catLegacy) && !in_array(1, $catLegacy)) $categoria = '0';
                            if (in_array(1, $catLegacy) && !in_array(0, $catLegacy)) $categoria = '1';
                        }
                    }
                @endphp

                <!-- 1. Incompatibilidade -->
                <div class="mb-4 p-3 border rounded">
                    <h6 class="fw-bold text-danger"><i class="bi bi-x-circle me-2"></i>Incompatibilidade (Não pode servir com)</h6>
                    <p class="text-muted small">Selecione os membros que NÃO DEVEM ser escalados na mesma equipe que este membro.</p>
                    <select name="incompatibilidade_ids[]" class="form-select select2-multiple" multiple data-placeholder="Buscar integrantes...">
                        @foreach($acolitos as $acolito)
                            @if($acolito->id != $selected_acolito_id)
                                <option value="{{ $acolito->id }}" {{ in_array($acolito->id, $idsIncompativeis) ? 'selected' : '' }}>
                                    {{ $acolito->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <!-- 2. Acompanhamento na Celebração (Unificado) -->
                <div class="mb-4 p-3 border rounded">
                    <h6 class="fw-bold text-primary"><i class="bi bi-people me-2"></i>Acompanhamento na Celebração</h6>
                    <p class="text-muted small">Defina se este membro precisa de uma quantidade mínima de outros integrantes na equipe para participar, independentemente de quem sejam.</p>
                    
                    <div class="row align-items-center mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Qtd Mínima de Acompanhantes</label>
                            <input type="number" name="minimo_acompanhantes" class="form-control" value="{{ $minimoAcompanhantes }}" min="0">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Categoria dos Acompanhantes</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="acompanhamento_categoria" id="cat_qualquer" value="qualquer" {{ $categoria === 'qualquer' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat_qualquer">Qualquer acólito ou coroinha</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="acompanhamento_categoria" id="cat_0" value="0" {{ $categoria === '0' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat_0">Somente acólitos</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="acompanhamento_categoria" id="cat_1" value="1" {{ $categoria === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="cat_1">Somente coroinhas</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="accordion" id="advancedAccordion">
                        <div class="accordion-item border-0 bg-light rounded-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-transparent shadow-none fw-semibold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#advancedSettings">
                                    <i class="bi bi-gear-fill me-2"></i> Configuração Avançada: Acompanhantes Específicos
                                </button>
                            </h2>
                            <div id="advancedSettings" class="accordion-collapse collapse {{ !empty($idsObrigatorios) ? 'show' : '' }}" data-bs-parent="#advancedAccordion">
                                <div class="accordion-body pt-0">
                                    <p class="small text-muted mb-2 mt-2">
                                        Use apenas se o integrante depender de <strong>pessoas específicas</strong> para servir. O algoritmo sempre tentará preservar o rodízio. Se os específicos não puderem ser escalados, este integrante também não será.
                                    </p>
                                    <select name="companhia_ids[]" class="form-select select2-multiple mb-2" multiple data-placeholder="Buscar integrantes específicos...">
                                        @foreach($acolitos as $acolito)
                                            @if($acolito->id != $selected_acolito_id)
                                                <option value="{{ $acolito->id }}" {{ in_array($acolito->id, $idsObrigatorios) ? 'selected' : '' }}>
                                                    {{ $acolito->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="radio" name="companhia_modo" id="modo_qualquer" value="qualquer" {{ $modoObrigatorio == 'qualquer' ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="modo_qualquer">
                                            Pelo menos UM dos selecionados acima deve estar presente
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="companhia_modo" id="modo_todos" value="todos" {{ $modoObrigatorio == 'todos' ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="modo_todos">
                                            TODOS os selecionados acima devem estar presentes simultaneamente
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary rounded-pill px-5 py-2">
                        <i class="bi bi-save me-2"></i> Salvar Regras de Composição
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>

<!-- O SisMatriz usa select2? A maioria usa. Se sim, podemos inicializar. -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            placeholder: "Selecione..."
        });
    }
});
</script>
@endsection
