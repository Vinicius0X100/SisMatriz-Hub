<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Lembrete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class EventoController extends Controller
{
    protected function ensureAccess()
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $allowedRules = [1, 111, 9, 10];
        if (!in_array((int) $user->rule, $allowedRules, true)) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $this->ensureAccess();

        $user = Auth::user();

        $query = Evento::where('paroquia_id', $user->paroquia_id)
            ->orderBy('date')
            ->orderBy('time');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $events = $query->get()->map(function ($event) use ($user) {
            $photoPath = $event->photo ? 'uploads/eventos/' . $event->photo : null;
            $exists = $photoPath && Storage::disk('public')->exists($photoPath);
            $event->photo_url = $exists ? asset('storage/' . $photoPath) : null;

            // Precompute lembrete linkage by matching description + date_hora
            $date = $event->date;
            $time = $event->time ?: '00:00:00';
            $dataHora = ($date ? ($date . ' ' . $time) : null);
            $descricao = 'Evento paroquial: ' . $event->title;
            $event->has_lembrete = false;
            if ($dataHora) {
                $event->has_lembrete = \App\Models\Lembrete::where('usuario_id', $user->id)
                    ->where('descricao', $descricao)
                    ->where('data_hora', $dataHora)
                    ->exists();
            }
            return $event;
        });

        return view('modules.eventos.index', compact('events'));
    }

    public function store(Request $request)
    {
        $this->ensureAccess();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'address' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:4096',
        ]);

        $validated['paroquia_id'] = Auth::user()->paroquia_id;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('uploads/eventos', 'public');
            $validated['photo'] = basename($path);
        }

        Evento::create($validated);

        return redirect()->route('eventos.index')->with('success', 'Evento criado com sucesso!');
    }

    public function destroy($id)
    {
        $this->ensureAccess();

        $user = Auth::user();

        $evento = Evento::where('paroquia_id', $user->paroquia_id)->findOrFail($id);

        if ($evento->photo) {
            Storage::disk('public')->delete('uploads/eventos/' . $evento->photo);
        }

        $evento->delete();

        return redirect()->route('eventos.index')->with('success', 'Evento excluído com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $this->ensureAccess();

        $user = Auth::user();
        $evento = Evento::where('paroquia_id', $user->paroquia_id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'time' => 'required',
            'address' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:4096',
        ]);

        // Handle photo update
        if ($request->hasFile('photo')) {
            if ($evento->photo) {
                Storage::disk('public')->delete('uploads/eventos/' . $evento->photo);
            }
            $path = $request->file('photo')->store('uploads/eventos', 'public');
            $validated['photo'] = basename($path);
        }

        $evento->update($validated);

        return redirect()->route('eventos.index')->with('success', 'Evento atualizado com sucesso!');
    }

    public function addToLembretes($id)
    {
        $this->ensureAccess();

        try {
            $user = Auth::user();
            $evento = Evento::where('paroquia_id', $user->paroquia_id)->findOrFail($id);

            $date = $evento->date;
            $time = $evento->time ?: '00:00:00';
            if (!$date) {
                return Response::json(['success' => false, 'message' => 'Evento sem data definida'], 422);
            }

            $dataHora = $date . ' ' . $time;
            $descricao = 'Evento paroquial: ' . $evento->title;

            // Toggle: if lembrete exists for this event and user, remove it; else create.
            $existing = Lembrete::where('usuario_id', $user->id)
                ->where('descricao', $descricao)
                ->where('data_hora', $dataHora)
                ->get();
            
            if ($existing->count() > 0) {
                foreach ($existing as $rem) {
                    $rem->delete();
                }
                return Response::json([
                    'success' => true,
                    'message' => 'Lembrete removido com sucesso!',
                    'action' => 'removed',
                ]);
            }

            $lembrete = new Lembrete();
            $lembrete->usuario_id = $user->id;
            $lembrete->descricao = $descricao;
            $lembrete->data_hora = $dataHora;
            $lembrete->status = 'ativo';
            $lembrete->repeat = 'none';
            $lembrete->pref_email = false;
            $lembrete->pref_sound = false;
            $lembrete->save();

            return Response::json([
                'success' => true,
                'message' => 'Lembrete criado com sucesso!',
                'lembrete_id' => $lembrete->id,
                'action' => 'added',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Erro ao criar lembrete de evento', [
                'user_id' => Auth::id(),
                'evento_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return Response::json(['success' => false, 'message' => 'Falha ao criar lembrete'], 500);
        }
    }
}
