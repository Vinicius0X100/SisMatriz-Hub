<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Fichas de Inscrição - Crisma</title>
    <style>
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: sans-serif;
            margin-top: 3cm;
            margin-bottom: 2cm;
            margin-left: 1cm;
            margin-right: 1cm;
            color: #1e293b;
            background: white;
        }
        
        .header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 3cm;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
            padding-top: 0.5cm;
            background: white;
            z-index: 1000;
        }

        .header img {
            height: 50px;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        
        .header h1 {
            margin: 5px 0 0;
            font-size: 18px;
            text-transform: uppercase;
            color: #0f172a;
        }
        
        .header p {
            margin: 2px 0 0;
            font-size: 12px;
            color: #64748b;
        }

        .footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
            background: white;
            z-index: 1000;
        }
        
        .footer p {
            margin: 2px 0;
            font-size: 10px;
            color: #94a3b8;
        }

        .container {
            width: 100%;
            margin-bottom: 20px;
        }

        .page-break {
            page-break-after: always;
        }

        .section-title {
            background: #f8fafc;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin: 20px 0 10px;
            border-left: 4px solid #0f172a;
            color: #334155;
        }

        /* Table layout for form fields to ensure alignment in PDF */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }

        .label {
            display: block;
            font-weight: bold;
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .value {
            display: block;
            font-size: 14px;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            min-height: 18px;
        }

        .attachments {
            margin-top: 30px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 20px;
        }

        .attachment-item {
            margin-bottom: 20px;
            text-align: center;
            page-break-inside: avoid;
        }

        .attachment-label {
            font-weight: bold;
            font-size: 14px;
            color: #334155;
            background: #f1f5f9;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .attachment-item img {
            max-width: 90%;
            max-height: 600px;
            border: 1px solid #e2e8f0;
            padding: 5px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <!-- Define Header and Footer blocks only once if they are fixed, but DOMPDF handles them on every page -->
    <div class="header">
        <img src="{{ public_path('images/logo.png') }}" alt="SisMatriz Logo">
        <h1>Ficha de Inscrição - Crisma</h1>
        <p>Sistema de Gestão Paroquial</p>
    </div>

    <div class="footer">
        <p>O SisMatriz é um serviço oferecido pela Sacratech Softwares.</p>
        <p>&copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.</p>
    </div>

    @foreach($records as $record)
        <div class="container {{ !$loop->last ? 'page-break' : '' }}">
            <!-- Content Header (Specific to Record) -->
            <div style="text-align: right; font-size: 10px; color: #64748b; margin-bottom: 10px;">
                Protocolo: #{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }} • Data: {{ $record->criado_em ? \Carbon\Carbon::parse($record->criado_em)->format('d/m/Y H:i') : '-' }}
            </div>

            <div class="section-title">Dados Pessoais</div>
            <table class="info-table">
                <tr>
                    <td style="width: 60%;">
                        <span class="label">Nome Completo</span>
                        <span class="value">{{ $record->nome }}</span>
                    </td>
                    <td style="width: 40%;">
                        <span class="label">Data de Nascimento</span>
                        <span class="value">{{ $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '-' }}</span>
                    </td>
                </tr>
            </table>
            <table class="info-table">
                <tr>
                    <td style="width: 33%;">
                        <span class="label">CPF</span>
                        <span class="value">
                            @if($record->cpf)
                                {{ preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $record->cpf) }}
                            @else
                                -
                            @endif
                        </span>
                    </td>
                    <td style="width: 33%;">
                        <span class="label">Sexo</span>
                        <span class="value">{{ $record->sexo }}</span>
                    </td>
                    <td style="width: 33%;">
                        <span class="label">Nacionalidade</span>
                        <span class="value">{{ $record->nacionalidade }}</span>
                    </td>
                </tr>
            </table>

            <div class="section-title">Contato e Endereço</div>
            <table class="info-table">
                <tr>
                    <td style="width: 50%;">
                        <span class="label">Telefone Principal</span>
                        <span class="value">{{ $record->telefone1 }}</span>
                    </td>
                    <td style="width: 50%;">
                        <span class="label">Telefone Secundário</span>
                        <span class="value">{{ $record->telefone2 ?? '-' }}</span>
                    </td>
                </tr>
            </table>
            <table class="info-table">
                <tr>
                    <td style="width: 70%;">
                        <span class="label">Logradouro</span>
                        <span class="value">{{ $record->endereco }}, {{ $record->numero }}</span>
                    </td>
                    <td style="width: 30%;">
                        <span class="label">Bairro</span>
                        <span class="value">{{ $record->bairro ?? '-' }}</span>
                    </td>
                </tr>
            </table>
            <table class="info-table">
                <tr>
                    <td style="width: 30%;">
                        <span class="label">CEP</span>
                        <span class="value">{{ $record->cep }}</span>
                    </td>
                    <td style="width: 40%;">
                        <span class="label">Cidade</span>
                        <span class="value">{{ $record->cidade ?? 'Guarapuava' }}</span>
                    </td>
                    <td style="width: 30%;">
                        <span class="label">Estado</span>
                        <span class="value">{{ $record->estado }}</span>
                    </td>
                </tr>
            </table>

            <div class="section-title">Filiação</div>
            <table class="info-table">
                <tr>
                    <td style="width: 100%;">
                        <span class="label">Nome dos Pais/Responsáveis</span>
                        <span class="value">{{ $record->filiacao }}</span>
                    </td>
                </tr>
            </table>

            @if($record->certidao_batismo || $record->certidao_primeira_comunhao)
                <div class="attachments">
                    <div class="section-title" style="margin-top: 0; background: none; padding-left: 0; border: none;">Documentos Anexos</div>
                    
                    @if($record->certidao_batismo)
                        @php
                            $ext = strtolower(pathinfo($record->certidao_batismo, PATHINFO_EXTENSION));
                            $batismoPath = public_path('storage/uploads/certidoes/' . $record->certidao_batismo);
                        @endphp
                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) && file_exists($batismoPath))
                        <div class="attachment-item">
                            <span class="attachment-label">Certidão de Batismo</span>
                            <br>
                            <img src="{{ $batismoPath }}" alt="Certidão de Batismo">
                        </div>
                        @endif
                    @endif

                    @if($record->certidao_primeira_comunhao)
                        @php
                            $ext = strtolower(pathinfo($record->certidao_primeira_comunhao, PATHINFO_EXTENSION));
                            $eucaristiaPath = public_path('storage/uploads/certidoes/' . $record->certidao_primeira_comunhao);
                        @endphp
                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) && file_exists($eucaristiaPath))
                        <div class="attachment-item">
                            <span class="attachment-label">Certidão de Primeira Eucaristia</span>
                            <br>
                            <img src="{{ $eucaristiaPath }}" alt="Certidão de Eucaristia">
                        </div>
                        @endif
                    @endif
                </div>
            @endif
        </div>
    @endforeach
</body>
</html>