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

        $celebrations = EscalaDataHora::where('es_id', $id)
                                      ->with(['escalados.acolito.user', 'escalados.funcao', 'entidade'])
                                      ->get()
                                      ->map(function ($item) {
                                          $item->type = 'published';
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
                    'data' => $payload['data'],
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
            'Janeiro' => 1, 'Fevereiro' => 2, 'Março' => 3, 'Abril' => 4,
            'Maio' => 5, 'Junho' => 6, 'Julho' => 7, 'Agosto' => 8,
            'Setembro' => 9, 'Outubro' => 10, 'Novembro' => 11, 'Dezembro' => 12
        ];
        $monthNum = $months[$escala->month] ?? date('n');
        $year = $escala->year;
        $daysInMonth = Carbon::createFromDate($year, $monthNum, 1)->daysInMonth;
        
        // Map celebrations by day for easy access in view
        $celebrationsByDay = $allCelebrations->groupBy('data');

        return view('modules.acolitos.escalas.manage', compact(
            'escala', 'celebrations', 'allCelebrations', 'celebrationsByDay', 'entidades', 
            'defaultEntId', 'acolitos', 'funcoes', 'daysInMonth', 'monthNum', 'year'
        ));
    }

    /**
     * Store a celebration in the scale.
     */
    public function storeCelebration(Request $request, $id)
    {
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
        $escala = Escala::where('es_id', $id)
                        ->where('paroquia_id', Auth::user()->paroquia_id)
                        ->firstOrFail();

        // Check if draft
        if (str_starts_with($celebrationId, 'draft_')) {
            $draftId = str_replace('draft_', '', $celebrationId);
            $draft = EscalaDraft::where('id', $draftId)
                                ->where('es_id', $escala->es_id)
                                ->firstOrFail();
            
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
                                         ->firstOrFail();

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
            return response()->json(['success' => false, 'message' => 'Erro ao excluir celebração.'], 500);
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

}
