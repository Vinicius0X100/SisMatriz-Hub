<?php

namespace App\Http\Controllers;

use App\Models\ReservaMatrimonio;
use App\Models\RegraMatrimonio;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarioMatrimonioController extends Controller
{
    public function index()
    {
        return view('modules.calendario-matrimonio.index');
    }

    public function events(Request $request)
    {
        $query = ReservaMatrimonio::where('paroquia_id', Auth::user()->paroquia_id);

        // Se houver parâmetros de data, filtra. Se não, retorna tudo (ou um padrão).
        if ($request->has(['start', 'end'])) {
            $start = Carbon::parse($request->start);
            $end = Carbon::parse($request->end);
            $query->whereBetween('data', [$start, $end]);
        }
        
        if ($request->ent_id) {
            $query->where('ent_id', $request->ent_id);
        }

        $events = $query->with('comunidade')->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->titulo,
                'start' => $event->data->format('Y-m-d') . 'T' . Carbon::parse($event->horario)->format('H:i:s'),
                'end' => $event->data->format('Y-m-d') . 'T' . Carbon::parse($event->horario)->addHour()->format('H:i:s'),
                'allDay' => false,
                'extendedProps' => [
                    'ent_id' => $event->ent_id,
                    'local' => $event->local,
                    'local_nome' => $event->comunidade->ent_name ?? $event->local ?? 'Local desconhecido',
                    'telefone_noivo' => $event->telefone_noivo,
                    'telefone_noiva' => $event->telefone_noiva,
                    'efeito_civil' => $event->efeito_civil,
                    'observacoes' => '',
                ],
                'backgroundColor' => $event->color ?? '#3788d8',
                'borderColor' => $event->color ?? '#3788d8',
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string',
            'data' => 'required|date',
            'horario' => 'required',
            'ent_id' => 'nullable|exists:entidades,ent_id',
            'local' => 'nullable|string',
        ]);

        if (!$request->ent_id && !$request->local) {
            return response()->json(['message' => 'Selecione uma comunidade ou informe um local.'], 422);
        }

        // Validação de Regras ignorada se force_save for true
        if ($request->ent_id && !$request->boolean('force_save')) {
            $date = Carbon::parse($request->data);
            $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) - 6 (Saturday)
            
            $rule = RegraMatrimonio::where('paroquia_id', Auth::user()->paroquia_id)
                ->where('comunidade_id', $request->ent_id)
                ->first();

            if ($rule) {
                // Validar dia da semana
                $allowedDays = explode(',', $rule->dias_permitidos ?? '');
                if (!in_array((string)$dayOfWeek, $allowedDays)) {
                    return response()->json(['message' => 'Casamentos não são permitidos neste dia da semana nesta comunidade.'], 422);
                }

                // Validar limite diário
                $count = ReservaMatrimonio::where('paroquia_id', Auth::user()->paroquia_id)
                    ->where('ent_id', $request->ent_id)
                    ->where('data', $request->data)
                    ->count();

                if ($count >= $rule->max_casamentos_por_dia) {
                    return response()->json(['message' => 'Limite de casamentos por dia excedido para esta comunidade.'], 422);
                }
            }
        }

        $reserva = ReservaMatrimonio::create([
            'titulo' => $request->titulo,
            'data' => $request->data,
            'horario' => $request->horario,
            'ent_id' => $request->ent_id,
            'local' => $request->local,
            'telefone_noivo' => $request->telefone_noivo,
            'telefone_noiva' => $request->telefone_noiva,
            'efeito_civil' => $request->efeito_civil ?? false,
            'color' => $request->color,
            'paroquia_id' => Auth::user()->paroquia_id,
        ]);

        return response()->json($reserva);
    }

    public function update(Request $request, $id)
    {
        $reserva = ReservaMatrimonio::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

        $request->validate([
            'titulo' => 'required|string',
            'data' => 'required|date',
            'horario' => 'required',
            'ent_id' => 'nullable|exists:entidades,ent_id',
            'local' => 'nullable|string',
        ]);

        if (!$request->ent_id && !$request->local) {
            return response()->json(['message' => 'Selecione uma comunidade ou informe um local.'], 422);
        }

        // Verificar regras se houve mudança relevante e se é em comunidade
        if ($request->ent_id && ($request->data != $reserva->data->format('Y-m-d') || $request->ent_id != $reserva->ent_id) && !$request->boolean('force_save')) {
            $date = Carbon::parse($request->data);
            $dayOfWeek = $date->dayOfWeek;
            
            $rule = RegraMatrimonio::where('paroquia_id', Auth::user()->paroquia_id)
                ->where('comunidade_id', $request->ent_id)
                ->first();

            if ($rule) {
                // Validar dia da semana
                $allowedDays = explode(',', $rule->dias_permitidos ?? '');
                if (!in_array((string)$dayOfWeek, $allowedDays)) {
                    return response()->json(['message' => 'Casamentos não são permitidos neste dia da semana nesta comunidade.'], 422);
                }

                // Validar limite diário (excluindo a própria reserva da contagem)
                $count = ReservaMatrimonio::where('paroquia_id', Auth::user()->paroquia_id)
                    ->where('ent_id', $request->ent_id)
                    ->where('data', $request->data)
                    ->where('id', '!=', $id)
                    ->count();

                if ($count >= $rule->max_casamentos_por_dia) {
                    return response()->json(['message' => 'Limite de casamentos por dia excedido para esta comunidade.'], 422);
                }
            }
        }
        
        $reserva->update([
            'titulo' => $request->titulo,
            'data' => $request->data,
            'horario' => $request->horario,
            'ent_id' => $request->ent_id,
            'local' => $request->local,
            'telefone_noivo' => $request->telefone_noivo,
            'telefone_noiva' => $request->telefone_noiva,
            'efeito_civil' => $request->efeito_civil ?? false,
            'color' => $request->color,
        ]);

        return response()->json($reserva);
    }

    public function destroy($id)
    {
        $reserva = ReservaMatrimonio::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $reserva->delete();

        return response()->json(['message' => 'Reserva excluída']);
    }

    // Regras
    public function getRules()
    {
        // Retorna todas as comunidades com suas regras (se houver)
        $comunidades = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->get()
            ->map(function ($comunidade) {
                $rule = RegraMatrimonio::where('comunidade_id', $comunidade->ent_id)->first();
                return [
                    'comunidade_id' => $comunidade->ent_id,
                    'nome' => $comunidade->ent_name,
                    'max_casamentos_por_dia' => $rule ? $rule->max_casamentos_por_dia : 0,
                    'dias_permitidos' => $rule ? explode(',', $rule->dias_permitidos) : [], // Array de strings/ints
                ];
            });

        return response()->json($comunidades);
    }

    public function saveRules(Request $request)
    {
        $request->validate([
            'regras' => 'required|array',
            'regras.*.comunidade_id' => 'required|integer',
            'regras.*.max_casamentos_por_dia' => 'required|integer|min:0',
            'regras.*.dias_permitidos' => 'nullable|array',
        ]);

        $rules = $request->input('regras');

        foreach ($rules as $r) {
            RegraMatrimonio::updateOrCreate(
                [
                    'paroquia_id' => Auth::user()->paroquia_id,
                    'comunidade_id' => $r['comunidade_id']
                ],
                [
                    'max_casamentos_por_dia' => $r['max_casamentos_por_dia'],
                    'dias_permitidos' => implode(',', $r['dias_permitidos'] ?? [])
                ]
            );
        }

        return response()->json(['message' => 'Regras atualizadas com sucesso']);
    }

    public function getLocais() {
        $locais = Entidade::where('paroquia_id', Auth::user()->paroquia_id)
            ->orderBy('ent_name')
            ->select('ent_id as id', 'ent_name as nome')
            ->get();
        return response()->json($locais);
    }
}
