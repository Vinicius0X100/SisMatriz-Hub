<?php

namespace App\Http\Controllers;

use App\Models\ReservaLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReservasLocaisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReservaLocal::where('paroquia_id', Auth::user()->paroquia_id);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $locais = $query->orderBy('name')->paginate(10);

        return view('modules.reservas-locais.index', compact('locais'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('modules.reservas-locais.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name']);
        $data['paroquia_id'] = Auth::user()->paroquia_id;

        if ($request->hasFile('foto')) {
            // Save to storage/app/public/uploads/spaces
            $path = $request->file('foto')->store('uploads/spaces', 'public');
            $data['foto'] = $path;
        }

        ReservaLocal::create($data);

        return redirect()->route('reservas-locais.index')->with('success', 'Local criado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $local = ReservaLocal::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();
        return view('modules.reservas-locais.edit', compact('local'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $local = ReservaLocal::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name']);

        if ($request->hasFile('foto')) {
            // Delete old photo if exists in storage
            if ($local->foto && Storage::disk('public')->exists($local->foto)) {
                Storage::disk('public')->delete($local->foto);
            }

            // Save new photo
            $path = $request->file('foto')->store('uploads/spaces', 'public');
            $data['foto'] = $path;
        }

        $local->update($data);

        return redirect()->route('reservas-locais.index')->with('success', 'Local atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $local = ReservaLocal::where('id', $id)->where('paroquia_id', Auth::user()->paroquia_id)->firstOrFail();

        if ($local->foto && Storage::disk('public')->exists($local->foto)) {
            Storage::disk('public')->delete($local->foto);
        }

        $local->delete();

        return redirect()->route('reservas-locais.index')->with('success', 'Local excluído com sucesso!');
    }
}
