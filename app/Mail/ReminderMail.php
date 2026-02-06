<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Invoice $invoice,
        public int $level,
        public string $customMessage
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $levelLabel = match ($this->level) {
            1 => 'Rappel',
            2 => 'Relance',
            3 => 'Mise en demeure',
            default => 'Rappel',
        };

        return new Envelope(
            subject: "{$levelLabel} : Facture {$this->invoice->number}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reminder',
            with: [
                'invoice' => $this->invoice,
                'seller' => $this->invoice->seller_snapshot,
                'buyer' => $this->invoice->buyer_snapshot,
                'customMessage' => $this->customMessage,
                'level' => $this->level,
                'daysOverdue' => $this->getDaysOverdue(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdfService = app(InvoicePdfService::class);
        $filename = "facture-{$this->invoice->number}.pdf";

        return [
            Attachment::fromData(
                fn () => $pdfService->getContent($this->invoice),
                $filename
            )->withMime('application/pdf'),
        ];
    }

    /**
     * Calculate days overdue.
     */
    protected function getDaysOverdue(): int
    {
        if (!$this->invoice->due_at) {
            return 0;
        }

        return max(0, now()->startOfDay()->diffInDays($this->invoice->due_at->startOfDay(), false) * -1);
    }
}
