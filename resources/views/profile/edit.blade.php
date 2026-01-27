@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark">Meu Perfil</h5>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
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

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-12 text-center">
                                <div class="position-relative d-inline-block">
                                    @php
                                        $userName = $user->name ?? $user->user;
                                        $parts = explode(' ', trim($userName));
                                        $initials = strtoupper(substr($parts[0], 0, 1));
                                        if (count($parts) > 1) {
                                            $initials .= strtoupper(substr(end($parts), 0, 1));
                                        }
                                    @endphp

                                    @if($user->avatar && file_exists(public_path('storage/uploads/avatars/' . $user->avatar)))
                                        <img src="{{ asset('storage/uploads/avatars/' . $user->avatar) }}" 
                                             alt="{{ $userName }}" 
                                             id="avatar-preview"
                                             width="120" height="120" 
                                             class="rounded-circle border shadow-sm mb-3" 
                                             style="object-fit: cover; object-position: center;">
                                    @else
                                        <div id="avatar-placeholder" class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center shadow-sm mb-3 mx-auto" style="width: 120px; height: 120px; font-size: 2.5rem;">
                                            {{ $initials }}
                                        </div>
                                        <img src="" alt="Preview" id="avatar-preview" width="120" height="120" class="rounded-circle border shadow-sm mb-3 d-none" style="object-fit: cover; object-position: center;">
                                    @endif

                                    <div class="mt-2">
                                        <label for="avatar" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-camera"></i> Alterar Foto
                                        </label>
                                        <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*" onchange="previewImage(this)">
                                    </div>
                                    <div class="form-text text-muted small mt-1">Formatos: JPG, PNG. Máx: 2MB.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label fw-semibold">Nome Completo</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-12">
                                <label for="email" class="form-label fw-semibold">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Salvar Alterações
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
                var preview = document.getElementById('avatar-preview');
                var placeholder = document.getElementById('avatar-placeholder');
                
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
