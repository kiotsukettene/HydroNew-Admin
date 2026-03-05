<?php

namespace App\Mail;

use App\Models\Feedback;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $feedback;
    public $replyMessage;

    /**
     * Create a new message instance.
     */
    public function __construct(Feedback $feedback, string $replyMessage)
    {
        $this->feedback = $feedback;
        $this->replyMessage = $replyMessage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->feedback->subject 
            ? "Re: {$this->feedback->subject}" 
            : "Re: Your {$this->getCategoryLabel($this->feedback->category)}";

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.feedback-reply',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get human-readable category label.
     */
    private function getCategoryLabel(string $category): string
    {
        $labels = [
            'bug_report' => 'Bug Report',
            'feature_request' => 'Feature Request',
            'general_feedback' => 'General Feedback',
            'device_issue' => 'Device Issue',
            'other' => 'Feedback',
        ];

        return $labels[$category] ?? 'Feedback';
    }
}
