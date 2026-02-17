@extends('layouts.pdf')

@section('title', 'Relatório de Festa/Evento')

@section('content')
    <div style="margin-bottom: 20px;">
        <div style="font-size: 12px; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">
            {{ $paroquiaName }}
        </div>
        <h2 style="margin-bottom: 5px; color: #2c3e50;">Relatório de Festa/Evento: {{ $festaEvento->titulo }}</h2>
        <p style="margin: 0; color: #666;">
            @if($festaEvento->data_inicio)
                <strong>Período:</strong> {{ \Carbon\Carbon::parse($festaEvento->data_inicio)->format('d/m/Y') }} 
                @if($festaEvento->data_fim)
                    a {{ \Carbon\Carbon::parse($festaEvento->data_fim)->format('d/m/Y') }}
                @endif
            @endif
            @if($festaEvento->comunidade)
                <br>
                <strong>Comunidade:</strong> {{ $festaEvento->comunidade->ent_name }}
            @endif
        </p>
        @if($festaEvento->descricao)
            <p style="margin-top: 10px; font-style: italic; color: #777;">
                {{ $festaEvento->descricao }}
            </p>
        @endif
        <div style="font-size: 11px; color: #888; margin-top: 10px;">
            Emitido em: {{ date('d/m/Y H:i') }}
        </div>
    </div>

    <div style="margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; background-color: #f9f9f9;">
        <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Resumo Financeiro</h3>
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; width: 33%; text-align: center;">
                    <div style="font-size: 10px; text-transform: uppercase; color: #198754; font-weight: bold;">Total de Entradas</div>
                    <div style="font-size: 18px; font-weight: bold; color: #198754;">R$ {{ number_format($totalEntradas, 2, ',', '.') }}</div>
                </td>
                <td style="border: none; width: 33%; text-align: center; border-left: 1px solid #ddd; border-right: 1px solid #ddd;">
                    <div style="font-size: 10px; text-transform: uppercase; color: #dc3545; font-weight: bold;">Total de Saídas</div>
                    <div style="font-size: 18px; font-weight: bold; color: #dc3545;">R$ {{ number_format($totalSaidas, 2, ',', '.') }}</div>
                </td>
                <td style="border: none; width: 33%; text-align: center;">
                    <div style="font-size: 10px; text-transform: uppercase; color: #0d6efd; font-weight: bold;">Saldo Financeiro</div>
                    <div style="font-size: 18px; font-weight: bold; color: #0d6efd;">R$ {{ number_format($saldoFinanceiro, 2, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <h3 style="margin-bottom: 10px;">Movimentações e Itens</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Data</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 20%;">Valor/Qtde</th>
                <th style="width: 50%;">Detalhe</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimentacoes as $mov)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mov['data'])->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $label = $mov['tipo'] === 'entrada' ? 'Entrada' : ($mov['tipo'] === 'saida' ? 'Saída' : ($mov['tipo'] === 'item_entrada' ? 'Item Entrada' : 'Item Saída'));
                        @endphp
                        {{ $label }}
                    </td>
                    <td>
                        @if(in_array($mov['tipo'], ['entrada', 'saida']))
                            R$ {{ number_format($mov['valor'], 2, ',', '.') }}
                        @else
                            {{ $mov['quantidade'] }}
                        @endif
                    </td>
                    <td>
                        @if(in_array($mov['tipo'], ['entrada', 'saida']))
                            {{ $mov['descricao'] ?? '-' }}
                        @else
                            {{ $mov['detalhe'] ?? '-' }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #777; padding: 20px;">Nenhuma movimentação registrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10px; color: #999; text-align: center;">
        Relatório gerado pelo SisMatriz
    </div>
@endsection
