@extends('layouts.pdf')

@section('title', 'Relatório de Campanha')

@section('content')
    <div style="margin-bottom: 20px;">
        <div style="font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
            {{ $paroquiaName }}
        </div>
        <h2 style="margin-bottom: 5px; color: #2c3e50;">Relatório de Campanha: {{ $campanha->nome }}</h2>
        <p style="margin: 0; color: #666;">
            <strong>Categoria:</strong> {{ $campanha->categoria->nome }} <br>
            @if($campanha->data_inicio)
                <strong>Período:</strong> {{ \Carbon\Carbon::parse($campanha->data_inicio)->format('d/m/Y') }} 
                @if($campanha->data_fim)
                    a {{ \Carbon\Carbon::parse($campanha->data_fim)->format('d/m/Y') }}
                @endif
            @endif
            <br>
            <strong>Status:</strong> <span style="text-transform: capitalize;">{{ $campanha->status }}</span>
        </p>
        @if($campanha->descricao)
            <p style="margin-top: 10px; font-style: italic; color: #777;">
                {{ $campanha->descricao }}
            </p>
        @endif
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; background-color: #f9f9f9;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Resumo Financeiro</h3>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 33%; text-align: center;">
                    <div style="font-size: 10px; text-transform: uppercase; color: #198754; font-weight: bold;">Total Arrecadado</div>
                    <div style="font-size: 18px; font-weight: bold; color: #198754;">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</div>
                </td>
                <td style="border: none; width: 33%; text-align: center; border-left: 1px solid #ddd; border-right: 1px solid #ddd;">
                    <div style="font-size: 10px; text-transform: uppercase; color: #dc3545; font-weight: bold;">Total Gasto</div>
                    <div style="font-size: 18px; font-weight: bold; color: #dc3545;">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</div>
                </td>
                <td style="border: none; width: 33%; text-align: center;">
                    <div style="font-size: 10px; text-transform: uppercase; color: #0d6efd; font-weight: bold;">Saldo Atual</div>
                    <div style="font-size: 18px; font-weight: bold; color: #0d6efd;">R$ {{ number_format($saldo, 2, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h3 style="margin-bottom: 10px;">Movimentações Recentes</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Data</th>
                <th style="width: 10%;">Tipo</th>
                <th style="width: 20%;">Valor</th>
                <th style="width: 20%;">Categoria/Forma</th>
                <th style="width: 35%;">Descrição/Obs</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimentacoes as $mov)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mov['data'])->format('d/m/Y') }}</td>
                    <td>
                        <span style="
                            padding: 2px 6px; 
                            border-radius: 4px; 
                            font-size: 10px; 
                            font-weight: bold;
                            color: {{ $mov['tipo'] == 'entrada' ? '#198754' : '#dc3545' }};
                            background-color: {{ $mov['tipo'] == 'entrada' ? '#d1e7dd' : '#f8d7da' }};
                        ">
                            {{ ucfirst($mov['tipo']) }}
                        </span>
                    </td>
                    <td style="font-weight: bold; color: {{ $mov['tipo'] == 'entrada' ? '#198754' : '#dc3545' }};">
                        {{ $mov['tipo'] == 'entrada' ? '+' : '-' }} R$ {{ number_format($mov['valor'], 2, ',', '.') }}
                    </td>
                    <td>{{ $mov['categoria'] ?? $mov['forma'] ?? '-' }}</td>
                    <td>{{ $mov['descricao'] ?? $mov['observacoes'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #777; padding: 20px;">Nenhuma movimentação registrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #999;">
        Relatório gerado em {{ date('d/m/Y H:i:s') }}
    </div>
@endsection
