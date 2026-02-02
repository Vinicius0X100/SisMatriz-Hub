<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lista de Passageiros - Ônibus {{ $onibus->numero }}</title>
    <style>
        @page { margin: 100px 25px 60px 25px; }
        body { font-family: sans-serif; font-size: 12px; }
        header { position: fixed; top: -80px; left: 0px; right: 0px; height: 60px; text-align: center; border-bottom: 1px solid #ccc; }
        footer { position: fixed; bottom: -50px; left: 0px; right: 0px; height: 40px; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ccc; padding-top: 5px; }
        .header-content h1 { margin: 0; font-size: 24px; color: #333; }
        .header-content p { margin: 0; font-size: 10px; color: #666; }
        
        .page-title { text-align: center; margin-top: 0px; margin-bottom: 20px; }
        .page-title h2 { margin: 0; font-size: 18px; }
        .page-title p { margin: 2px 0; }
        
        .info-box { border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; background-color: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .checkbox { width: 12px; height: 12px; border: 1px solid #000; display: inline-block; margin-right: 5px; }
        .center { text-align: center; }
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

    <div class="page-title">
        <h2>Lista de Passageiros - Ônibus {{ $onibus->numero }}</h2>
        <p><strong>Excursão:</strong> {{ $excursao->destino }}</p>
        <p><strong>Data:</strong> {{ $excursao->data_inicio ? $excursao->data_inicio->format('d/m/Y') : '__/__/____' }} a {{ $excursao->data_fim ? $excursao->data_fim->format('d/m/Y') : '__/__/____' }}</p>
    </div>

    <div class="info-box">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 50%; vertical-align: top;">
                    <strong>Responsável pelo Ônibus:</strong> {{ $onibus->responsavel ?? 'Não informado' }} <br>
                    <strong>Telefone do Responsável:</strong> {{ $onibus->telefone_responsavel ?? 'Não informado' }} <br>
                    <strong>Motorista:</strong> {{ $onibus->motorista ?? 'Não informado' }} <br>
                    <strong>Placa:</strong> {{ $onibus->placa ?? 'Não informado' }}
                </td>
                <td style="border: none; width: 50%; vertical-align: top;">
                    <strong>Saída:</strong> {{ $onibus->local_saida ?? 'Não informado' }} <br>
                    <strong>Horário Saída:</strong> {{ $onibus->horario_saida ? $onibus->horario_saida->format('d/m/Y H:i') : '--:--' }} <br>
                    <strong>Horário Retorno:</strong> {{ $onibus->horario_retorno ? $onibus->horario_retorno->format('d/m/Y H:i') : '--:--' }}
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Nome</th>
                <th style="width: 10%;">RG</th>
                <th style="width: 10%;">Telefone</th>
                <th style="width: 15%;">Tipo</th>
                <th style="width: 15%;">Responsável (Menor)</th>
                <th style="width: 10%;" class="center">Emb. Ida</th>
                <th style="width: 10%;" class="center">Emb. Volta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($onibus->assentosVendidos as $assento)
            <tr>
                <td>{{ $assento->poltrona }}</td>
                <td>{{ $assento->passageiro_nome }}</td>
                <td>{{ $assento->passageiro_rg }}</td>
                <td>{{ $assento->passageiro_telefone }}</td>
                <td>{{ $assento->menor ? 'Menor' : 'Adulto' }}</td>
                <td>
                    @if($assento->menor)
                        {{ $assento->responsavel_nome }}<br>
                        <small>{{ $assento->responsavel_telefone }}</small>
                    @else
                        -
                    @endif
                </td>
                <td class="center"><div class="checkbox"></div></td>
                <td class="center"><div class="checkbox"></div></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div style="margin-top: 20px; text-align: right;">
        <p>Total de Passageiros: {{ $onibus->assentosVendidos->count() }} / {{ $onibus->capacidade }}</p>
    </div>
</body>
</html>
