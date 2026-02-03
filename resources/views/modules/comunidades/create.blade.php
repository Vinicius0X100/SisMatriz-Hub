@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Nova Comunidade</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('comunidades.index') }}" class="text-decoration-none">Comunidades</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova</li>
            </ol>
        </nav>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('comunidades.store') }}" method="POST">
                        @csrf
                        
                        <h5 class="fw-bold mb-4">Informações da Comunidade</h5>
                        
                        <div class="mb-4">
                            <label for="ent_name" class="form-label fw-bold text-muted small">Nome da Comunidade</label>
                            <input type="text" class="form-control rounded-pill @error('ent_name') is-invalid @enderror" id="ent_name" name="ent_name" value="{{ old('ent_name') }}" required placeholder="Ex: Capela São José">
                            @error('ent_name')
                                <div class="invalid-feedback ps-3">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4 text-muted opacity-25">
                        
                        <h5 class="fw-bold mb-4">Endereço</h5>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="cep" class="form-label fw-bold text-muted small">CEP</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control rounded-pill @error('cep') is-invalid @enderror" id="cep" name="cep" value="{{ old('cep') }}" required placeholder="00000-000" onblur="pesquisarCep(this.value);">
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3" id="cep-loading" style="display: none;">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    </div>
                                </div>
                                @error('cep')
                                    <div class="invalid-feedback ps-3 d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-8">
                                <label for="rua" class="form-label fw-bold text-muted small">Rua</label>
                                <input type="text" class="form-control rounded-pill bg-light @error('rua') is-invalid @enderror" id="rua" name="rua" value="{{ old('rua') }}" required readonly>
                                @error('rua')
                                    <div class="invalid-feedback ps-3">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="numero" class="form-label fw-bold text-muted small">Número</label>
                                <input type="text" class="form-control rounded-pill @error('numero') is-invalid @enderror" id="numero" name="numero" value="{{ old('numero') }}" required placeholder="123">
                                @error('numero')
                                    <div class="invalid-feedback ps-3">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label for="bairro" class="form-label fw-bold text-muted small">Bairro</label>
                                <input type="text" class="form-control rounded-pill bg-light @error('bairro') is-invalid @enderror" id="bairro" name="bairro" value="{{ old('bairro') }}" required readonly>
                                @error('bairro')
                                    <div class="invalid-feedback ps-3">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="cidade" class="form-label fw-bold text-muted small">Cidade</label>
                                <input type="text" class="form-control rounded-pill bg-light @error('cidade') is-invalid @enderror" id="cidade" name="cidade" value="{{ old('cidade') }}" required readonly>
                                @error('cidade')
                                    <div class="invalid-feedback ps-3">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-1">
                                <label for="estado" class="form-label fw-bold text-muted small">UF</label>
                                <input type="text" class="form-control rounded-pill bg-light @error('estado') is-invalid @enderror" id="estado" name="estado" value="{{ old('estado') }}" required readonly>
                                @error('estado')
                                    <div class="invalid-feedback ps-3">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5">
                            <a href="{{ route('comunidades.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-5">Salvar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function limpa_formulário_cep() {
        document.getElementById('rua').value=("");
        document.getElementById('bairro').value=("");
        document.getElementById('cidade').value=("");
        document.getElementById('estado').value=("");
    }

    function meu_callback(conteudo) {
        document.getElementById('cep-loading').style.display = 'none';
        if (!("erro" in conteudo)) {
            document.getElementById('rua').value=(conteudo.logradouro);
            document.getElementById('bairro').value=(conteudo.bairro);
            document.getElementById('cidade').value=(conteudo.localidade);
            document.getElementById('estado').value=(conteudo.uf);
            document.getElementById('numero').focus();
        } else {
            limpa_formulário_cep();
            alert("CEP não encontrado.");
        }
    }
        
    function pesquisarCep(valor) {
        var cep = valor.replace(/\D/g, '');

        if (cep != "") {
            var validacep = /^[0-9]{8}$/;

            if(validacep.test(cep)) {
                document.getElementById('rua').value="...";
                document.getElementById('bairro').value="...";
                document.getElementById('cidade').value="...";
                document.getElementById('estado').value="...";
                
                document.getElementById('cep-loading').style.display = 'block';

                var script = document.createElement('script');
                script.src = 'https://viacep.com.br/ws/'+ cep + '/json/?callback=meu_callback';
                document.body.appendChild(script);
            } else {
                limpa_formulário_cep();
                alert("Formato de CEP inválido.");
            }
        } else {
            limpa_formulário_cep();
        }
    };
</script>
@endsection
