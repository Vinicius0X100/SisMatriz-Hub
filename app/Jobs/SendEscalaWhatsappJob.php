<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twilio\Rest\Client;
use App\Models\Acolito;
use Illuminate\Support\Facades\Log;

class SendEscalaWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $acolitoIds;
    protected $details;

    /**
     * Create a new job instance.
     *
     * @param array $acolitoIds
     * @param array $details
     */
    public function __construct(array $acolitoIds, array $details)
    {
        $this->acolitoIds = $acolitoIds;
        $this->details = $details;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('SendEscalaWhatsappJob started', ['acolitoIds' => $this->acolitoIds]);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');
        
        // Messaging Service ID e Content SID fornecidos explicitamente
        $messagingServiceSid = 'MG0c8f18b918fffa9283c956b7631f1230';
        $contentSid = 'HXf3131ce5e50a977bcfa2ad874628cec9';

        if (!$sid || !$token) {
            Log::error('Twilio credentials (SID/Token) not configured.');
            return;
        }

        // Ensure 'whatsapp:' prefix is present in 'from' if it exists
        if ($from && !str_starts_with($from, 'whatsapp:')) {
            $from = 'whatsapp:' . $from;
        }

        try {
            $twilio = new Client($sid, $token);
        } catch (\Exception $e) {
            Log::error('Twilio Client init failed: ' . $e->getMessage());
            return;
        }

        $acolitos = Acolito::whereIn('id', $this->acolitoIds)->with('register')->get();
        
        if ($acolitos->isEmpty()) {
            Log::warning('No acolitos found for IDs provided.', ['ids' => $this->acolitoIds]);
            return;
        }

        foreach ($acolitos as $acolito) {
            if (!$acolito->register || empty($acolito->register->phone)) {
                continue;
            }

            // Remove non-numeric characters
            $phone = preg_replace('/\D/', '', $acolito->register->phone);

            // Validação: 11 dígitos (DDD + 9 dígitos)
            if (strlen($phone) !== 11 || (int)$phone === 0) {
                continue;
            }

            $to = 'whatsapp:+55' . $phone;

            try {
                $messageOptions = [
                    'messagingServiceSid' => $messagingServiceSid,
                    'contentSid' => $contentSid,
                    'contentVariables' => json_encode([
                        "1" => "SisMatriz para Android",
                        "2" => "https://central.sismatriz.online"
                    ])
                ];

                // Se houver 'from' configurado, adiciona (embora MessagingServiceSid geralmente substitua)
                if ($from) {
                    $messageOptions['from'] = $from;
                }

                $twilio->messages->create($to, $messageOptions);
                
                Log::info("Message sent to {$to} using MessagingService {$messagingServiceSid}");

            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp to {$to}: " . $e->getMessage());
            }
        }
    }
}
