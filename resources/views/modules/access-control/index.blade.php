@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Acessos e Usuários</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Acessos e Usuários</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                        <i class="bi bi-people fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total de Usuários</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                        <i class="bi bi-check-circle fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Ativos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 me-3">
                        <i class="bi bi-x-circle fs-3 text-danger"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Inativos</h6>
                        <h3 class="mb-0 fw-bold">{{ $stats['inactive'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <!-- Barra de Ferramentas -->
            <form method="GET" action="{{ route('access-control.index') }}" class="row g-3 mb-4 align-items-end">
                <!-- Pesquisa -->
                <div class="col-md-4">
                    <label for="searchInput" class="form-label fw-bold text-muted small">Pesquisar</label>
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" name="search" id="searchInput" class="form-control ps-5 rounded-pill" placeholder="Nome, Email ou Login..." value="{{ request('search') }}" style="height: 45px;">
                    </div>
                </div>
                
                <!-- Filtro: Cargo -->
                <div class="col-md-3">
                    <label for="roleFilter" class="form-label fw-bold text-muted small">Cargo</label>
                    <select name="role" id="roleFilter" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                        <option value="">Todos os Cargos</option>
                        @foreach($roles as $id => $label)
                            <option value="{{ $id }}" {{ request('role') == $id ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filtro: Status -->
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label fw-bold text-muted small">Status</label>
                    <select name="status" id="statusFilter" class="form-select rounded-pill" style="height: 45px;" onchange="this.form.submit()">
                        <option value="">Todos os Status</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Ativo</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>

                <!-- Botões de Ação -->
                <div class="col-md-2 text-end d-flex gap-2 justify-content-end">
                     <a href="{{ route('access-control.create') }}" class="btn btn-primary rounded-pill px-3 d-flex align-items-center justify-content-center gap-2 w-100" style="height: 45px;">
                        <i class="bi bi-person-plus fs-5"></i> <span class="d-none d-lg-inline">Novo</span>
                    </a>
                </div>
            </form>

            <!-- Tabela -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3 border-0">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => (request('sort', 'id') == 'name' && request('direction', 'desc') == 'asc' ? 'desc' : 'asc')]) }}" class="text-decoration-none text-secondary small text-uppercase fw-bold d-flex align-items-center">
                                    Usuário
                                    @if(request('sort', 'id') == 'name')
                                        <i class="bi bi-sort-{{ request('direction', 'desc') == 'asc' ? 'alpha-down' : 'alpha-down-alt' }} ms-1"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up text-muted opacity-25 ms-1" style="font-size: 0.8rem;"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 border-0">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'user', 'direction' => (request('sort', 'id') == 'user' && request('direction', 'desc') == 'asc' ? 'desc' : 'asc')]) }}" class="text-decoration-none text-secondary small text-uppercase fw-bold d-flex align-items-center">
                                    Login
                                    @if(request('sort', 'id') == 'user')
                                        <i class="bi bi-sort-{{ request('direction', 'desc') == 'asc' ? 'alpha-down' : 'alpha-down-alt' }} ms-1"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up text-muted opacity-25 ms-1" style="font-size: 0.8rem;"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-secondary small text-uppercase fw-bold border-0">Cargos/Permissões</th>
                            <th class="px-4 py-3 border-0">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => (request('sort', 'id') == 'status' && request('direction', 'desc') == 'asc' ? 'desc' : 'asc')]) }}" class="text-decoration-none text-secondary small text-uppercase fw-bold d-flex align-items-center">
                                    Status
                                    @if(request('sort', 'id') == 'status')
                                        <i class="bi bi-sort-{{ request('direction', 'desc') == 'asc' ? 'numeric-down' : 'numeric-down-alt' }} ms-1"></i>
                                    @else
                                        <i class="bi bi-arrow-down-up text-muted opacity-25 ms-1" style="font-size: 0.8rem;"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="px-4 py-3 text-secondary small text-uppercase fw-bold border-0 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            @if($user->avatar && file_exists(public_path('storage/uploads/avatars/' . $user->avatar)))
                                                <img src="{{ asset('storage/uploads/avatars/' . $user->avatar) }}" class="rounded-circle w-100 h-100 object-fit-cover">
                                            @else
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted small">{{ $user->user }}</td>
                                <td class="px-4 py-3">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-light text-dark border mb-1">
                                            {{ \App\Models\User::ROLE_LABELS[$role] ?? $role }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-4 py-3">
                                    @if($user->status == 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Ativo</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Inativo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($user->id === Auth::id() && $user->hasRole('1'))
                                            <span class="text-muted small" title="Você não pode editar seu próprio usuário com regra de Administrador Geral">
                                                <i class="bi bi-lock fs-5"></i>
                                            </span>
                                        @else
                                            <a href="{{ route('access-control.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if($user->id !== Auth::id())
                                                <form action="{{ route('access-control.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;" title="Excluir">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-5 text-center text-muted">
                                    <i class="bi bi-people fs-1 d-block mb-3"></i>
                                    Nenhum usuário encontrado com os filtros selecionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-top">
                {{ $users->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
