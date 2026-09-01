<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignRefundedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Campaign $campaign, public float $amount)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Refund Campaign - ' . $this->campaign->title,
            from: 'no-reply@crowdfunding.test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign-refunded',
            with: [
                'campaign' => $this->campaign,
                'backer' => $this->backer ?? null,
                'amount' => $this->amount,
            ],
        );
    }
}
