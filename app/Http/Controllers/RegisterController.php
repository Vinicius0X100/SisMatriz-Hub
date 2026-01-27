<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Register;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class RegisterController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax() || $request->wantsJson()) {
                $query = Register::query();

                // Filtro por Paróquia (segurança básica, assumindo que o usuário só vê da sua paróquia se aplicável)
                // Se o usuário for superadmin global talvez veja tudo, mas vamos filtrar pela paróquia do usuário logado por padrão
                $user = Auth::user();
                if ($user->paroquia_id) {
                    $query->where('paroquia_id', $user->paroquia_id);
                }

                // Pesquisa (Nome, Email, CPF, Telefone)
                if ($request->has('search') && !empty($request->search)) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%")
                          ->orWhere('cpf', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
                }

                // Filtros Dinâmicos
                if ($request->has('civil_status') && !empty($request->civil_status)) {
                    $query->where('civil_status', $request->civil_status);
                }
                if (($request->has('status') && $request->status !== null && $request->status !== '') || $request->status === '0') { 
                     if (is_numeric($request->status)) {
                        $query->where('status', $request->status);
                     }
                }

                // Ordenação
                $sortColumn = $request->get('sort_by', 'id'); // Default para id (mais recente inserido)
                $sortDirection = $request->get('sort_dir', 'desc');   // Default para desc
                
                // Validar colunas permitidas para ordenação para evitar SQL Injection indireto
                $allowedSorts = ['id', 'name', 'email', 'phone', 'born_date', 'age', 'city', 'status'];
                if (in_array($sortColumn, $allowedSorts)) {
                    $query->orderBy($sortColumn, $sortDirection);
                } else {
                    $query->orderBy('id', 'desc');
                }

                $registers = $query->paginate(15);

                // Transformar dados para o formato necessário no front (opcional, mas bom para formatar datas/status)
                $registers->getCollection()->transform(function ($register) {
                    return [
                        'id' => $register->id,
                        'photo_url' => $register->photo ? asset('storage/uploads/registers/' . $register->photo) : null,
                        'name' => $register->name,
                        'email' => $register->email,
                        'phone' => $register->phone,
                        'born_date_formatted' => ($register->born_date && $register->born_date->format('d/m/Y') !== '01/01/0001') ? $register->born_date->format('d/m/Y') : 'Não informado',
                        'age' => $register->age,
                        'city' => $register->city,
                        'status' => $register->status,
                        'civil_status' => $register->civil_status, // Passar o valor numérico
                        'sexo' => $register->sexo,
                    ];
                });

                return response()->json($registers);
            }

            // Estatísticas para os cards
            $user = Auth::user();
            $baseQuery = Register::where('paroquia_id', $user->paroquia_id);
            
            $stats = [
                'total' => (clone $baseQuery)->count(),
                'active' => (clone $baseQuery)->where('status', 0)->count(),
                'inactive' => (clone $baseQuery)->where('status', 1)->count(),
            ];

            return view('modules.registers.index', compact('stats'));
        } catch (\Exception $e) {
            Log::error('Erro no RegisterController@index: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Erro interno ao carregar dados.'], 500);
            }
            abort(500, 'Erro interno do servidor.');
        }
    }

    public function show($id)
    {
        $register = Register::with('attachments')
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->findOrFail($id);
        
        $data = $register->toArray();
        // Ajuste para caminho solicitado: /uploads/registers/
        $data['photo_url'] = $register->photo ? asset('storage/uploads/registers/' . $register->photo) : null;
        
        $data['born_date_formatted'] = ($register->born_date && $register->born_date->format('d/m/Y') !== '01/01/0001') 
            ? $register->born_date->format('d/m/Y') . ' (' . $register->age . ' anos)' 
            : 'Não informado';
        
        // Mapeamento de Status Civil
        $civilStatusMap = [
            1 => 'Solteiro',
            2 => 'Casado',
            3 => 'União Estável',
            4 => 'Divorciado',
            5 => 'Separado',
            6 => 'Viúvo',
            7 => 'Não declarado'
        ];
        $data['civil_status_label'] = $civilStatusMap[$register->civil_status] ?? 'Não informado';
        $data['sexo_label'] = ($register->sexo == 1) ? 'Masculino' : (($register->sexo == 2) ? 'Feminino' : 'Não informado');

        // Anexos
        $data['attachments'] = $register->attachments->map(function($att) {
            $att->url = asset('uploads/anexos_registers/' . $att->filename);
            // Formatar tamanho
            $size = $att->size_bytes;
            if ($size < 1024) {
                $att->size_formatted = $size . ' B';
            } elseif ($size < 1048576) {
                $att->size_formatted = round($size / 1024, 2) . ' KB';
            } else {
                $att->size_formatted = round($size / 1048576, 2) . ' MB';
            }
            return $att;
        });

        return response()->json($data);
    }

    public function create()
    {
        return view('modules.registers.create');
    }

    public function store(Request $request)
    {
        // Validação
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'born_date' => 'required|date',
            'cpf' => 'nullable|string|max:14', 
            'rg' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'address_number' => 'nullable|string|max:20',
            'home_situation' => 'nullable|string|max:255', // Bairro (mapeado corretamente)
            'cep' => 'nullable|string|max:10',
            'sexo' => 'required|integer|in:1,2',
            'civil_status' => 'nullable|integer',
            'work_state' => 'nullable|integer',
            'race' => 'nullable|integer',
            'mother_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'familly_qntd' => 'nullable|integer',
            'photo' => 'nullable|image|max:2048',
        ]);

        $validated['paroquia_id'] = Auth::user()->paroquia_id;
        $validated['status'] = $request->input('status', 0); // Default Active
        
        // Calcular idade se data de nascimento presente
        if (!empty($validated['born_date'])) {
            $validated['age'] = \Carbon\Carbon::parse($validated['born_date'])->age;
        }

        // Upload de Foto
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('uploads/registers', 'public');
            $validated['photo'] = basename($path);
        }

        Register::create($validated);

        return redirect()->route('registers.index')->with('success', 'Registro criado com sucesso!');
    }

    public function edit($id)
    {
        $register = Register::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        return view('modules.registers.edit', compact('register'));
    }

    public function update(Request $request, $id)
    {
        $register = Register::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'born_date' => 'required|date',
            'cpf' => 'nullable|string|max:14',
            'rg' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'address_number' => 'nullable|string|max:20',
            'home_situation' => 'nullable|string|max:255', // Bairro (mapeado corretamente)
            'cep' => 'nullable|string|max:10',
            'sexo' => 'required|integer|in:1,2',
            'civil_status' => 'nullable|integer',
            'work_state' => 'nullable|integer',
            'race' => 'nullable|integer',
            'mother_name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'familly_qntd' => 'nullable|integer',
            'status' => 'required|boolean',
            'photo' => 'nullable|image|max:2048',
        ]);

        if (!empty($validated['born_date'])) {
            $validated['age'] = \Carbon\Carbon::parse($validated['born_date'])->age;
        }

        // Upload de Foto
        if ($request->hasFile('photo')) {
            // Deletar foto antiga se existir
            if ($register->photo) {
                Storage::disk('public')->delete('uploads/registers/' . $register->photo);
            }
            $path = $request->file('photo')->store('uploads/registers', 'public');
            $validated['photo'] = basename($path);
        }

        $register->update($validated);

        return redirect()->route('registers.index')->with('success', 'Registro atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $register = Register::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        
        // Deletar foto se existir
        if ($register->photo) {
            Storage::disk('public')->delete('uploads/registers/' . $register->photo);
        }

        $register->delete();

        return response()->json(['message' => 'Registro excluído com sucesso!']);
    }

    public function searchPeople(Request $request)
    {
        $search = $request->get('q');
        $query = Register::where('paroquia_id', Auth::user()->paroquia_id);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        $results = $query->limit(10)->get(['id', 'name', 'cpf']);
        return response()->json($results);
    }

    public function generatePdf(Request $request)
    {
        $user = Auth::user();
        $query = Register::where('paroquia_id', $user->paroquia_id);

        // Filtrar por IDs selecionados se houver
        if ($request->has('selected_ids') && !empty($request->selected_ids)) {
            $ids = explode(',', $request->selected_ids);
            $query->whereIn('id', $ids);
        }

        $registers = $query->get();
        $columns = $request->get('columns', ['name', 'email', 'phone']); // Default columns
        $paroquia = $user->paroquia;

        $pdf = PDF::loadView('modules.registers.pdf', compact('registers', 'columns', 'paroquia'));
        return $pdf->download('relatorio_registros.pdf');
    }
}
