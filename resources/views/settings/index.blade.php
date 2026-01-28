@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mt-4 fw-bold">Configurações</h2>
            <p class="text-muted">Gerencie suas preferências de privacidade e segurança.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Card de Privacidade -->
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-shield-lock fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Privacidade</h5>
                    </div>
                    <p class="text-muted small ps-1">Controle como você aparece para outros usuários.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ route('settings.update.privacy') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="d-flex flex-column gap-3">
                            <!-- Aparecer Online -->
                            <div class="form-check form-switch p-3 bg-light rounded-3 d-flex justify-content-between align-items-center ps-3 pe-3 mb-0">
                                <div>
                                    <label class="form-check-label fw-bold mb-1" for="isVisible">Aparecer Online</label>
                                    <div class="text-muted small" style="line-height: 1.2;">Permitir que outros vejam quando estou usando o sistema.</div>
                                </div>
                                <input class="form-check-input ms-2 fs-5" type="checkbox" role="switch" id="isVisible" name="is_visible" value="1" {{ old('is_visible', $user->is_visible) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>

                            <!-- Ocultar Nome -->
                            <div class="form-check form-switch p-3 bg-light rounded-3 d-flex justify-content-between align-items-center ps-3 pe-3 mb-0">
                                <div>
                                    <label class="form-check-label fw-bold mb-1" for="hideName">Ocultar meu Nome</label>
                                    <div class="text-muted small" style="line-height: 1.2;">Exibir apenas iniciais ou "Usuário" na lista de online.</div>
                                </div>
                                <input class="form-check-input ms-2 fs-5" type="checkbox" role="switch" id="hideName" name="hide_name" value="1" {{ old('hide_name', $user->hide_name) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill fw-bold">
                                <i class="bi bi-check-lg me-1"></i> Salvar Privacidade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card de Segurança (Senha) -->
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <div class="d-flex align-items-center mb-2">
                        <div class="icon-box bg-danger bg-opacity-10 text-danger rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="bi bi-key fs-4"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Segurança</h5>
                    </div>
                    <p class="text-muted small ps-1">Atualize sua senha de acesso.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ route('settings.update.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label small text-uppercase fw-bold text-muted">Senha Atual</label>
                            <input type="password" class="form-control form-control-lg bg-light border-0" id="current_password" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small text-uppercase fw-bold text-muted">Nova Senha</label>
                            <input type="password" class="form-control form-control-lg bg-light border-0" id="password" name="password" required>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label small text-uppercase fw-bold text-muted">Confirmar Nova Senha</label>
                            <input type="password" class="form-control form-control-lg bg-light border-0" id="password_confirmation" name="password_confirmation" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-danger px-4 rounded-pill fw-bold">
                                <i class="bi bi-save me-1"></i> Alterar Senha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .form-control:focus {
        background-color: #fff;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    }
</style>
@endsection
