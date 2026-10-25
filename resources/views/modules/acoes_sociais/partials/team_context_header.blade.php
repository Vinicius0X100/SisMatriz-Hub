<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <!-- Sub-navegação das Ações Sociais -->
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('acoes-sociais.familias.index') }}" 
               class="btn btn-sm rounded-pill px-3 py-2 fw-semibold {{ request()->routeIs('acoes-sociais.familias.*') || request()->routeIs('vicentinos.*') ? 'btn-primary shadow-sm text-white' : 'btn-light text-muted' }}">
                <i class="bi bi-people me-1"></i> Famílias Acompanhadas
            </a>
            <a href="{{ route('acoes-sociais.atendimentos.index') }}" 
               class="btn btn-sm rounded-pill px-3 py-2 fw-semibold {{ request()->routeIs('acoes-sociais.atendimentos.*') || request()->routeIs('vicentinos-apuracoes.*') ? 'btn-primary shadow-sm text-white' : 'btn-light text-muted' }}">
                <i class="bi bi-calculator me-1"></i> Atendimentos e Entregas
            </a>
            <a href="{{ route('acoes-sociais.relatorios') }}" 
               class="btn btn-sm rounded-pill px-3 py-2 fw-semibold {{ request()->routeIs('acoes-sociais.relatorios') ? 'btn-primary shadow-sm text-white' : 'btn-light text-muted' }}">
                <i class="bi bi-file-earmark-bar-graph me-1"></i> Relatórios
            </a>
            @if(Auth::user()->hasAnyRole(['1', '111']) || Auth::user()->isCoordenadorAcaoSocial())
                <a href="{{ route('acoes-sociais.equipes.index') }}" 
                   class="btn btn-sm rounded-pill px-3 py-2 fw-semibold {{ request()->routeIs('acoes-sociais.equipes.*') ? 'btn-primary shadow-sm text-white' : 'btn-light text-muted' }}">
                    <i class="bi bi-gear me-1"></i> Equipes e Pastorais
                </a>
            @endif
        </div>

        <!-- Identificação e Seletor de Equipe -->
        <div class="d-flex align-items-center gap-2">
            @if(isset($activeEquipe) && $activeEquipe)
                @if(isset($hasMultipleEquipes) && $hasMultipleEquipes && isset($authorizedEquipes) && $authorizedEquipes->count() > 1)
                    <!-- Cenário B: Usuário com múltiplas equipes autorizadas -->
                    <form action="{{ route('acoes-sociais.switch-equipe') }}" method="POST" class="d-flex align-items-center gap-2 mb-0">
                        @csrf
                        <label for="selectEquipeContext" class="small text-muted fw-bold text-nowrap d-none d-lg-inline">Equipe Ativa:</label>
                        <select id="selectEquipeContext" name="equipe_id" class="form-select form-select-sm rounded-pill bg-light border-0 px-3 py-2 fw-semibold text-primary" onchange="this.form.submit()">
                            @foreach($authorizedEquipes as $eq)
                                <option value="{{ $eq->id }}" {{ $eq->id == $activeEquipe->id ? 'selected' : '' }}>
                                    {{ $eq->nome }} {{ $eq->tipo === 'vicentinos' ? '(SSVP)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @else
                    <!-- Cenário A: Usuário ou paróquia com equipe única (sem seletores desnecessários) -->
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-semibold" title="Equipe de Ações Sociais responsável pelos registros">
                        <i class="bi bi-people-fill me-1"></i> Equipe: {{ $activeEquipe->nome }}
                    </span>
                @endif
            @else
                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Nenhuma equipe autorizada
                </span>
            @endif
        </div>
    </div>
</div>
