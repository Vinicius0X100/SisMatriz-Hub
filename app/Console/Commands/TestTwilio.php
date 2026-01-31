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
    protected $signature = 'test:twilio {phone : Phone number with DDD (e.g., 11999999999)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Twilio WhatsApp sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phoneInput = $this->argument('phone');
        $this->info("Starting Twilio test for phone: {$phoneInput}");

        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');
        
        // Hardcoded IDs from user request
        $messagingServiceSid = 'MG0c8f18b918fffa9283c956b7631f1230';
        $contentSid = 'HXf3131ce5e50a977bcfa2ad874628cec9';

        $this->info("Config:");
        $this->info("SID: " . substr($sid, 0, 5) . "...");
        $this->info("Token: " . substr($token, 0, 5) . "...");
        $this->info("From: {$from}");
        $this->info("MessagingServiceSid: {$messagingServiceSid}");
        $this->info("ContentSid: {$contentSid}");

        if (!$sid || !$token) {
            $this->error('Twilio credentials (SID/Token) not configured.');
            return;
        }

        // Ensure 'whatsapp:' prefix is present in 'from' if it exists
        if ($from && !str_starts_with($from, 'whatsapp:')) {
            $from = 'whatsapp:' . $from;
        }

        try {
            $twilio = new Client($sid, $token);
            $this->info('Twilio Client initialized successfully');
        } catch (\Exception $e) {
            $this->error('Twilio Client init failed: ' . $e->getMessage());
            return;
        }

        // Clean phone
        $phone = preg_replace('/\D/', '', $phoneInput);
        if (strlen($phone) !== 11) {
            $this->error("Invalid phone length: " . strlen($phone) . ". Expected 11 (DDD + 9 digits).");
            return;
        }

        $to = 'whatsapp:+55' . $phone;
        $this->info("Sending to: {$to}");

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

            $this->info("Calling Twilio API create...");
            
            $message = $twilio->messages->create($to, $messageOptions);
            
            $this->info("SUCCESS! Message sent. SID: " . $message->sid);
            $this->info("Status: " . $message->status);
            $this->info("Error Code: " . $message->errorCode);
            $this->info("Error Message: " . $message->errorMessage);

        } catch (\Exception $e) {
            $this->error("FAILED to send WhatsApp: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
        }
    }
}
