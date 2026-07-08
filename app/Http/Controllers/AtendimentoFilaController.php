<?php

namespace App\Http\Controllers;

use App\Events\FilaAtualizada;
use App\Jobs\SendAtendimentoWhatsappJob;
use App\Models\AtendimentoFila;
use App\Models\AtendimentoFilaItem;
use App\Models\ParoquiaSuperadmin;
use App\Models\Register;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AtendimentoFilaController extends Controller
{
    /**
     * Roles com acesso ao módulo de Fila de Atendimento.
     */
    private const ROLES_PERMITIDAS = ['1', '111'];

    private function verificarPermissao(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasAnyRole(self::ROLES_PERMITIDAS);
    }

    // =========================================================================
    // SECRETARIA — Gerenciamento de filas
    // =========================================================================

    /**
     * Lista todas as filas da paróquia (index da secretaria).
     */
    public function index(Request $request)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $filas = AtendimentoFila::where('paroquia_id', $user->paroquia_id)
            ->withCount('itens')
            ->orderBy('data', 'desc')
            ->paginate(15);

        return view('modules.atendimento-fila.index', compact('filas'));
    }

    /**
     * Formulário / ação de criar nova fila.
     */
    public function create()
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        return view('modules.atendimento-fila.create');
    }

    /**
     * Salva nova fila.
     */
    public function store(Request $request)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        $request->validate([
            'data' => 'required|date',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Evita duplicata de fila para o mesmo dia
        $existe = AtendimentoFila::where('paroquia_id', $user->paroquia_id)
            ->whereDate('data', $request->data)
            ->first();

        if ($existe) {
            return redirect()->route('atendimento-fila.show', $existe->id)
                ->with('warning', 'Já existe uma fila para esta data.');
        }

        $fila = AtendimentoFila::create([
            'paroquia_id' => $user->paroquia_id,
            'data'        => $request->data,
            'status'      => AtendimentoFila::STATUS_AGUARDANDO,
            'created_by'  => $user->id,
        ]);

        return redirect()->route('atendimento-fila.show', $fila->id)
            ->with('success', 'Fila criada com sucesso!');
    }

    /**
     * Gerenciamento da fila do dia (tela principal da secretaria).
     */
    public function show(Request $request, $id)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $id)
            ->where('paroquia_id', $user->paroquia_id)
            ->firstOrFail();

        // Agendados por hora
        $agendados = $fila->itens()
            ->where('tipo', AtendimentoFilaItem::TIPO_AGENDADO)
            ->orderBy('hora_agendada')
            ->get();

        // Walk-ins por chegada
        $walkins = $fila->itens()
            ->where('tipo', AtendimentoFilaItem::TIPO_WALKIN)
            ->orderBy('created_at')
            ->get();

        return view('modules.atendimento-fila.show', compact('fila', 'agendados', 'walkins'));
    }

    /**
     * Altera o status da fila (Ativar / Encerrar).
     */
    public function alterarStatus(Request $request, $id)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $id)
            ->where('paroquia_id', $user->paroquia_id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|integer|in:0,1,2',
        ]);

        $fila->update(['status' => $request->status]);

        broadcast(new FilaAtualizada($fila->id, $fila->paroquia_id, 'status_alterado'))->toOthers();

        return back()->with('success', 'Status da fila atualizado.');
    }

    /**
     * Adiciona uma pessoa na fila (agendado ou walk-in).
     */
    public function adicionarItem(Request $request, $filaId)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $filaId)
            ->where('paroquia_id', $user->paroquia_id)
            ->firstOrFail();

        $request->validate([
            'nome'          => 'required|string|max:255',
            'tipo'          => 'required|integer|in:0,1',
            'assunto'       => 'nullable|string|max:500',
            'hora_agendada' => 'required_if:tipo,1|nullable|date_format:H:i',
            'register_id'   => 'nullable|integer',
            'telefone'      => 'nullable|string|max:20',
        ]);

        $item = AtendimentoFilaItem::create([
            'fila_id'       => $fila->id,
            'register_id'   => $request->register_id,
            'nome'          => $request->nome,
            'assunto'       => $request->assunto,
            'hora_agendada' => $request->hora_agendada,
            'tipo'          => $request->tipo,
            'status'        => AtendimentoFilaItem::STATUS_AGUARDANDO,
            'telefone'      => $request->telefone,
        ]);

        // Envia WhatsApp para agendados que têm telefone
        if ($item->isAgendado() && $item->telefone) {
            $paroquia = ParoquiaSuperadmin::find($fila->paroquia_id);

            SendAtendimentoWhatsappJob::dispatch(
                $item->telefone,
                $item->nome,
                Carbon::parse($fila->data)->format('d/m/Y'),
                $item->hora_agendada,
                $paroquia?->name ?? 'Paróquia'
            );

            $item->update(['whatsapp_enviado' => true]);
        }

        broadcast(new FilaAtualizada($fila->id, $fila->paroquia_id, 'item_adicionado'))->toOthers();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'item'    => $item->fresh(),
                'message' => 'Pessoa adicionada à fila com sucesso.',
            ]);
        }

        return back()->with('success', "{$item->nome} adicionado(a) à fila.");
    }

    /**
     * Busca registro por CPF para auto-preencher o formulário de agendado.
     */
    public function buscarPessoa(Request $request)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['found' => false], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $q = trim($request->input('q', ''));

        if (strlen($q) < 3) {
            return response()->json(['found' => false, 'message' => 'Digite pelo menos 3 caracteres.']);
        }

        $registers = \App\Models\Register::where('paroquia_id', $user->paroquia_id)
            ->where(function ($query) use ($q) {
                $query->where('cpf', $q)
                      ->orWhere('name', 'like', '%' . $q . '%');
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'cpf', 'email', 'born_date']);

        if ($registers->isEmpty()) {
            return response()->json(['found' => false, 'message' => 'Nenhum registro encontrado.']);
        }

        return response()->json([
            'found'     => true,
            'registers' => $registers->map(function ($r) {
                return [
                    'id'         => $r->id,
                    'nome'       => $r->name,
                    'telefone'   => $r->phone,
                    'cpf'        => $r->cpf,
                    'email'      => $r->email,
                    'nascimento' => $r->born_date ? $r->born_date->format('d/m/Y') : 'Não informada',
                ];
            })
        ]);
    }

    /**
     * Altera o status de um item (ausente, remover etc).
     */
    public function alterarStatusItem(Request $request, $filaId, $itemId)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $filaId)
            ->where('paroquia_id', $user->paroquia_id)
            ->firstOrFail();

        $item = AtendimentoFilaItem::where('id', $itemId)
            ->where('fila_id', $fila->id)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        $item->update(['status' => $request->status]);

        broadcast(new FilaAtualizada($fila->id, $fila->paroquia_id, 'status_alterado'))->toOthers();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'item' => $item->fresh()]);
        }

        return back()->with('success', 'Status atualizado.');
    }

    /**
     * Remove um item da fila.
     */
    public function removerItem(Request $request, $filaId, $itemId)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $filaId)
            ->where('paroquia_id', $user->paroquia_id)
            ->firstOrFail();

        $item = AtendimentoFilaItem::where('id', $itemId)
            ->where('fila_id', $fila->id)
            ->firstOrFail();

        $nome = $item->nome;
        $item->delete();

        broadcast(new FilaAtualizada($fila->id, $fila->paroquia_id, 'item_removido'))->toOthers();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', "{$nome} removido(a) da fila.");
    }

    /**
     * Exclui toda a fila (secretaria).
     */
    public function destroy($id)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $id)
            ->where('paroquia_id', $user->paroquia_id)
            ->firstOrFail();

        $fila->delete(); // cascade deleta os itens

        return redirect()->route('atendimento-fila.index')
            ->with('success', 'Fila excluída com sucesso.');
    }

    // =========================================================================
    // PADRE — Painel ao vivo
    // =========================================================================

    /**
     * Painel ao vivo para o padre visualizar a fila.
     */
    public function painel(Request $request, $filaId = null)
    {
        if (!$this->verificarPermissao()) {
            abort(403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Se não informou fila, pega a fila ativa de hoje
        if (!$filaId) {
            $fila = AtendimentoFila::where('paroquia_id', $user->paroquia_id)
                ->whereDate('data', today())
                ->where('status', AtendimentoFila::STATUS_ATIVA)
                ->first();
        } else {
            $fila = AtendimentoFila::where('id', $filaId)
                ->where('paroquia_id', $user->paroquia_id)
                ->first();
        }

        return view('modules.atendimento-fila.painel', compact('fila', 'user'));
    }

    /**
     * Retorna JSON com o estado atual da fila (para polling de fallback e carga inicial do painel).
     */
    public function painelDados(Request $request, $filaId)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['error' => 'Não autorizado.'], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $filaId)
            ->where('paroquia_id', $user->paroquia_id)
            ->first();

        if (!$fila) {
            return response()->json(['error' => 'Fila não encontrada.'], 404);
        }

        return response()->json($this->montarDadosPainel($fila));
    }

    /**
     * Chama o próximo na fila (ação do padre).
     */
    public function chamarProximo(Request $request, $filaId)
    {
        if (!$this->verificarPermissao()) {
            return response()->json(['error' => 'Não autorizado.'], 403);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $fila = AtendimentoFila::where('id', $filaId)
            ->where('paroquia_id', $user->paroquia_id)
            ->firstOrFail();

        // Finaliza quem está em atendimento
        AtendimentoFilaItem::where('fila_id', $fila->id)
            ->where('status', AtendimentoFilaItem::STATUS_EM_ATENDIMENTO)
            ->update(['status' => AtendimentoFilaItem::STATUS_ATENDIDO]);

        // Chama o próximo da fila ordenada
        $proximo = $this->getProximoDaFila($fila);

        $mensagem = "Ninguém aguardando na fila.";
        if ($proximo) {
            $proximo->update(['status' => AtendimentoFilaItem::STATUS_EM_ATENDIMENTO]);
            $mensagem = "Padre chamou: " . $proximo->nome;

            // Cria notificação na navbar (como Lembrete) para as secretárias e admins da paróquia
            $usuarios = \App\Models\User::where('paroquia_id', $fila->paroquia_id)
                ->whereIn('level', ['1', '111'])
                ->get();

            foreach ($usuarios as $u) {
                \App\Models\Lembrete::create([
                    'usuario_id' => $u->id,
                    'descricao'  => $mensagem,
                    'data_hora'  => now(),
                    'status'     => 'ativo',
                    'pref_email' => false,
                    'pref_sound' => true,
                ]);
            }
        }

        broadcast(new FilaAtualizada($fila->id, $fila->paroquia_id, 'proximo_chamado', $mensagem))->toOthers();

        return response()->json($this->montarDadosPainel($fila));
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    /**
     * Monta o payload de dados do painel (em atendimento + próximos).
     */
    private function montarDadosPainel(AtendimentoFila $fila): array
    {
        $emAtendimento = $fila->itens()
            ->where('status', AtendimentoFilaItem::STATUS_EM_ATENDIMENTO)
            ->first();

        // Agendados aguardando ordenados por hora
        $agendadosAguardando = $fila->itens()
            ->where('tipo', AtendimentoFilaItem::TIPO_AGENDADO)
            ->where('status', AtendimentoFilaItem::STATUS_AGUARDANDO)
            ->orderBy('hora_agendada')
            ->get();

        // Walk-ins aguardando ordenados por chegada
        $walkinAguardando = $fila->itens()
            ->where('tipo', AtendimentoFilaItem::TIPO_WALKIN)
            ->where('status', AtendimentoFilaItem::STATUS_AGUARDANDO)
            ->orderBy('created_at')
            ->get();

        // Histórico atendidos hoje
        $atendidos = $fila->itens()
            ->whereIn('status', [AtendimentoFilaItem::STATUS_ATENDIDO, AtendimentoFilaItem::STATUS_AUSENTE])
            ->orderByDesc('updated_at')
            ->get();

        $formatarItem = fn($item, $pos = null) => [
            'id'            => $item->id,
            'nome'          => $item->nome,
            'assunto'       => $item->assunto,
            'tipo'          => $item->tipo,
            'tipo_label'    => $item->tipo_label,
            'hora_agendada' => $item->hora_agendada,
            'status'        => $item->status,
            'status_label'  => $item->status_label,
            'posicao'       => $pos,
        ];

        $posicao = 1;
        $proximos = [];
        foreach ($agendadosAguardando as $item) {
            $proximos[] = $formatarItem($item, $posicao++);
        }
        foreach ($walkinAguardando as $item) {
            $proximos[] = $formatarItem($item, $posicao++);
        }

        return [
            'fila_id'        => $fila->id,
            'fila_status'    => $fila->status,
            'data'           => $fila->data->format('d/m/Y'),
            'em_atendimento' => $emAtendimento ? $formatarItem($emAtendimento) : null,
            'proximos'       => $proximos,
            'total_aguardando' => count($proximos),
            'total_atendidos'  => $atendidos->count(),
            'atendidos'      => $atendidos->map(fn($i) => $formatarItem($i))->values()->toArray(),
        ];
    }

    /**
     * Retorna o próximo item da fila respeitando a regra de prioridade.
     */
    private function getProximoDaFila(AtendimentoFila $fila): ?AtendimentoFilaItem
    {
        // Primeiro tenta um agendado aguardando
        $agendado = AtendimentoFilaItem::where('fila_id', $fila->id)
            ->where('tipo', AtendimentoFilaItem::TIPO_AGENDADO)
            ->where('status', AtendimentoFilaItem::STATUS_AGUARDANDO)
            ->orderBy('hora_agendada')
            ->first();

        if ($agendado) {
            return $agendado;
        }

        // Depois walk-in por chegada
        return AtendimentoFilaItem::where('fila_id', $fila->id)
            ->where('tipo', AtendimentoFilaItem::TIPO_WALKIN)
            ->where('status', AtendimentoFilaItem::STATUS_AGUARDANDO)
            ->orderBy('created_at')
            ->first();
    }
}
