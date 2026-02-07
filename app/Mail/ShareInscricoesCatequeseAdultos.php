<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShareInscricoesCatequeseAdultos extends Mailable
{
    use Queueable, SerializesModels;

    public $senderName;
    public $userMessage;
    public $inscritos;
    protected $pdfContent;
    protected $pdfFilename;

    /**
     * Create a new message instance.
     */
    public function __construct($senderName, $userMessage, $inscritos, $pdfContent, $pdfFilename)
    {
        $this->senderName = $senderName;
        $this->userMessage = $userMessage;
        $this->inscritos = $inscritos;
        $this->pdfContent = $pdfContent;
        $this->pdfFilename = $pdfFilename;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "{$this->senderName} enviou e compartilhou as fichas de catequese de adultos",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.share-inscricoes-catequese-adultos',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->pdfFilename)
                ->withMime('application/pdf'),
        ];
    }
}
