@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Detalhes da Ficha Vicentina</h2>
            <p class="text-muted small mb-0">Visualizando informações da família assistida.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('vicentinos.index') }}" class="text-decoration-none">Registros Vicentinos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <div class="d-flex justify-content-end mb-4">
                <a href="{{ route('vicentinos.edit', $record->id) }}" class="btn btn-primary rounded-pill px-4 me-2">
                    <i class="bi bi-pencil-fill me-2"></i> Editar Ficha
                </a>
                <a href="{{ route('vicentinos.index') }}" class="btn btn-light rounded-pill px-4">
                    <i class="bi bi-arrow-left me-2"></i> Voltar
                </a>
            </div>

            <!-- Dados Iniciais -->
            <h5 class="fw-bold text-dark mb-4">Informações Iniciais</h5>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Data da Ficha</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->created_at->format('d/m/Y') }}
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Conferência</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->conferencia ?? '-' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Conselho Particular</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->conselho_particular ?? '-' }}
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- Responsável -->
            <h5 class="fw-bold text-dark mb-4">Dados do Responsável</h5>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Nome do Responsável</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark fw-bold">
                        {{ $record->responsavel_nome }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Data Nascimento</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '-' }}
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-bold small text-muted">Idade</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->idade ?? '-' }}
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Sexo</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->sexo ?? '-' }}
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">CPF</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->cpf ?? '-' }}
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">RG</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->rg ?? '-' }}
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">CEP</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->cep ?? '-' }}
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Endereço</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->endereco ?? '-' }}
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Número</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->endereco_numero ?? '-' }}
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Bairro</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->bairro ?? '-' }}
                    </div>
                </div>
                 <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Cidade</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->cidade ?? '-' }}
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Estado</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->estado ?? '-' }}
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Telefone</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->telefone ?? '-' }}
                    </div>
                </div>
                 <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Contato Recado</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->contato_recado ?? '-' }}
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- Situação Socioeconômica -->
            <h5 class="fw-bold text-dark mb-4">Situação Socioeconômica</h5>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Recebe Bolsa Família?</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->recebe_bolsa_familia ? 'Sim' : 'Não' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Valor Bolsa Família</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        R$ {{ number_format($record->valor_bolsa_familia, 2, ',', '.') }}
                    </div>
                </div>
                 <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Outro Benefício</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->outro_beneficio_nome ?? '-' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Valor Outro Benefício</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        R$ {{ number_format($record->outro_beneficio_valor, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Tipo Residência</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->tipo_residencia ?? '-' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Valor Aluguel/Prestação</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        R$ {{ number_format($record->valor_aluguel_prestacao, 2, ',', '.') }}
                    </div>
                </div>
                 <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Quem Trabalha?</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->quem_trabalha ?? '-' }}
                    </div>
                </div>
                 <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Local Trabalho</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->local_trabalho ?? '-' }}
                    </div>
                </div>
            </div>
            

            <hr class="my-4 text-muted opacity-25">

            <!-- Religiosidade -->
            <h5 class="fw-bold text-dark mb-4">Religiosidade</h5>
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Religião</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->religiao ?? '-' }}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Católico c/ Sacramentos?</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->catolico_tem_sacramentos ? 'Sim' : 'Não' }}
                    </div>
                </div>
                 <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Sacramento Faltando</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->sacramento_faltando ?? '-' }}
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- Composição Familiar -->
            <h5 class="fw-bold text-dark mb-4">Composição Familiar</h5>
            
            <div class="card border-0 bg-light rounded-4 mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="border-bottom">
                                <tr>
                                    <th class="ps-4 py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Nome</th>
                                    <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Parentesco</th>
                                    <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Nascimento</th>
                                    <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Profissão</th>
                                    <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Escolaridade</th>
                                    <th class="py-3 bg-transparent text-muted small text-uppercase fw-bold border-0">Renda (R$)</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($record->families as $family)
                                    <tr>
                                        <td class="ps-4 text-dark fw-semibold">{{ $family->nome }}</td>
                                        <td class="text-muted">{{ $family->parentesco ?? '-' }}</td>
                                        <td class="text-muted">{{ $family->nascimento ? \Carbon\Carbon::parse($family->nascimento)->format('d/m/Y') : '-' }}</td>
                                        <td class="text-muted">{{ $family->profissao ?? '-' }}</td>
                                        <td class="text-muted">{{ $family->escolaridade ?? '-' }}</td>
                                        <td class="text-muted">R$ {{ number_format($family->renda, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-people display-6 mb-3 d-block opacity-50"></i>
                                            Nenhum membro familiar registrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- Observações e Finalização -->
            <h5 class="fw-bold text-dark mb-4">Outras Informações</h5>
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <label class="form-label fw-bold small text-muted">Observações</label>
                    <div class="form-control rounded-4 bg-light border-0 px-4 py-3 text-dark" style="min-height: 100px;">
                        {{ $record->observacoes ?? 'Nenhuma observação registrada.' }}
                    </div>
                </div>
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted">Responsáveis pela Sindicância</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->responsaveis_sindicancia ?? '-' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted">Data Dispensa</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->data_dispensa ? \Carbon\Carbon::parse($record->data_dispensa)->format('d/m/Y') : '-' }}
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-bold small text-muted">Motivo Dispensa</label>
                    <div class="form-control rounded-pill bg-light border-0 px-4 py-2 text-dark">
                        {{ $record->motivo_dispensa ?? '-' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
