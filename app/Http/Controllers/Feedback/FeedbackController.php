<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\ReplyFeedbackRequest;
use App\Jobs\SendFeedbackReplyJob;
use App\Models\Feedback;
use App\Models\FeedbackReply;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource (all user feedback, API-shaped).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'device_id', 'search']);

        $query = Feedback::with(['user', 'device'])
            ->orderBy('created_at', 'desc');

        // Handle "replied" special category
        if (!empty($filters['category']) && $filters['category'] === 'replied') {
            $query->where('replied', true);
        } elseif (!empty($filters['category']) && $filters['category'] !== 'all') {
            // Exclude replied feedbacks from "All" and other categories
            $query->where('category', $filters['category']);
        } else {
            // For "All" tab, exclude replied feedbacks
            if (empty($filters['category']) || $filters['category'] === 'all') {
                $query->where('replied', false);
            }
        }

        if (!empty($filters['device_id'])) {
            $query->where('device_id', $filters['device_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('message', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('subject', 'like', '%' . $filters['search'] . '%');
            });
        }

        $feedback = $query->paginate($request->input('per_page', 20));

        return Inertia::render('feedback/index', [
            'feedback' => $feedback,
            'filters' => $filters,
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Send a reply email to the user who submitted feedback.
     */
    public function reply(ReplyFeedbackRequest $request, Feedback $feedback)
    {
        $feedback->load('user');

        if (!$feedback->user || !$feedback->user->email) {
            return back()->with('error', 'Cannot send reply: User email not found.');
        }

        $feedbackReply = FeedbackReply::create([
            'feedback_id' => $feedback->id,
            'reply_message' => $request->validated()['reply_message'],
            'sent_to_email' => $feedback->user->email,
            'status' => FeedbackReply::STATUS_PENDING,
        ]);

        $feedback->update(['replied' => true]);

        SendFeedbackReplyJob::dispatch($feedbackReply);

        return back()->with('success', 'Reply sent successfully!');
    }
}
