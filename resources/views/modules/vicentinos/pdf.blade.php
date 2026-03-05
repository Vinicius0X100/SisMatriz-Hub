@extends('layouts.pdf')

@section('title', 'Relatório Vicentinos')

@section('content')
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0; text-transform: uppercase; color: #1e293b;">Relatório de Assistidos - Vicentinos</h2>
    </div>

    @if(isset($mode) && $mode === 'ficha')
        @forelse($records as $record)
            <div style="page-break-after: {{ $loop->last ? 'avoid' : 'always' }}; padding: 20px; border: 1px solid #e2e8f0; margin-bottom: 20px; border-radius: 8px;">
                <h3 style="margin-top: 0; margin-bottom: 15px; border-bottom: 2px solid #cbd5e1; padding-bottom: 10px; color: #1e293b;">
                    {{ $record->responsavel_nome }} <span style="font-size: 12px; color: #64748b; font-weight: normal;">(ID: {{ $record->id }})</span>
                </h3>

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <tbody>
                        @foreach(array_chunk($columns, 2) as $row)
                            <tr>
                                @foreach($row as $column)
                                    <td style="width: 50%; padding: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top;">
                                        <div style="font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 4px;">
                                            {{ $columnLabels[$column] ?? ucfirst(str_replace('_', ' ', $column)) }}
                                        </div>
                                        <div style="font-size: 12px; color: #334155;">
                                            @if($column === 'created_at')
                                                {{ $record->created_at->format('d/m/Y') }}
                                            @elseif($column === 'data_nascimento' || $column === 'data_dispensa')
                                                {{ $record->$column ? \Carbon\Carbon::parse($record->$column)->format('d/m/Y') : '-' }}
                                            @elseif(in_array($column, ['valor_aluguel_prestacao', 'valor_bolsa_familia', 'outro_beneficio_valor', 'renda']))
                                                R$ {{ number_format($record->$column, 2, ',', '.') }}
                                            @elseif($column === 'recebe_bolsa_familia' || $column === 'catolico_tem_sacramentos')
                                                {{ $record->$column ? 'Sim' : 'Não' }}
                                            @else
                                                {{ $record->$column ?? '-' }}
                                            @endif
                                        </div>
                                    </td>
                                @endforeach
                                @if(count($row) === 1)
                                    <td style="width: 50%; padding: 8px; border-bottom: 1px solid #f1f5f9;"></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if(isset($includeFamily) && $includeFamily && $record->families->count() > 0)
                    <div style="margin-top: 15px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
                        <h4 style="margin: 0 0 10px 0; font-size: 12px; text-transform: uppercase; color: #475569;">Composição Familiar</h4>
                        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                            <thead>
                                <tr>
                                    <th style="background-color: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; text-align: left; color: #64748b;">Nome</th>
                                    <th style="background-color: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; text-align: left; color: #64748b;">Parentesco</th>
                                    <th style="background-color: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; text-align: left; color: #64748b;">Data Nasc.</th>
                                    <th style="background-color: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; text-align: left; color: #64748b;">Escolaridade</th>
                                    <th style="background-color: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; text-align: left; color: #64748b;">Profissão</th>
                                    <th style="background-color: #f8fafc; padding: 6px; border: 1px solid #e2e8f0; text-align: right; color: #64748b;">Renda</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->families as $family)
                                    <tr>
                                        <td style="padding: 6px; border: 1px solid #e2e8f0; color: #334155;">{{ $family->nome }}</td>
                                        <td style="padding: 6px; border: 1px solid #e2e8f0; color: #334155;">{{ ucfirst($family->parentesco) }}</td>
                                        <td style="padding: 6px; border: 1px solid #e2e8f0; color: #334155;">{{ $family->nascimento ? \Carbon\Carbon::parse($family->nascimento)->format('d/m/Y') : '-' }}</td>
                                        <td style="padding: 6px; border: 1px solid #e2e8f0; color: #334155;">{{ ucfirst($family->escolaridade) ?? '-' }}</td>
                                        <td style="padding: 6px; border: 1px solid #e2e8f0; color: #334155;">{{ $family->profissao ?? '-' }}</td>
                                        <td style="padding: 6px; border: 1px solid #e2e8f0; color: #334155; text-align: right;">R$ {{ number_format($family->renda, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #64748b;">
                Nenhum registro encontrado.
            </div>
        @endforelse
    @else
        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
            <thead>
                <tr>
                    @foreach($columns as $column)
                        <th style="background-color: #f1f5f9; padding: 8px; border: 1px solid #e2e8f0; text-align: left; font-weight: bold; color: #475569;">
                            {{ $columnLabels[$column] ?? ucfirst(str_replace('_', ' ', $column)) }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        @foreach($columns as $column)
                            <td style="padding: 8px; border: 1px solid #e2e8f0; color: #334155;">
                                @if($column === 'created_at')
                                    {{ $record->created_at->format('d/m/Y') }}
                                @elseif($column === 'data_nascimento' || $column === 'data_dispensa')
                                    {{ $record->$column ? \Carbon\Carbon::parse($record->$column)->format('d/m/Y') : '-' }}
                                @elseif(in_array($column, ['valor_aluguel_prestacao', 'valor_bolsa_familia', 'outro_beneficio_valor', 'renda']))
                                    R$ {{ number_format($record->$column, 2, ',', '.') }}
                                @elseif($column === 'recebe_bolsa_familia' || $column === 'catolico_tem_sacramentos')
                                    {{ $record->$column ? 'Sim' : 'Não' }}
                                @else
                                    {{ $record->$column ?? '-' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) }}" style="text-align: center; padding: 20px; color: #64748b;">
                            Nenhum registro encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div style="margin-top: 20px; text-align: right; font-size: 10px; color: #64748b;">
        Total de registros: {{ $records->count() }}
    </div>
@endsection
