<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lembrete</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #0d6efd;">Lembrete SisMatriz</h2>
        
        <p>Olá, {{ $lembrete->user->name }}!</p>
        
        <p>Você tem um lembrete agendado:</p>
        
        <div style="background-color: #f8f9fa; border-left: 4px solid #0d6efd; padding: 15px; margin: 20px 0;">
            <p style="margin: 0; font-size: 18px; font-weight: bold;">{{ $lembrete->descricao }}</p>
            <p style="margin: 5px 0 0; color: #6c757d;">{{ $lembrete->data_hora->format('d/m/Y H:i') }}</p>
        </div>
        
        <p>Para gerenciar seus lembretes, acesse o sistema.</p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #999;">
            Este e-mail foi enviado automaticamente pelo SisMatriz Hub via Brevo API.
        </p>
    </div>
</body>
</html>
