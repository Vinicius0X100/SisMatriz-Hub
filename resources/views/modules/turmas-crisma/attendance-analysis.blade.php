@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h2 class="mb-0 fw-bold text-dark">Apuração de Presenças</h2>
            <p class="text-muted small mb-0">Turma: {{ $turma->turma }}</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('turmas-crisma.index') }}" class="text-decoration-none">Turmas de Crisma</a></li>
                <li class="breadcrumb-item active" aria-current="page">Apuração</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('turmas-crisma.attendance-analysis', $turma->id) }}" method="GET" class="row g-3" id="filterForm">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" 
                               placeholder="Pesquisar por nome..." 
                               value="{{ request('search') }}"
                               oninput="debounceSubmit()"
                               @if(request('search')) autofocus onfocus="var val=this.value; this.value=''; this.value= val;" @endif>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="filter_attendance" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">Todos (Presenças e Faltas)</option>
                        <option value="has_faults" {{ request('filter_attendance') == 'has_faults' ? 'selected' : '' }}>Com Faltas</option>
                        <option value="has_presences" {{ request('filter_attendance') == 'has_presences' ? 'selected' : '' }}>Com Presenças</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="filter_batizado" class="form-select bg-light" onchange="this.form.submit()">
                        <option value="">Todos (Batizado)</option>
                        <option value="1" {{ request('filter_batizado') == '1' ? 'selected' : '' }}>Batizados</option>
                        <option value="0" {{ request('filter_batizado') == '0' ? 'selected' : '' }}>Não Batizados</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th scope="col" class="border-0 rounded-start ps-4">Nome do Crismando(a)</th>
                            <th scope="col" class="border-0 text-center">Presenças</th>
                            <th scope="col" class="border-0 text-center">Faltas</th>
                            <th scope="col" class="border-0 text-center">Situação</th>
                            <th scope="col" class="border-0 rounded-end text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                        <tr>
                            <td class="ps-4 fw-bold text-secondary">{{ $student['name'] }}</td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" 
                                        onclick="showDetailsModal('Presenças - {{ addslashes($student['name']) }}', {{ json_encode($student['presencas_list']) }}, 'success')">
                                    {{ $student['presencas'] }}
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold"
                                        onclick="showDetailsModal('Faltas - {{ addslashes($student['name']) }}', {{ json_encode($student['faltas_list']) }}, 'danger')">
                                    {{ $student['faltas'] }}
                                </button>
                            </td>
                            <td class="text-center">
                                @if($student['batizado'])
                                    <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">Batizado</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Não Batizado</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('turmas-crisma.attendance-history', ['id' => $turma->id, 'student_id' => $student['id']]) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-clock-history me-1"></i> Histórico
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                                Nenhum crismando encontrado nesta turma.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="detailsModalTitle">Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="detailsTable">
                        <thead class="bg-light">
                            <tr>
                                <th scope="col" class="border-0 rounded-start ps-3">Data</th>
                                <th scope="col" class="border-0 rounded-end">Tema</th>
                            </tr>
                        </thead>
                        <tbody id="detailsList">
                            <!-- Content injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
    function showDetailsModal(title, list, type) {
        document.getElementById('detailsModalTitle').innerText = title;
        const listContainer = document.getElementById('detailsList');
        listContainer.innerHTML = '';

        if (list.length === 0) {
            listContainer.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-3">Nenhum registro encontrado.</td></tr>';
        } else {
            list.forEach(item => {
                // Ensure date is properly formatted
                let date = item.data_aula;
                
                // If date is in ISO format (YYYY-MM-DDTHH:mm:ss.sssZ), extract only the date part
                if (date.includes('T')) {
                    date = date.split('T')[0];
                }
                
                // Format as DD/MM/YYYY
                const dateParts = date.split('-');
                if (dateParts.length === 3) {
                     date = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
                }
                
                const icon = type === 'success' ? '<i class="bi bi-check-circle-fill text-success me-2"></i>' : '<i class="bi bi-x-circle-fill text-danger me-2"></i>';
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="ps-3 fw-bold">${icon} ${date}</td>
                    <td class="text-muted">${item.title || 'Sem tema'}</td>
                `;
                listContainer.appendChild(tr);
            });
        }

        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        modal.show();
    }

    let timeout = null;
    function debounceSubmit() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            document.getElementById('filterForm').submit();
        }, 1000);
    }
</script>
@endsection
