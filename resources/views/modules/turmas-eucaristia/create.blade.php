@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Nova Turma de Primeira Eucaristia</h2>
            <p class="text-muted small mb-0">Preencha os dados para criar uma nova turma.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('turmas-eucaristia.index') }}" class="text-decoration-none">Turmas Eucaristia</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova Turma</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('turmas-eucaristia.store') }}" method="POST">
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="turma" class="form-label fw-bold text-muted small">Nome da Turma</label>
                        <input type="text" class="form-control" id="turma" name="turma" value="{{ old('turma') }}" required>
                        @error('turma')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="tutor" class="form-label fw-bold text-muted small">Tutor (Catequista)</label>
                        <select class="form-select" id="tutor" name="tutor" required>
                            <option value="">Selecione um catequista...</option>
                            @foreach($catequistas as $catequista)
                                <option value="{{ $catequista->id }}" {{ old('tutor') == $catequista->id ? 'selected' : '' }}>
                                    {{ $catequista->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('tutor')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="inicio" class="form-label fw-bold text-muted small">Data de Início</label>
                        <input type="date" class="form-control" id="inicio" name="inicio" value="{{ old('inicio') }}" required>
                        @error('inicio')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="termino" class="form-label fw-bold text-muted small">Data de Término</label>
                        <input type="date" class="form-control" id="termino" name="termino" value="{{ old('termino') }}" required>
                        @error('termino')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="status" class="form-label fw-bold text-muted small">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>Não Iniciada</option>
                            <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Concluída</option>
                            <option value="3" {{ old('status') == 3 ? 'selected' : 'selected' }}>Em Catequese</option>
                            <option value="4" {{ old('status') == 4 ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <hr class="my-5">

                <div class="mb-4">
                    <h4 class="fw-bold text-dark mb-3">Adicionar Alunos</h4>
                    <p class="text-muted small">Pesquise e adicione alunos à turma. Marque se já são batizados.</p>
                    
                    <div class="position-relative mb-3">
                        <input type="text" class="form-control" id="studentSearch" placeholder="Digite o nome do aluno para pesquisar..." autocomplete="off">
                        <div id="searchResults" class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 1000; max-height: 200px; overflow-y: auto;">
                            <!-- Search results will appear here -->
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th class="text-center" style="width: 150px;">Batizado</th>
                                    <th class="text-end" style="width: 100px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <tr id="emptyRow">
                                    <td colspan="3" class="text-center text-muted py-4">Nenhum aluno adicionado</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('turmas-eucaristia.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-2"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const searchInput = document.getElementById('studentSearch');
    const resultsContainer = document.getElementById('searchResults');
    const tableBody = document.getElementById('studentsTableBody');
    const emptyRow = document.getElementById('emptyRow');
    let addedStudents = []; 

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Prevent Enter key from submitting form
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
        }
    });

    // Fetch recent students on focus if empty
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim() === '') {
            fetchStudents('');
        }
    });

    searchInput.addEventListener('input', debounce((e) => {
        fetchStudents(e.target.value.trim());
    }, 300));

    async function fetchStudents(query) {
        try {
            const response = await fetch(`{{ route('registers.search') }}?q=${query}`);
            const data = await response.json();
            
            resultsContainer.innerHTML = '';
            
            if (data.length > 0) {
                let hasResults = false;
                data.forEach(student => {
                    if (addedStudents.includes(student.id)) return;

                    hasResults = true;
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `<strong>${student.name}</strong> <small class="text-muted ms-2">CPF: ${student.cpf || 'N/A'}</small>`;
                    item.onclick = (e) => {
                        e.preventDefault();
                        addStudent(student);
                        searchInput.value = '';
                        resultsContainer.classList.add('d-none');
                    };
                    resultsContainer.appendChild(item);
                });
                
                if (hasResults) {
                    resultsContainer.classList.remove('d-none');
                } else {
                    resultsContainer.classList.add('d-none');
                }
            } else {
                resultsContainer.classList.add('d-none');
            }
        } catch (error) {
            console.error('Error searching:', error);
        }
    }

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.classList.add('d-none');
        }
    });

    function addStudent(student) {
        if (addedStudents.includes(student.id)) return;

        if (emptyRow) emptyRow.style.display = 'none';

        const row = document.createElement('tr');
        row.id = `student-row-${student.id}`;
        row.innerHTML = `
            <td>
                <span class="fw-medium">${student.name}</span>
                <input type="hidden" name="students[${student.id}][id]" value="${student.id}">
            </td>
            <td class="text-center">
                <div class="form-check d-inline-block">
                    <input class="form-check-input" type="checkbox" name="students[${student.id}][batizado]" value="1" id="batizado-${student.id}">
                    <label class="form-check-label small" for="batizado-${student.id}">Sim</label>
                </div>
            </td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStudent(${student.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tableBody.appendChild(row);
        addedStudents.push(student.id);
    }

    window.removeStudent = function(id) {
        const row = document.getElementById(`student-row-${id}`);
        if (row) row.remove();
        
        addedStudents = addedStudents.filter(sId => sId !== id);

        if (addedStudents.length === 0 && emptyRow) {
            emptyRow.style.display = 'table-row';
        }
    };
</script>
@endsection
