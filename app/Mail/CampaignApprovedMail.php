<?php

namespace App\Mail;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Campaign $campaign)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Campaign Kamu Sudah Disetujui - ' . $this->campaign->title,
            from: 'no-reply@crowdfunding.test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign-approved',
            with: [
                'campaign' => $this->campaign,
                'creator' => $this->campaign->user,
            ],
        );
    }
}
