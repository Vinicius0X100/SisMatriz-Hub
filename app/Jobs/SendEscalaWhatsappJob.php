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
        Log::info('DEBUG: SendEscalaWhatsappJob started', ['acolitoIds' => $this->acolitoIds]);

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');
        
        // Messaging Service ID e Content SID fornecidos explicitamente
        $messagingServiceSid = 'MG0c8f18b918fffa9283c956b7631f1230';
        $contentSid = 'HXf3131ce5e50a977bcfa2ad874628cec9';

        Log::info('DEBUG: Twilio Config', [
            'sid_set' => !empty($sid),
            'token_set' => !empty($token),
            'from_set' => !empty($from),
            'messagingServiceSid' => $messagingServiceSid,
            'contentSid' => $contentSid
        ]);

        if (!$sid || !$token) {
            Log::error('DEBUG: Twilio credentials (SID/Token) not configured.');
            return;
        }

        // Ensure 'whatsapp:' prefix is present in 'from' if it exists
        if ($from && !str_starts_with($from, 'whatsapp:')) {
            $from = 'whatsapp:' . $from;
        }

        try {
            $twilio = new Client($sid, $token);
            Log::info('DEBUG: Twilio Client initialized successfully');
        } catch (\Exception $e) {
            Log::error('DEBUG: Twilio Client init failed: ' . $e->getMessage());
            return;
        }

        $acolitos = Acolito::whereIn('id', $this->acolitoIds)->with('register')->get();
        
        Log::info('DEBUG: Acolitos found', ['count' => $acolitos->count()]);

        if ($acolitos->isEmpty()) {
            Log::warning('DEBUG: No acolitos found for IDs provided.', ['ids' => $this->acolitoIds]);
            return;
        }

        foreach ($acolitos as $acolito) {
            Log::info("DEBUG: Processing acolito {$acolito->id} - {$acolito->name}");

            if (!$acolito->register || empty($acolito->register->phone)) {
                Log::warning("DEBUG: Acolito {$acolito->id} has no register or phone");
                continue;
            }

            // Remove non-numeric characters
            $phone = preg_replace('/\D/', '', $acolito->register->phone);

            Log::info("DEBUG: Phone raw: {$acolito->register->phone}, Cleaned: {$phone}");

            // Validação: 11 dígitos (DDD + 9 dígitos)
            if (strlen($phone) !== 11 || (int)$phone === 0) {
                Log::warning("DEBUG: Invalid phone length or value for acolito {$acolito->id}: {$phone}");
                continue;
            }

            $to = 'whatsapp:+55' . $phone;
            Log::info("DEBUG: Preparing to send to {$to}");

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

                Log::info("DEBUG: Calling Twilio API create for {$to}", ['options' => $messageOptions]);

                $message = $twilio->messages->create($to, $messageOptions);
                
                Log::info("DEBUG: Message sent successfully to {$to} using MessagingService {$messagingServiceSid}. SID: " . $message->sid);

            } catch (\Exception $e) {
                Log::error("DEBUG: Failed to send WhatsApp to {$to}: " . $e->getMessage());
                Log::error("DEBUG: Exception Trace: " . $e->getTraceAsString());
            }
        }
        
        Log::info('DEBUG: SendEscalaWhatsappJob finished');
    }
}
