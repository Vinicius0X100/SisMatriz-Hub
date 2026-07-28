<?php

namespace App\Http\Controllers;

use App\Models\EscalaAcolitoRegra;
use App\Models\Entidade;
use App\Models\AcolitoFuncao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcolitoEscalaRegraController extends Controller
{
    /**
     * Display a listing of the rules, grouped by community.
     */
    public function index(Request $request)
    {
        $paroquia_id = Auth::user()->paroquia_id;
        
        $entidades = Entidade::where('paroquia_id', $paroquia_id)->orderBy('ent_name')->get();
        $funcoes = AcolitoFuncao::where('paroquia_id', $paroquia_id)->orderBy('title')->get();

        $selected_ent_id = $request->get('ent_id', $entidades->first()->ent_id ?? null);
        
        $regras = [];
        if ($selected_ent_id) {
            $regras = EscalaAcolitoRegra::where('ent_id', $selected_ent_id)
                ->where('paroquia_id', $paroquia_id)
                ->get()
                ->keyBy('dia_semana');
        }

        // 1: Segunda, 2: Terça, ..., 7: Domingo
        $dias_semana = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        return view('modules.acolitos.regras.index', compact('entidades', 'selected_ent_id', 'regras', 'dias_semana', 'funcoes'));
    }

    /**
     * Store or update rules for a specific community.
     */
    public function store(Request $request)
    {
        if (Auth::user()->rule == 8) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'ent_id' => 'required|exists:entidades,ent_id',
            'regras' => 'required|array',
            'regras.*.min_acolitos' => 'required|integer|min:0',
            'regras.*.max_acolitos' => 'required|integer|min:0',
            'regras.*.min_coroinhas' => 'required|integer|min:0',
            'regras.*.max_coroinhas' => 'required|integer|min:0',
            'regras.*.max_serves_per_month' => 'required|integer|min:0',
            'regras.*.coroinha_funcao_id' => 'nullable|exists:acolitos_funcoes,f_id',
        ]);

        $paroquia_id = Auth::user()->paroquia_id;

        foreach ($request->regras as $dia_semana => $dados) {
            EscalaAcolitoRegra::updateOrCreate(
                [
                    'ent_id' => $request->ent_id,
                    'dia_semana' => $dia_semana,
                    'paroquia_id' => $paroquia_id
                ],
                [
                    'min_acolitos' => $dados['min_acolitos'],
                    'max_acolitos' => $dados['max_acolitos'],
                    'min_coroinhas' => $dados['min_coroinhas'],
                    'max_coroinhas' => $dados['max_coroinhas'],
                    'max_serves_per_month' => $dados['max_serves_per_month'],
                    'coroinha_funcao_id' => $dados['coroinha_funcao_id'] ?? null,
                ]
            );
        }

        return redirect()->route('acolitos.escalas.regras.index', ['ent_id' => $request->ent_id])
                         ->with('success', 'Regras salvas com sucesso!');
    }
}
