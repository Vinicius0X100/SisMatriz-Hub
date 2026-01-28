<?php

namespace App\Http\Controllers;

use App\Models\Lembrete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LembreteController extends Controller
{
    public function index()
    {
        $active = Lembrete::where('usuario_id', Auth::id())
            ->where('status', 'ativo')
            ->orderBy('data_hora', 'asc')
            ->get();

        $completed = Lembrete::where('usuario_id', Auth::id())
            ->where('status', 'concluido')
            ->orderBy('data_hora', 'desc')
            ->take(20)
            ->get();

        return view('modules.lembretes.index', compact('active', 'completed'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'data_hora' => 'required|date',
            'repeat' => 'nullable|string',
            'pref_email' => 'boolean',
            'pref_sound' => 'boolean',
        ]);

        $lembrete = new Lembrete($validated);
        $lembrete->usuario_id = Auth::id();
        $lembrete->status = 'ativo';
        // Checkboxes return "1" or null, but validate boolean handles true/false/1/0/yes/no
        // We explicitly cast to ensure
        $lembrete->pref_email = $request->boolean('pref_email');
        $lembrete->pref_sound = $request->boolean('pref_sound');
        
        $lembrete->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'lembrete' => $lembrete]);
        }

        return redirect()->route('lembretes.index')->with('success', 'Lembrete criado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $lembrete = Lembrete::where('usuario_id', Auth::id())->findOrFail($id);

        if ($request->has('toggle_status')) {
            $lembrete->status = $lembrete->status === 'ativo' ? 'concluido' : 'ativo';
            $lembrete->save();
            return response()->json(['success' => true, 'status' => $lembrete->status]);
        }

        $validated = $request->validate([
            'descricao' => 'sometimes|required|string|max:255',
            'data_hora' => 'sometimes|required|date',
            'repeat' => 'nullable|string',
            'pref_email' => 'boolean',
            'pref_sound' => 'boolean',
        ]);

        $lembrete->fill($validated);
        if ($request->has('pref_email')) $lembrete->pref_email = $request->boolean('pref_email');
        if ($request->has('pref_sound')) $lembrete->pref_sound = $request->boolean('pref_sound');
        
        $lembrete->save();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'lembrete' => $lembrete]);
        }

        return redirect()->route('lembretes.index')->with('success', 'Lembrete atualizado!');
    }

    public function destroy($id)
    {
        $lembrete = Lembrete::where('usuario_id', Auth::id())->findOrFail($id);
        $lembrete->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('lembretes.index')->with('success', 'Lembrete excluído!');
    }

    public function snooze(Request $request, $id)
    {
        $lembrete = Lembrete::where('usuario_id', Auth::id())->findOrFail($id);
        
        $minutes = (int) $request->input('minutes', 60);
        
        // Update data_hora to now + minutes
        $lembrete->data_hora = now()->addMinutes($minutes);
        // Reset email sent if we snooze, so we get reminded again
        $lembrete->last_email_sent = null; 
        // Also ensure it is active
        $lembrete->status = 'ativo';
        
        $lembrete->save();
        
        return response()->json(['success' => true, 'new_time' => $lembrete->data_hora]);
    }

    public function checkDue()
    {
        // Check for reminders that are due within the last minute (to avoid playing sound repeatedly for old ones)
        // Or simply check for any active reminder that matches current minute.
        
        $now = now();
        $start = $now->copy()->startOfMinute();
        $end = $now->copy()->endOfMinute();

        $due = Lembrete::where('usuario_id', Auth::id())
            ->where('status', 'ativo')
            ->where('pref_sound', true)
            ->whereBetween('data_hora', [$start, $end])
            ->exists();

        return response()->json(['due' => $due]);
    }
}
