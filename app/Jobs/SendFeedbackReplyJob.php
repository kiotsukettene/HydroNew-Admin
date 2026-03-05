<?php

namespace App\Jobs;

use App\Mail\FeedbackReplyMail;
use App\Models\FeedbackReply;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendFeedbackReplyJob implements ShouldQueue
{
    use Queueable;

    public $feedbackReply;

    /**
     * Create a new job instance.
     */
    public function __construct(FeedbackReply $feedbackReply)
    {
        $this->feedbackReply = $feedbackReply;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->feedbackReply->load('feedback.user', 'feedback.device');

            Mail::to($this->feedbackReply->sent_to_email)
                ->send(new FeedbackReplyMail($this->feedbackReply));

            $this->feedbackReply->update([
                'status' => FeedbackReply::STATUS_SENT,
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->feedbackReply->update([
                'status' => FeedbackReply::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
