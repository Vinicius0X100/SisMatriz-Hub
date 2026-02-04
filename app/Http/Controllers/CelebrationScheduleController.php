<?php

namespace App\Http\Controllers;

use App\Models\CelebrationSchedule;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CelebrationScheduleController extends Controller
{
    public function index(Request $request)
    {
        $query = CelebrationSchedule::with('comunidade')
            ->where('paroquia_id', Auth::user()->paroquia_id);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('comunidade', function($sq) use ($search) {
                    $sq->where('ent_name', 'like', "%{$search}%");
                })
                ->orWhere('dia_semana', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('dia_semana', 'asc')
                         ->orderBy('horario', 'asc')
                         ->paginate(10);

        return view('modules.celebration-schedules.index', compact('records'));
    }

    public function create()
    {
        $paroquiaId = Auth::user()->paroquia_id;
        $comunidades = Entidade::where('paroquia_id', $paroquiaId)->orderBy('ent_name')->get();
        
        $diasSemana = [
            'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 
            'Quinta-feira', 'Sexta-feira', 'Sábado'
        ];

        return view('modules.celebration-schedules.create', compact('comunidades', 'diasSemana'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'ent_id' => 'required|exists:entidades,ent_id',
                'dia_semana' => 'required|string|max:50',
                'horario' => 'required',
            ]);

            $validated['paroquia_id'] = Auth::user()->paroquia_id;

            CelebrationSchedule::create($validated);

            return redirect()->route('celebration-schedules.index')
                ->with('success', 'Horário de celebração criado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao criar horário: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $paroquiaId = Auth::user()->paroquia_id;
        $record = CelebrationSchedule::where('paroquia_id', $paroquiaId)->findOrFail($id);
        $comunidades = Entidade::where('paroquia_id', $paroquiaId)->orderBy('ent_name')->get();
        
        $diasSemana = [
            'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 
            'Quinta-feira', 'Sexta-feira', 'Sábado'
        ];

        return view('modules.celebration-schedules.edit', compact('record', 'comunidades', 'diasSemana'));
    }

    public function update(Request $request, $id)
    {
        try {
            $record = CelebrationSchedule::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);

            $validated = $request->validate([
                'ent_id' => 'required|exists:entidades,ent_id',
                'dia_semana' => 'required|string|max:50',
                'horario' => 'required',
            ]);

            $record->update($validated);

            return redirect()->route('celebration-schedules.index')
                ->with('success', 'Horário de celebração atualizado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar horário: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $record = CelebrationSchedule::where('paroquia_id', Auth::user()->paroquia_id)->findOrFail($id);
        $record->delete();

        return redirect()->route('celebration-schedules.index')
            ->with('success', 'Horário de celebração excluído com sucesso!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids');

        if (empty($ids)) {
            return redirect()->route('celebration-schedules.index')
                ->with('error', 'Nenhum registro selecionado.');
        }

        CelebrationSchedule::where('paroquia_id', Auth::user()->paroquia_id)
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->route('celebration-schedules.index')
            ->with('success', 'Horários excluídos com sucesso!');
    }
}
