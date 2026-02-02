<?php

namespace App\Http\Controllers;

use App\Models\NotaFiscal;
use App\Models\Entidade;
use App\Http\Requests\StoreNotaFiscalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NotaFiscalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = NotaFiscal::where('paroquia_id', $user->paroquia_id);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero', 'like', "%{$search}%")
                  ->orWhere('emitente_nome', 'like', "%{$search}%")
                  ->orWhere('chave_acesso', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('entidade_id')) {
            $query->where('entidade_id', $request->entidade_id);
        }

        if ($request->filled('data_inicio')) {
            $query->whereDate('data_emissao', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->whereDate('data_emissao', '<=', $request->data_fim);
        }

        // Ordenação
        $sort_by = $request->get('sort_by', 'data_emissao');
        $sort_dir = $request->get('sort_dir', 'desc');
        $allowed_sorts = ['numero', 'tipo', 'emitente_nome', 'valor_total', 'data_emissao'];
        
        if (in_array($sort_by, $allowed_sorts)) {
            $query->orderBy($sort_by, $sort_dir);
        } else {
            $query->orderBy('data_emissao', 'desc');
        }

        $notas = $query->with(['entidade', 'user'])->paginate(15);

        if ($request->ajax()) {
            $notas->through(function ($nota) {
                return [
                    'id' => $nota->id,
                    'numero' => $nota->numero,
                    'tipo' => $nota->tipo,
                    'emitente_nome' => $nota->emitente_nome,
                    'entidade_nome' => $nota->entidade ? $nota->entidade->ent_name : 'Geral',
                    'valor_total_formatted' => 'R$ ' . number_format($nota->valor_total, 2, ',', '.'),
                    'data_emissao_formatted' => $nota->data_emissao->format('d/m/Y'),
                    'caminho_arquivo' => $nota->caminho_arquivo,
                    'has_arquivo' => !empty($nota->caminho_arquivo) && Storage::disk('public')->exists('uploads/notas_fiscais/' . $nota->caminho_arquivo),
                    'download_url' => route('notas-fiscais.download', $nota->id),
                    'edit_url' => route('notas-fiscais.edit', $nota->id),
                ];
            });
            return response()->json($notas);
        }
        
        $entidades = Entidade::where('paroquia_id', $user->paroquia_id)->get();

        return view('modules.notas-fiscais.index', compact('notas', 'entidades'));
    }

    public function create()
    {
        $user = Auth::user();
        $entidades = Entidade::where('paroquia_id', $user->paroquia_id)->get();
        return view('modules.notas-fiscais.create', compact('entidades'));
    }

    public function store(StoreNotaFiscalRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        
        $data['paroquia_id'] = $user->paroquia_id;
        $data['user_id'] = $user->id;

        // Upload do Arquivo
        if ($request->hasFile('arquivo')) {
            $file = $request->file('arquivo');
            $filename = time() . '_' . Str::slug($data['emitente_nome']) . '_' . $data['numero'] . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/notas_fiscais', $filename, 'public');
            $data['caminho_arquivo'] = $filename;
        }

        NotaFiscal::create($data);

        return redirect()->route('notas-fiscais.index')->with('success', 'Nota Fiscal cadastrada com sucesso!');
    }

    public function edit(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        return view('modules.notas-fiscais.edit', compact('notaFiscal', 'entidades'));
    }

    public function update(StoreNotaFiscalRequest $request, NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        $data = $request->validated();

        // Upload do Arquivo (Substituição)
        if ($request->hasFile('arquivo')) {
            // Remove arquivo antigo
            if ($notaFiscal->caminho_arquivo && Storage::disk('public')->exists('uploads/notas_fiscais/' . $notaFiscal->caminho_arquivo)) {
                Storage::disk('public')->delete('uploads/notas_fiscais/' . $notaFiscal->caminho_arquivo);
            }

            $file = $request->file('arquivo');
            $filename = time() . '_' . Str::slug($data['emitente_nome']) . '_' . $data['numero'] . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/notas_fiscais', $filename, 'public');
            $data['caminho_arquivo'] = $filename;
        }

        $notaFiscal->update($data);

        return redirect()->route('notas-fiscais.index')->with('success', 'Nota Fiscal atualizada com sucesso!');
    }

    public function destroy(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        if ($notaFiscal->caminho_arquivo && Storage::disk('public')->exists('uploads/notas_fiscais/' . $notaFiscal->caminho_arquivo)) {
            Storage::disk('public')->delete('uploads/notas_fiscais/' . $notaFiscal->caminho_arquivo);
        }

        $notaFiscal->delete();

        return redirect()->route('notas-fiscais.index')->with('success', 'Nota Fiscal excluída com sucesso!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = explode(',', $request->ids);
        
        $notas = NotaFiscal::whereIn('id', $ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->get();

        $count = 0;
        foreach ($notas as $nota) {
            if ($nota->caminho_arquivo && Storage::disk('public')->exists('uploads/notas_fiscais/' . $nota->caminho_arquivo)) {
                Storage::disk('public')->delete('uploads/notas_fiscais/' . $nota->caminho_arquivo);
            }
            $nota->delete();
            $count++;
        }

        return redirect()->route('notas-fiscais.index')->with('success', "$count notas fiscais excluídas com sucesso!");
    }

    public function download(NotaFiscal $notaFiscal)
    {
        if ($notaFiscal->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        if (!$notaFiscal->caminho_arquivo || !Storage::disk('public')->exists('uploads/notas_fiscais/' . $notaFiscal->caminho_arquivo)) {
            return back()->with('error', 'Arquivo não encontrado.');
        }

        return Storage::disk('public')->download('uploads/notas_fiscais/' . $notaFiscal->caminho_arquivo);
    }
}
