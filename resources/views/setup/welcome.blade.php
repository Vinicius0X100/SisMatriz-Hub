@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="text-center mb-5 fade-in-down">
                @if($user->paroquia && $user->paroquia->foto)
                    <img src="https://sismatriz.online/uploads/paroquias/{{ $user->paroquia->foto }}" 
                         class="rounded-circle shadow-lg mb-4 bg-white p-1" 
                         width="120" height="120" 
                         style="object-fit: cover;"
                         alt="Logo Paróquia"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                    <div class="rounded-circle shadow-lg mb-4 bg-primary text-white align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 3rem; display: none;">
                        <i class="bi bi-church"></i>
                    </div>
                @else
                    <div class="rounded-circle shadow-lg mb-4 bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px; font-size: 3rem;">
                        <i class="bi bi-church"></i>
                    </div>
                @endif
                
                <h2 class="fw-bold text-dark">Bem-vindo(a) à {{ $user->paroquia->name ?? 'Nossa Paróquia' }}!</h2>
                <p class="text-muted lead">Estamos felizes em ter você aqui. Para finalizar seu acesso, precisamos que configure seu perfil.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5 text-center">
                    <h5 class="fw-bold mb-2">Foto de Perfil Obrigatória</h5>
                    <p class="text-muted small mb-4">Por favor, envie uma foto sua para identificação no sistema.</p>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger text-start">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('setup.welcome.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4 d-flex justify-content-center">
                            <div class="position-relative" style="width: 160px; height: 160px;">
                                <img id="avatarPreview" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random&size=160" 
                                     class="rounded-circle w-100 h-100 border border-4 border-light shadow" style="object-fit: cover;">
                                
                                <label for="avatar" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-3 shadow cursor-pointer hover-scale" style="cursor: pointer; transform: translate(10%, 10%);">
                                    <i class="bi bi-camera-fill fs-5"></i>
                                </label>
                            </div>
                        </div>

                        <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*" required onchange="previewImage(this)">
                        
                        <div class="progress mt-4 d-none" style="height: 25px;" id="uploadProgressContainer">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" id="uploadProgressBar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm" id="submitBtn">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true" id="loadingSpinner"></span>
                                <span id="btnText"><i class="bi bi-check-lg me-2"></i> Confirmar e Acessar</span>
                            </button>
                        </div>
                    </form>
                    
                    <form action="{{ route('setup.welcome.skip') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted text-decoration-none btn-sm">
                            Pular esta etapa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-scale {
        transition: transform 0.2s;
    }
    .hover-scale:hover {
        transform: translate(10%, 10%) scale(1.1) !important;
    }
    .fade-in-down {
        animation: fadeInDown 0.8s ease-out;
    }
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('avatarPreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var form = this;
        var btn = document.getElementById('submitBtn');
        var spinner = document.getElementById('loadingSpinner');
        var text = document.getElementById('btnText');
        var progressContainer = document.getElementById('uploadProgressContainer');
        var progressBar = document.getElementById('uploadProgressBar');
        
        // Reset state
        btn.disabled = true;
        spinner.classList.remove('d-none');
        text.innerHTML = 'Enviando...';
        progressContainer.classList.remove('d-none');
        progressBar.style.width = '0%';
        progressBar.innerHTML = '0%';
        progressBar.classList.remove('bg-success', 'bg-danger');
        
        var formData = new FormData(form);
        var xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressBar.innerHTML = percentComplete + '%';
            }
        });
        
        xhr.addEventListener('load', function() {
            if (xhr.status === 200 || xhr.status === 302) {
                progressBar.classList.add('bg-success');
                progressBar.innerHTML = 'Concluído!';
                text.innerHTML = 'Redirecionando...';
                
                // If the response is a redirect (which Laravel usually does), follow it
                // Or if it returns HTML, we might just assume success and reload/redirect
                // For simplicity, since the controller redirects, we can reload or go to dashboard
                // But AJAX handling of redirects is tricky.
                // Best approach: If controller returns redirect, XHR follows it transparently usually, 
                // but the final URL might be needed.
                // Let's just check if we landed on dashboard or if we should manually redirect.
                // Since the controller returns `redirect()->route('dashboard')`, 
                // XHR will receive the content of the dashboard page.
                
                // Ideally, the controller should return JSON for AJAX.
                // But to avoid changing controller too much, let's assume success = redirect to dashboard.
                window.location.href = "{{ route('dashboard') }}";
            } else {
                progressBar.classList.add('bg-danger');
                progressBar.innerHTML = 'Erro!';
                btn.disabled = false;
                spinner.classList.add('d-none');
                text.innerHTML = '<i class="bi bi-check-lg me-2"></i> Tentar Novamente';
                alert('Ocorreu um erro ao enviar a imagem. Por favor, tente novamente.');
            }
        });
        
        xhr.addEventListener('error', function() {
            progressBar.classList.add('bg-danger');
            progressBar.innerHTML = 'Erro de Conexão';
            btn.disabled = false;
            spinner.classList.add('d-none');
            text.innerHTML = '<i class="bi bi-check-lg me-2"></i> Tentar Novamente';
            alert('Erro de conexão. Verifique sua internet.');
        });
        
        xhr.open('POST', form.action, true);
        // Add CSRF token header if not in FormData (FormData usually handles inputs, but X-CSRF-TOKEN is good practice)
        // However, form has @csrf which adds _token input, so it should be fine.
        xhr.send(formData);
    });
</script>
@endsection
