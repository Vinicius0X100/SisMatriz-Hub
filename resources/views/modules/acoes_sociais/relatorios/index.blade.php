@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Ações Sociais — Relatórios</h2>
            <p class="text-muted small mb-0">Emissão de relatórios gerenciais e prestação de contas de assistência social.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('acoes-sociais.familias.index') }}" class="text-decoration-none">Ações Sociais</a></li>
                <li class="breadcrumb-item active" aria-current="page">Relatórios</li>
            </ol>
        </nav>
    </div>

    <!-- Sub-menu e Contexto -->
    @include('modules.acoes_sociais.partials.team_context_header')

    <div class="row g-4 mb-4">
        <!-- Card 1: Relatório de Famílias -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-people-fill text-primary fs-3"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1 text-dark">Relatório de Famílias Acompanhadas</h4>
                                <span class="badge bg-light text-muted rounded-pill">Formato PDF</span>
                            </div>
                        </div>
                        <p class="text-muted">
                            Gera um documento customizável contendo a listagem das famílias atendidas pela equipe <strong>{{ $activeEquipe->nome ?? 'Ativa' }}</strong>, com opções para selecionar colunas (dados cadastrais, renda, moradia, religiosidade) e modo ficha com composição familiar detalhada.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('acoes-sociais.familias.index') }}" class="btn btn-primary rounded-pill px-4 fw-bold">
                            <i class="bi bi-file-earmark-pdf me-2"></i> Configurar e Gerar Relatório de Famílias
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Relatório de Atendimentos -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-md-5 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                <i class="bi bi-calculator-fill text-success fs-3"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1 text-dark">Relatório de Atendimentos e Entregas</h4>
                                <span class="badge bg-light text-muted rounded-pill">Formatos PDF e CSV/Excel</span>
                            </div>
                        </div>
                        <p class="text-muted">
                            Consolida os atendimentos mensais de assistidos e não assistidos por período (mês atual, bimestral, trimestral ou intervalo de datas personalizado), filtrados por comunidade e equipe ativa, com exportação para planilha e impressão em folha A4 paisagem.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('acoes-sociais.atendimentos.index') }}" class="btn btn-success rounded-pill px-4 fw-bold text-white">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i> Configurar e Gerar Relatório de Atendimentos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
