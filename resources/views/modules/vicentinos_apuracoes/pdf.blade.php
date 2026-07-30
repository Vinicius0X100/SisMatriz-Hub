@extends('layouts.pdf')

@section('title', 'Relatório de Apuração - Vicentinos')

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0; text-transform: uppercase; color: #1e293b;">Relatório de Apuração Mensal - Vicentinos</h2>
        @if(request('mes_ano'))
            @php
                $parts = explode('-', request('mes_ano'));
                $mesNome = $meses[(int)$parts[1]] ?? $parts[1];
            @endphp
            <p style="color: #64748b; margin-top: 5px;">Referência: {{ $mesNome }}/{{ $parts[0] }}</p>
        @endif
    </div>

    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
        <thead>
            <tr>
                <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">Nome</th>
                <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">Endereço</th>
                <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">Comunidade</th>
                <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">Mês (Ref.)</th>
                <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">Tipo</th>
                <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">Data Envio</th>
                <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">Enviado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
                <tr>
                    <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">{{ $record->name }}</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">{{ $record->address }}{{ $record->address_number ? ', ' . $record->address_number : '' }}</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">{{ $record->entidade->ent_name ?? 'N/A' }}</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">{{ $meses[$record->month_entire] ?? 'N/A' }}</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">{{ $record->kind == 1 ? 'Assistido' : 'Não Assistido' }}</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">{{ $record->created_at ? $record->created_at->format('d/m/Y') : '-' }}</td>
                    <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">{{ $record->sender->name ?? $record->sendby }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #64748b;">
                        Nenhum registro encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right; font-size: 10px; color: #64748b;">
        Total de registros: {{ $records->count() }}
    </div>
@endsection
