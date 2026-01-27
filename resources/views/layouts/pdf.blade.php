<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Relatório SisMatriz')</title>
    <style>
        @page {
            margin: 100px 25px 80px 25px; /* Top, Right, Bottom, Left */
        }
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
        }
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 80px;
            /* border-bottom: 1px solid #ddd; */
            padding-bottom: 10px;
        }
        footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .header-content {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            text-align: left;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .system-logo {
            width: 50px;
            height: 50px;
            border-radius: 10px; /* Bordas arredondadas */
            vertical-align: middle;
            margin-right: 10px;
            object-fit: cover;
        }
        .parish-logo {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            object-fit: cover;
        }
        .system-title {
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
            vertical-align: middle;
            color: #2c3e50;
        }
        .parish-name {
            display: block;
            font-size: 12px;
            color: #555;
            margin-top: 5px;
            margin-left: 64px; /* Align with text after logo */
        }
        .page-number:before {
            content: "Página " counter(page);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
    </style>
    @yield('styles')
</head>
<body>
    <header>
        <div class="header-content">
            <div class="header-left">
                <!-- Logo do Sistema -->
                <img src="{{ public_path('images/logo.png') }}" class="system-logo" onerror="this.src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='">
                <span class="system-title">SisMatriz</span>
                <!-- Nome da Paróquia -->
                <div class="parish-name">
                    {{ $paroquia->name ?? 'Paróquia não identificada' }}
                </div>
            </div>
            <div class="header-right">
                <!-- Logo da Paróquia -->
                @if(isset($paroquia) && $paroquia->foto)
                    <img src="{{ public_path('uploads/paroquias/' . $paroquia->foto) }}" class="parish-logo" onerror="this.style.display='none'">
                @endif
            </div>
        </div>
    </header>

    <footer>
        <div>
            &copy; {{ date('Y') }} Sacratech Softwares Ltda. Todos os Direitos reservados.
        </div>
        <div style="margin-top: 2px;">
            SisMatriz é um serviço oferecido pela Sacratech Softwares.
        </div>
        <div style="margin-top: 2px;">
            Emitido em: {{ date('d/m/Y') }}
        </div>
    </footer>

    <main>
        @yield('content')
    </main>
</body>
</html>
