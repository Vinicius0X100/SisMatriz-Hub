<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Escala - {{ $escala->month }}/{{ $escala->year }}</title>
    <style>
        @page { margin: 30px 30px 30px 30px; }
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        
        /* Avoid page breaks inside table rows */
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        td { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        
        /* Header */
        .header { text-align: center; margin-bottom: 20px; position: relative; }
        .header h1 { margin: 0; font-size: 18px; font-weight: bold; color: #000; }
        .header p { margin: 2px 0; font-size: 11px; color: #444; }
        .logo-container { position: absolute; right: 0; top: 0; }
        
        /* Observations Box */
        .obs-box { 
            background-color: #f8f9fa; 
            border-left: 4px solid #0d6efd; 
            padding: 10px; 
            margin-bottom: 20px; 
            font-size: 10px;
        }
        .obs-title { font-weight: bold; display: block; margin-bottom: 5px; text-transform: uppercase; }
        .obs-list { margin: 0; padding-left: 15px; }
        .obs-list li { margin-bottom: 2px; }

        /* Main Title */
        .page-title { text-align: center; margin-bottom: 15px; }
        .page-title h2 { margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background-color: #fff8e1; font-weight: bold; text-align: center; color: #000; }
        
        .col-date { width: 15%; text-align: center; }
        .col-time { width: 8%; text-align: center; }
        .col-local { width: 15%; text-align: center; }
        .col-celebration { width: 22%; text-align: center; }
        .col-acolyte { width: 25%; }
        .col-function { width: 15%; text-align: center; }

        .cell-center { text-align: center; }
        .bg-stripe { background-color: #fff0f3; } /* Light pink for alternating rows or specific style */
        
        /* Specific styling from image */
        th { background-color: #fff3cd; } /* Light yellow header */
        td.function-cell { background-color: #fff0f3; } /* Light pink for function column */
        
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="{{ public_path('images/logo.png') }}" height="50">
        </div>
        <h1>SisMatriz</h1>
        <p>Email: contato@sismatriz.online | Telefone: {{ $parishPhone }}</p>
    </div>

    <div class="obs-box">
        <span class="obs-title">OBSERVAÇÕES:</span>
        <ul class="obs-list">
            <li>SEMPRE chegar com 30 minutos de antecedência em TODAS missas e celebrações.</li>
            <li>Se não puder servir no dia escalado, você mesmo deve fazer a troca com outra pessoa. - Troca deve ser realizada com antecedência.</li>
            <li>Faltas são inadmissíveis.</li>
            <li>A Túnica deve estar sempre limpa e passada, usar calçados fechados e Meninas sempre cabelo preso: "Rabo de Cavalo", "Tranças" ou "Coque".</li>
        </ul>
    </div>

    <div class="page-title">
        <h2>Escala - {{ $escala->month }} de {{ $escala->year }} - SisMatriz</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-date">Data</th>
                <th class="col-time">Hora</th>
                <th class="col-local">Local</th>
                <th class="col-celebration">Celebração</th>
                <th class="col-acolyte">Acólito</th>
                <th class="col-function">Função</th>
            </tr>
        </thead>
        <tbody>
            @foreach($finalGrouped as $dateKey => $group)
                @php
                    $dateObj = $group['date'];
                    $celebrations = $group['celebrations'];
                    
                    // Calculate total rows for this date
                    $totalDateRows = 0;
                    foreach($celebrations as $cel) {
                        $count = $cel->escalados->count();
                        $totalDateRows += ($count > 0 ? $count : 1);
                    }
                @endphp

                @foreach($celebrations as $celIndex => $cel)
                    @php
                        $escalados = $cel->escalados;
                        $celRows = ($escalados->count() > 0 ? $escalados->count() : 1);
                    @endphp

                    @if($escalados->count() > 0)
                        @foreach($escalados as $escIndex => $esc)
                            <tr>
                                {{-- Render Date cell only for the very first row of the very first celebration of the date --}}
                                @if($celIndex === 0 && $escIndex === 0)
                                    <td rowspan="{{ $totalDateRows }}" class="cell-center">
                                        {{ $dateObj->format('d') }} de {{ $escala->month }} de {{ $dateObj->format('Y') }}
                                    </td>
                                @endif

                                {{-- Render Celebration details only for the first row of this celebration --}}
                                @if($escIndex === 0)
                                    <td rowspan="{{ $celRows }}" class="cell-center" style="background-color: #fff8e1;">
                                        @if($cel->hora && preg_match('/^\d{2}:\d{2}/', $cel->hora))
                                            {{ \Carbon\Carbon::parse($cel->hora)->format('H:i') }}
                                        @else
                                            {{ $cel->hora ?? '00:00' }}
                                        @endif
                                    </td>
                                    <td rowspan="{{ $celRows }}" class="cell-center">{{ $cel->entidade->ent_name ?? 'Local' }}</td>
                                    <td rowspan="{{ $celRows }}" class="cell-center">{{ $cel->celebration }}</td>
                                @endif

                                {{-- Render Acolyte details --}}
                                <td>{{ $esc->acolito->user->name ?? 'N/A' }}</td>
                                <td class="function-cell cell-center">{{ $esc->funcao->name ?? 'Função' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            @if($celIndex === 0)
                                <td rowspan="{{ $totalDateRows }}" class="cell-center">
                                    {{ $dateObj->format('d') }} de {{ $escala->month }} de {{ $dateObj->format('Y') }}
                                </td>
                            @endif
                            
                            <td rowspan="1" class="cell-center" style="background-color: #fff8e1;">
                                @if($cel->hora && preg_match('/^\d{2}:\d{2}/', $cel->hora))
                                    {{ \Carbon\Carbon::parse($cel->hora)->format('H:i') }}
                                @else
                                    {{ $cel->hora ?? '00:00' }}
                                @endif
                            </td>
                            <td rowspan="1" class="cell-center">{{ $cel->entidade->ent_name ?? 'Local' }}</td>
                            <td rowspan="1" class="cell-center">{{ $cel->celebration }}</td>
                            
                            <td colspan="2" style="color: #999; text-align: center;">Nenhum acólito escalado</td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>