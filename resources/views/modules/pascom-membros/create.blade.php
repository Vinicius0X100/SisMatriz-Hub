@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex align-items-center mt-4 mb-4">
        <a href="{{ route('pascom-membros.index') }}" class="btn btn-light rounded-circle me-3 shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="mb-0 fw-bold text-dark">Novo Membro Pascom</h2>
            <p class="text-muted small mb-0">Pesquise a pessoa para preencher os dados automaticamente.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form id="createForm" action="{{ route('pascom-membros.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold text-muted small">Pesquisar Pessoa</label>
                        <div class="position-relative">
                            <input type="text" class="form-control rounded-pill" id="searchRegister" placeholder="Digite o nome ou CPF...">
                            <div id="searchResults" class="list-group position-absolute w-100 mt-1 shadow-sm" style="z-index: 1000; display:none;"></div>
                        </div>
                        <input type="hidden" name="register_id" id="registerId">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">Nome</label>
                        <input type="text" class="form-control" id="nameInput" disabled>
                        <input type="hidden" name="name" id="nameHidden">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Idade</label>
                        <input type="number" class="form-control" name="age" id="ageInput" min="0" max="120" step="1" placeholder="Ex.: 27">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Ano de Entrada</label>
                        <input type="number" class="form-control" name="year_member" value="{{ date('Y') }}" min="1900" max="2100">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">Comunidade</label>
                        <select name="ent_id" class="form-select" required>
                            <option value="">Selecione...</option>
                            @foreach($entidades as $e)
                                <option value="{{ $e->ent_id }}">{{ $e->ent_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Tipo</label>
                        <select name="type" class="form-select" required>
                            <option value="">Selecione...</option>
                            <option value="0">Fotógrafo</option>
                            <option value="1">Redator</option>
                            <option value="2">Video Maker</option>
                            <option value="3">Designer</option>
                            <option value="4">Editor de Vídeo</option>
                            <option value="5">Streamer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-muted small">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="0">Ativo</option>
                            <option value="1">Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('pascom-membros.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button id="createSubmitBtn" type="submit" class="btn btn-primary rounded-pill px-4">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createForm');
    const submitBtn = document.getElementById('createSubmitBtn');
    const input = document.getElementById('searchRegister');
    const results = document.getElementById('searchResults');
    const nameInput = document.getElementById('nameInput');
    const nameHidden = document.getElementById('nameHidden');
    const ageInput = document.getElementById('ageInput');
    const registerId = document.getElementById('registerId');
    let debounceTimer;

    ageInput.addEventListener('change', function() {
        const v = parseInt(ageInput.value, 10);
        const currentYear = new Date().getFullYear();
        if (!isNaN(v) && v >= 1900 && v <= currentYear + 1) {
            const computed = Math.max(0, Math.min(120, currentYear - v));
            ageInput.value = computed;
        }
    });

    form.addEventListener('submit', function() {
        submitBtn.setAttribute('disabled', 'true');
        submitBtn.setAttribute('aria-busy', 'true');
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Salvando...';
    });

    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const q = input.value;
        if (q.length < 2) {
            results.style.display = 'none';
            return;
        }
        debounceTimer = setTimeout(() => {
            fetch(`{{ route('pascom-membros.search-registers') }}?q=${encodeURIComponent(q)}`)
                .then(res => res.json())
                .then(data => {
                    results.innerHTML = '';
                    if (!data || data.length === 0) {
                        results.style.display = 'none';
                        return;
                    }
                    data.forEach(p => {
                        const a = document.createElement('a');
                        a.href = '#';
                        a.className = 'list-group-item list-group-item-action';
                        a.textContent = `${p.name}`;
                        a.onclick = (e) => {
                            e.preventDefault();
                            registerId.value = p.id;
                            nameInput.value = p.name;
                            nameHidden.value = p.name;
                            ageInput.value = p.age ?? '';
                            input.value = p.name;
                            input.classList.add('border-primary', 'bg-primary', 'bg-opacity-10', 'text-primary');
                            results.style.display = 'none';
                        };
                        results.appendChild(a);
                    });
                    results.style.display = 'block';
                });
        }, 300);
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !results.contains(e.target)) {
            results.style.display = 'none';
        }
    });
});
</script>
@endsection
