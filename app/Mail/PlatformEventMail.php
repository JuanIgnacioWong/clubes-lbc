<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class PlatformEventMail extends Mailable
{
    use Queueable;

    /**
     * @param array<int,string> $lines
     */
    public function __construct(
        public string $subjectLine,
        public string $title,
        public array $lines,
        public ?string $actionUrl = null,
        public ?string $actionText = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform-event',
            with: [
                'title' => $this->title,
                'lines' => $this->lines,
                'actionUrl' => $this->actionUrl,
                'actionText' => $this->actionText,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
