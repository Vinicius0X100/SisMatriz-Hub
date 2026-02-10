<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Estoque</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }
        @page {
            margin: 100px 25px 80px 25px;
        }
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 80px;
            border-bottom: 2px solid #eee;
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 60px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .logo-container {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
        }
        .logo {
            width: 50px;
            height: 50px;
            border-radius: 8px; /* Cantos arredondados */
            object-fit: cover;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            padding-left: 15px;
        }
        .system-name {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0;
        }
        .report-name {
            font-size: 14px;
            color: #555;
            margin: 2px 0;
        }
        .meta-info {
            font-size: 10px;
            color: #888;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            border-bottom: 1px solid #dee2e6;
            padding: 8px;
            vertical-align: middle;
        }
        .item-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #eee;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            background-color: #eee;
            font-size: 10px;
        }
        .text-end {
            text-align: right;
        }
        .footer-text {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-container">
                <!-- Logo do Sistema -->
                <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            </div>
            <div class="header-text">
                <h1 class="system-name">SisMatriz</h1>
                <h2 class="report-name">Relatório de Estoque</h2>
                <div class="meta-info">
                    Gerado em: {{ date('d/m/Y H:i') }} | Por: {{ Auth::user()->name }}
                </div>
            </div>
        </div>
    </header>

    <footer>
        <p class="footer-text">O SisMatriz é um serviço oferecido pela Sacratech Softwares.</p>
        <p class="footer-text">Copyright &copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.</p>
    </footer>

    <main>
        <!-- Filtros Aplicados -->
        <div style="margin-bottom: 15px; font-size: 11px; color: #555;">
            <strong>Filtros:</strong>
            {{ $filters_label ?? 'Todos os registros' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th width="50">Img</th>
                    <th>Descrição</th>
                    <th>Categoria</th>
                    <th>Comunidade</th>
                    <th>Local</th>
                    <th>Qtd.</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>
                            @if($item->images->count() > 0)
                                <img src="{{ public_path('storage/uploads/estoque/' . $item->images->first()->filename) }}" class="item-image">
                            @else
                                <div style="width: 40px; height: 40px; background: #f8f9fa; border: 1px solid #eee; border-radius: 4px; line-height: 40px; text-align: center; color: #ccc; font-size: 20px;">
                                    &bull;
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: bold;">{{ $item->description }}</div>
                            <div style="font-size: 10px; color: #777;">{{ $item->type }}</div>
                        </td>
                        <td>{{ $item->categoria->name ?? '-' }}</td>
                        <td>{{ $item->entidade->ent_name ?? '-' }}</td>
                        <td>{{ $item->sala->name ?? '-' }}</td>
                        <td><strong>{{ $item->qntd_destributed }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #777;">
                            Nenhum item encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>