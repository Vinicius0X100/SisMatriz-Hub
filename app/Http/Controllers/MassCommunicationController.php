<?php

namespace App\Http\Controllers;

use App\Models\Register;
use App\Models\MassCommunication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class MassCommunicationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Register::where('paroquia_id', $user->paroquia_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $registers = $query->orderBy('name')->paginate(15);
        
        if ($request->ajax()) {
            return view('modules.mass-communication.registers-table', compact('registers'));
        }

        $history = MassCommunication::where('paroquia_id', $user->paroquia_id)
            ->with('recipient')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('modules.mass-communication.index', compact('registers', 'history'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*' => 'exists:registers,id',
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        $messageBody = $request->message;
        $recipients = Register::whereIn('id', $request->recipients)->get();
        
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $messagingServiceSid = config('services.twilio.messaging_service_sid');
        $from = config('services.twilio.whatsapp_from');
        
        $successCount = 0;
        $failCount = 0;

        if (!$sid || !$token || !$messagingServiceSid) {
            return back()->with('error', 'Twilio não está configurado corretamente.');
        }

        $twilio = new Client($sid, $token);

        foreach ($recipients as $recipient) {
            try {
                $phone = $recipient->phone;
                // Basic phone sanitization
                $phone = preg_replace('/[^0-9]/', '', $phone);
                
                if (empty($phone)) {
                    $failCount++;
                    continue;
                }

                // Add country code if missing (assuming BR +55)
                if (strlen($phone) >= 10 && strlen($phone) <= 11) {
                    $phone = '55' . $phone;
                }
                
                $to = 'whatsapp:+' . $phone;
                
                // Template: HXd45e8dad964e205eac8c0d89fab4432e
                // Variables: 1: Recipient Name, 2: Sender Name, 3: Message
                $message = $twilio->messages->create($to, [
                    'from' => $from,
                    'messagingServiceSid' => $messagingServiceSid,
                    'contentSid' => 'HXd45e8dad964e205eac8c0d89fab4432e',
                    'contentVariables' => json_encode([
                        "1" => $recipient->name,
                        "2" => $user->name,
                        "3" => $messageBody
                    ])
                ]);

                MassCommunication::create([
                    'sender_id' => $user->id,
                    'recipient_id' => $recipient->id,
                    'message_body' => $messageBody,
                    'status' => 'sent',
                    'sid' => $message->sid,
                    'paroquia_id' => $user->paroquia_id,
                ]);

                $successCount++;

            } catch (\Exception $e) {
                Log::error('Mass Communication Error: ' . $e->getMessage());
                
                MassCommunication::create([
                    'sender_id' => $user->id,
                    'recipient_id' => $recipient->id,
                    'message_body' => $messageBody,
                    'status' => 'failed',
                    'sid' => null,
                    'paroquia_id' => $user->paroquia_id,
                ]);

                $failCount++;
            }
        }

        if ($successCount == 0 && $failCount > 0) {
            return back()->with('error', 'Falha ao enviar mensagens. Verifique os logs.');
        }

        return back()->with('success', "Mensagens enviadas: {$successCount}. Falhas: {$failCount}.");
    }
}
