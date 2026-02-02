<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Passagens para Recorte - Ônibus {{ $onibus->numero }}</title>
    <style>
        @page { margin: 100px 25px 60px 25px; }
        body { font-family: 'Helvetica', sans-serif; margin: 0; padding: 0px; }
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 60px; text-align: center; border-bottom: 1px solid #ccc; }
        footer { position: fixed; bottom: -50px; left: 0px; right: 0px; height: 40px; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ccc; padding-top: 5px; }
        .header-content h1 { margin: 0; font-size: 24px; color: #333; }
        .header-content p { margin: 0; font-size: 10px; color: #666; }

        .ticket-container {
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px dashed #999;
            padding-bottom: 15px;
            page-break-inside: avoid;
        }
        .ticket-container:last-child { border-bottom: none; }
        
        .ticket {
            border: 1px solid #333;
            display: table;
            width: 100%;
            min-height: 240px;
        }
        
        .stub, .main { display: table-cell; vertical-align: top; padding: 10px; }
        
        .stub {
            width: 25%;
            border-right: 2px dashed #333;
            background-color: #f5f5f5;
            text-align: center;
        }
        
        .main { width: 75%; position: relative; }
        
        .label { font-size: 9px; color: #666; text-transform: uppercase; margin-bottom: 1px; }
        .value { font-size: 12px; font-weight: bold; margin-bottom: 8px; white-space: normal; overflow: visible; }
        
        .seat-number { font-size: 28px; font-weight: bold; margin: 10px 0; display: block; }
        
        .row { display: table; width: 100%; }
        .col { display: table-cell; width: 50%; }
        
        .qr-small { margin-top: 10px; }
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

    @foreach($onibus->assentosVendidos as $index => $assento)
        <div class="ticket-container">
            <div class="ticket">
                <div class="stub">
                    <div class="label">Poltrona</div>
                    <span class="seat-number">{{ $assento->poltrona }}</span>
                    <div class="label">{{ ucfirst($assento->posicao) }}</div>
                    
                    <div style="margin-top: 20px;">
                        <div class="label">Ônibus</div>
                        <div class="value">{{ $onibus->numero }}</div>
                    </div>
                    
                    <div class="qr-small">
                        <img src="data:image/svg+xml;base64,{{ $assento->qr_code }}" width="60">
                    </div>
                </div>
                
                <div class="main">
                    <div style="position: absolute; top: 10px; right: 10px; font-size: 10px; color: #999;">
                        #{{ str_pad($assento->id, 6, '0', STR_PAD_LEFT) }}
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <img src="{{ public_path('images/logo.png') }}" height="30">
                    </div>
                
                    <div class="label">Passageiro</div>
                    <div class="value" style="font-size: 16px;">{{ $assento->passageiro_nome }}</div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="label">RG</div>
                            <div class="value">{{ $assento->passageiro_rg ?? '-' }}</div>
                        </div>
                        <div class="col">
                            <div class="label">Telefone</div>
                            <div class="value">{{ $assento->passageiro_telefone ?? '-' }}</div>
                        </div>
                    </div>
                    
                    <div class="label">Excursão</div>
                    <div class="value">{{ $excursao->destino }}</div>
                    
                    <div class="row">
                        <div class="col">
                            <div class="label">Saída</div>
                            <div class="value">{{ $onibus->local_saida }}</div>
                        </div>
                        <div class="col">
                            <div class="label">Horário</div>
                            <div class="value">{{ $onibus->horario_saida ? $onibus->horario_saida->format('d/m/Y H:i') : '--:--' }}</div>
                        </div>
                    </div>

                    @if($assento->menor)
                    <div style="border-top: 1px solid #eee; margin-top: 5px; padding-top: 5px;">
                        <div class="label">Responsável</div>
                        <div class="value">{{ $assento->responsavel_nome }} ({{ $assento->responsavel_telefone }})</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        {{-- Page break every 4 tickets --}}
        @if(($index + 1) % 4 == 0 && !$loop->last)
            <div style="page-break-after: always;"></div>
        @endif
    @endforeach
</body>
</html>
