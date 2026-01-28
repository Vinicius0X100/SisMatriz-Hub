<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lembrete;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SendReminderEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lembretes:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications for active reminders via Brevo API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        $this->info("Checking for reminders...");

        $lembretes = Lembrete::with('user')
            ->where('status', 'ativo')
            ->where('pref_email', true)
            ->where('data_hora', '<=', $now)
            ->get();

        $count = 0;

        foreach ($lembretes as $lembrete) {
            $shouldSend = false;

            if (!$lembrete->last_email_sent) {
                // Never sent, send now
                $shouldSend = true;
            } else {
                // Check repeat logic
                if ($lembrete->repeat == 'daily') {
                    if ($lembrete->last_email_sent->diffInHours($now) >= 24) {
                        $shouldSend = true;
                    }
                } elseif ($lembrete->repeat == 'weekly') {
                    if ($lembrete->last_email_sent->diffInWeeks($now) >= 1) {
                        $shouldSend = true;
                    }
                } elseif ($lembrete->repeat == 'monthly') {
                    if ($lembrete->last_email_sent->diffInMonths($now) >= 1) {
                        $shouldSend = true;
                    }
                }
            }

            if ($shouldSend) {
                try {
                    $apiKey = config('services.brevo.key');
                    
                    if (!$apiKey) {
                        $this->error("Brevo API Key not configured in services.brevo.key");
                        return;
                    }

                    $response = Http::withHeaders([
                        'api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->post('https://api.brevo.com/v3/smtp/email', [
                        'sender' => [
                            'name' => config('mail.from.name', 'SisMatriz'),
                            'email' => config('mail.from.address', 'noreply@sismatriz.com'),
                        ],
                        'to' => [
                            [
                                'email' => $lembrete->user->email,
                                'name' => $lembrete->user->name,
                            ]
                        ],
                        'subject' => 'Lembrete: ' . Str::limit($lembrete->descricao, 30),
                        'htmlContent' => view('emails.lembrete', ['lembrete' => $lembrete])->render(),
                    ]);

                    if ($response->successful()) {
                        $lembrete->last_email_sent = $now;
                        $lembrete->save();
                        
                        $this->info("Email sent via Brevo API for lembrete ID: {$lembrete->id} to {$lembrete->user->email}");
                        $count++;
                    } else {
                        $this->error("Failed Brevo API for lembrete ID: {$lembrete->id}. Status: {$response->status()} Body: {$response->body()}");
                    }
                } catch (\Exception $e) {
                    $this->error("Exception for lembrete ID: {$lembrete->id}. Error: " . $e->getMessage());
                }
            }
        }
        
        $this->info("Done. Sent {$count} emails.");
    }
}
