<?php

namespace App\Mail;

use App\Models\BillSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BillDigest extends Mailable
{
    use Queueable, SerializesModels;

    public BillSubscription $subscription;
    public Collection $bills;
    public int $totalCount;

    /**
     * Create a new message instance.
     */
    public function __construct(BillSubscription $subscription, Collection $bills, int $totalCount = null)
    {
        $this->subscription = $subscription;
        $this->bills = $bills;
        $this->totalCount = $totalCount ?? $bills->count();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $frequency = match($this->subscription->frequency) {
            'instant' => 'Notificare Instant',
            'daily' => 'Rezumat Zilnic',
            'weekly' => 'Rezumat Săptămânal',
            default => 'Notificare'
        };

        return new Envelope(
            subject: $frequency . ' - ' . $this->bills->count() . ' ' . $this->getPluralLabel(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bill-digest',
            with: [
                'manageUrl' => $this->subscription->getManageUrl(),
                'unsubscribeUrl' => $this->subscription->getUnsubscribeUrl(),
                'subscriptionSummary' => $this->subscription->getSummary(),
                'frequency' => $this->subscription->getFrequencyLabel(),
                'includeAiSummary' => $this->subscription->include_ai_summary,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get plural label for bills
     */
    private function getPluralLabel(): string
    {
        $count = $this->bills->count();
        if ($count === 1) {
            return 'proiect nou';
        } elseif ($count < 20) {
            return 'proiecte noi';
        } else {
            return 'de proiecte noi';
        }
    }
}
