<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Solicitações de Segunda Via</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header table {
            width: 100%;
            border: none;
        }
        .header td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .logo {
            max-height: 50px;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
        }
        .date {
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            font-size: 9px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #ddd;
            padding-top: 5px;
            text-align: center;
            font-size: 8px;
            color: #777;
        }
        .badge {
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: #fff;
            display: inline-block;
        }
        .bg-success { background-color: #198754; }
        .bg-warning { background-color: #ffc107; color: #000; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td width="20%">
                    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
                </td>
                <td width="60%" class="title">
                    Relatório de Solicitações<br>
                    <span style="font-size: 12px; color: #555; font-weight: normal;">Segunda Via de Documentos</span>
                </td>
                <td width="20%" class="date">
                    Emissão: {{ date('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                @if(in_array('data_solicitacao', $columns)) <th width="10%">Data</th> @endif
                @if(in_array('solicitante', $columns)) <th width="25%">Solicitante</th> @endif
                @if(in_array('telefone', $columns)) <th width="15%">Telefone</th> @endif
                @if(in_array('sacramento', $columns)) <th width="15%">Sacramento</th> @endif
                @if(in_array('status', $columns)) <th width="10%" class="text-center">Status</th> @endif
                @if(in_array('detalhes', $columns)) <th>Detalhes Principais</th> @endif
            </tr>
        </thead>
        <tbody>
            @forelse($records as $record)
            <tr>
                @if(in_array('data_solicitacao', $columns))
                <td>{{ $record->created_at->format('d/m/Y') }}</td>
                @endif
                
                @if(in_array('solicitante', $columns))
                <td>
                    <strong>{{ $record->nome_completo }}</strong><br>
                    <span style="font-size: 8px; color: #666;">Mãe: {{ $record->nome_mae }}</span>
                </td>
                @endif
                
                @if(in_array('telefone', $columns))
                <td>{{ $record->telefone }}</td>
                @endif
                
                @if(in_array('sacramento', $columns))
                <td style="text-transform: capitalize;">{{ $record->sacramento }}</td>
                @endif
                
                @if(in_array('status', $columns))
                <td class="text-center">
                    @if($record->status == 1)
                        <span class="badge bg-success">Finalizado</span>
                    @else
                        <span class="badge bg-warning">Pendente</span>
                    @endif
                </td>
                @endif

                @if(in_array('detalhes', $columns))
                <td style="font-size: 9px;">
                    @if($record->sacramento == 'batismo')
                        Local: {{ $record->local_batismo }}<br>Data: {{ $record->data_batismo ? $record->data_batismo->format('d/m/Y') : '-' }}
                    @elseif($record->sacramento == 'matrimonio')
                        Cônjuges: {{ $record->nome_conjuges }}
                    @elseif($record->sacramento == 'crisma')
                        Local: {{ $record->local_celebracao }}
                    @else
                        {{ Str::limit($record->mais_detalhes, 50) }}
                    @endif
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ count($columns) }}" class="text-center">Nenhum registro encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>SisMatriz é um serviço fornecido pela Sacratech Softwares</p>
        <p>Copyright &copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.</p>
    </div>
</body>
</html>