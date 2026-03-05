<?php

namespace App\Http\Controllers;

use App\Models\VinWatched;
use App\Models\Register;
use App\Models\Entidade;
use App\Models\VicentinosRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VicentinoController extends Controller
{
    // Listagem
    public function index(Request $request)
    {
        $query = VinWatched::where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['entidade', 'sender'])
            ->orderBy('w_id', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('month')) {
            $query->where('month_entire', $request->input('month'));
        }

        if ($request->filled('kind')) {
            $query->where('kind', $request->input('kind'));
        }

        if ($request->filled('ent_id')) {
            $query->where('ent_id', $request->input('ent_id'));
        }

        $records = $query->paginate(15);
        
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        
        $stats = [
            'total' => VinWatched::where('paroquia_id', Auth::user()->paroquia_id)->count(),
            'assistidos' => VinWatched::where('paroquia_id', Auth::user()->paroquia_id)->where('kind', 1)->count(),
            'nao_assistidos' => VinWatched::where('paroquia_id', Auth::user()->paroquia_id)->where('kind', 0)->count(),
        ];

        return view('modules.vicentinos_apuracoes.index', compact('records', 'entidades', 'stats'));
    }

    // Formulário de Criação
    public function create()
    {
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        return view('modules.vicentinos_apuracoes.create', compact('entidades'));
    }

    // Salvar
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'ent_id' => 'required|exists:entidades,ent_id',
                'kind' => 'required|in:0,1',
                'month_entire' => 'required|integer|min:1|max:12',
                'address' => 'nullable|string',
                'address_number' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $data = $validated;
            $data['sendby'] = Auth::id(); // Alterado para enviar o ID do usuário
            $data['paroquia_id'] = Auth::user()->paroquia_id;
            $data['created_at'] = now();

            VinWatched::create($data);

            return redirect()->route('vicentinos-apuracoes.index')->with('success', 'Apuração registrada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao registrar apuração: ' . $e->getMessage());
        }
    }

    // Formulário de Edição
    public function edit($id)
    {
        $record = VinWatched::where('w_id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();
            
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        
        return view('modules.vicentinos_apuracoes.edit', compact('record', 'entidades'));
    }

    // Atualizar
    public function update(Request $request, $id)
    {
        try {
            $record = VinWatched::where('w_id', $id)
                ->where('paroquia_id', Auth::user()->paroquia_id)
                ->firstOrFail();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'ent_id' => 'required|exists:entidades,ent_id',
                'kind' => 'required|in:0,1',
                'month_entire' => 'required|integer|min:1|max:12',
                'address' => 'nullable|string',
                'address_number' => 'nullable|string',
                'description' => 'nullable|string',
            ]);

            $record->update($validated);

            return redirect()->route('vicentinos-apuracoes.index')->with('success', 'Apuração atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar apuração: ' . $e->getMessage());
        }
    }

    // Excluir
    public function destroy($id)
    {
        $record = VinWatched::where('w_id', $id)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->firstOrFail();

        $record->delete();

        return redirect()->route('vicentinos-apuracoes.index')->with('success', 'Registro excluído com sucesso.');
    }

    // Exclusão em massa
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:vin_watcheds,w_id'
        ]);

        $count = VinWatched::whereIn('w_id', $request->ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->delete();

        return response()->json(['message' => "$count registros excluídos com sucesso."]);
    }

    // Busca de Registros (AJAX) - VicentinosRecord
    public function searchVicentinosRecords(Request $request)
    {
        $search = trim($request->input('q'));
        
        $query = VicentinosRecord::where('paroquia_id', Auth::user()->paroquia_id);
        
        if (strlen($search) >= 3) {
            // Verifica se é CPF (apenas números ou formato de CPF)
            $isCpf = preg_match('/^[\d.-]+$/', $search);
            
            if ($isCpf) {
                // Remove caracteres não numéricos para busca flexível
                $cleanCpf = preg_replace('/\D/', '', $search);
                $query->where('cpf', 'like', "%{$cleanCpf}%");
            } else {
                // Busca por nome com tokens (palavras separadas)
                $tokens = array_filter(explode(' ', $search), function($token) {
                    return strlen($token) >= 2; // Ignora conectivos muito curtos (e, da, de)
                });
                
                if (count($tokens) > 0) {
                    $query->where(function($q) use ($tokens) {
                        // Grupo para garantir que corresponda a TODOS os tokens (AND)
                        // Isso resolve "João Silva" encontrando "João da Silva"
                        $q->where(function($subQ) use ($tokens) {
                            foreach ($tokens as $token) {
                                $subQ->where('responsavel_nome', 'like', "%{$token}%");
                            }
                        });

                        // Opcional: Adicionar Soundex para o primeiro termo se for busca simples
                        // Isso ajuda em erros de digitação (ex: "Viniscius" -> "Vinicius")
                        // MySQL SOUNDEX funciona melhor com palavras simples
                        if (count($tokens) === 1) {
                            $firstToken = reset($tokens);
                            // SOUNDEX pode não ser perfeito, mas ajuda como fallback
                            // Usamos orWhere para não restringir demais
                            // Adicionamos verificação para garantir que o termo não seja muito curto
                            if (strlen($firstToken) > 3) {
                                $q->orWhereRaw('SOUNDEX(responsavel_nome) LIKE CONCAT(SOUNDEX(?), "%")', [$firstToken]);
                            }
                        }
                    });
                } else {
                    // Fallback para string original se não gerou tokens válidos
                    $query->where('responsavel_nome', 'like', "%{$search}%");
                }
            }
        }
        
        $records = $query->orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'responsavel_nome', 'endereco', 'endereco_numero', 'bairro', 'cidade']);

        // Transformar para formato padrão
        $results = $records->map(function($record) {
            return [
                'id' => $record->id,
                'name' => $record->responsavel_nome,
                'address' => $record->endereco,
                'address_number' => $record->endereco_numero,
                'full_address' => $record->endereco . ($record->endereco_numero ? ', ' . $record->endereco_numero : '') . ($record->bairro ? ' - ' . $record->bairro : ''),
            ];
        });

        return response()->json($results);
    }

    // Busca de Registros (AJAX) - Register (Legado, mantido por compatibilidade)
    public function searchRegisters(Request $request)
    {
        $search = $request->input('q');
        if (strlen($search) < 3) {
            return response()->json([]);
        }

        $registers = Register::where('paroquia_id', Auth::user()->paroquia_id)
            ->where('name', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'address', 'address_number']);

        return response()->json($registers);
    }
}
