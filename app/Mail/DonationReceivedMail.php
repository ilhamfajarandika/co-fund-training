<?php

namespace App\Mail;

use App\Models\Backing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Backing $backing)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Donasi Baru Diterima - ' . $this->backing->campaign->title,
            from: 'no-reply@crowdfunding.test',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.donation-received',
            with: [
                'backing' => $this->backing,
                'campaign' => $this->backing->campaign,
                'backer' => $this->backing->user,
            ],
        );
    }
}
