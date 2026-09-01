<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignDisbursedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public float $platformFee,
        public float $creatorReceive
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dana Campaign Kamu Sudah Dicairkan - ' . $this->campaign->title,
            from: 'no-reply@crowdfunding.test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign-disbursed',
            with: [
                'campaign' => $this->campaign,
                'creator' => $this->campaign->user,
                'platformFee' => $this->platformFee,
                'creatorReceive' => $this->creatorReceive,
                'collectedAmount' => $this->campaign->collected_amount,
            ],
        );
    }
}
