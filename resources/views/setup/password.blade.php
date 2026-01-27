@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h4 class="mb-0 fw-bold">Alteração de Senha</h4>
                    <p class="mb-0 opacity-75 small">Para sua segurança, defina uma nova senha.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('setup.password.update') }}" method="POST" id="passwordForm">
                        @csrf
                        <!-- New Password -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nova Senha</label>
                            <input type="password" name="password" id="password" class="form-control form-control-lg" required minlength="6" placeholder="Mínimo 6 caracteres">
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="mb-4 position-relative">
                            <label class="form-label fw-bold text-muted small text-uppercase">Confirmar Senha</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-lg" required minlength="6" placeholder="Repita a senha">
                            <div id="passwordMatchFeedback" class="form-text mt-2 fw-bold" style="min-height: 20px;"></div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true" id="loadingSpinner"></span>
                                <span id="btnText">Salvar e Continuar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirm = document.getElementById('password_confirmation');
        const feedback = document.getElementById('passwordMatchFeedback');
        const btn = document.getElementById('submitBtn');
        const spinner = document.getElementById('loadingSpinner');
        const btnText = document.getElementById('btnText');
        const form = document.getElementById('passwordForm');

        function checkMatch() {
            const p1 = password.value;
            const p2 = confirm.value;

            if (p2.length === 0) {
                feedback.textContent = '';
                confirm.classList.remove('is-valid', 'is-invalid');
                btn.disabled = true;
                return;
            }

            if (p1 === p2) {
                if (p1.length < 6) {
                    feedback.textContent = 'A senha deve ter no mínimo 6 caracteres.';
                    feedback.className = 'form-text mt-2 fw-bold text-warning';
                    confirm.classList.add('is-invalid');
                    btn.disabled = true;
                    return;
                }
                feedback.textContent = 'As senhas coincidem!';
                feedback.className = 'form-text mt-2 fw-bold text-success';
                confirm.classList.add('is-valid');
                confirm.classList.remove('is-invalid');
                btn.disabled = false;
            } else {
                feedback.textContent = 'As senhas não coincidem.';
                feedback.className = 'form-text mt-2 fw-bold text-danger';
                confirm.classList.add('is-invalid');
                confirm.classList.remove('is-valid');
                btn.disabled = true;
            }
        }

        password.addEventListener('input', checkMatch);
        confirm.addEventListener('input', checkMatch);

        form.addEventListener('submit', function() {
            btn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Salvando...';
        });
    });
</script>
@endsection
