<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryAssignedToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customerEmail,
        public string $customerName,
        public string $inquiryId,
        public string $dealerName,
        public string $dealerEmail
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your inquiry has been assigned',
            to: [$this->customerEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry_assigned_customer',
        );
    }
}
