<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Acolito;
use App\Models\AcolitoFuncao;
use App\Models\Escala;
use App\Models\EscalaDataHora;
use App\Models\EscaladoData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcolitoApiController extends Controller
{
    /**
     * Roles com acesso ao módulo de Acólitos.
     *  6  = Coordenador - Acólitos
     *  8  = Acólito
     *  1  = Administrador Geral
     * 111 = Administrador do Sistema
     */
    private const ROLES_PERMITIDAS = ['1', '111', '6', '8'];

    /**
     * Verifica se o usuário logado tem permissão para acessar o módulo.
     */
    private function verificarPermissao(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasAnyRole(self::ROLES_PERMITIDAS);
    }

    // -------------------------------------------------------------------------
    // GET /api/acolitos/escalas
    // -------------------------------------------------------------------------

    /**
     * Retorna a lista paginada de escalas da paróquia.
     * Equivalente à tela web: /acolitos/escalas (index)
     *
     * Query params opcionais:
     *   ?search=<texto>   Filtra por mês, ano ou igreja
     *   ?per_page=<n>     Itens por página (padrão: 15)
     */
    public function getEscalas(Request $request)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = Escala::where('paroquia_id', $user->paroquia_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('month', 'like', "%{$search}%")
                  ->orWhere('year', 'like', "%{$search}%")
                  ->orWhere('church', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 15);
        $escalas = $query->paginate($perPage);

        // Mapeia para um payload limpo
        $escalas->getCollection()->transform(function ($escala) {
            return [
                'id'            => $escala->es_id,
                'month'         => $escala->month,
                'year'          => $escala->year,
                'church'        => $escala->church,
                'send_date'     => $escala->send_date?->format('Y-m-d'),
                'qntd_acolitos' => $escala->qntd_acolitos,
                'situation'     => $escala->situation, // 0 = Aberta, 1 = Fechada
            ];
        });

        return response()->json($escalas);
    }

    // -------------------------------------------------------------------------
    // GET /api/acolitos/escalas/{id}
    // -------------------------------------------------------------------------

    /**
     * Retorna os detalhes completos de uma escala, incluindo todas as
     * celebrações com os acólitos escalados e suas funções.
     * Equivalente à tela web: /acolitos/escalas/{id}/manage
     */
    public function getEscalaDetalhe(Request $request, $id)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $escala = Escala::where('es_id', $id)
            ->where('paroquia_id', $user->paroquia_id)
            ->first();

        if (!$escala) {
            return response()->json(['error' => 'Escala não encontrada.'], 404);
        }

        $celebracoes = EscalaDataHora::where('es_id', $escala->es_id)
            ->with(['escalados.acolito.user', 'escalados.funcao', 'entidade'])
            ->orderBy('data')
            ->orderBy('hora')
            ->get()
            ->map(function ($item) use ($escala) {
                return $this->formatarCelebracao($item, $escala);
            });

        return response()->json([
            'escala'      => [
                'id'            => $escala->es_id,
                'month'         => $escala->month,
                'year'          => $escala->year,
                'church'        => $escala->church,
                'send_date'     => $escala->send_date?->format('Y-m-d'),
                'qntd_acolitos' => $escala->qntd_acolitos,
                'situation'     => $escala->situation,
            ],
            'celebracoes' => $celebracoes,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/acolitos/escalas/{id}/celebracoes
    // -------------------------------------------------------------------------

    /**
     * Retorna somente as celebrações de uma escala com acólitos e funções.
     * Suporta filtro por dia específico.
     *
     * Query params opcionais:
     *   ?dia=<1-31>   Filtra celebrações de um dia específico do mês
     */
    public function getCelebracoes(Request $request, $id)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $escala = Escala::where('es_id', $id)
            ->where('paroquia_id', $user->paroquia_id)
            ->first();

        if (!$escala) {
            return response()->json(['error' => 'Escala não encontrada.'], 404);
        }

        $query = EscalaDataHora::where('es_id', $escala->es_id)
            ->with(['escalados.acolito.user', 'escalados.funcao', 'entidade'])
            ->orderBy('data')
            ->orderBy('hora');

        if ($request->filled('dia')) {
            $query->where('data', $request->input('dia'));
        }

        $celebracoes = $query->get()->map(function ($item) use ($escala) {
            return $this->formatarCelebracao($item, $escala);
        });

        return response()->json([
            'escala_id'   => $escala->es_id,
            'month'       => $escala->month,
            'year'        => $escala->year,
            'celebracoes' => $celebracoes,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/acolitos/meus-servicos
    // -------------------------------------------------------------------------

    /**
     * Retorna as celebrações em que o usuário logado está escalado.
     * Funciona apenas para usuários com um acólito vinculado (user_id na tabela acolitos).
     *
     * Query params opcionais:
     *   ?apenas_futuros=true   Retorna apenas celebrações a partir de hoje (padrão: true)
     *   ?escala_id=<id>        Filtra por uma escala específica
     */
    public function getMeusServicos(Request $request)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Localiza o cadastro de acólito vinculado ao usuário logado
        $acolito = Acolito::where('user_id', $user->id)
            ->where('paroquia_id', $user->paroquia_id)
            ->first();

        if (!$acolito) {
            return response()->json([
                'vinculado'  => false,
                'message'    => 'Nenhum cadastro de acólito vinculado a este usuário.',
                'servicos'   => [],
            ]);
        }

        // Busca os registros de escalonamento deste acólito
        $query = EscaladoData::where('acolito_id', $acolito->id)
            ->with([
                'escalaDataHora.escala',
                'escalaDataHora.entidade',
                'funcao',
            ]);

        // Filtro por escala específica
        if ($request->filled('escala_id')) {
            $query->where('escala_id', $request->input('escala_id'));
        }

        $escalados = $query->get();

        // Monta mapa de meses para calcular a data real da celebração
        $monthsMap = [
            'janeiro' => 1, 'fevereiro' => 2, 'março' => 3, 'marco' => 3,
            'abril' => 4, 'maio' => 5, 'junho' => 6, 'julho' => 7,
            'agosto' => 8, 'setembro' => 9, 'outubro' => 10,
            'novembro' => 11, 'dezembro' => 12,
        ];

        $apenasPosteriores = $request->input('apenas_futuros', 'true') !== 'false';
        $hoje = Carbon::today();

        $servicos = $escalados
            ->map(function ($escalado) use ($monthsMap, $hoje, $apenasPosteriores) {
                $celebracao = $escalado->escalaDataHora;

                if (!$celebracao || !$celebracao->escala) {
                    return null;
                }

                $escala = $celebracao->escala;
                $monthName = mb_strtolower($escala->month, 'UTF-8');
                $monthNum = $monthsMap[$monthName] ?? null;

                // Monta a data real: YYYY-MM-DD
                $dataReal = null;
                if ($monthNum) {
                    try {
                        $dataReal = Carbon::createFromDate(
                            $escala->year,
                            $monthNum,
                            (int) $celebracao->data
                        );
                    } catch (\Exception $e) {
                        $dataReal = null;
                    }
                }

                // Aplica filtro de apenas futuros
                if ($apenasPosteriores && $dataReal && $dataReal->lt($hoje)) {
                    return null;
                }

                return [
                    'cal_id'        => $escalado->cal_id,
                    'escala_id'     => $escala->es_id,
                    'escala_month'  => $escala->month,
                    'escala_year'   => $escala->year,
                    'd_id'          => $celebracao->d_id,
                    'dia'           => (int) $celebracao->data,
                    'dia_semana'    => (int) $celebracao->dia,  // 1=Seg ... 7=Dom
                    'hora'          => $celebracao->hora,
                    'celebration'   => $celebracao->celebration,
                    'local'         => $celebracao->entidade?->ent_name ?? $escala->church,
                    'ent_id'        => $celebracao->ent_id,
                    'data_real'     => $dataReal?->format('Y-m-d'),
                    'funcao'        => $escalado->funcao
                        ? [
                            'id'    => $escalado->funcao->f_id,
                            'title' => $escalado->funcao->title,
                        ]
                        : null,
                ];
            })
            ->filter()
            ->sortBy('data_real')
            ->values();

        return response()->json([
            'vinculado' => true,
            'acolito'   => [
                'id'              => $acolito->id,
                'name'            => $acolito->name,
                'type'            => $acolito->type, // 0 = Acólito, 1 = Coroinha
                'graduation_year' => $acolito->graduation_year,
                'status'          => $acolito->status,
            ],
            'total_servicos' => $servicos->count(),
            'servicos'       => $servicos,
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /api/acolitos/funcoes
    // -------------------------------------------------------------------------

    /**
     * Retorna todas as funções cadastradas para a paróquia.
     * Equivalente à tela web: /acolitos/funcoes (index)
     */
    public function getFuncoes(Request $request)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['error' => 'Acesso não autorizado.'], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $funcoes = AcolitoFuncao::where('paroquia_id', $user->paroquia_id)
            ->orderBy('title')
            ->get(['f_id as id', 'title']);

        return response()->json([
            'funcoes' => $funcoes,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    /**
     * Formata uma celebração (EscalaDataHora) para o payload da API.
     */
    private function formatarCelebracao(EscalaDataHora $item, Escala $escala): array
    {
        return [
            'd_id'        => $item->d_id,
            'dia'         => (int) $item->data,
            'dia_semana'  => (int) $item->dia,  // 1=Seg ... 7=Dom
            'hora'        => $item->hora,
            'celebration' => $item->celebration,
            'local'       => $item->entidade?->ent_name ?? $escala->church,
            'ent_id'      => $item->ent_id,
            'acolitos'    => $item->escalados->map(function ($escalado) {
                $acolito = $escalado->acolito;
                return [
                    'cal_id'    => $escalado->cal_id,
                    'id'        => $acolito?->id,
                    'name'      => $acolito?->user?->name ?? $acolito?->name ?? 'N/A',
                    'avatar_url' => $this->resolverAvatarUrl(
                        $acolito?->user?->avatar,
                        $acolito?->user?->name ?? $acolito?->name
                    ),
                    'type'      => $acolito?->type, // 0 = Acólito, 1 = Coroinha
                    'funcao'    => $escalado->funcao
                        ? [
                            'id'    => $escalado->funcao->f_id,
                            'title' => $escalado->funcao->title,
                        ]
                        : null,
                ];
            })->values(),
        ];
    }

    /**
     * Monta a URL completa do avatar do usuário.
     * Segue a mesma lógica da view web (manage.blade.php):
     *   - Se há avatar: /storage/uploads/avatars/{filename}
     *   - Fallback:     https://ui-avatars.com/api/?name=<nome>
     */
    private function resolverAvatarUrl(?string $avatarFilename, ?string $name): string
    {
        if ($avatarFilename) {
            return url('storage/uploads/avatars/' . $avatarFilename);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($name ?? 'A') . '&background=random';
    }
}
