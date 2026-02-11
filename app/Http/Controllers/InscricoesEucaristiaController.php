<?php

namespace App\Http\Controllers;

use App\Models\InscricaoEucaristia;
use App\Models\PrazoInscricao;
use App\Models\InscricaoTaxaConfig;
use App\Models\InscricaoTaxaItem;
use App\Models\Register;
use App\Models\Batismo;
use App\Models\User;
use App\Mail\ShareInscricoesEucaristia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Twilio\Rest\Client;

use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;

class InscricoesEucaristiaController extends Controller
{
    public function index(Request $request)
    {
        $query = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

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

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('criado_em', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('criado_em', '<=', $request->date_to);
        }

        $records = $query->orderBy('criado_em', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('modules.inscricoes-eucaristia.partials.list', compact('records'))->render();
        }

        // Stats for cards
        $stats = [
            'total' => InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'pending' => InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 0)->count(),
            'approved' => InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 1)->count(),
            'rejected' => InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 2)->count(),
        ];

        $deadline = PrazoInscricao::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('tipo_inscricao', 'eucaristia')
            ->first();

        $taxConfig = InscricaoTaxaConfig::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('tipo', 'eucaristia')
            ->with('items')
            ->first();

        return view('modules.inscricoes-eucaristia.index', compact('records', 'stats', 'deadline', 'taxConfig'));
    }

    public function storeTaxConfig(Request $request)
    {
        $request->validate([
            'titulo' => 'nullable|string',
            'chave_pix' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.nome' => 'required_with:items|string',
            'items.*.valor' => 'required_with:items|string',
        ]);

        $taxConfig = InscricaoTaxaConfig::firstOrNew([
            'paroquia_id' => Auth::user()->paroquia_id,
            'tipo' => 'eucaristia'
        ]);

        $taxConfig->inscricao_com_taxa = $request->has('ativo') ? 1 : 0;
        $taxConfig->metodo_pagamento_label = $request->titulo;
        $taxConfig->metodo_pagamento_valor = $request->chave_pix;
        $taxConfig->save();

        // Handle Items
        $incomingItems = $request->input('items', []);
        $incomingIds = collect($incomingItems)->pluck('id')->filter()->toArray();

        // Delete items not in incoming list (if config exists)
        if ($taxConfig->exists) {
            $taxConfig->items()->whereNotIn('id', $incomingIds)->delete();
        }

        foreach ($incomingItems as $itemData) {
            // Format value: "R$ 1.234,56" -> 1234.56
            // Remove everything that is not a digit or a comma
            $valor = preg_replace('/[^\d,]/', '', $itemData['valor']);
            $valor = str_replace(',', '.', $valor);

            if (isset($itemData['id']) && $itemData['id']) {
                // Update
                $item = InscricaoTaxaItem::find($itemData['id']);
                if ($item && $item->config_id == $taxConfig->id) {
                    $item->update([
                        'nome' => $itemData['nome'],
                        'valor' => $valor,
                        'ativo' => 1
                    ]);
                }
            } else {
                // Create
                $taxConfig->items()->create([
                    'nome' => $itemData['nome'],
                    'valor' => $valor,
                    'ativo' => 1
                ]);
            }
        }

        return redirect()->back()->with('success', 'Configuração de taxas atualizada com sucesso!');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:inscricoes_eucaristia,id'
        ]);

        $count = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json(['success' => true, 'message' => "{$count} inscrições excluídas com sucesso."]);
    }

    public function bulkPrint(Request $request)
    {
        $query = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

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
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('criado_em', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('criado_em', '<=', $request->date_to);
            }
            
            $records = $query->orderBy('nome', 'asc')->get();

        } elseif ($request->scope == 'selected') {
            $ids = json_decode($request->ids);
            if (empty($ids)) {
                return redirect()->back()->with('warning', 'Nenhum registro selecionado.');
            }
            $records = $query->whereIn('id', $ids)->orderBy('nome', 'asc')->get();
        } else {
            return redirect()->back()->with('warning', 'Opção de impressão inválida.');
        }

        if ($records->isEmpty()) {
            return redirect()->back()->with('warning', 'Nenhum registro encontrado para impressão.');
        }

        return $this->generatePdfForRecords($records);
    }

    public function printSingle($id)
    {
        $record = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('taxa')
            ->findOrFail($id);
            
        return $this->generatePdfForRecords(collect([$record]), true);
    }

    public function searchUsers(Request $request)
    {
        $search = $request->input('q');
        $users = User::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('status', 0) // Only active users (0 = active)
            ->where('name', 'like', "%{$search}%")
            ->select('id', 'name', 'email', 'avatar')
            ->limit(10)
            ->get();
            
        $users->transform(function ($user) {
            if ($user->avatar) {
                $user->avatar = asset("storage/uploads/avatars/{$user->id}.png");
            }
            return $user;
        });

        return response()->json($users);
    }

    public function share(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'ids' => 'required', // can be array or json string depending on how sent
        ]);

        $ids = is_string($request->ids) ? json_decode($request->ids) : $request->ids;
        
        if (empty($ids)) {
             return response()->json(['success' => false, 'message' => 'Nenhum registro selecionado.']);
        }

        $records = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $ids)
            ->orderBy('nome', 'asc')
            ->get();

        if ($records->isEmpty()) {
             return response()->json(['success' => false, 'message' => 'Nenhum registro encontrado.']);
        }

        // Generate PDF
        $pdfData = $this->generatePdfForRecords($records, false, true); // true for raw return
        
        $inscritosNames = $records->pluck('nome')->toArray();
        $senderName = Auth::user()->name;
        $userMessage = $request->input('message');
        
        $filename = 'fichas-eucaristia-compartilhadas-' . date('d-m-Y') . '.pdf';

        // Send Email
        $users = User::whereIn('id', $request->users)->get();
        foreach ($users as $user) {
             Mail::to($user->email)->send(new ShareInscricoesEucaristia(
                 $senderName,
                 $userMessage,
                 $inscritosNames,
                 $pdfData,
                 $filename
             ));
        }

        return response()->json(['success' => true, 'message' => 'Fichas compartilhadas com sucesso!']);
    }

    private function generatePdfForRecords($records, $isSingle = false, $returnRaw = false)
    {
        // Initialize FPDI
        $pdf = new Fpdi();
        // FPDF does not have setPrintHeader/Footer methods by default, so we remove them
        
        foreach ($records as $record) {
            // Generate Ficha PDF using DomPDF
            // We pass a collection of ONE record to the view because the view expects a loop
            // We reuse or create 'modules.inscricoes-eucaristia.print'
            $fichaPdf = Pdf::loadView('modules.inscricoes-eucaristia.print', ['records' => [$record]])->output();
            
            // Save to temp file
            $tempFicha = tempnam(sys_get_temp_dir(), 'ficha_');
            file_put_contents($tempFicha, $fichaPdf);
            
            // Import Ficha Pages
            try {
                $pageCount = $pdf->setSourceFile($tempFicha);
                for ($i = 1; $i <= $pageCount; $i++) {
                    $tplIdx = $pdf->importPage($i);
                    $pdf->AddPage();
                    $pdf->useTemplate($tplIdx, ['adjustPageSize' => true]);
                }
            } catch (\Exception $e) {
                Log::error("Error processing ficha PDF: " . $e->getMessage());
            }
            
            // Cleanup temp file
            if (file_exists($tempFicha)) {
                unlink($tempFicha);
            }
            
            // Append PDF Attachments
            $attachments = [
                $record->certidao_batismo
            ];
            
            foreach ($attachments as $att) {
                if ($att) {
                    // Use storage_path directly to avoid symlink issues
                    $fullPath = storage_path('app/public/uploads/certidoes/' . $att);
                    
                    Log::info("Processing attachment: {$att}");
                    Log::info("Full path: {$fullPath}");
                    Log::info("File exists: " . (file_exists($fullPath) ? 'Yes' : 'No'));
                    
                    if (file_exists($fullPath)) {
                        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                        Log::info("Extension: {$ext}");
                        if ($ext === 'pdf') {
                             try {
                                $pageCount = $pdf->setSourceFile($fullPath);
                                for ($i = 1; $i <= $pageCount; $i++) {
                                    $tplIdx = $pdf->importPage($i);
                                    $pdf->AddPage();
                                    $pdf->useTemplate($tplIdx, ['adjustPageSize' => true]);
                                }
                             } catch (\Exception $e) {
                                 Log::error("Error merging PDF attachment {$att}: " . $e->getMessage());
                                 // Continue without this attachment
                             }
                        } 
                        // Image attachments (jpg, png, etc) are already rendered in the Blade view
                        // so we don't need to append them here to avoid duplication.
                    }
                }
            }
        }

        // Determine filename
        if ($isSingle) {
            $name = Str::slug($records->first()->nome, '-');
            $filename = "ficha-eucaristia-{$name}.pdf";
        } else {
            $date = date('d-m-Y');
            $filename = "fichas-eucaristia-lote-{$date}.pdf";
        }
        
        // Output
        $output = $pdf->Output('S');
        
        if ($returnRaw) {
            return $output;
        }

        return response($output)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function export(Request $request)
    {
        $query = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

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
        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('criado_em', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('criado_em', '<=', $request->date_to);
        }

        if ($request->has('ids') && !empty($request->ids)) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        $records = $query->orderBy('nome', 'asc')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=inscricoes_eucaristia_" . date('d-m-Y_H-i') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'ID', 'Status', 'Nome', 'CPF', 'Data Nascimento', 'Sexo', 
            'Telefone 1', 'Telefone 2', 'Endereço', 'Número', 'CEP', 'Cidade', 'Estado',
            'Certidão Batismo', 'Comprovante Pagamento', 'Data Inscrição'
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
                    $record->cidade ?? 'Guarapuava',
                    $record->estado,
                    $record->certidao_batismo ? asset('storage/uploads/certidoes/' . $record->certidao_batismo) : '',
                    $record->comprovante_pagamento ? asset('storage/uploads/comprovantes/eucaristia/' . $record->comprovante_pagamento) : '',
                    $dtCriado
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        $record = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('taxa')
            ->findOrFail($id);
        return response()->json($record);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2',
        ]);

        $record = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
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

                    $newRegister = Register::create([
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

                    // Sincroniza com Batismos
                    // Se tem certidão de batismo, consideramos batizado
                    $isBatizado = !empty($record->certidao_batismo);
                    Batismo::syncFromTurma($newRegister->id, $isBatizado);

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
                Log::error('Twilio WhatsApp Error: ' . $e->getMessage());
                $messages[] = 'Erro ao enviar WhatsApp.';
                $messageType = 'warning';
            }
        }

        $record->update(['status' => $request->status]);

        return redirect()->back()->with($messageType, implode(' ', $messages));
    }

    public function destroy($id)
    {
        $record = InscricaoEucaristia::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $record->delete();

        return redirect()->back()->with('success', 'Inscrição excluída com sucesso!');
    }

    public function storeDeadline(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'ativo' => 'required|boolean',
        ]);

        $deadline = PrazoInscricao::firstOrNew([
            'paroquia_id' => Auth::user()->paroquia_id,
            'tipo_inscricao' => 'eucaristia'
        ]);

        $deadline->data_inicio = $request->data_inicio;
        $deadline->data_fim = $request->data_fim;
        $deadline->ativo = $request->ativo;
        
        if (!$deadline->exists) {
            $deadline->criado_por = Auth::id();
        }

        $deadline->save();

        return redirect()->back()->with('success', 'Prazo de inscrição atualizado com sucesso!');
    }
}
