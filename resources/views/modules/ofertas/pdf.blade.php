@extends('layouts.pdf')

@section('title', 'Relatório Financeiro')

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0; text-transform: uppercase; font-size: 16px;">Relatório Financeiro de Oferta, Dízimos e mais</h2>
        <div style="margin-top: 5px; font-size: 12px; color: #555;">
            Período: {{ $period }}
        </div>
        <div style="margin-top: 2px; font-size: 12px; color: #555;">
            Tipos: {{ $types_label }}
        </div>
    </div>

    @php
        $kindMap = [
            1 => 'Dízimo',
            2 => 'Oferta',
            3 => 'Moedas',
            4 => 'Doação em Cofre',
            5 => 'Bazares',
            6 => 'Vendas',
        ];
    @endphp

    @foreach($groupedData as $entName => $ofertas)
        <div style="margin-bottom: 20px; page-break-inside: avoid;">
            <h3 style="background-color: #eee; padding: 5px 10px; margin-bottom: 0; font-size: 14px; border: 1px solid #ccc; border-bottom: none;">
                {{ $entName }}
            </h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <thead>
                    <tr>
                        <th style="width: 15%;">Data</th>
                        <th style="width: 20%;">Valor</th>
                        <th style="width: 30%;">Celebração</th>
                        <th style="width: 35%;">Tipo de Lançamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ofertas as $oferta)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($oferta->data)->format('d/m/Y') }}</td>
                            <td>R$ {{ number_format($oferta->valor_total, 2, ',', '.') }}</td>
                            <td>{{ $oferta->tipo ?? '-' }}</td>
                            <td>{{ $kindMap[$oferta->kind] ?? 'Outro' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f9f9f9; font-weight: bold;">
                        <td colspan="1" style="text-align: right;">Total {{ $entName }}:</td>
                        <td colspan="3">R$ {{ number_format($ofertas->sum('valor_total'), 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <!-- Subtotais por Tipo na Comunidade -->
            <div style="margin-top: 5px; font-size: 10px; color: #555;">
                <strong>Resumo da Comunidade:</strong>
                @foreach($ofertas->groupBy('kind') as $kind => $items)
                    <span style="margin-right: 10px;">
                        {{ $kindMap[$kind] ?? 'Outro' }}: R$ {{ number_format($items->sum('valor_total'), 2, ',', '.') }}
                    </span>
                @endforeach
            </div>
        </div>
    @endforeach

    <div style="margin-top: 30px; page-break-inside: avoid;">
        <h3 style="margin-bottom: 10px; border-bottom: 2px solid #333; padding-bottom: 5px;">Resumo Geral</h3>
        
        <table style="width: 50%; border-collapse: collapse; font-size: 11px; margin-bottom: 20px;">
            <thead>
                <tr>
                    <th style="background-color: #333; color: #fff;">Tipo de Lançamento</th>
                    <th style="background-color: #333; color: #fff;">Total Acumulado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($totalsByType as $kind => $total)
                    <tr>
                        <td>{{ $kindMap[$kind] ?? 'Outro' }}</td>
                        <td>R$ {{ number_format($total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #eee;">
                    <td>TOTAL GERAL</td>
                    <td>R$ {{ number_format($grandTotal, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
