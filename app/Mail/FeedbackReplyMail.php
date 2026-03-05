<?php

namespace App\Mail;

use App\Models\FeedbackReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FeedbackReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $feedbackReply;

    /**
     * Create a new message instance.
     */
    public function __construct(FeedbackReply $feedbackReply)
    {
        $this->feedbackReply = $feedbackReply;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $feedback = $this->feedbackReply->feedback;
        $subject = $feedback->subject 
            ? "Re: {$feedback->subject}" 
            : "Re: Your {$this->getCategoryLabel($feedback->category)}";

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
