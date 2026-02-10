<?php

namespace App\Http\Controllers;

use App\Models\SocialAssistant;
use App\Models\SocialAssistantImage;
use App\Models\Entidade;
use App\Models\CategoriaDoacao;
use App\Models\ReservaLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class EstoqueController extends Controller
{
    public function index(Request $request)
    {
        $query = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['categoria', 'images', 'entidade', 'sala']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('ent_id')) {
            $query->where('ent_id', $request->ent_id);
        }

        if ($request->filled('sala_id')) {
            $query->where('sala_id', $request->sala_id);
        }

        if ($request->filled('type')) {
             $query->where('type', $request->type);
        }

        $items = $query->orderBy('last_update', 'desc')->paginate(15);

        // Dados para filtros
        $categorias = CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id)->get();
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        $locais = ReservaLocal::where('paroquia_id', Auth::user()->paroquia_id)->get();
        
        $stats = [
            'total' => SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)->count()
        ];

        return view('modules.estoque.index', compact('items', 'categorias', 'entidades', 'locais', 'stats'));
    }

    public function create()
    {
        $categorias = CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id)->get();
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        $locais = ReservaLocal::where('paroquia_id', Auth::user()->paroquia_id)->get();

        return view('modules.estoque.create', compact('categorias', 'entidades', 'locais'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'type' => 'required|string',
            'category' => 'required|integer',
            'ent_id' => 'nullable|integer',
            'sala_id' => 'nullable|integer',
            'qntd_destributed' => 'required|integer',
            'images.*' => 'nullable|image|max:10240', // 10MB
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->only([
                'type', 'category', 'ent_id', 'sala_id', 'description', 
                'qntd_destributed'
            ]);
            
            $data['paroquia_id'] = Auth::user()->paroquia_id;
            $data['last_update'] = now();
            $data['qntd_anterior'] = 0;

            $item = SocialAssistant::create($data);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('uploads/estoque', 'public');
                    
                    SocialAssistantImage::create([
                        'social_assistant_id' => $item->s_id,
                        'filename' => basename($path),
                        'original_filename' => $file->getClientOriginalName(),
                    ]);
                }
            }
        });

        return redirect()->route('estoque.index')->with('success', 'Item registrado com sucesso!');
    }

    public function edit($id)
    {
        $item = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $categorias = CategoriaDoacao::where('paroquia_id', Auth::user()->paroquia_id)->get();
        $entidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)->get();
        $locais = ReservaLocal::where('paroquia_id', Auth::user()->paroquia_id)->get();

        return view('modules.estoque.edit', compact('item', 'categorias', 'entidades', 'locais'));
    }

    public function update(Request $request, $id)
    {
        $item = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        $request->validate([
            'description' => 'required|string|max:255',
            'type' => 'required|string',
            'category' => 'required|integer',
            'ent_id' => 'nullable|integer',
            'sala_id' => 'nullable|integer',
            'qntd_destributed' => 'required|integer',
            'images.*' => 'nullable|image|max:10240',
        ]);

        DB::transaction(function () use ($request, $item) {
            $data = $request->only([
                'type', 'category', 'ent_id', 'sala_id', 'description', 
                'qntd_destributed'
            ]);
            
            $data['last_update'] = now();
            $data['qntd_anterior'] = $item->qntd_destributed;

            $item->update($data);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('uploads/estoque', 'public');
                    
                    SocialAssistantImage::create([
                        'social_assistant_id' => $item->s_id,
                        'filename' => basename($path),
                        'original_filename' => $file->getClientOriginalName(),
                    ]);
                }
            }
        });

        return redirect()->route('estoque.index')->with('success', 'Item atualizado com sucesso!');
    }

    public function show($id)
    {
        $item = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['categoria', 'images', 'entidade', 'sala'])
            ->findOrFail($id);

        return view('modules.estoque.show', compact('item'));
    }

    public function generatePdf(Request $request)
    {
        $query = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)
            ->with(['categoria', 'images', 'entidade', 'sala']);

        // Filtro por IDs selecionados (prioridade)
        if ($request->has('ids') && !empty($request->ids)) {
            $ids = explode(',', $request->ids);
            $query->whereIn('s_id', $ids);
            $filters_label = "Itens Selecionados (" . count($ids) . ")";
        } else {
            // Filtros gerais
            $filters = [];
            
            if ($request->filled('category')) {
                $query->where('category', $request->category);
                $catName = CategoriaDoacao::find($request->category)->name ?? 'N/A';
                $filters[] = "Categoria: $catName";
            }
            
            if ($request->filled('ent_id')) {
                $query->where('ent_id', $request->ent_id);
                $entName = Entidade::find($request->ent_id)->ent_name ?? 'N/A';
                $filters[] = "Comunidade: $entName";
            }

            if ($request->filled('sala_id')) {
                $query->where('sala_id', $request->sala_id);
                $salaName = ReservaLocal::find($request->sala_id)->name ?? 'N/A';
                $filters[] = "Sala: $salaName";
            }

            $filters_label = count($filters) > 0 ? implode(' | ', $filters) : 'Todos os registros';
        }

        $items = $query->orderBy('description', 'asc')->get();

        $pdf = Pdf::loadView('modules.estoque.pdf', compact('items', 'filters_label'));
        
        return $pdf->stream('relatorio_estoque_' . date('YmdHis') . '.pdf');
    }

    public function destroy($id)
    {
        $item = SocialAssistant::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        
        foreach ($item->images as $img) {
            Storage::disk('public')->delete('uploads/estoque/' . $img->filename);
            $img->delete();
        }

        $item->delete();

        return redirect()->route('estoque.index')->with('success', 'Item excluído com sucesso!');
    }
    
    public function bulkDestroy(Request $request)
    {
        $ids = explode(',', $request->ids);
        
        $items = SocialAssistant::whereIn('s_id', $ids)
            ->where('paroquia_id', Auth::user()->paroquia_id)
            ->get();

        $count = 0;
        foreach ($items as $item) {
            foreach ($item->images as $img) {
                Storage::disk('public')->delete('uploads/estoque/' . $img->filename);
                $img->delete();
            }
            $item->delete();
            $count++;
        }

        return redirect()->route('estoque.index')->with('success', "$count itens excluídos com sucesso!");
    }
    
    public function deleteImage($id)
    {
        $image = SocialAssistantImage::findOrFail($id);
        
        if ($image->socialAssistant->paroquia_id != Auth::user()->paroquia_id) {
            abort(403);
        }

        Storage::disk('public')->delete('uploads/estoque/' . $image->filename);
        $image->delete();

        return back()->with('success', 'Imagem removida.');
    }
}
