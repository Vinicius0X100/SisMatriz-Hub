@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Detalhes do Item</h2>
            <p class="text-muted small mb-0">Visualização completa das informações.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('estoque.index') }}" class="text-decoration-none">Estoque</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detalhes</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4">
        <!-- Detalhes e Galeria -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="bi bi-box-seam fs-3 text-primary"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">{{ $item->description }}</h4>
                            <span class="badge bg-light text-dark border mt-1">{{ $item->type }}</span>
                        </div>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase">Categoria</label>
                            <div class="fs-5">{{ $item->categoria->name ?? 'Não informada' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase">Quantidade</label>
                            <div class="fs-5 fw-bold text-primary">{{ $item->qntd_destributed }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase">Comunidade</label>
                            <div class="fs-5">{{ $item->entidade->ent_name ?? 'Todas / Não definida' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small text-muted fw-bold text-uppercase">Local / Sala</label>
                            <div class="fs-5">{{ $item->sala->name ?? 'Não definido' }}</div>
                        </div>
                        <div class="col-12">
                            <label class="small text-muted fw-bold text-uppercase">Última Atualização</label>
                            <div class="fs-5">{{ \Carbon\Carbon::parse($item->last_update)->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    @if($item->images->count() > 0)
                        <h5 class="fw-bold mb-3 border-bottom pb-2">Galeria de Imagens</h5>
                        <div class="row g-3">
                            @foreach($item->images as $img)
                                <div class="col-6 col-md-4 col-lg-3">
                                    <a href="{{ asset('storage/uploads/estoque/' . $img->filename) }}" target="_blank">
                                        <img src="{{ asset('storage/uploads/estoque/' . $img->filename) }}" class="img-fluid rounded shadow-sm border object-fit-cover w-100" style="height: 150px;" alt="Imagem do Item">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-light border text-center text-muted">
                            <i class="bi bi-images fs-1 d-block mb-2"></i>
                            Sem imagens cadastradas para este item.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ações -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">Ações</h5>
                    <div class="d-grid gap-3">
                        <a href="{{ route('estoque.edit', $item->s_id) }}" class="btn btn-primary rounded-pill py-2 fw-bold">
                            <i class="bi bi-pencil me-2"></i> Editar Item
                        </a>
                        
                        <button type="button" class="btn btn-outline-danger rounded-pill py-2 fw-bold" onclick="if(confirm('Tem certeza que deseja excluir este item?')) document.getElementById('deleteForm').submit();">
                            <i class="bi bi-trash me-2"></i> Excluir Item
                        </button>
                        
                        <a href="{{ route('estoque.index') }}" class="btn btn-light rounded-pill py-2 fw-bold border">
                            <i class="bi bi-arrow-left me-2"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" action="{{ route('estoque.destroy', $item->s_id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection