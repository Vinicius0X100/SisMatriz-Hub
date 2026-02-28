<?php

namespace App\Http\Controllers;

use App\Models\Escala;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\EscalaDataHora;
use App\Models\EscaladoData;
use App\Models\Acolito;
use App\Models\AcolitoFuncao;
use App\Models\EscalaDraft;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Barryvdh\DomPDF\Facade\Pdf;
// use App\Jobs\SendEscalaWhatsappJob;

class AcolitoEscalaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Escala::where('paroquia_id', Auth::user()->paroquia_id);

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('month', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%")
                  ->orWhere('church', 'like', "%{$search}%");
            });
        }

        $escalas = $query->withCount(['escalados as total_participacoes' => function ($query) {
                             $query->select(DB::raw('count(distinct acolito_id)'));
                         }])
                         ->orderBy('year', 'desc')
                         ->orderBy('month', 'desc')
                         ->paginate(10);
        
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('ent_name')
                             ->get();

        return view('modules.acolitos.escalas.index', compact('escalas', 'entidades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->rule == 8) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'month' => 'required|string',
            'ent_id' => 'required|exists:entidades,ent_id',
            'send_date' => 'required|date',
        ]);

        $entidade = Entidade::where('ent_id', $request->ent_id)->firstOrFail();

        Escala::create([
            'month' => $request->month,
            'year' => date('Y'),
            'church' => $entidade->ent_name, // Store name as requested
            'send_date' => $request->send_date,
            'qntd_acolitos' => 0,
            'situation' => 0,
            'paroquia_id' => Auth::user()->paroquia_id
        ]);

        return redirect()->route('acolitos.escalas.index')
                         ->with('success', 'Escala criada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->firstOrFail();

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('ent_name')
                             ->get();
                             
        // Find ent_id by name to pre-select in edit modal/form if needed
        // Assuming we pass data to view for editing
        return view('modules.acolitos.escalas.edit', compact('escala', 'entidades'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Auth::user()->rule == 8) {
            abort(403, 'Acesso não autorizado.');
        }

        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->firstOrFail();

        $request->validate([
            'month' => 'required|string',
            'ent_id' => 'required|exists:entidades,ent_id',
            'send_date' => 'required|date',
            'situation' => 'required|in:0,1'
        ]);

        $entidade = Entidade::where('ent_id', $request->ent_id)->firstOrFail();

        $escala->update([
            'month' => $request->month,
            'church' => $entidade->ent_name,
            'send_date' => $request->send_date,
            'situation' => $request->situation
        ]);

        return redirect()->route('acolitos.escalas.index')
                         ->with('success', 'Escala atualizada com sucesso!');
    }

    /**
     * Manage the scale (calendar view).
     */
    public function manage($id)
    {
        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->firstOrFail();

        $canEdit = Auth::user()->rule != 8;
        $myAcolitoId = null;

        if (!$canEdit) {
            $myAcolitoId = Acolito::where('user_id', Auth::id())->value('id');
        }

        $celebrations = EscalaDataHora::where('es_id', $id)
                                      ->with(['escalados.acolito.user', 'escalados.funcao', 'entidade'])
                                      ->get()
                                      ->map(function ($item) {
                                          $item->type = 'published';
                                          $item->data = (int)$item->data;
                                          return $item;
                                      });

        // Fetch drafts
        $drafts = EscalaDraft::where('es_id', $id)->get();
        $draftCelebrations = $drafts->map(function ($draft) {
            $path = 'escalas/drafts/' . $draft->payload;
            if (Storage::disk('local')->exists($path)) {
                $payload = json_decode(Storage::disk('local')->get($path), true);
                
                // Construct a pseudo-object for the view
                // We need to fetch Entity name for display if possible, or just use ID
                $entidade = Entidade::find($payload['ent_id']);
                
                // Map acolitos from payload IDs to objects for display in modal/tooltip if needed
                // For now, the view might need basic info. 
                // The payload has 'acolitos' array with 'id' and 'funcao_id'.
                
                return (object) [
                    'd_id' => 'draft_' . $draft->id,
                    'draft_id' => $draft->id,
                    'data' => (int)$payload['data'],
                    'dia' => $payload['dia'],
                    'hora' => $payload['hora'],
                    'celebration' => $draft->title,
                    'ent_id' => $payload['ent_id'],
                    'entidade' => $entidade,
                    'type' => 'draft',
                    'payload' => $payload // Full payload for JS
                ];
            }
            return null;
        })->filter();

        // Merge published and drafts
        $allCelebrations = $celebrations->concat($draftCelebrations);

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
                             ->orderBy('ent_name')
                             ->get();

        // Find the ent_id corresponding to the scale's church name
        $defaultEntidade = $entidades->firstWhere('ent_name', $escala->church);
        $defaultEntId = $defaultEntidade ? $defaultEntidade->ent_id : null;

        $acolitos = Acolito::leftJoin('users', 'acolitos.user_id', '=', 'users.id')
                           ->where('acolitos.paroquia_id', Auth::user()->paroquia_id)
                           ->select('acolitos.*', 'users.avatar as user_avatar', 'users.name as user_name')
                           ->orderBy('acolitos.name')
                           ->get();

        $funcoes = AcolitoFuncao::where('paroquia_id', Auth::user()->paroquia_id)
                                ->orderBy('title')
                                ->get();

        // Calculate days in month
        $months = [
            'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'abril' => 4,
            'maio' => 5, 'junho' => 6, 'julho' => 7, 'agosto' => 8,
            'setembro' => 9, 'outubro' => 10, 'novembro' => 11, 'dezembro' => 12
        ];
        $normalizedMonth = mb_strtolower(trim($escala->month), 'UTF-8');
        $monthNum = $months[$normalizedMonth] ?? date('n');
        $year = $escala->year;
        $daysInMonth = Carbon::createFromDate($year, $monthNum, 1)->daysInMonth;
        
        // Map celebrations by day for easy access in view
        $celebrationsByDay = $allCelebrations->groupBy('data');

        return view('modules.acolitos.escalas.manage', compact(
            'escala', 'celebrations', 'allCelebrations', 'celebrationsByDay', 'entidades', 
            'defaultEntId', 'acolitos', 'funcoes', 'daysInMonth', 'monthNum', 'year',
            'canEdit', 'myAcolitoId'
        ));
    }

    /**
     * Store a celebration in the scale.
     */
    public function storeCelebration(Request $request, $id)
    {
        if (Auth::user()->rule == 8) {
            return response()->json(['success' => false, 'message' => 'Acesso não autorizado.'], 403);
        }

        Log::info('DEBUG: storeCelebration hit', ['id' => $id, 'data' => $request->all()]);

        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->firstOrFail();

        $request->validate([
            'data' => 'required|integer|min:1|max:31',
            'dia' => 'required|integer|min:1|max:7', // 1=Mon, 7=Sun or typical PHP date('N')
            'hora' => 'required',
            'celebration' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'acolitos' => 'nullable|array',
            'acolitos.*.id' => 'required|exists:acolitos,id',
            'acolitos.*.funcao_id' => 'nullable|exists:acolitos_funcoes,f_id',
            'status' => 'nullable|in:published,draft', // Added status validation
        ]);

        if ($request->status === 'draft') {
            try {
                $filename = 'draft_' . $escala->es_id . '_' . time() . '.json';
                $payload = $request->all();
                
                // Ensure directory exists
                if (!Storage::disk('local')->exists('escalas/drafts')) {
                    Storage::disk('local')->makeDirectory('escalas/drafts');
                }
                
                Storage::disk('local')->put('escalas/drafts/' . $filename, json_encode($payload));
                
                EscalaDraft::create([
                    'es_id' => $escala->es_id,
                    'paroquia_id' => Auth::user()->paroquia_id,
                    'user_id' => Auth::id(),
                    'title' => $request->celebration,
                    'payload' => $filename,
                    'status' => 'draft'
                ]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Rascunho salvo com sucesso!',
                    'type' => 'draft'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar rascunho: ' . $e->getMessage()
                ], 500);
            }
        }

        DB::beginTransaction();

        try {
            $celebration = EscalaDataHora::create([
                'es_id' => $escala->es_id,
                'data' => $request->data,
                'dia' => $request->dia,
                'celebration' => $request->celebration,
                'hora' => $request->hora,
                'ent_id' => $request->ent_id,
            ]);

            $acolitoIds = [];
            if ($request->has('acolitos')) {
                foreach ($request->acolitos as $acolitoData) {
                    EscaladoData::create([
                        'd_id' => $celebration->d_id,
                        'escala_id' => $escala->es_id,
                        'acolito_id' => $acolitoData['id'],
                        'funcao_id' => $acolitoData['funcao_id'] ?? null,
                    ]);
                    $acolitoIds[] = $acolitoData['id'];
                }
            }
            
            Log::info('DEBUG: acolitoIds collected', ['ids' => $acolitoIds]);
            
            // Update total acolytes count in scale
            $totalAcolitos = EscaladoData::where('escala_id', $escala->es_id)->count();
            $escala->update(['qntd_acolitos' => $totalAcolitos]);

            DB::commit();

            // Send WhatsApp Notification (Inline)
            if (!empty($acolitoIds)) {
                $details = [
                    'title' => $request->celebration,
                    'date' => $request->data . '/' . $escala->month . '/' . $escala->year,
                    'time' => $request->hora,
                    'local' => Entidade::find($request->ent_id)->ent_name ?? 'Local não informado'
                ];
                
                // Call private method directly
                $this->sendWhatsappNotification($acolitoIds, $details);
            }

            return response()->json([
                'success' => true,
                'message' => 'Celebração publicada com sucesso!',
                'celebration' => $celebration->load(['escalados.acolito.user', 'escalados.funcao', 'entidade']),
                'type' => 'published'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar celebração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a celebration.
     */
    public function updateCelebration(Request $request, $id, $celebrationId)
    {
        if (Auth::user()->rule == 8) {
            return response()->json(['success' => false, 'message' => 'Acesso não autorizado.'], 403);
        }

        Log::info('DEBUG: updateCelebration hit', ['id' => $id, 'celebrationId' => $celebrationId, 'data' => $request->all()]);

        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->firstOrFail();

        // Check if it's a draft update
        if (str_starts_with($celebrationId, 'draft_')) {
            return $this->handleDraftUpdate($request, $escala, $celebrationId);
        }

        $celebration = EscalaDataHora::where('d_id', $celebrationId)
                                     ->where('es_id', $escala->es_id)
                                     ->firstOrFail();

        $request->validate([
            'hora' => 'required',
            'celebration' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'acolitos' => 'nullable|array',
            'acolitos.*.id' => 'required|exists:acolitos,id',
            'acolitos.*.funcao_id' => 'nullable|exists:acolitos_funcoes,f_id',
        ]);

        DB::beginTransaction();

        try {
            $celebration->update([
                'celebration' => $request->celebration,
                'hora' => $request->hora,
                'ent_id' => $request->ent_id,
            ]);

            // Sync acolytes: remove all and re-add (simplest approach)
            EscaladoData::where('d_id', $celebration->d_id)->delete();

            $acolitoIds = [];
            if ($request->has('acolitos')) {
                foreach ($request->acolitos as $acolitoData) {
                    EscaladoData::create([
                        'd_id' => $celebration->d_id,
                        'escala_id' => $escala->es_id,
                        'acolito_id' => $acolitoData['id'],
                        'funcao_id' => $acolitoData['funcao_id'] ?? null,
                    ]);
                    $acolitoIds[] = $acolitoData['id'];
                }
            }
            
            // Update total acolytes count in scale
            $totalAcolitos = EscaladoData::where('escala_id', $escala->es_id)->count();
            $escala->update(['qntd_acolitos' => $totalAcolitos]);

            DB::commit();

            // Send WhatsApp Notification (Inline)
            if (!empty($acolitoIds)) {
                $details = [
                    'title' => $request->celebration,
                    'date' => $celebration->data . '/' . $escala->month . '/' . $escala->year,
                    'time' => $request->hora,
                    'local' => Entidade::find($request->ent_id)->ent_name ?? 'Local não informado'
                ];
                
                // Call private method directly
                $this->sendWhatsappNotification($acolitoIds, $details);
            }

            return response()->json([
                'success' => true,
                'message' => 'Celebração atualizada com sucesso!',
                'celebration' => $celebration->load(['escalados.acolito.user', 'escalados.funcao', 'entidade'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar celebração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a celebration.
     */
    public function destroyCelebration($id, $celebrationId)
    {
        if (Auth::user()->rule == 8) {
            return response()->json(['success' => false, 'message' => 'Acesso não autorizado.'], 403);
        }

        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->first();

        if (!$escala) {
            return response()->json(['success' => false, 'message' => 'Escala não encontrada.'], 404);
        }

        // Check if draft
        if (str_starts_with($celebrationId, 'draft_')) {
            $draftId = str_replace('draft_', '', $celebrationId);
            $draft = EscalaDraft::where('id', $draftId)
                                ->where('es_id', $escala->es_id)
                                ->first();

            if (!$draft) {
                 return response()->json(['success' => false, 'message' => 'Rascunho não encontrado.'], 404);
            }
            
            if (Storage::disk('local')->exists('escalas/drafts/' . $draft->payload)) {
                Storage::disk('local')->delete('escalas/drafts/' . $draft->payload);
            }
            $draft->delete();
            
            return response()->json(['success' => true, 'message' => 'Rascunho excluído com sucesso!']);
        }

        DB::beginTransaction();

        try {
            $celebration = EscalaDataHora::where('d_id', $celebrationId)
                                         ->where('es_id', $id)
                                         ->first();

            if (!$celebration) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Celebração não encontrada.'], 404);
            }

            // Remove assigned acolytes
            EscaladoData::where('d_id', $celebration->d_id)->delete();
            
            // Remove celebration
            $celebration->delete();
            
            // Update total acolytes count
            $totalAcolitos = EscaladoData::where('escala_id', $escala->es_id)->count();
            $escala->update(['qntd_acolitos' => $totalAcolitos]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Celebração excluída com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Erro ao excluir celebração: ' . $e->getMessage()], 500);
        }
    }

    private function handleDraftUpdate(Request $request, Escala $escala, $draftIdStr)
    {
        Log::info('DEBUG: handleDraftUpdate called', ['request_all' => $request->all(), 'draft_id' => $draftIdStr]);

        $draftId = str_replace('draft_', '', $draftIdStr);
        $draft = EscalaDraft::where('id', $draftId)
                            ->where('es_id', $escala->es_id)
                            ->firstOrFail();

        $request->validate([
            'data' => 'required|integer|min:1|max:31',
            'dia' => 'required|integer|min:1|max:7',
            'hora' => 'required',
            'celebration' => 'required|string|max:255',
            'ent_id' => 'required|exists:entidades,ent_id',
            'acolitos' => 'nullable|array',
            'status' => 'nullable|in:published,draft',
        ]);

        // If keeping as draft, update JSON and DB record
        if ($request->status === 'draft') {
            try {
                $filename = $draft->payload;
                $payload = $request->all();
                
                Storage::disk('local')->put('escalas/drafts/' . $filename, json_encode($payload));
                
                $draft->update([
                    'title' => $request->celebration,
                ]);
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Rascunho atualizado com sucesso!',
                    'type' => 'draft'
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Erro ao atualizar rascunho.'], 500);
            }
        }

        // Publishing a draft
        if ($request->status === 'published') {
            // Create real records
            DB::beginTransaction();
            try {
                $celebration = EscalaDataHora::create([
                    'es_id' => $escala->es_id,
                    'data' => $request->data,
                    'dia' => $request->dia,
                    'celebration' => $request->celebration,
                    'hora' => $request->hora,
                    'ent_id' => $request->ent_id,
                ]);

                $acolitoIds = [];
                if ($request->has('acolitos')) {
                    foreach ($request->acolitos as $acolitoData) {
                        EscaladoData::create([
                            'd_id' => $celebration->d_id,
                            'escala_id' => $escala->es_id,
                            'acolito_id' => $acolitoData['id'],
                            'funcao_id' => $acolitoData['funcao_id'] ?? null,
                        ]);
                        $acolitoIds[] = $acolitoData['id'];
                    }
                }
                
                Log::info('DEBUG: acolitoIds collected (Draft Publish)', ['ids' => $acolitoIds]);
                
                // Update total acolytes count
                $totalAcolitos = EscaladoData::where('escala_id', $escala->es_id)->count();
                $escala->update(['qntd_acolitos' => $totalAcolitos]);

                // Delete draft
                if (Storage::disk('local')->exists('escalas/drafts/' . $draft->payload)) {
                    Storage::disk('local')->delete('escalas/drafts/' . $draft->payload);
                }
                $draft->delete();

                DB::commit();

                // Send WhatsApp Notification (Inline)
                if (!empty($acolitoIds)) {
                    $details = [
                        'title' => $request->celebration,
                        'date' => $request->data . '/' . $escala->month . '/' . $escala->year,
                        'time' => $request->hora,
                        'local' => Entidade::find($request->ent_id)->ent_name ?? 'Local não informado'
                    ];
                    
                    // Call private method directly
                    $this->sendWhatsappNotification($acolitoIds, $details);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Rascunho publicado com sucesso!',
                    'type' => 'published'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Erro ao publicar rascunho: ' . $e->getMessage()], 500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::user()->rule == 8) {
            abort(403, 'Acesso não autorizado.');
        }

        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->firstOrFail();
        
        $escala->delete();

        return redirect()->route('acolitos.escalas.index')
                         ->with('success', 'Escala removida com sucesso!');
    }

    /**
     * Send WhatsApp notification directly (Inline)
     */
    private function sendWhatsappNotification(array $acolitoIds, array $details)
    {
        Log::info('DEBUG: sendWhatsappNotification started', ['acolitoIds' => $acolitoIds]);

        $sid  = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');
        $messagingServiceSid = config('services.twilio.messaging_service_sid');
        $contentSid = config('services.twilio.content_sid_acolitos');

        // 🔒 Validação COMPLETA
        if (!$sid || !$token || !$messagingServiceSid || !$contentSid || !$from) {
            Log::error('Twilio config missing', [
                'sid' => (bool) $sid,
                'token' => (bool) $token,
                'from' => $from,
                'messagingServiceSid' => $messagingServiceSid,
                'contentSid' => $contentSid,
            ]);
            return;
        }

        try {
            $twilio = new Client($sid, $token);
        } catch (\Exception $e) {
            Log::error('Twilio client init failed: ' . $e->getMessage());
            return;
        }

        $acolitos = Acolito::with(['register'])->whereIn('id', $acolitoIds)->get();

        foreach ($acolitos as $acolito) {
            $phone = $acolito->register->phone ?? null;
            $userName = $acolito->register->name ?? 'Unknown';

            if (empty($phone)) {
                Log::warning("Acolito {$acolito->id} ({$userName}) sem telefone no registro.");
                Log::warning("Register details: " . json_encode($acolito->register));
                continue;
            }

            // 🔹 Normaliza telefone
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

            // 🇧🇷 Aceita apenas padrões válidos
            if (strlen($cleanPhone) === 10 || strlen($cleanPhone) === 11) {
                $to = 'whatsapp:+55' . $cleanPhone;
            } elseif ((strlen($cleanPhone) === 12 || strlen($cleanPhone) === 13) && str_starts_with($cleanPhone, '55')) {
                $to = 'whatsapp:+' . $cleanPhone;
            } else {
                Log::warning("Telefone inválido para acolito {$acolito->id}: {$phone}");
                continue;
            }

            try {
                $messageOptions = [
                    'from' => $from,
                    'messagingServiceSid' => $messagingServiceSid,
                    'contentSid' => $contentSid,
                    'contentVariables' => json_encode([
                        "1" => "SisMatriz para Android",
                        "2" => "https://central.sismatriz.online"
                    ])
                ];

                $message = $twilio->messages->create($to, $messageOptions);

                Log::info('WhatsApp enviado', [
                    'acolito_id' => $acolito->id,
                    'to' => $to,
                    'sid' => $message->sid,
                    'status' => $message->status,
                ]);

            } catch (\Exception $e) {
                Log::error("Erro ao enviar WhatsApp para {$to}: " . $e->getMessage());
                Log::error($e->getTraceAsString());
            }
        }

        Log::info('DEBUG: sendWhatsappNotification finished');
    }

    public function generatePdf($id)
    {
        $escala = Escala::where('es_id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        // Use the existing Stored Procedure as requested
        // Procedure: GeneratePDFEscalaComAcolitos(es_id)
        // Returns: d_id, data_formatada, hora, church, celebration, acolitos_funcoes (concatenated)
        
        try {
            $results = DB::select('CALL GeneratePDFEscalaComAcolitos(?)', [$id]);
        } catch (\Exception $e) {
            Log::error("Error calling procedure GeneratePDFEscalaComAcolitos: " . $e->getMessage());
            $results = [];
        }
        
        // Transform the flat procedure results into the structured format expected by the view
        // The view expects $finalGrouped collection with dates as keys
        
        $finalGrouped = collect();
        
        // Group results by date (raw date needed for sorting, but procedure gives formatted date?)
        // The procedure returns 'data_formatada' which is like "05 de Outubro de 2025".
        // It also returns 'd_id' which is unique per day/time slot? No, d_id comes from escalados_datas? 
        // Wait, the SQL provided shows: FROM escalas_datas_horas edh ... GROUP BY edh.d_id
        // So each row is a celebration slot.
        // But the user wants them grouped by DATE in the PDF table.
        
        // We need to parse the results.
        foreach ($results as $row) {
            // Create a date object for sorting keys
            // The procedure doesn't return raw YYYY-MM-DD date? 
            // The SQL provided: SELECT edh.d_id, CONCAT(...) as data_formatada ...
            // It orders by edh.data ASC.
            // We need the raw date to group correctly if we want to stick to previous logic,
            // OR we just iterate sequentially since the procedure already orders by date/time.
            // The previous view logic used $finalGrouped = [ 'Y-m-d' => [ 'date' => obj, 'celebrations' => [...] ] ]
            
            // Let's reconstruct the structure.
            // Since the procedure groups acolytes into one string, we need to split them back.
            
            $acolitosList = [];
            if (!empty($row->acolitos_funcoes)) {
                $pairs = explode(';;', $row->acolitos_funcoes);
                foreach ($pairs as $pair) {
                    $parts = explode('||', $pair);
                    $name = $parts[0] ?? 'N/A';
                    $func = $parts[1] ?? 'Sem função';
                    $acolitosList[] = (object)['name' => $name, 'function' => $func];
                }
            } else {
                 // Even if empty, we might want to show N/A?
                 $acolitosList[] = (object)['name' => 'N/A', 'function' => 'Função'];
            }
            
            // We need a key for grouping by day to handle rowspan in the view
            // But wait, the previous view logic grouped by Y-m-d.
            // Here we don't have Y-m-d easily unless we parse 'data_formatada' or fetch it.
            // ACTUALLY, the SQL provided in the prompt implies we CAN change the SELECT if we wanted, 
            // but the user said "nao modifique ela". 
            // However, the procedure returns what it returns.
            // Let's assume we can't get raw date easily from result if it's not selected.
            // BUT, we can probably use the 'data_formatada' as the grouping key since it's unique per day.
            // OR, better: The procedure returns one row per celebration (d_id).
            // So we just need to group these rows by their date text.
            
            $dateKey = $row->data_formatada; // e.g. "05 de Outubro de 2025"
            
            if (!$finalGrouped->has($dateKey)) {
                $finalGrouped->put($dateKey, [
                    'date_string' => $row->data_formatada, // We use this for display
                    'celebrations' => collect()
                ]);
            }
            
            // Create a celebration object that matches what view expects somewhat
            // View uses: $cel->hora, $cel->entidade->ent_name, $cel->celebration, $cel->escalados
            // We need to mock this structure or update the view.
            // Updating view is cleaner.
            
            $celebrationObj = (object)[
                'hora' => $row->hora,
                'local' => $row->church, // Procedure returns 'church' from scales table? No, wait.
                // The SQL says: SELECT ... es.church ... 
                // Wait, es.church is the COMMUNITY name (e.g. Matriz).
                // But previously we had edh.entidade->ent_name (Local).
                // Does the procedure return the LOCAL of the mass?
                // The SQL provided: JOIN escalas_datas_horas edh ... 
                // It does NOT select ent_id or join entities.
                // It selects 'es.church'. This might be wrong if the mass is in a different chapel.
                // But the user said "use de base para corrigir isso".
                // If the procedure returns 'es.church', then all rows will have the same location?
                // That seems like a limitation of the procedure if true.
                // HOWEVER, the user said "O codigo que chama procedure... adapte tudo isso... porem chamando a procedure que ja existe".
                // If the procedure is "GeneratePDFEscalaComAcolitos", and the SQL provided is just an EXAMPLE of what it might do?
                // "EU tenho essa procedure que faz esse sql... use de base para corrigir isso"
                // It seems the user WANTS us to use the procedure.
                // Let's trust the procedure's output for 'church'.
                
                'celebration' => $row->celebration,
                'acolitos' => $acolitosList
            ];
            
            $finalGrouped[$dateKey]['celebrations']->push($celebrationObj);
        }

        // Sort the collection chronologically based on the Portuguese date string
        $finalGrouped = $finalGrouped->sortBy(function ($item, $key) {
            // $key format: "05 de Outubro de 2025"
            if (preg_match('/^(\d{1,2})\s+de\s+(\w+)\s+de\s+(\d{4})/ui', $key, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $monthName = mb_strtolower($matches[2]);
                $year = $matches[3];
                
                $months = [
                    'janeiro' => '01', 'fevereiro' => '02', 'março' => '03', 'marco' => '03',
                    'abril' => '04', 'maio' => '05', 'junho' => '06', 'julho' => '07',
                    'agosto' => '08', 'setembro' => '09', 'outubro' => '10', 
                    'novembro' => '11', 'dezembro' => '12'
                ];
                
                $month = $months[$monthName] ?? '00';
                
                // Return YYYYMMDD integer for correct chronological sorting
                return (int) ($year . $month . $day);
            }
            
            // Fallback: if parsing fails, try to sort by the key string itself
            return $key;
        });

        $parish = Auth::user()->paroquia;
        $parishPhone = $parish ? $parish->phone : 'Não informado';

        $pdf = Pdf::loadView('modules.acolitos.escalas.pdf_procedure', compact('escala', 'finalGrouped', 'parishPhone'))
            ->setPaper('a4', 'landscape');
            
        return $pdf->download('escala_acolitos_' . $escala->month . '_' . $escala->year . '.pdf');
    }
}
