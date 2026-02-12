@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
            <h2 class="mb-0 fw-bold text-dark">Calendário Matrimonial</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Calendário Matrimonial</li>
                </ol>
            </nav>
        </div>

        <div id="calendar-matrimonio-root" style="min-height: 800px;"></div>
    </div>

    <style>
        .rbc-event {
            min-height: auto !important;
            height: auto !important;
            white-space: normal !important;
            padding: 0 !important;
            overflow: visible !important;
            display: flex !important;
            flex-direction: column;
            background-color: transparent !important; /* Deixa o CustomEvent controlar a cor */
            border: none !important;
        }
        .rbc-event-content {
            white-space: normal !important;
            font-size: 0.9em;
            overflow: visible !important;
            max-height: none !important;
            flex: 1;
            display: block !important;
            padding: 0 !important;
        }
        .rbc-row-segment {
            padding: 2px 4px !important;
            height: auto !important;
            overflow: visible !important;
        }
        /* Garantir que as linhas cresçam */
        .rbc-month-row {
            overflow: visible !important;
            min-height: 120px; /* Aumentar altura da célula */
        }
        /* Ajuste para ícones */
        .rbc-event i {
            margin-right: 4px;
        }
    </style>
@endsection

@section('scripts')
    @viteReactRefresh
    @vite('resources/js/calendar-matrimonio.tsx')
@endsection
