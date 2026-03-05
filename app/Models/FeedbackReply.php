<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackReply extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'feedback_id',
        'reply_message',
        'sent_to_email',
        'status',
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'sent_at' => 'datetime',
    ];

    public function feedback()
    {
        return $this->belongsTo(Feedback::class);
    }
}
