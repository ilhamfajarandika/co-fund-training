<?php

namespace App\Mail;

use App\Models\CampaignUpdate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CampaignUpdate $update)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update Campaign - ' . $this->update->campaign->title,
            from: 'no-reply@crowdfunding.test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign-update',
            with: [
                'update' => $this->update,
                'campaign' => $this->update->campaign,
                'creator' => $this->update->campaign->user,
            ],
        );
    }
}
