<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TestTwilio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:twilio {phone}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa o envio de WhatsApp via Twilio usando as configurações atuais';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->argument('phone');
        $this->info("Iniciando teste de envio para: {$phone}");

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        // $from = config('services.twilio.whatsapp_from');
        // if (!str_starts_with($from, 'whatsapp:')) {
        //     $from = 'whatsapp:' . $from;
        // }
        $messagingServiceSid = config('services.twilio.messaging_service_sid');
        $contentSid = config('services.twilio.content_sid_acolitos');

        $this->info("Configurações carregadas:");
        $this->line("SID: " . substr($sid, 0, 5) . "...");
        // $this->line("From: {$from}");
        $this->line("MessagingServiceSid: {$messagingServiceSid}");
        $this->line("ContentSid: {$contentSid}");

        // Limpeza e formatação do número (Lógica do Controller)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($cleanPhone) >= 10 && strlen($cleanPhone) <= 11) {
            $to = "whatsapp:+55" . $cleanPhone;
        } elseif (strlen($cleanPhone) > 11 && str_starts_with($cleanPhone, '55')) {
             $to = "whatsapp:+" . $cleanPhone;
        } else {
             $to = "whatsapp:+" . $cleanPhone;
        }
        
        $this->info("Número formatado para envio: {$to}");

        try {
            $twilio = new Client($sid, $token);
            
            $messageOptions = [
                // 'from' => $from, // Removido conforme solicitado
                'messagingServiceSid' => $messagingServiceSid,
                'contentSid' => $contentSid,
                'contentVariables' => json_encode([
                    "1" => "Teste de Sistema - " . date('d/m/Y H:i'),
                    "2" => "https://central.sismatriz.online"
                ])
            ];

            $this->info("Enviando requisição ao Twilio...");
            
            $message = $twilio->messages->create($to, $messageOptions);
            
            $this->info("SUCESSO! Mensagem enviada.");
            $this->info("SID da Mensagem: " . $message->sid);
            $this->info("Status: " . $message->status);
            
        } catch (\Exception $e) {
            $this->error("ERRO ao enviar mensagem:");
            $this->error($e->getMessage());
            $this->line("Trace:");
            $this->line($e->getTraceAsString());
        }
    }
}
