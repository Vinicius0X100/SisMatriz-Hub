@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Nova Turma de Catequese de Adultos</h2>
            <p class="text-muted small mb-0">Preencha os dados para criar uma nova turma.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('turmas-adultos.index') }}" class="text-decoration-none">Turmas de Adultos</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova Turma</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-5">
            <form action="{{ route('turmas-adultos.store') }}" method="POST">
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

                <div class="row">
                    <!-- Left Card: Available Students -->
                    <div class="col-md-6">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-header bg-white py-3">
                                <h5 class="card-title mb-0 fw-bold text-dark">Cadastros Disponíveis</h5>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="studentSearch" placeholder="Pesquisar por nome..." autocomplete="off">
                                </div>
                                <div id="availableStudentsList" class="list-group list-group-flush overflow-auto" style="max-height: 400px;">
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-search fs-1"></i>
                                        <p class="mt-2">Digite para pesquisar alunos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Card: Selected Students -->
                    <div class="col-md-6">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fw-bold text-dark">Adicionados a esta turma</h5>
                                <span class="badge bg-primary rounded-pill" id="selectedCount">0</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th class="ps-3">Nome</th>
                                                <th class="text-center">Batizado?</th>
                                                <th class="text-end pe-3">Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="selectedStudentsTable">
                                            <tr id="emptySelectionRow">
                                                <td colspan="3" class="text-center text-muted py-5">
                                                    <i class="bi bi-people fs-1"></i>
                                                    <p class="mt-2">Nenhum aluno selecionado</p>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-5">
                    <a href="{{ route('turmas-adultos.index') }}" class="btn btn-light rounded-pill px-4">Cancelar</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-check-lg me-2"></i>Salvar Turma
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
    const availableList = document.getElementById('availableStudentsList');
    const selectedTable = document.getElementById('selectedStudentsTable');
    const emptySelectionRow = document.getElementById('emptySelectionRow');
    const selectedCountBadge = document.getElementById('selectedCount');
    
    let addedStudents = []; 
    // We will keep a map of loaded students from search results to easily access their data
    let loadedStudentsMap = new Map();

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Prevent Enter key from submitting form globally
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            // Allow Enter on textareas or submit buttons if needed, but generally prevent form submit
            if (event.target.type !== 'textarea' && event.target.tagName !== 'BUTTON') {
                event.preventDefault();
                return false;
            }
        }
    });

    // Initial fetch on focus if empty
    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim() === '') {
            fetchStudents('');
        }
    });

    // Load students on page load
    document.addEventListener('DOMContentLoaded', () => {
        fetchStudents('');
    });

    searchInput.addEventListener('input', debounce((e) => {
        fetchStudents(e.target.value.trim());
    }, 300));

    async function fetchStudents(query) {
        try {
            availableList.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';
            
            const response = await fetch(`{{ route('registers.search') }}?q=${encodeURIComponent(query)}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const data = await response.json();
            
            availableList.innerHTML = '';
            loadedStudentsMap.clear();
            
            if (data.length > 0) {
                data.forEach(student => {
                    loadedStudentsMap.set(student.id, student);
                    renderAvailableItem(student);
                });
            } else {
                availableList.innerHTML = `
                    <div class="text-center text-muted py-4">
                        <p class="mb-0">Nenhum registro encontrado</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error searching:', error);
            availableList.innerHTML = '<div class="text-center text-danger py-3">Erro ao buscar alunos</div>';
        }
    }

    function renderAvailableItem(student) {
        const isAdded = addedStudents.includes(student.id);
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex justify-content-between align-items-center p-3';
        item.id = `available-item-${student.id}`;
        
        item.innerHTML = `
            <div>
                <div class="fw-bold text-dark">${student.name}</div>
                <div class="text-muted small">CPF: ${student.cpf || 'Não informado'}</div>
            </div>
            <button type="button" 
                class="btn btn-sm ${isAdded ? 'btn-secondary' : 'btn-outline-primary'} rounded-pill px-3"
                onclick="addStudent(${student.id})"
                ${isAdded ? 'disabled' : ''}>
                ${isAdded ? 'Adicionado' : 'Adicionar'}
            </button>
        `;
        availableList.appendChild(item);
    }

    window.addStudent = function(id) {
        if (addedStudents.includes(id)) return;
        
        const student = loadedStudentsMap.get(id);
        if (!student) return;

        // Hide empty row
        if (emptySelectionRow) emptySelectionRow.style.display = 'none';

        const row = document.createElement('tr');
        row.id = `selected-row-${id}`;
        row.innerHTML = `
            <td class="ps-3">
                <span class="fw-medium">${student.name}</span>
                <input type="hidden" name="students[${id}][id]" value="${id}">
            </td>
            <td class="text-center">
                <div class="form-check d-inline-block">
                    <input class="form-check-input" type="checkbox" name="students[${id}][batizado]" value="1" id="batizado-${id}">
                    <label class="form-check-label small" for="batizado-${id}">Sim</label>
                </div>
            </td>
            <td class="text-end pe-3">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStudent(${id})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        selectedTable.appendChild(row);
        addedStudents.push(id);
        updateCount();

        // Update button in available list if visible
        const btn = document.querySelector(`#available-item-${id} button`);
        if (btn) {
            btn.className = 'btn btn-sm btn-secondary rounded-pill px-3';
            btn.textContent = 'Adicionado';
            btn.disabled = true;
        }
    };

    window.removeStudent = function(id) {
        const row = document.getElementById(`selected-row-${id}`);
        if (row) row.remove();
        
        addedStudents = addedStudents.filter(sId => sId !== id);
        updateCount();

        if (addedStudents.length === 0 && emptySelectionRow) {
            emptySelectionRow.style.display = 'table-row';
        }

        // Update button in available list if visible
        const btn = document.querySelector(`#available-item-${id} button`);
        if (btn) {
            btn.className = 'btn btn-sm btn-outline-primary rounded-pill px-3';
            btn.textContent = 'Adicionar';
            btn.disabled = false;
        }
    };

    function updateCount() {
        if (selectedCountBadge) {
            selectedCountBadge.textContent = addedStudents.length;
        }
    }
</script>
@endsection
