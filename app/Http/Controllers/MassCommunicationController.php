<?php

namespace App\Http\Controllers;

use App\Models\Catecando;
use App\Models\CatecandoAdultos;
use App\Models\Crismando;
use App\Models\Register;
use App\Models\MassCommunication;
use App\Models\TurmaAdultos;
use App\Models\TurmaCrisma;
use App\Models\TurmaEucaristia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class MassCommunicationController extends Controller
{
    private function sanitizeTwilioVariable(?string $value, string $fallback = ''): string
    {
        $value = (string) $value;
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = str_replace("\t", '    ', $value);
        $value = trim($value);
        if ($value === '') {
            $value = $fallback;
        }

        $value = str_replace(['{{', '}}'], ['{', '}'], $value);

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? $value;

        return $value;
    }

    private function canUseTurmasGroup(string $tipo): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if ($user->hasAnyRole(['1', '111'])) return true;

        return match ($tipo) {
            'eucaristia' => $user->hasAnyRole(['7', '12']),
            'crisma' => $user->hasAnyRole(['3', '13']),
            'adultos' => $user->hasAnyRole(['17']),
            default => false,
        };
    }

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

        $groupCapabilities = [
            'eucaristia' => $this->canUseTurmasGroup('eucaristia'),
            'crisma' => $this->canUseTurmasGroup('crisma'),
            'adultos' => $this->canUseTurmasGroup('adultos'),
        ];

        return view('modules.mass-communication.index', compact('registers', 'history', 'groupCapabilities'));
    }

    public function turmas(Request $request)
    {
        $request->validate([
            'tipo' => 'required|string|in:eucaristia,crisma,adultos',
        ]);

        $tipo = $request->input('tipo');
        if (!$this->canUseTurmasGroup($tipo)) {
            abort(403);
        }

        $paroquiaId = Auth::user()->paroquia_id;

        $turmas = match ($tipo) {
            'eucaristia' => TurmaEucaristia::where('paroquia_id', $paroquiaId)->orderBy('turma')->get(['id', 'turma']),
            'crisma' => TurmaCrisma::where('paroquia_id', $paroquiaId)->orderBy('turma')->get(['id', 'turma']),
            'adultos' => TurmaAdultos::where('paroquia_id', $paroquiaId)->orderBy('turma')->get(['id', 'turma']),
            default => collect(),
        };

        return response()->json([
            'tipo' => $tipo,
            'turmas' => $turmas->map(fn ($t) => ['id' => $t->id, 'nome' => $t->turma])->values(),
        ]);
    }

    public function turmaRecipients(Request $request, string $tipo, int $turmaId)
    {
        if (!in_array($tipo, ['eucaristia', 'crisma', 'adultos'], true)) {
            abort(404);
        }
        if (!$this->canUseTurmasGroup($tipo)) {
            abort(403);
        }

        $paroquiaId = Auth::user()->paroquia_id;

        $recipients = match ($tipo) {
            'eucaristia' => Catecando::where('turma_id', $turmaId)
                ->whereHas('register', fn ($q) => $q->where('paroquia_id', $paroquiaId))
                ->with('register:id,name,phone')
                ->get()
                ->map(fn ($c) => $c->register)
                ->filter(),
            'crisma' => Crismando::where('turma_id', $turmaId)
                ->whereHas('register', fn ($q) => $q->where('paroquia_id', $paroquiaId))
                ->with('register:id,name,phone')
                ->get()
                ->map(fn ($c) => $c->register)
                ->filter(),
            'adultos' => CatecandoAdultos::where('turma_id', $turmaId)
                ->whereHas('register', fn ($q) => $q->where('paroquia_id', $paroquiaId))
                ->with('register:id,name,phone')
                ->get()
                ->map(fn ($c) => $c->register)
                ->filter(),
            default => collect(),
        };

        $turmaNome = match ($tipo) {
            'eucaristia' => TurmaEucaristia::where('paroquia_id', $paroquiaId)->find($turmaId)?->turma,
            'crisma' => TurmaCrisma::where('paroquia_id', $paroquiaId)->find($turmaId)?->turma,
            'adultos' => TurmaAdultos::where('paroquia_id', $paroquiaId)->find($turmaId)?->turma,
            default => null,
        };

        if (!$turmaNome) abort(404);

        return response()->json([
            'tipo' => $tipo,
            'turma' => ['id' => $turmaId, 'nome' => $turmaNome],
            'recipients' => $recipients
                ->unique('id')
                ->values()
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'phone' => $r->phone]),
        ]);
    }

    public function allTurmasRecipients(Request $request)
    {
        if (
            !$this->canUseTurmasGroup('eucaristia') ||
            !$this->canUseTurmasGroup('crisma') ||
            !$this->canUseTurmasGroup('adultos')
        ) {
            abort(403);
        }

        $paroquiaId = Auth::user()->paroquia_id;

        $turmasEucaristia = TurmaEucaristia::where('paroquia_id', $paroquiaId)->orderBy('turma')->get(['id', 'turma']);
        $turmasCrisma = TurmaCrisma::where('paroquia_id', $paroquiaId)->orderBy('turma')->get(['id', 'turma']);
        $turmasAdultos = TurmaAdultos::where('paroquia_id', $paroquiaId)->orderBy('turma')->get(['id', 'turma']);

        $idsEucaristia = Catecando::whereIn('turma_id', $turmasEucaristia->pluck('id'))
            ->pluck('register_id')
            ->filter()
            ->unique();
        $idsCrisma = Crismando::whereIn('turma_id', $turmasCrisma->pluck('id'))
            ->pluck('register_id')
            ->filter()
            ->unique();
        $idsAdultos = CatecandoAdultos::whereIn('turma_id', $turmasAdultos->pluck('id'))
            ->pluck('register_id')
            ->filter()
            ->unique();

        $allRegisterIds = $idsEucaristia
            ->merge($idsCrisma)
            ->merge($idsAdultos)
            ->unique()
            ->values();

        $recipients = Register::where('paroquia_id', $paroquiaId)
            ->whereIn('id', $allRegisterIds)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        return response()->json([
            'turmas' => [
                'eucaristia' => $turmasEucaristia->map(fn ($t) => ['id' => $t->id, 'nome' => $t->turma])->values(),
                'crisma' => $turmasCrisma->map(fn ($t) => ['id' => $t->id, 'nome' => $t->turma])->values(),
                'adultos' => $turmasAdultos->map(fn ($t) => ['id' => $t->id, 'nome' => $t->turma])->values(),
            ],
            'counts' => [
                'turmas' => [
                    'eucaristia' => $turmasEucaristia->count(),
                    'crisma' => $turmasCrisma->count(),
                    'adultos' => $turmasAdultos->count(),
                ],
                'recipients' => $recipients->count(),
            ],
            'recipients' => $recipients->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'phone' => $r->phone])->values(),
        ]);
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
                $contentVariables = [
                    "1" => $this->sanitizeTwilioVariable($recipient->name, 'Paroquiano(a)'),
                    "2" => $this->sanitizeTwilioVariable($user->name, 'Secretaria Paroquial'),
                    "3" => $this->sanitizeTwilioVariable($messageBody, 'Mensagem da paróquia'),
                ];

                $message = $twilio->messages->create($to, [
                    'from' => $from,
                    'messagingServiceSid' => $messagingServiceSid,
                    'contentSid' => 'HXd45e8dad964e205eac8c0d89fab4432e',
                    'contentVariables' => json_encode($contentVariables, JSON_UNESCAPED_UNICODE),
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
                Log::error('Mass Communication Error: ' . $e->getMessage(), [
                    'recipient_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'recipient_phone' => $recipient->phone,
                    'sender_id' => $user->id,
                    'content_sid' => 'HXd45e8dad964e205eac8c0d89fab4432e',
                ]);
                
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
