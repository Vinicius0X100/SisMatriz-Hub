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
        $contentSid = config('services.twilio.content_sid_acolitos');

        if (!$sid || !$token || !$from) {
            Log::error('Twilio credentials not configured.', [
                'sid_configured' => !empty($sid),
                'token_configured' => !empty($token),
                'from_configured' => !empty($from)
            ]);
            return;
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
            // Ignora números inválidos ou nulos/zeros
            if (strlen($phone) !== 11 || (int)$phone === 0) {
                continue;
            }

            $to = 'whatsapp:+55' . $phone;

            try {
                // Se o Content SID estiver configurado, usa o template
                if ($contentSid) {
                    $twilio->messages->create($to, [
                        'from' => $from,
                        'contentSid' => $contentSid,
                        'contentVariables' => json_encode([
                            '1' => 'SisMatriz',
                            '2' => 'https://central.sismatriz.online'
                        ]),
                    ]);
                } else {
                    // Fallback para mensagem de texto simples caso o template não esteja configurado
                    // Mantém a mensagem detalhada apenas no fallback
                    $messageBody = "Olá {$acolito->name}, você foi escalado para: {$this->details['title']} \nData: {$this->details['date']} \nHorário: {$this->details['time']} \nLocal: {$this->details['local']}.";
                    
                    $twilio->messages->create($to, [
                        'from' => $from,
                        'body' => $messageBody,
                    ]);
                }

                Log::info("WhatsApp sent to {$acolito->name} ({$to})");
                
                // Delay of 5 seconds to avoid spam detection
                sleep(5);

            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp to {$acolito->name}: " . $e->getMessage());
            }
        }
    }
}
