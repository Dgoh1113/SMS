<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryAssignedToDealer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $dealerEmail,
        public string $dealerName,
        public int $leadId,
        public string $inquiryId,
        public string $companyName,
        public string $contactName,
        public string $viewInquiryUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New inquiry assigned to you: #' . $this->inquiryId,
            to: [$this->dealerEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry_assigned',
        );
    }
}
