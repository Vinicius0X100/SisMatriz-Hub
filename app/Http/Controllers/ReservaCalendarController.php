<?php

namespace App\Http\Controllers;

use App\Models\ReservaCalendar;
use App\Models\ReservaLocal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReservaCalendarController extends Controller
{
    /**
     * Display the calendar view.
     */
    public function view()
    {
        return view('modules.reservas-calendar.index');
    }

    /**
     * Display a listing of the resource (JSON API).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $start = $request->query('start');
        $end = $request->query('end');

        $query = ReservaCalendar::where('paroquia_id', $user->paroquia_id);

        if ($start && $end) {
            $query->whereBetween('data', [$start, $end]);
        }

        $reservas = $query->with('localModel')->get();

        // Transform for frontend if needed, or send as is
        // Frontend likely expects: { title, start, end, allDay?, resource? }
        // We will send raw data and map in frontend.

        return response()->json([
            'events' => $reservas,
            'holidays' => $this->getHolidays($start ? Carbon::parse($start)->year : date('Y')),
        ]);
    }

    /**
     * Get available locations
     */
    public function getLocais()
    {
        $user = Auth::user();
        $locais = ReservaLocal::where('paroquia_id', $user->paroquia_id)->get();
        return response()->json($locais);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'data' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'required|after:hora_inicio',
            'descricao' => 'required|string',
            'local' => 'nullable|exists:reservas_locais,id',
            'responsavel' => 'nullable|string',
            'color' => 'nullable|string',
        ]);

        if ($this->checkOverlap($request, $user->paroquia_id)) {
            return response()->json(['message' => 'Já existe um agendamento para este horário e local.'], 422);
        }

        $reserva = ReservaCalendar::create([
            'data' => $request->data,
            'hora_inicio' => $request->hora_inicio,
            'hora_fim' => $request->hora_fim,
            'descricao' => $request->descricao,
            'local' => $request->local,
            'responsavel' => $request->responsavel,
            'observacoes' => $request->observacoes,
            'color' => $request->color ?? '#3788d8',
            'paroquia_id' => $user->paroquia_id,
        ]);

        return response()->json($reserva, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reserva = ReservaCalendar::findOrFail($id);
        if ($reserva->paroquia_id !== Auth::user()->paroquia_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($reserva);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $reserva = ReservaCalendar::findOrFail($id);

        if ($reserva->paroquia_id !== $user->paroquia_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fim' => 'required|after:hora_inicio',
            'descricao' => 'required|string',
            'local' => 'nullable|exists:reservas_locais,id',
            'responsavel' => 'nullable|string',
        ]);

        if ($this->checkOverlap($request, $user->paroquia_id, $id)) {
            return response()->json(['message' => 'Já existe um agendamento para este horário e local.'], 422);
        }

        $reserva->update($request->all());

        return response()->json($reserva);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reserva = ReservaCalendar::findOrFail($id);
        if ($reserva->paroquia_id !== Auth::user()->paroquia_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $reserva->delete();
        return response()->json(['message' => 'Reserva removida']);
    }

    private function checkOverlap($request, $paroquiaId, $ignoreId = null)
    {
        // Only check overlap if local is specified?
        // Or if local is NOT specified, does it block everything?
        // Usually, if a room is booked, you can't book it.
        // If "local" is null, maybe it's a general event that doesn't block a room?
        // Or maybe "local" is required? Screenshot shows "Local" dropdown.
        // Let's assume if Local is set, we check against that Local.
        // If Local is NOT set, we might assume it doesn't conflict with physical spaces, 
        // OR it conflicts with other "no-location" events?
        // Let's enforce check on Local if provided.
        
        if (!$request->local) {
            return false; // Assuming no location means no conflict logic needed (or user can manage)
        }

        $query = ReservaCalendar::where('data', $request->data)
            ->where('paroquia_id', $paroquiaId)
            ->where('local', $request->local)
            ->where(function ($q) use ($request) {
                $q->where('hora_inicio', '<', $request->hora_fim)
                  ->where('hora_fim', '>', $request->hora_inicio);
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function getHolidays($year)
    {
        // Static list of main Catholic/BR holidays
        // In a real app, this might come from a DB or API
        $holidays = [
            ['date' => "$year-01-01", 'title' => 'Confraternização Universal', 'type' => 'feriado'],
            ['date' => "$year-04-21", 'title' => 'Tiradentes', 'type' => 'feriado'],
            ['date' => "$year-05-01", 'title' => 'Dia do Trabalho', 'type' => 'feriado'],
            ['date' => "$year-09-07", 'title' => 'Independência do Brasil', 'type' => 'feriado'],
            ['date' => "$year-10-12", 'title' => 'Nossa Senhora Aparecida', 'type' => 'catolico'],
            ['date' => "$year-11-02", 'title' => 'Finados', 'type' => 'catolico'],
            ['date' => "$year-11-15", 'title' => 'Proclamação da República', 'type' => 'feriado'],
            ['date' => "$year-12-25", 'title' => 'Natal', 'type' => 'catolico'],
            ['date' => "$year-12-08", 'title' => 'Imaculada Conceição', 'type' => 'catolico'],
        ];

        // Mobile holidays like Easter, Corpus Christi require calculation
        $easter = Carbon::createFromDate($year, 3, 21)->addDays(easter_days($year));
        $holidays[] = ['date' => $easter->format('Y-m-d'), 'title' => 'Páscoa', 'type' => 'catolico'];
        $holidays[] = ['date' => $easter->copy()->subDays(2)->format('Y-m-d'), 'title' => 'Sexta-feira Santa', 'type' => 'catolico'];
        $holidays[] = ['date' => $easter->copy()->addDays(60)->format('Y-m-d'), 'title' => 'Corpus Christi', 'type' => 'catolico'];
        $holidays[] = ['date' => $easter->copy()->subDays(47)->format('Y-m-d'), 'title' => 'Carnaval', 'type' => 'optional'];

        return $holidays;
    }
}
