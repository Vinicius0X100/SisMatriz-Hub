<?php

namespace App\Http\Controllers;

use App\Models\Protocol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminProtocolController extends Controller
{
    public function index(Request $request)
    {
        $query = Protocol::with(['user', 'files'])->orderBy('created_at', 'desc');

        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== null) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $protocols = $query->paginate(10);

        return view('modules.protocols.admin.index', compact('protocols'));
    }

    public function show($id)
    {
        $protocol = Protocol::with(['user', 'files'])->findOrFail($id);
        return response()->json($protocol);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3',
            'message' => 'nullable|string|max:1000',
        ]);

        $protocol = Protocol::findOrFail($id);
        
        $protocol->update([
            'status' => $request->status,
            'message' => $request->message,
        ]);

        return redirect()->back()->with('success', 'Protocolo atualizado com sucesso!');
    }
}
