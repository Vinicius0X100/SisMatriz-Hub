<?php

namespace App\Http\Controllers;

use App\Models\EscalaAcolitoRegra;
use App\Models\Entidade;
use App\Models\AcolitoFuncao;
use App\Models\CelebrationSchedule;
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
        $dias_semana_full = [
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
            7 => 'Domingo'
        ];

        $dias_semana = [];
        if ($selected_ent_id) {
            $diasComMissa = CelebrationSchedule::where('ent_id', $selected_ent_id)
                ->where('paroquia_id', $paroquia_id)
                ->pluck('dia_semana')
                ->unique()
                ->toArray();
                
            foreach ($dias_semana_full as $id => $nome) {
                if (in_array($nome, $diasComMissa)) {
                    $dias_semana[$id] = $nome;
                }
            }
        } else {
            $dias_semana = $dias_semana_full;
        }

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

    /**
     * Display composition rules for individual acolytes.
     */
    public function composicao(Request $request)
    {
        $paroquia_id = Auth::user()->paroquia_id;
        
        $entidades = Entidade::where('paroquia_id', $paroquia_id)->orderBy('ent_name')->get();
        $selected_ent_id = $request->get('ent_id', $entidades->first()->ent_id ?? null);
        
        $acolitos = [];
        $selected_acolito_id = $request->get('acolito_id');
        $regras_composicao = collect();

        if ($selected_ent_id) {
            $acolitos = \App\Models\Acolito::where('paroquia_id', $paroquia_id)
                ->where('ent_id', $selected_ent_id)
                ->where('status', 0) // Ativos
                ->orderBy('name')
                ->get();
        }

        if ($selected_acolito_id) {
            $regras_composicao = \App\Models\AcolitoRegraComposicao::where('paroquia_id', $paroquia_id)
                ->where('acolito_id', $selected_acolito_id)
                ->get()
                ->keyBy('tipo_regra');
        }

        return view('modules.acolitos.regras.composicao', compact(
            'entidades', 'selected_ent_id', 'acolitos', 'selected_acolito_id', 'regras_composicao'
        ));
    }

    /**
     * Store composition rules for an acolyte.
     */
    public function storeComposicao(Request $request, \App\Services\AcolitoTeamCompositionService $compositionService)
    {
        if (Auth::user()->rule == 8) {
            abort(403, 'Acesso não autorizado.');
        }

        $request->validate([
            'acolito_id' => 'required|exists:acolitos,id',
            'ent_id' => 'required|exists:entidades,ent_id',
        ]);

        $paroquia_id = Auth::user()->paroquia_id;
        $acolito_id = $request->acolito_id;

        // Limpar regras ativas antigas para recriar a nova versão unificada
        \App\Models\AcolitoRegraComposicao::where('paroquia_id', $paroquia_id)
            ->where('acolito_id', $acolito_id)
            ->update(['ativo' => false]);

        // Process Incompatibilidade
        if ($request->filled('incompatibilidade_ids')) {
            $compositionService->salvarRegra(
                $paroquia_id, $acolito_id, 'incompatibilidade', 
                ['acolitos_incompativeis' => $request->incompatibilidade_ids]
            );
        }

        // Process Acompanhamento Unificado
        $minimo = (int) $request->input('minimo_acompanhantes', 0);
        $especificos = $request->input('companhia_ids', []);
        
        if ($minimo > 0 || !empty($especificos)) {
            $categoria = $request->input('acompanhamento_categoria', 'qualquer');
            $modo = $request->input('companhia_modo', 'qualquer');

            $configuracao = [
                'minimo_acompanhantes' => $minimo,
                'categoria' => $categoria,
                'acompanhantes_especificos' => $especificos,
                'modo' => $modo
            ];

            $compositionService->salvarRegra(
                $paroquia_id, $acolito_id, 'acompanhamento', $configuracao
            );
        }

        return redirect()->route('acolitos.escalas.regras.composicao', [
            'ent_id' => $request->ent_id,
            'acolito_id' => $acolito_id
        ])->with('success', 'Regras de composição salvas com sucesso!');
    }
}
