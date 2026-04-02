<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserPasskeySetupLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $toEmail,
        public string $recipientName,
        public string $setupUrl,
        public string $systemName,
        public string $subjectLine = 'Set up your SQL SMS passkey',
        public string $introLine = 'Your SQL SMS account is ready.',
        public string $instructionLine = 'Click the link below to start setting up your passkey:',
        public string $buttonLabel = 'Set up passkey',
        public string $expiryLine = 'This link will expire in 24 hours.',
        public string $ignoreLine = ''
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            to: [$this->toEmail],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_passkey_setup_link',
        );
    }
}
