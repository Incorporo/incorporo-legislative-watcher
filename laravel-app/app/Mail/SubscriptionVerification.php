<?php

namespace App\Mail;

use App\Models\BillSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionVerification extends Mailable
{
    use Queueable, SerializesModels;

    public BillSubscription $subscription;

    /**
     * Create a new message instance.
     */
    public function __construct(BillSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmă Subscriere - Legislative Watcher',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-verification',
            with: [
                'verificationUrl' => $this->subscription->getVerificationUrl(),
                'subscriptionSummary' => $this->subscription->getSummary(),
                'frequency' => $this->subscription->getFrequencyLabel(),
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
}
