<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Passagens - Ônibus {{ $onibus->numero }}</title>
    <style>
        @page { margin: 100px 25px 60px 25px; }
        body { font-family: 'Helvetica', sans-serif; margin: 0; padding: 0px; }
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 60px; text-align: center; border-bottom: 1px solid #ccc; }
        footer { position: fixed; bottom: -50px; left: 0px; right: 0px; height: 40px; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ccc; padding-top: 5px; }
        .header-content h1 { margin: 0; font-size: 24px; color: #333; }
        .header-content p { margin: 0; font-size: 10px; color: #666; }

        .ticket {
            border: 2px solid #333;
            border-radius: 10px;
            margin-bottom: 20px;
            page-break-inside: avoid;
            position: relative;
            overflow: hidden;
            /* height: 300px; Removed fixed height to allow dynamic content */
            min-height: 250px;
        }
        .ticket-header {
            background-color: #333;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ticket-header h2 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .ticket-body { padding: 20px; display: table; width: 100%; }
        .col-info { display: table-cell; vertical-align: top; width: 70%; }
        .col-qr { display: table-cell; vertical-align: middle; width: 30%; text-align: center; border-left: 1px dashed #ccc; }
        
        .label { font-size: 10px; color: #666; text-transform: uppercase; margin-bottom: 2px; }
        .value { font-size: 14px; font-weight: bold; margin-bottom: 12px; color: #000; }
        
        .row { display: table; width: 100%; margin-bottom: 10px; }
        .col { display: table-cell; }
        
        .badge {
            background-color: #eee;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .badge-minor { background-color: #ffeba7; color: #5c4500; }
        
        .footer-note {
            background-color: #f9f9f9;
            padding: 10px 20px;
            border-top: 1px dashed #ccc;
            font-size: 10px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <img src="{{ public_path('images/logo.png') }}" height="40" style="vertical-align: middle; margin-right: 10px;">
            <span style="display: inline-block; vertical-align: middle; text-align: left;">
                <h1 style="margin: 0; font-size: 24px; color: #333;">SisMatriz</h1>
                <p style="margin: 0; font-size: 10px; color: #666;">Sistema de Gestão Paroquial</p>
            </span>
        </div>
    </header>

    <footer>
        <p>Copyright © {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.</p>
        <p>SisMatriz é um serviço oferecido pela Sacratech Softwares.</p>
    </footer>

    @foreach($onibus->assentosVendidos as $assento)
    <div class="ticket">
        <div class="ticket-header">
            <div style="display: flex; align-items: center;">
                <img src="{{ public_path('images/logo.png') }}" height="30" style="margin-right: 10px; background: white; padding: 2px; border-radius: 2px;">
                <h2 style="margin: 0;">Bilhete de Embarque</h2>
            </div>
            <span style="font-size: 14px;">#{{ str_pad($assento->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>
        
        <div class="ticket-body">
            <div class="col-info">
                <div class="row">
                    <div class="col">
                        <div class="label">Passageiro</div>
                        <div class="value">{{ $assento->passageiro_nome }}</div>
                    </div>
                    <div class="col">
                        <div class="label">RG</div>
                        <div class="value">{{ $assento->passageiro_rg ?? '-' }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="label">Excursão</div>
                        <div class="value">{{ $excursao->destino }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <div class="label">Ônibus</div>
                        <div class="value">{{ $onibus->numero }}</div>
                    </div>
                    <div class="col">
                        <div class="label">Poltrona</div>
                        <div class="value" style="font-size: 24px; color: #333;">{{ $assento->poltrona }} <span style="font-size: 12px; font-weight: normal; color: #666;">({{ ucfirst($assento->posicao) }})</span></div>
                    </div>
                </div>

                @if($assento->menor)
                <div style="background-color: #fff8e1; padding: 10px; border-radius: 5px; margin-top: 5px;">
                    <div class="label" style="color: #d4a017;">Responsável (Menor de Idade)</div>
                    <div class="value" style="margin-bottom: 0;">{{ $assento->responsavel_nome }} - {{ $assento->responsavel_telefone }}</div>
                </div>
                @endif
            </div>
            
            <div class="col-qr">
                <img src="data:image/svg+xml;base64,{{ $assento->qr_code }}" alt="QR Code" width="120">
                <div style="margin-top: 10px; font-size: 10px; color: #999;">Escaneie para validar</div>
            </div>
        </div>
        
        <div class="footer">
            Saída: {{ $onibus->local_saida }} - {{ $onibus->horario_saida->format('d/m/Y H:i') }} | Chegue com 30 minutos de antecedência.
        </div>
    </div>
    
    @if(!$loop->last)
        <div style="page-break-after: always;"></div>
    @endif
    @endforeach
</body>
</html>
