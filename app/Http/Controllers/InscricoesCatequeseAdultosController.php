<?php

namespace App\Http\Controllers;

use App\Models\InscricaoCatequeseAdultos;
use App\Models\PrazoInscricao;
use App\Models\InscricaoTaxaConfig;
use App\Models\InscricaoTaxaItem;
use App\Models\Register;
use App\Models\User;
use App\Mail\ShareInscricoesCatequeseAdultos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;

class InscricoesCatequeseAdultosController extends Controller
{
    public function index(Request $request)
    {
        $query = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

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

        if ($request->has('matrimonio') && $request->matrimonio !== '') {
            if ($request->matrimonio == '1') {
                $query->whereNotNull('certidao_matrimonio')->where('certidao_matrimonio', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('certidao_matrimonio')->orWhere('certidao_matrimonio', '');
                });
            }
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('data_inscricao', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('data_inscricao', '<=', $request->date_to);
        }

        $records = $query->orderBy('data_inscricao', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('modules.inscricoes-catequese-adultos.partials.list', compact('records'))->render();
        }

        // Stats for cards
        $stats = [
            'total' => InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'pending' => InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 0)->count(),
            'approved' => InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 1)->count(),
            'rejected' => InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->where('status', 2)->count(),
        ];

        $deadline = PrazoInscricao::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('tipo_inscricao', 'adultos')
            ->first();

        $taxConfig = InscricaoTaxaConfig::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('tipo', 'adultos')
            ->with('items')
            ->first();

        return view('modules.inscricoes-catequese-adultos.index', compact('records', 'stats', 'deadline', 'taxConfig'));
    }

    public function storeTaxConfig(Request $request)
    {
        $request->validate([
            'inscricao_com_taxa' => 'required|boolean',
            'metodo_pagamento_label' => 'nullable|string',
            'metodo_pagamento_valor' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.nome' => 'required_with:items|string',
            'items.*.valor' => 'required_with:items|string',
        ]);

        $taxConfig = InscricaoTaxaConfig::updateOrCreate(
            [
                'paroquia_id' => Auth::user()->paroquia_id,
                'tipo' => 'adultos'
            ],
            [
                'inscricao_com_taxa' => $request->boolean('inscricao_com_taxa'),
                'metodo_pagamento_label' => $request->metodo_pagamento_label,
                'metodo_pagamento_valor' => $request->metodo_pagamento_valor,
            ]
        );

        // Handle Items
        $incomingItems = $request->input('items', []);
        $incomingIds = collect($incomingItems)->pluck('id')->filter()->toArray();

        // Remove items that are not in the incoming list
        $taxConfig->items()->whereNotIn('id', $incomingIds)->delete();

        foreach ($incomingItems as $itemData) {
            $valor = preg_replace('/[^\d,]/', '', $itemData['valor']);
            $valor = str_replace(',', '.', $valor);

            if (isset($itemData['id']) && $itemData['id']) {
                $item = InscricaoTaxaItem::find($itemData['id']);
                if ($item && $item->config_id == $taxConfig->id) {
                    $item->update([
                        'nome' => $itemData['nome'],
                        'valor' => $valor,
                        'ativo' => 1
                    ]);
                }
            } else {
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
            'ids.*' => 'exists:inscricao_catequese_adultos,id'
        ]);

        $count = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json(['success' => true, 'message' => "{$count} inscrições excluídas com sucesso."]);
    }

    public function destroy($id)
    {
        $record = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $record->delete();
        return redirect()->back()->with('success', 'Inscrição excluída com sucesso!');
    }

    public function bulkPrint(Request $request)
    {
        $query = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

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
            if ($request->has('matrimonio') && $request->matrimonio !== '') {
                if ($request->matrimonio == '1') {
                    $query->whereNotNull('certidao_matrimonio')->where('certidao_matrimonio', '!=', '');
                } else {
                    $query->where(function($q) {
                        $q->whereNull('certidao_matrimonio')->orWhere('certidao_matrimonio', '');
                    });
                }
            }
            if ($request->has('date_from') && $request->date_from != '') {
                $query->whereDate('data_inscricao', '>=', $request->date_from);
            }
            if ($request->has('date_to') && $request->date_to != '') {
                $query->whereDate('data_inscricao', '<=', $request->date_to);
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
        $record = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('taxa')
            ->findOrFail($id);
            
        return $this->generatePdfForRecords(collect([$record]), true);
    }

    public function searchUsers(Request $request)
    {
        $search = $request->input('q');
        $users = User::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('status', 0) 
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
            'ids' => 'required', 
        ]);

        $ids = is_string($request->ids) ? json_decode($request->ids) : $request->ids;
        
        if (empty($ids)) {
             return response()->json(['success' => false, 'message' => 'Nenhum registro selecionado.']);
        }

        $records = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $ids)
            ->orderBy('nome', 'asc')
            ->get();

        if ($records->isEmpty()) {
             return response()->json(['success' => false, 'message' => 'Nenhum registro encontrado.']);
        }

        $pdfData = $this->generatePdfForRecords($records, false, true);
        
        $inscritosNames = $records->pluck('nome')->toArray();
        $senderName = Auth::user()->name;
        $userMessage = $request->input('message');
        
        $filename = 'fichas-catequese-adultos-compartilhadas-' . date('d-m-Y') . '.pdf';

        $users = User::whereIn('id', $request->users)->get();
        foreach ($users as $user) {
             Mail::to($user->email)->send(new ShareInscricoesCatequeseAdultos(
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
        $pdf = new Fpdi();
        
        foreach ($records as $record) {
            $fichaPdf = Pdf::loadView('modules.inscricoes-catequese-adultos.print', ['records' => [$record]])->output();
            
            $tempFicha = tempnam(sys_get_temp_dir(), 'ficha_');
            file_put_contents($tempFicha, $fichaPdf);
            
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
            
            if (file_exists($tempFicha)) {
                unlink($tempFicha);
            }
            
            // Attachments
            $attachments = [
                $record->certidao_batismo,
                $record->certidao_primeira_comunhao,
                $record->certidao_matrimonio
            ];
            
            foreach ($attachments as $att) {
                if ($att) {
                    $fullPath = storage_path('app/public/uploads/certidoes/' . $att);
                    
                    if (file_exists($fullPath)) {
                        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
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
                             }
                        } 
                    }
                }
            }
        }

        if ($isSingle) {
            $name = Str::slug($records->first()->nome, '-');
            $filename = "ficha-adultos-{$name}.pdf";
        } else {
            $date = date('d-m-Y');
            $filename = "fichas-adultos-lote-{$date}.pdf";
        }
        
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
        $query = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->with('taxa');

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
        if ($request->has('matrimonio') && $request->matrimonio !== '') {
            if ($request->matrimonio == '1') {
                $query->whereNotNull('certidao_matrimonio')->where('certidao_matrimonio', '!=', '');
            } else {
                $query->where(function($q) {
                    $q->whereNull('certidao_matrimonio')->orWhere('certidao_matrimonio', '');
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
            "Content-Disposition" => "attachment; filename=inscricoes_adultos_" . date('d-m-Y_H-i') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'ID', 'Status', 'Nome', 'CPF', 'Data Nascimento', 'Sexo', 
            'Telefone 1', 'Telefone 2', 'Endereço', 'Número', 'CEP', 'Estado Civil',
            'Certidão Batismo', 'Certidão Eucaristia', 'Certidão Matrimônio', 'Data Inscrição'
        ];

        $callback = function() use($records, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, $columns, ';');

            foreach ($records as $record) {
                $status = match($record->status) {
                    0 => 'Pendente',
                    1 => 'Aprovado',
                    2 => 'Reprovado',
                    default => 'Desconhecido'
                };
                
                $dtNasc = $record->data_nascimento ? \Carbon\Carbon::parse($record->data_nascimento)->format('d/m/Y') : '';
                $dtInscricao = $record->data_inscricao ? \Carbon\Carbon::parse($record->data_inscricao)->format('d/m/Y H:i') : '';

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
                    $record->estado_civil,
                    $record->certidao_batismo ? asset('storage/uploads/certidoes/' . $record->certidao_batismo) : '',
                    $record->certidao_primeira_comunhao ? asset('storage/uploads/certidoes/' . $record->certidao_primeira_comunhao) : '',
                    $record->certidao_matrimonio ? asset('storage/uploads/certidoes/' . $record->certidao_matrimonio) : '',
                    $dtInscricao
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show($id)
    {
        $record = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)
            ->with('taxa')
            ->findOrFail($id);
        return response()->json($record);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2',
        ]);

        $record = InscricaoCatequeseAdultos::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        
        $record->status = $request->status;
        $record->save();
        
        // Register creation logic is optional, skipping for brevity unless requested
        
        return redirect()->back()->with('success', 'Situação atualizada com sucesso!');
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
            'tipo_inscricao' => 'adultos'
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
