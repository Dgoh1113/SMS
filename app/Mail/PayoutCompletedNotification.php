<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayoutCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $toEmail,
        public string $dealerName,
        public int $leadId,
        public string $inquiryId,
        public string $referralCode,
        public string $senderAlias,
        public string $companyName = ''
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Inquiry #'.$this->inquiryId.' completed – Payout notification',
            to: [$this->toEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payout_completed',
        );
    }
}
