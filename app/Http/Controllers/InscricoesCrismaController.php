<?php

namespace App\Http\Controllers;

use App\Models\InscricaoCrisma;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Twilio\Rest\Client;

use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class InscricoesCrismaController extends Controller
{
    public function index(Request $request)
    {
        $query = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nome', 'like', "%{$search}%");
        }

        // Filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('batismo') && $request->batismo !== '') {
            if ($request->batismo == '1') {
                $query->whereNotNull('certidao_batismo')->where('certidao_batismo', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('certidao_batismo')->orWhere('certidao_batismo', '');
                });
            }
        }

        if ($request->has('eucaristia') && $request->eucaristia !== '') {
            if ($request->eucaristia == '1') {
                $query->whereNotNull('certidao_primeira_comunhao')->where('certidao_primeira_comunhao', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('certidao_primeira_comunhao')->orWhere('certidao_primeira_comunhao', '');
                });
            }
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('criado_em', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('criado_em', '<=', $request->date_to);
        }

        $records = $query->orderBy('criado_em', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('modules.inscricoes-crisma.partials.list', compact('records'))->render();
        }

        // Stats for cards
        $stats = [
            'total' => InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'pending' => InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 0)->count(),
            'approved' => InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 1)->count(),
            'rejected' => InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 2)->count(),
        ];

        return view('modules.inscricoes-crisma.index', compact('records', 'stats'));
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:inscricoes_crisma,id'
        ]);

        $count = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json(['success' => true, 'message' => "{$count} inscrições excluídas com sucesso."]);
    }

    public function bulkPrint(Request $request)
    {
        $query = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

        if ($request->scope == 'all') {
            if ($request->has('search') && $request->search != '') {
                $query->where('nome', 'like', "%{$request->search}%");
            }
            if ($request->has('status') && $request->status !== '') {
                $query->where('status', $request->status);
            }
            if ($request->has('batismo') && $request->batismo !== '') {
                if ($request->batismo == '1') {
                    $query->whereNotNull('certidao_batismo')->where('certidao_batismo', '!=', '');
                } else {
                    $query->where(function($q) {
                        $q->whereNull('certidao_batismo')->orWhere('certidao_batismo', '');
                    });
                }
            }
            if ($request->has('eucaristia') && $request->eucaristia !== '') {
                if ($request->eucaristia == '1') {
                    $query->whereNotNull('certidao_primeira_comunhao')->where('certidao_primeira_comunhao', '!=', '');
                } else {
                    $query->where(function($q) {
                        $q->whereNull('certidao_primeira_comunhao')->orWhere('certidao_primeira_comunhao', '');
                    });
                }
            }
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('criado_em', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('criado_em', '<=', $request->date_to);
            }
        } else {
            // Scope: selected
            $ids = json_decode($request->ids, true);
            // Fallback for comma separated if logic changes or legacy
            if (!is_array($ids)) {
                 $ids = explode(',', $request->ids);
            }
            
            if (!$ids || empty($ids)) {
                return redirect()->back()->with('warning', 'Nenhum registro selecionado para impressão.');
            }
            $query->whereIn('id', $ids);
        }

        $records = $query->orderBy('nome', 'asc')->get();

        if ($records->isEmpty()) {
            return redirect()->back()->with('warning', 'Nenhum registro encontrado para impressão.');
        }

        // Determine filename
        if ($records->count() === 1) {
            $name = \Illuminate\Support\Str::slug($records->first()->nome, '-');
            $filename = "ficha-crisma-{$name}.pdf";
        } else {
            $date = date('d-m-Y');
            $filename = "fichas-crisma-lote-{$date}.pdf";
        }

        $pdf = Pdf::loadView('modules.inscricoes-crisma.print', compact('records'));
        return $pdf->download($filename);
    }

    public function export(Request $request)
    {
        $query = InscricaoCrisma::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

        // Apply filters
        if ($request->has('search') && $request->search != '') {
            $query->where('nome', 'like', "%{$request->search}%");
        }
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->has('batismo') && $request->batismo !== '') {
            if ($request->batismo == '1') {
                $query->whereNotNull('certidao_batismo')->where('certidao_batismo', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('certidao_batismo')->orWhere('certidao_batismo', '');
                });
            }
        }
        if ($request->has('eucaristia') && $request->eucaristia !== '') {
            if ($request->eucaristia == '1') {
                $query->whereNotNull('certidao_primeira_comunhao')->where('certidao_primeira_comunhao', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('certidao_primeira_comunhao')->orWhere('certidao_primeira_comunhao', '');
                });
            }
        }
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('criado_em', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('criado_em', '<=', $request->date_to);
        }

        // Handle specific IDs if passed (optional, for future proofing or if called via mass action)
        if ($request->has('ids') && !empty($request->ids)) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        $records = $query->orderBy('nome', 'asc')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=inscricoes_crisma_" . date('d-m-Y_H-i') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'ID', 'Status', 'Nome', 'CPF', 'Data Nascimento', 'Sexo', 
            'Telefone 1', 'Telefone 2', 'Endereço', 'Número', 'CEP', 'Cidade', 'Estado',
            'Certidão Batismo', 'Certidão Eucaristia', 'Comprovante Pagamento', 'Data Inscrição'
        ];

        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM for Excel

            fputcsv($file, $columns, ';');

            foreach ($records as $record) {
                $status = match($record->status) {
                    0 => 'Pendente',
                    1 => 'Aprovado',
                    2 => 'Reprovado',
                    default => 'Desconhecido'
                };
                
                $dtNasc = $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '';
                $dtCriado = $record->criado_em ? \Carbon\Carbon::parse($record->criado_em)->format('d/m/Y H:i') : '';

                fputcsv($file, [
                    $record->id,
                    $status,
                    $record->nome,
                    $record->cpf,
                    $dtNasc,
                    $record->sexo,
                    $record->telefone1,
                    $record->telefone2,
                    $record->endereco,
                    $record->numero,
                    $record->cep,
                    'Guarapuava',
                    $record->estado,
                    $record->certidao_batismo ? asset($record->certidao_batismo) : '',
                    $record->certidao_primeira_comunhao ? asset($record->certidao_primeira_comunhao) : '',
                    $record->comprovante_pagamento ? asset($record->comprovante_pagamento) : '',
                    $dtCriado
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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
