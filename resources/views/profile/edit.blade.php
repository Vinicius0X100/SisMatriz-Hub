@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Meu Perfil</h2>
            <p class="text-muted small mb-0">Gerencie suas informações pessoais.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Meu Perfil</li>
            </ol>
        </nav>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center mb-1">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <strong class="me-auto">Erro ao salvar perfil</strong>
            </div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="text-center mb-5">
                            <div class="position-relative d-inline-block">
                                @if($user->avatar && file_exists(public_path('storage/uploads/avatars/' . $user->avatar)))
                                    <img src="{{ asset('storage/uploads/avatars/' . $user->avatar) }}" 
                                         alt="Avatar" 
                                         id="avatarPreview"
                                         class="rounded-circle border shadow-sm" 
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                @else
                                    <div id="avatarPlaceholder" class="rounded-circle bg-light d-flex align-items-center justify-content-center border shadow-sm" style="width: 150px; height: 150px;">
                                        <span class="display-4 fw-bold text-primary">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <img src="" alt="Preview" id="avatarPreview" class="rounded-circle border shadow-sm d-none" style="width: 150px; height: 150px; object-fit: cover;">
                                @endif
                                
                                <label for="avatar" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle p-2 shadow-sm cursor-pointer hover-scale" style="cursor: pointer; transition: transform 0.2s;">
                                    <i class="bi bi-camera-fill fs-5"></i>
                                    <input type="file" id="avatar" name="avatar" class="d-none" accept="image/*" onchange="previewImage(this)">
                                </label>
                            </div>
                            <div class="mt-2 text-muted small">Clique na câmera para alterar a foto</div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="name" class="form-label fw-bold small text-muted">Nome Completo</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control bg-light border-0 rounded-end-pill py-2" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="email" class="form-label fw-bold small text-muted">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 rounded-start-pill ps-3"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" class="form-control bg-light border-0 rounded-end-pill py-2" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('dashboard') }}" class="btn btn-light border rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                <i class="bi bi-check-lg me-2"></i> Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.getElementById('avatarPreview');
                const placeholder = document.getElementById('avatarPlaceholder');
                
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
