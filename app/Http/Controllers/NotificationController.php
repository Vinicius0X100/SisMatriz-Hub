<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Lembrete;
use App\Models\ProcessoNotificacao;
use App\Models\ProtocolStatusNotification;

class NotificationController extends Controller
{
    /**
     * Marca todas as notificações do usuário atual como lidas.
     * Acionado quando o dropdown de notificações é aberto.
     */
    public function markAllAsRead(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false], 401);
        }

        // 1. Mensagens
        Message::where('receiver_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 2. Lembretes (Eventos/Avisos passados ou atuais)
        Lembrete::where('usuario_id', $userId)
            ->where('status', 'ativo')
            ->where('data_hora', '<=', now())
            ->update(['status' => 'concluido']);

        // 3. Notificações de Processos
        ProcessoNotificacao::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // 4. Notificações de Protocolos
        ProtocolStatusNotification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
