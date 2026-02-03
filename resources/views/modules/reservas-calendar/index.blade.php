@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <h2 class="mb-0 fw-bold text-dark">Reservas e Calendário</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reservas e Calendário</li>
            </ol>
        </nav>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div id="calendar-root" style="min-height: 700px;"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @viteReactRefresh
    @vite('resources/js/calendar.tsx')
@endsection
