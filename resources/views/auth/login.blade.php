@extends('layouts.app')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5 col-lg-4">
        <div class="text-center mb-5 fade-in-down">
            <!-- Logo Section -->
            <img src="{{ asset('images/logo.png') }}" alt="SisMatriz Logo" class="login-logo" style="max-height: 90px; width: auto;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
            <!-- Fallback Placeholder if no logo -->
            <div class="bg-dark rounded-circle align-items-center justify-content-center text-white mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem; display: none;">
                SM
            </div>
            <h3 class="fw-bold text-dark mb-1">SisMatriz</h3>
            <p class="text-secondary small">Gestão Integrada e Segura</p>
        </div>

        <div class="card card-login mb-4">
            <div class="card-body p-5">
                <div class="mb-4 text-center">
                    <h5 class="fw-semibold text-dark">Bem-vindo de volta</h5>
                    <p class="text-muted small">Insira suas credenciais para acessar o painel.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 rounded-3 small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="user" class="form-label text-secondary small fw-bold text-uppercase">Usuário</label>
                        <input type="text" class="form-control" id="user" name="user" placeholder="Seu usuário de acesso" required autofocus value="{{ old('user') }}">
                    </div>
                    <div class="mb-4">
                        <label for="user" class="form-label text-secondary small fw-bold text-uppercase d-flex justify-content-between">
                            Senha
                        </label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary shadow-sm">
                            Acessar Sistema
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center">
            <p class="text-muted-small mb-0">
                <i class="bi bi-lock-fill me-1"></i> Ambiente Seguro. Seus dados estão protegidos.
            </p>
        </div>
    </div>
</div>
</div>
<style>
    .fade-in-down {
        animation: fadeInDown 1s ease-out;
    }
    @keyframes fadeInDown {
        0% { opacity: 0; transform: translateY(-20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection
