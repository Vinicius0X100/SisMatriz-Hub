<?php

namespace App\Mail;

use App\Models\ProcessoParoquial;
use App\Models\ProcessoTramitacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProcessoTramitadoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $processo;
    public $tramitacao;
    public $url;
    public $nomeDestinatario;

    /**
     * Create a new message instance.
     */
    public function __construct(ProcessoParoquial $processo, ProcessoTramitacao $tramitacao, $nomeDestinatario)
    {
        $this->processo = $processo;
        $this->tramitacao = $tramitacao;
        $this->url = url('/processos?show_processo=' . $processo->id);
        $this->nomeDestinatario = $nomeDestinatario;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Processo Encaminhado: ' . $this->processo->protocolo,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.processos.tramitado',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
