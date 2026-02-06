<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Compartilhamento de Fichas de Crisma</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .content {
            padding: 30px;
        }
        .message-box {
            background-color: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .list-box {
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .list-box h3 {
            margin-top: 0;
            font-size: 16px;
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .list-box ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .list-box li {
            padding: 5px 0;
            border-bottom: 1px solid #f9f9f9;
            font-size: 14px;
        }
        .list-box li:last-child {
            border-bottom: none;
        }
        .footer {
            background-color: #343a40;
            color: #ffffff;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Compartilhamento de Fichas</h2>
        </div>
        
        <div class="content">
            <p>Olá,</p>
            
            <p><strong>{{ $senderName }}</strong> compartilhou fichas de inscrição de crisma com você.</p>
            
            @if($userMessage)
            <div class="message-box">
                <p><strong>Mensagem:</strong></p>
                <p>{{ $userMessage }}</p>
            </div>
            @endif
            
            <div class="list-box">
                <h3>Inscritos Selecionados:</h3>
                <ul>
                    @foreach($inscritos as $nome)
                        <li>{{ $nome }}</li>
                    @endforeach
                </ul>
            </div>
            
            <p>O arquivo PDF com as fichas completas está anexado a este e-mail.</p>
        </div>
        
        <div class="footer">
            <p>SisMatriz é um serviço oferecido pela Sacratech Softwares.</p>
            <p>&copy; {{ date('Y') }} Sacratech Softwares LTDA. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
