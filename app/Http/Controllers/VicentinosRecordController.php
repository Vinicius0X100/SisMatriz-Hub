<?php

namespace App\Http\Controllers;

use App\Models\VicentinosRecord;
use App\Models\VicentinosFamily;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class VicentinosRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if ($request->ajax() || $request->wantsJson()) {
                $query = VicentinosRecord::where('paroquia_id', Auth::user()->paroquia_id);

                // Search
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('responsavel_nome', 'like', "%{$search}%")
                          ->orWhere('cpf', 'like', "%{$search}%")
                          ->orWhere('rg', 'like', "%{$search}%")
                          ->orWhere('telefone', 'like', "%{$search}%");
                    });
                }

                // Filter by Bairro (home_situation equivalent in Registers but stored as bairro here)
                if ($request->has('bairro') && !empty($request->bairro)) {
                    $query->where('bairro', 'like', "%{$request->bairro}%");
                }

                // Filter by Status
                if ($request->has('status') && !empty($request->status)) {
                    if ($request->status === 'ativo') {
                        $query->whereNull('data_dispensa');
                    } elseif ($request->status === 'inativo') {
                        $query->whereNotNull('data_dispensa');
                    }
                }

                // Sorting
                $sortColumn = $request->get('sort_by', 'created_at');
                $sortDirection = $request->get('sort_dir', 'desc');
                
                $allowedSorts = ['responsavel_nome', 'cpf', 'telefone', 'created_at', 'bairro'];
                if (in_array($sortColumn, $allowedSorts)) {
                    $query->orderBy($sortColumn, $sortDirection);
                } else {
                    $query->orderBy('created_at', 'desc');
                }

                $records = $query->paginate(15);

                // Transform collection
                $records->getCollection()->transform(function ($record) {
                    return [
                        'id' => $record->id,
                        'responsavel_nome' => $record->responsavel_nome,
                        'cpf' => $record->cpf,
                        'telefone' => $record->telefone,
                        'bairro' => $record->bairro,
                        'families_count' => $record->families->count(),
                        'created_at_formatted' => $record->created_at->format('d/m/Y'),
                    ];
                });

                return response()->json($records);
            }

            // Initial view load
            $stats = [
                'total' => VicentinosRecord::where('paroquia_id', Auth::user()->paroquia_id)->count(),
                'families_total' => VicentinosFamily::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            ];

            return view('modules.vicentinos.index', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Erro no VicentinosRecordController@index: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Erro interno ao carregar dados.'], 500);
            }
            abort(500, 'Erro interno do servidor.');
        }
    }

    /**
     * Search registers for autocomplete/import.
     */
    public function searchRegisters(Request $request)
    {
        try {
            $search = $request->get('term');
            $user = Auth::user();

            if (empty($search)) {
                return response()->json([]);
            }

            $registers = Register::where('paroquia_id', $user->paroquia_id)
                ->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('cpf', 'like', "%{$search}%");
                })
                ->select([
                    'id', 'name', 'cpf', 'rg', 'address', 'address_number',
                    'home_situation', 'cep', 'city', 'state', 'phone',
                    'born_date', 'sexo'
                ])
                ->limit(10)
                ->get();

            return response()->json($registers);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modules.vicentinos.create');
    }

    /**
     * Parse currency string to float.
     */
    private function parseCurrency($value)
    {
        if (empty($value)) return null;
        
        // Remove R$, spaces, dots, and replace comma with dot
        $value = str_replace(['R$', ' ', '.'], '', $value);
        $value = str_replace(',', '.', $value);
        
        return (float) $value;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'responsavel_nome' => 'required|string|max:255',
            'telefone' => 'required|string',
            'cpf' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:20',
            // Add other validations as needed
            'families' => 'nullable|array',
            'families.*.nome' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $data = $request->except(['families', '_token']);
            $data['user_id'] = $user->id;
            $data['paroquia_id'] = $user->paroquia_id;

            // Strip punctuation from CPF, RG, CEP, Telefone
            if (!empty($data['cpf'])) {
                $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
            }
            if (!empty($data['rg'])) {
                $data['rg'] = preg_replace('/\D/', '', $data['rg']);
            }
            if (!empty($data['cep'])) {
                $data['cep'] = preg_replace('/\D/', '', $data['cep']);
            }
            if (!empty($data['telefone'])) {
                $data['telefone'] = preg_replace('/\D/', '', $data['telefone']);
            }

            // Parse currency fields
            $currencyFields = ['valor_bolsa_familia', 'outro_beneficio_valor', 'valor_aluguel_prestacao'];
            foreach ($currencyFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = $this->parseCurrency($data[$field]);
                }
            }

            // Create Vicentinos Record
            $record = VicentinosRecord::create($data);

            // Create Family Members
            if ($request->has('families')) {
                foreach ($request->families as $familyData) {
                    if (!empty($familyData['nome'])) {
                        $familyData['vicentinos_record_id'] = $record->id;
                        $familyData['user_id'] = $user->id;
                        $familyData['paroquia_id'] = $user->paroquia_id;
                        
                        // Parse income
                        if (isset($familyData['renda'])) {
                            $familyData['renda'] = $this->parseCurrency($familyData['renda']);
                        }

                        VicentinosFamily::create($familyData);
                    }
                }
            }

            // Sync with Registers (General Registry)
            $register = null;
            if (!empty($data['cpf'])) {
                $register = Register::where('paroquia_id', $user->paroquia_id)
                    ->where('cpf', $data['cpf'])
                    ->first();
            }

            $registerData = [
                'name' => $data['responsavel_nome'],
                'cpf' => $data['cpf'] ?? null,
                'rg' => $data['rg'] ?? null,
                'address' => $data['endereco'] ?? null,
                'address_number' => $data['endereco_numero'] ?? null,
                'home_situation' => $data['bairro'] ?? null,
                'cep' => $data['cep'] ?? null,
                'city' => $data['cidade'] ?? null,
                'state' => $data['estado'] ?? null,
                'paroquia_id' => $user->paroquia_id,
                'phone' => $data['telefone'] ?? ($data['contato_principal'] ?? null),
                'sexo' => ($data['sexo'] ?? '') === 'Masculino' ? 1 : (($data['sexo'] ?? '') === 'Feminino' ? 2 : null),
                'born_date' => $data['data_nascimento'] ?? null,
                'age' => $data['idade'] ?? null,
            ];

            if ($register) {
                $register->update($registerData);
                $record->update(['register_id' => $register->id]);
            } else {
                $registerData['status'] = 0; // Active
                $newRegister = Register::create($registerData);
                $record->update(['register_id' => $newRegister->id]);
            }

            // Check for duplicate phone in OTHER registers (warning only)
            $phone = $registerData['phone'] ?? null;
            if ($phone) {
                $currentRegisterId = $register ? $register->id : ($newRegister->id ?? null);
                
                $duplicate = Register::where('paroquia_id', $user->paroquia_id)
                    ->where('phone', $phone)
                    ->when($currentRegisterId, function($q) use ($currentRegisterId) {
                        return $q->where('id', '!=', $currentRegisterId);
                    })
                    ->exists();

                if ($duplicate) {
                    session()->flash('warning_modal', [
                        'title' => 'Atenção: Telefone Duplicado',
                        'message' => 'O registro foi salvo com sucesso nos Vicentinos, mas já existe um registro geral com este número de telefone. Verifique se não é a mesma pessoa.'
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('vicentinos.index')->with('success', 'Ficha Vicentina criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar ficha vicentina: ' . $e->getMessage());
            return back()->with('error', 'Erro ao salvar ficha: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $record = VicentinosRecord::with('families')
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);
            
        return view('modules.vicentinos.edit', compact('record'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $record = VicentinosRecord::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        $request->validate([
            'responsavel_nome' => 'required|string|max:255',
            'telefone' => 'required|string',
            'families' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->except(['families', '_token']);

            // Strip punctuation from CPF, RG, CEP, Telefone
            if (!empty($data['cpf'])) {
                $data['cpf'] = preg_replace('/\D/', '', $data['cpf']);
            }
            if (!empty($data['rg'])) {
                $data['rg'] = preg_replace('/\D/', '', $data['rg']);
            }
            if (!empty($data['cep'])) {
                $data['cep'] = preg_replace('/\D/', '', $data['cep']);
            }
            if (!empty($data['telefone'])) {
                $data['telefone'] = preg_replace('/\D/', '', $data['telefone']);
            }

            // Parse currency fields
            $currencyFields = ['valor_bolsa_familia', 'outro_beneficio_valor', 'valor_aluguel_prestacao'];
            foreach ($currencyFields as $field) {
                if (isset($data[$field])) {
                    $data[$field] = $this->parseCurrency($data[$field]);
                }
            }

            $record->update($data);

            // Update Register Sync
            $registerData = [
                'name' => $data['responsavel_nome'],
                'cpf' => $data['cpf'] ?? null,
                'rg' => $data['rg'] ?? null,
                'address' => $data['endereco'] ?? null,
                'address_number' => $data['endereco_numero'] ?? null,
                'home_situation' => $data['bairro'] ?? null,
                'cep' => $data['cep'] ?? null,
                'city' => $data['cidade'] ?? null,
                'state' => $data['estado'] ?? null,
                'phone' => $data['telefone'] ?? ($data['contato_principal'] ?? null),
                'sexo' => ($data['sexo'] ?? '') === 'Masculino' ? 1 : (($data['sexo'] ?? '') === 'Feminino' ? 2 : null),
                'born_date' => $data['data_nascimento'] ?? null,
                'age' => $data['idade'] ?? null,
            ];

            if ($record->register_id) {
                $register = Register::find($record->register_id);
                if ($register) {
                    $register->update($registerData);
                }
            } else {
                 // Try to find by CPF if register_id missing
                 if (!empty($data['cpf'])) {
                    $register = Register::where('paroquia_id', Auth::user()->paroquia_id)
                        ->where('cpf', $data['cpf'])
                        ->first();
                    if ($register) {
                        $register->update($registerData);
                        $record->update(['register_id' => $register->id]);
                    }
                 }
            }

            // Sync Families
            // Strategy: Delete all and recreate? Or update existing?
            // Simpler to delete and recreate for this use case if IDs not tracked in form
            // Or use updateOrCreate if ID provided.
            
            // Let's look at input. If 'families' has ID, update. If not, create.
            // If ID in DB not in input, delete.
            
            if ($request->has('families')) {
                $incomingIds = [];
                foreach ($request->families as $familyData) {
                    // Parse income
                    if (isset($familyData['renda'])) {
                        $familyData['renda'] = $this->parseCurrency($familyData['renda']);
                    }

                    if (isset($familyData['id']) && $familyData['id']) {
                        $incomingIds[] = $familyData['id'];
                        $familyMember = VicentinosFamily::find($familyData['id']);
                        if ($familyMember && $familyMember->vicentinos_record_id == $record->id) {
                            $familyMember->update($familyData);
                        }
                    } else {
                        if (!empty($familyData['nome'])) {
                            $familyData['vicentinos_record_id'] = $record->id;
                            $familyData['user_id'] = Auth::id();
                            $familyData['paroquia_id'] = Auth::user()->paroquia_id;
                            $newFamily = VicentinosFamily::create($familyData);
                            $incomingIds[] = $newFamily->id;
                        }
                    }
                }
                // Delete removed members
                VicentinosFamily::where('vicentinos_record_id', $record->id)
                    ->whereNotIn('id', $incomingIds)
                    ->delete();
            } else {
                // If families array is empty/null, maybe delete all? 
                // Careful with empty array vs not present.
                // Assuming if present but empty, delete all.
                // But if not present in request (e.g. partial update), don't delete.
                // HTML forms usually send empty array if no items.
                if ($request->has('families')) { // present
                     VicentinosFamily::where('vicentinos_record_id', $record->id)->delete();
                }
            }

            // Sync with Registers again?
            // Yes, update info.
             if (!empty($data['cpf'])) {
                $register = Register::where('paroquia_id', Auth::user()->paroquia_id)
                    ->where('cpf', $data['cpf'])
                    ->first();
                
                if ($register) {
                     $register->update([
                        'name' => $data['responsavel_nome'],
                        'address' => $data['endereco'] ?? $register->address,
                        'address_number' => $data['endereco_numero'] ?? $register->address_number,
                        'home_situation' => $data['bairro'] ?? $register->home_situation,
                        'cep' => $data['cep'] ?? $register->cep,
                     ]);
                }
            }

            DB::commit();

            return redirect()->route('vicentinos.index')->with('success', 'Ficha atualizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar ficha vicentina: ' . $e->getMessage());
            return back()->with('error', 'Erro ao atualizar: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $record = VicentinosRecord::with('families')
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);

        return view('modules.vicentinos.show', compact('record'));
    }

    /**
     * Generate PDF report.
     */
    public function generatePdf(Request $request)
    {
        $user = Auth::user();
        $query = VicentinosRecord::where('paroquia_id', $user->paroquia_id);

        if ($request->has('selected_ids') && !empty($request->selected_ids)) {
            $ids = explode(',', $request->selected_ids);
            $query->whereIn('id', $ids);
        }

        $order = (string) $request->input('order', 'responsavel_nome_asc');
        $allowedOrders = [
            'responsavel_nome_asc' => ['responsavel_nome', 'asc'],
            'responsavel_nome_desc' => ['responsavel_nome', 'desc'],
            'created_at_desc' => ['created_at', 'desc'],
            'created_at_asc' => ['created_at', 'asc'],
        ];

        [$orderBy, $orderDir] = $allowedOrders[$order] ?? $allowedOrders['responsavel_nome_asc'];
        $query->orderBy($orderBy, $orderDir)->orderBy('id', 'asc');

        $records = $query->get();
        $columns = $request->get('columns', ['responsavel_nome', 'cpf', 'telefone']);
        
        // Ensure columns is an array
        if (!is_array($columns)) {
            $columns = explode(',', $columns);
        }

        $columnLabels = [
            'responsavel_nome' => 'Nome',
            'cpf' => 'CPF',
            'rg' => 'RG',
            'data_nascimento' => 'Data Nasc.',
            'telefone' => 'Telefone',
            'endereco' => 'Endereço',
            'bairro' => 'Bairro',
            'cidade' => 'Cidade',
            'estado' => 'Estado',
            'cep' => 'CEP',
            'quem_trabalha' => 'Quem Trabalha',
            'local_trabalho' => 'Local Trabalho',
            'responsaveis_sindicancia' => 'Resp. Sindicância',
            'data_dispensa' => 'Data Dispensa',
            'motivo_dispensa' => 'Motivo Dispensa',
            'sexo' => 'Sexo',
            'tipo_residencia' => 'Tipo Residência',
            'valor_aluguel_prestacao' => 'Valor Aluguel/Prest.',
            'recebe_bolsa_familia' => 'Recebe Bolsa Família?',
            'valor_bolsa_familia' => 'Valor Bolsa Família',
            'outro_beneficio_nome' => 'Outro Benefício',
            'outro_beneficio_valor' => 'Valor Outro Benefício',
            'religiao' => 'Religião',
            'catolico_tem_sacramentos' => 'Católico?',
            'created_at' => 'Data Cadastro',
        ];

        $paroquia = $user->paroquia;
        $mode = count($columns) > 10 ? 'ficha' : 'table';
        $includeFamily = $request->has('include_family') && $request->include_family == '1';

        if ($includeFamily && $mode === 'ficha') {
            $records->load('families');
        }

        $pdf = Pdf::loadView('modules.vicentinos.pdf', compact('records', 'columns', 'columnLabels', 'paroquia', 'mode', 'includeFamily'));
        
        if ($mode === 'ficha') {
            $pdf->setPaper('a4', 'portrait');
        } elseif (count($columns) > 5) {
            $pdf->setPaper('a4', 'landscape');
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        return $pdf->download('relatorio_vicentinos_' . date('YmdHis') . '.pdf');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $record = VicentinosRecord::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $record->families()->delete();
        $record->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => 'Ficha excluída com sucesso!']);
        }

        return redirect()->route('vicentinos.index')->with('success', 'Ficha excluída com sucesso!');
    }
}
