<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class SendAtendimentoWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $telefone;
    protected string $nomeCompleto;
    protected string $data;
    protected string $hora;
    protected string $nomeParoquia;

    /**
     * @param string $telefone     Telefone do fiel (11 dígitos, sem formatação)
     * @param string $nomeCompleto Nome do fiel
     * @param string $data         Data formatada (ex: 10/07/2026)
     * @param string $hora         Hora formatada (ex: 10:30)
     * @param string $nomeParoquia Nome da paróquia
     */
    public function __construct(
        string $telefone,
        string $nomeCompleto,
        string $data,
        string $hora,
        string $nomeParoquia
    ) {
        $this->telefone     = $telefone;
        $this->nomeCompleto = $nomeCompleto;
        $this->data         = $data;
        $this->hora         = $hora;
        $this->nomeParoquia = $nomeParoquia;
    }

    /**
     * Envia notificação WhatsApp usando o template "atendimento_sismatriz_paroquial".
     * Template: Olá {{1}}, seu atendimento na {{4}} está confirmado!
     *           📅 Data: {{2}}
     *           🕐 Horário: {{3}}
     *           Compareça com alguns minutos de antecedência. Até logo! 🙏
     */
    public function handle(): void
    {
        $sid               = config('services.twilio.sid');
        $token             = config('services.twilio.token');
        $messagingService  = config('services.twilio.messaging_service_sid');
        $contentSid        = config('services.twilio.content_sid_atendimento');

        if (!$sid || !$token) {
            Log::error('SendAtendimentoWhatsappJob: Credenciais Twilio não configuradas.');
            return;
        }

        // Limpa o telefone e valida
        $phone = preg_replace('/\D/', '', $this->telefone);

        if (strlen($phone) !== 11 || (int)$phone === 0) {
            Log::warning("SendAtendimentoWhatsappJob: Telefone inválido: {$this->telefone}");
            return;
        }

        $to = 'whatsapp:+55' . $phone;

        try {
            $twilio = new Client($sid, $token);

            $from = config('services.twilio.whatsapp_from');

            $messageOptions = [
                'messagingServiceSid' => $messagingService,
                'contentSid'          => $contentSid,
                'contentVariables'    => json_encode([
                    '1' => $this->nomeCompleto,
                    '2' => $this->data,
                    '3' => $this->hora,
                    '4' => $this->nomeParoquia,
                ]),
            ];

            if ($from) {
                $messageOptions['from'] = $from;
            }

            $message = $twilio->messages->create($to, $messageOptions);

            Log::info("SendAtendimentoWhatsappJob: Mensagem enviada para {$to}. SID: {$message->sid}");

        } catch (\Exception $e) {
            Log::error("SendAtendimentoWhatsappJob: Falha ao enviar para {$to}: " . $e->getMessage());
        }
    }
}
