<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ficha de Solicitação de Segunda Via</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
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
            max-height: 60px;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
        }
        .date {
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        .section-title {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 8px 10px;
            font-weight: bold;
            font-size: 14px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .field-row {
            margin-bottom: 8px;
        }
        .field-label {
            font-weight: bold;
            color: #555;
            width: 150px;
            display: inline-block;
        }
        .field-value {
            display: inline-block;
            color: #000;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 40px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            display: inline-block;
        }
        .bg-success { color: #fff; background-color: #198754; }
        .bg-warning { color: #000; background-color: #ffc107; }
        .bg-secondary { color: #fff; background-color: #6c757d; }
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
                    Ficha de Solicitação<br>
                    <span style="font-size: 14px; color: #555; font-weight: normal;">Segunda Via de Documentos</span>
                </td>
                <td width="20%" class="date">
                    Emissão: {{ date('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        <div class="section-title">Informações Gerais</div>
        
        <div class="field-row">
            <span class="field-label">ID da Solicitação:</span>
            <span class="field-value">#{{ $record->id }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Data do Pedido:</span>
            <span class="field-value">{{ $record->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Status Atual:</span>
            <span class="field-value">
                @if($record->status == 1)
                    <span class="badge bg-success">Finalizado</span>
                @else
                    <span class="badge bg-warning">Pendente</span>
                @endif
            </span>
        </div>

        <div class="section-title">Dados do Solicitante</div>

        @if($record->sacramento === 'matrimonio')
        <div class="field-row">
            <span class="field-label">Nome dos Cônjuges:</span>
            <span class="field-value">{{ $record->nome_conjuges }}</span>
        </div>
        @else
        <div class="field-row">
            <span class="field-label">Nome Completo:</span>
            <span class="field-value">{{ $record->nome_completo }}</span>
        </div>
        @endif
        
        <div class="field-row">
            <span class="field-label">Telefone:</span>
            <span class="field-value">{{ $record->telefone }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Data Nascimento:</span>
            <span class="field-value">{{ $record->data_nascimento ? $record->data_nascimento->format('d/m/Y') : 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Nome da Mãe:</span>
            <span class="field-value">{{ $record->nome_mae ?? 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Nome do Pai:</span>
            <span class="field-value">{{ $record->nome_pais ?? 'N/A' }}</span>
        </div>

        <div class="section-title">Detalhes do Sacramento</div>

        <div class="field-row">
            <span class="field-label">Sacramento:</span>
            <span class="field-value" style="text-transform: uppercase; font-weight: bold;">{{ $record->sacramento }}</span>
        </div>

        @if($record->sacramento == 'batismo')
        <div class="field-row">
            <span class="field-label">Paróquia do Batismo:</span>
            <span class="field-value">{{ $record->local_batismo ?? 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Data do Batismo:</span>
            <span class="field-value">{{ $record->data_batismo ? $record->data_batismo->format('d/m/Y') : 'N/A' }}</span>
        </div>
        @endif

        @if($record->sacramento == 'matrimonio')
        <div class="field-row">
            <span class="field-label">Data Cerimônia:</span>
            <span class="field-value">{{ $record->data_cerimonia ? $record->data_cerimonia->format('d/m/Y') : 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Testemunhas:</span>
            <span class="field-value">{{ $record->testemunhas ?? 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Celebrante:</span>
            <span class="field-value">{{ $record->celebrante ?? 'N/A' }}</span>
        </div>
        @endif

        @if($record->sacramento == 'crisma')
        <div class="field-row">
            <span class="field-label">Local Celebração:</span>
            <span class="field-value">{{ $record->local_celebracao ?? 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Data Crisma:</span>
            <span class="field-value">{{ $record->data_crisma ? $record->data_crisma->format('d/m/Y') : 'N/A' }}</span>
        </div>
        @endif
        
        <div class="field-row">
            <span class="field-label">Finalidade:</span>
            <span class="field-value">{{ $record->finalidade ?? 'Não informada' }}</span>
        </div>
        
        <div class="field-row">
            <span class="field-label">Observações:</span>
            <span class="field-value">{{ $record->mais_detalhes ?? 'Nenhuma observação' }}</span>
        </div>
    </div>

    <div class="footer">
        <p>SisMatriz é um serviço fornecido pela Sacratech Softwares</p>
        <p>Copyright &copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.</p>
    </div>
</body>
</html>