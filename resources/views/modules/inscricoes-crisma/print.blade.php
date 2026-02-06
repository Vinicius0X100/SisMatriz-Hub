<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fichas de Inscrição - Crisma</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .ficha {
            border: 1px solid #ccc;
            padding: 30px;
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 10pt;
            color: #666;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-left: 5px solid #333;
            text-transform: uppercase;
            font-size: 10pt;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        .col {
            flex: 1;
            padding-right: 15px;
        }
        .label {
            font-weight: bold;
            display: block;
            font-size: 9pt;
            color: #666;
            text-transform: uppercase;
        }
        .value {
            display: block;
            font-size: 11pt;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 2px;
            min-height: 20px;
        }
        @media print {
            body {
                padding: 0;
            }
            .ficha {
                border: none;
                page-break-after: always;
            }
            .ficha:last-child {
                page-break-after: auto;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-bottom: 1px solid #ddd;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background: #0d6efd; color: white; border: none; border-radius: 5px;">Imprimir Fichas</button>
    </div>

    @foreach($records as $record)
        <div class="ficha">
            <div class="header">
                <h1>Ficha de Inscrição - Crisma</h1>
                <p>Inscrição #{{ $record->id }} • Data: {{ $record->criado_em ? \Carbon\Carbon::parse($record->criado_em)->format('d/m/Y') : '-' }}</p>
            </div>

            <div class="section-title">Dados Pessoais</div>
            <div class="row">
                <div class="col">
                    <span class="label">Nome Completo</span>
                    <span class="value">{{ $record->nome }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <span class="label">Data de Nascimento</span>
                    <span class="value">{{ $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="col">
                    <span class="label">Sexo</span>
                    <span class="value">{{ $record->sexo }}</span>
                </div>
                <div class="col">
                    <span class="label">CPF</span>
                    <span class="value">{{ $record->cpf ?? '-' }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <span class="label">Nacionalidade</span>
                    <span class="value">{{ $record->nacionalidade ?? '-' }}</span>
                </div>
                <div class="col">
                    <span class="label">Estado (UF)</span>
                    <span class="value">{{ $record->estado ?? '-' }}</span>
                </div>
            </div>

            <div class="section-title">Endereço e Contato</div>
            <div class="row">
                <div class="col" style="flex: 2;">
                    <span class="label">Endereço Residencial</span>
                    <span class="value">{{ $record->endereco ?? '-' }}</span>
                </div>
                <div class="col" style="flex: 0.5;">
                    <span class="label">Número</span>
                    <span class="value">{{ $record->numero ?? '-' }}</span>
                </div>
                <div class="col">
                    <span class="label">CEP</span>
                    <span class="value">{{ $record->cep ?? '-' }}</span>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <span class="label">Telefone Principal</span>
                    <span class="value">{{ $record->telefone1 ?? '-' }}</span>
                </div>
                <div class="col">
                    <span class="label">Telefone Secundário</span>
                    <span class="value">{{ $record->telefone2 ?? '-' }}</span>
                </div>
            </div>

            <div class="section-title">Filiação</div>
            <div class="row">
                <div class="col">
                    <span class="label">Nome do Pai e da Mãe</span>
                    <span class="value">{{ $record->filiacao ?? '-' }}</span>
                </div>
            </div>

            <div class="section-title">Dados Religiosos</div>
            <div class="row">
                <div class="col">
                    <span class="label">Possui Batismo?</span>
                    <span class="value">
                        @if(!empty($record->certidao_batismo)) Sim (Certidão Anexada) @else Não / Não Informado @endif
                    </span>
                </div>
                <div class="col">
                    <span class="label">Possui 1ª Eucaristia?</span>
                    <span class="value">
                        @if(!empty($record->certidao_primeira_comunhao)) Sim (Certidão Anexada) @else Não / Não Informado @endif
                    </span>
                </div>
            </div>

            <div class="section-title">Situação e Pagamento</div>
            <div class="row">
                <div class="col">
                    <span class="label">Status da Inscrição</span>
                    <span class="value">
                        @if($record->status == 1) Aprovado
                        @elseif($record->status == 2) Reprovado
                        @else Pendente @endif
                    </span>
                </div>
                <div class="col">
                    <span class="label">Taxa</span>
                    <span class="value">{{ $record->taxa ? $record->taxa->nome . ' (R$ ' . number_format($record->taxa->valor, 2, ',', '.') . ')' : 'Não informada' }}</span>
                </div>
                <div class="col">
                    <span class="label">Pagamento</span>
                    <span class="value">{{ $record->taxaPaga ? 'Pago' : 'Pendente' }}</span>
                </div>
            </div>
            
            <div style="margin-top: 50px; text-align: center; display: flex; justify-content: space-around;">
                <div style="width: 40%; border-top: 1px solid #000; padding-top: 5px;">
                    Assinatura do Responsável/Inscrito
                </div>
                <div style="width: 40%; border-top: 1px solid #000; padding-top: 5px;">
                    Visto da Secretaria
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
