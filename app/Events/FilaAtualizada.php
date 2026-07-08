<?php

namespace App\Events;

use App\Models\AtendimentoFila;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FilaAtualizada implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $filaId;
    public int $paroquiaId;
    public string $acao; // 'item_adicionado', 'proximo_chamado', 'item_removido', 'status_alterado'
    public ?string $mensagem;

    public function __construct(int $filaId, int $paroquiaId, string $acao = 'atualizado', ?string $mensagem = null)
    {
        $this->filaId     = $filaId;
        $this->paroquiaId = $paroquiaId;
        $this->acao       = $acao;
        $this->mensagem   = $mensagem;
    }

    /**
     * Canal público por paróquia — qualquer tela logada da paróquia pode ouvir.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("paroquia.{$this->paroquiaId}.fila"),
        ];
    }

    /**
     * Nome do evento no lado do cliente.
     */
    public function broadcastAs(): string
    {
        return 'fila.atualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'fila_id'    => $this->filaId,
            'paroquia_id' => $this->paroquiaId,
            'acao'       => $this->acao,
            'mensagem'   => $this->mensagem,
        ];
    }
}
