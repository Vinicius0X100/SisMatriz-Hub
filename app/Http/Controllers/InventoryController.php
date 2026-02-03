<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\InventoryPhoto;
use App\Models\CategoriaDoacao;
use App\Models\Entidade;
use App\Models\ReservaLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inventory::with(['categoria', 'comunidade', 'local', 'photos'])
            ->where('paroquia_id', Auth::user()->paroquia_id);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('item', 'asc')->paginate(10);

        return view('modules.inventory.index', compact('records'));
    }

    public function create()
    {
        $paroquiaId = Auth::user()->paroquia_id;
        $categorias = CategoriaDoacao::where('paroquia_id', $paroquiaId)->orderBy('name')->get();
        $comunidades = Entidade::where('paroquia_id', $paroquiaId)->orderBy('ent_name')->get();
        $locais = ReservaLocal::where('paroquia_id', $paroquiaId)->orderBy('name')->get();

        return view('modules.inventory.create', compact('categorias', 'comunidades', 'locais'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'item' => 'required|string|max:255',
                'category' => 'required|exists:categorias_doacao,id',
                'ent_id' => 'required|exists:entidades,ent_id',
                'sala_id' => 'required|exists:reservas_locais,id',
                'qntd_destributed' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $validated['paroquia_id'] = Auth::user()->paroquia_id;

            $inventory = Inventory::create($validated);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $filename = $photo->hashName();
                    $photo->storeAs('uploads/inventario', $filename, 'public');
                    
                    InventoryPhoto::create([
                        'i_id' => $inventory->i_id,
                        'filename' => $filename
                    ]);
                }
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Item criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar item: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $paroquiaId = Auth::user()->paroquia_id;
        $record = Inventory::where('paroquia_id', $paroquiaId)->findOrFail($id);
        
        $categorias = CategoriaDoacao::where('paroquia_id', $paroquiaId)->orderBy('name')->get();
        $comunidades = Entidade::where('paroquia_id', $paroquiaId)->orderBy('ent_name')->get();
        $locais = ReservaLocal::where('paroquia_id', $paroquiaId)->orderBy('name')->get();

        return view('modules.inventory.edit', compact('record', 'categorias', 'comunidades', 'locais'));
    }

    public function update(Request $request, $id)
    {
        try {
            $record = Inventory::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

            $validated = $request->validate([
                'item' => 'required|string|max:255',
                'category' => 'required|exists:categorias_doacao,id',
                'ent_id' => 'required|exists:entidades,ent_id',
                'sala_id' => 'required|exists:reservas_locais,id',
                'qntd_destributed' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $record->update($validated);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $filename = $photo->hashName();
                    $photo->storeAs('uploads/inventario', $filename, 'public');
                    
                    InventoryPhoto::create([
                        'i_id' => $record->i_id,
                        'filename' => $filename
                    ]);
                }
            }

            return redirect()->route('inventory.index')
                ->with('success', 'Item atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar item: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $record = Inventory::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        
        // Delete photos
        foreach ($record->photos as $photo) {
            Storage::disk('public')->delete('uploads/inventario/' . $photo->filename);
            $photo->delete();
        }

        $record->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Item excluído com sucesso!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids)) {
            return redirect()->route('inventory.index')
                ->with('error', 'Nenhum registro selecionado.');
        }

        $records = Inventory::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('i_id', $ids)
            ->get();

        foreach ($records as $record) {
            foreach ($record->photos as $photo) {
                Storage::disk('public')->delete('uploads/inventario/' . $photo->filename);
                $photo->delete();
            }
            $record->delete();
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Itens excluídos com sucesso!');
    }

    public function destroyPhoto($id)
    {
        // Find photo ensuring it belongs to user's parish via inventory item
        $photo = InventoryPhoto::whereHas('item', function($q) {
            $q->where('paroquia_id', Auth::user()->paroquia_id);
        })->findOrFail($id);

        Storage::disk('public')->delete('uploads/inventario/' . $photo->filename);
        $photo->delete();

        return back()->with('success', 'Foto removida com sucesso!');
    }
}
