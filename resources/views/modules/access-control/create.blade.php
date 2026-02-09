@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex align-items-center mb-4">
                <a href="{{ route('access-control.index') }}" class="btn btn-light rounded-circle me-3 shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Novo Usuário</h1>
                    <p class="text-muted small mb-0">Preencha os dados para criar um novo acesso.</p>
                </div>
            </div>

            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('access-control.store') }}" method="POST">
                        @csrf
                        
                        <h5 class="fw-bold mb-3 text-primary">Dados Pessoais</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label for="name" class="form-label small fw-bold text-uppercase text-muted">Nome Completo</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="user" class="form-label small fw-bold text-uppercase text-muted">Nome de Usuário (Login)</label>
                                <input type="text" class="form-control" id="user" name="user" value="{{ old('user') }}" required>
                                @error('user') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label small fw-bold text-uppercase text-muted">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-12">
                                <label for="password" class="form-label small fw-bold text-uppercase text-muted">Senha</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Digite a senha..." required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Mínimo de 6 caracteres.</div>
                                @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3 text-primary">Permissões e Cargos</h5>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-uppercase text-muted mb-3">Selecione os cargos para este usuário:</label>
                            <div class="row g-3">
                                @foreach($roles as $id => $label)
                                    <div class="col-md-6">
                                        <div class="form-check p-3 border rounded bg-light h-100">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $id }}" id="role_{{ $id }}" {{ in_array($id, old('roles', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-medium" for="role_{{ $id }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @error('roles') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('access-control.index') }}" class="btn btn-light">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-2"></i>Criar Usuário
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const btn = field.nextElementSibling;
    const icon = btn.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endsection
