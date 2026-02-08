<?php

namespace App\Http\Controllers;

use App\Models\Protocol;
use App\Models\ProtocolFile;
use App\Models\ProtocolStatusNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProtocolController extends Controller
{
    public function index()
    {
        $protocols = Protocol::where('user_id', Auth::id())
            ->with('files')
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        return view('modules.protocols.index', compact('protocols'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:1000',
            'files' => 'nullable|array|max:10',
            'files.*' => 'file|max:10240', // 10MB max per file
        ]);

        $protocol = Protocol::create([
            'code' => 'PROTO-' . strtoupper(Str::random(12)),
            'user_id' => Auth::id(),
            'description' => $request->description,
            'paroquia_id' => Auth::user()->paroquia_id ?? 1, // Fallback to 1 if null, though it should be set
            'status' => 0, // Pending
            'message' => 'Protocolo criado e aguardando análise.',
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = 'file_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                // Store in storage/app/public/uploads/protocols
                $file->storeAs('uploads/protocols', $filename, 'public');

                ProtocolFile::create([
                    'protocol_id' => $protocol->id,
                    'file_name' => $filename,
                ]);
            }
        }

        return redirect()->route('protocols.index')->with('success', 'Protocolo criado com sucesso!');
    }

    public function markNotificationAsRead($id)
    {
        $notification = ProtocolStatusNotification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return redirect()->route('protocols.index')->with('info', 'Notificação marcada como lida. Verifique o status do seu protocolo.');
    }
}
