<?php

namespace App\Http\Controllers;

use App\Models\InscricaoCrisma;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Twilio\Rest\Client;

class InscricoesCrismaController extends Controller
{
    public function index(Request $request)
    {
        $query = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%");
        }

        $records = $query->orderBy('criado_em', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('modules.inscricoes-crisma.partials.list', compact('records'))->render();
        }

        return view('modules.inscricoes-crisma.index', compact('records'));
    }

    public function show($id)
    {
        $record = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('taxa')
            ->findOrFail($id);
        return response()->json($record);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2',
        ]);

        $record = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $messages = ['Situação atualizada com sucesso!'];
        $messageType = 'success';
        
        // If status changing to Approved (1)
        if ($request->status == 1 && $record->status != 1) {
            // Check if Register already exists by CPF
            $exists = Register::where('cpf', $record->cpf)->exists();
            
            if (!$exists) {
                try {
                    // Phone Logic: Try phone1, if exists try phone2
                    $phone = $record->telefone1;
                    if (Register::where('phone', $phone)->exists()) {
                        $phone = $record->telefone2;
                    }

                    // Sex Mapping
                    $sexo = 3; // Default
                    if (stripos($record->sexo, 'masc') !== false) $sexo = 1;
                    if (stripos($record->sexo, 'fem') !== false) $sexo = 2;

                    Register::create([
                        'name' => $record->nome,
                        'phone' => $phone,
                        'address' => $record->endereco,
                        'address_number' => $record->numero,
                        'cpf' => $record->cpf,
                        'sexo' => $sexo,
                        'born_date' => $record->data_nascimento,
                        'age' => $record->data_nascimento ? Carbon::parse($record->data_nascimento)->age : null,
                        'country' => 'Brasil',
                        'state' => $record->estado,
                        'cep' => $record->cep,
                        'civil_status' => 1, // Default Solteiro
                        'paroquia_id' => Auth::user()->paroquia_id,
                    ]);
                    $messages[] = 'Registro Geral criado com sucesso.';
                } catch (\Exception $e) {
                    $messages[] = 'Erro ao criar Registro Geral: ' . $e->getMessage();
                    $messageType = 'warning';
                }
            } else {
                $messages[] = 'Inscrito já possui Registro Geral.';
            }
        }

        // If status changing to Rejected (2)
        if ($request->status == 2 && $record->status != 2) {
            try {
                $sid = config('services.twilio.sid');
                $token = config('services.twilio.token');
                $messagingServiceSid = config('services.twilio.messaging_service_sid');
                $from = config('services.twilio.whatsapp_from');
                
                if ($sid && $token && $messagingServiceSid) {
                    $twilio = new Client($sid, $token);
                    
                    $phone = $record->telefone1 ?? $record->telefone2;
                    // Remove non-numeric characters
                    $phone = preg_replace('/[^0-9]/', '', $phone);
                    
                    if (!empty($phone)) {
                        // Add country code if missing (assuming BR +55)
                        // Common logic: if length is 10 or 11 (DD+Num), add 55.
                        if (strlen($phone) >= 10 && strlen($phone) <= 11) {
                            $phone = '55' . $phone;
                        }
                        
                        $to = 'whatsapp:+' . $phone;
                        
                        $twilio->messages->create($to, [
                            'from' => $from,
                            'messagingServiceSid' => $messagingServiceSid,
                            'contentSid' => 'HX93133072f99753b94b6781427c6c7a30',
                            'contentVariables' => json_encode([
                                "1" => $record->nome,
                                "2" => "REPROVADA"
                            ])
                        ]);
                        $messages[] = 'Notificação WhatsApp enviada.';
                    } else {
                         $messages[] = 'Telefone inválido para envio de WhatsApp.';
                         $messageType = 'warning';
                    }
                } else {
                    $messages[] = 'Credenciais Twilio não configuradas.';
                    $messageType = 'warning';
                }
            } catch (\Exception $e) {
                \Log::error('Twilio WhatsApp Error: ' . $e->getMessage());
                $messages[] = 'Erro ao enviar WhatsApp.';
                $messageType = 'warning';
            }
        }

        $record->update(['status' => $request->status]);

        return redirect()->back()->with($messageType, implode(' ', $messages));
    }

    public function destroy($id)
    {
        $record = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        // We might want to delete files too, but user didn't explicitly ask. 
        // I'll just delete the record for now to be safe, or check if I should delete files.
        // User said "uploads/certidoes/crisma" and "/comprovantes/crisma/".
        // Usually safe to keep files or soft delete, but strict delete removes them.
        // I will just delete the record.
        $record->delete();

        return redirect()->back()->with('success', 'Inscrição excluída com sucesso!');
    }
}
