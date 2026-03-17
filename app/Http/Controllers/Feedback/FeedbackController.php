<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\ReplyFeedbackRequest;
use App\Mail\FeedbackReplyMail;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource (all user feedback, API-shaped).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'device_id', 'search']);
        $perPage = $request->input('per_page', 20);
        $page = $request->get('page', 1);
        $cacheKey = 'feedback:index:' . md5(serialize($filters + ['per_page' => $perPage, 'page' => $page]));

        $data = Cache::tags(['feedback'])->remember($cacheKey, 600, function () use ($filters, $perPage) {
            $query = Feedback::with(['user', 'device'])
                ->orderBy('created_at', 'desc');

            // Handle "replied" special category
            if (!empty($filters['category']) && $filters['category'] === 'replied') {
                $query->where('replied', true);
            } elseif (!empty($filters['category']) && $filters['category'] !== 'all') {
                $query->where('category', $filters['category']);
            } else {
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
                        ->orWhere('subject', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('category', 'like', '%' . $filters['search'] . '%')
                        ->orWhereHas('user', function ($userQuery) use ($filters) {
                            $userQuery->where('first_name', 'like', '%' . $filters['search'] . '%')
                                ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                                ->orWhere('email', 'like', '%' . $filters['search'] . '%');
                        });
                });
            }

            $feedback = $query->paginate($perPage);

            $unrepliedCount = (empty($filters['category']) || $filters['category'] === 'all')
                && empty($filters['device_id'])
                && empty($filters['search'])
                ? $feedback->total()
                : Feedback::where('replied', false)->count();

            return [
                'feedback' => $feedback,
                'filters' => $filters,
                'unrepliedFeedbackCount' => $unrepliedCount,
            ];
        });

        return Inertia::render('feedback/index', $data);
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
        $feedback->load('user', 'device');

        if (!$feedback->user || !$feedback->user->email) {
            return back()->with('error', 'Cannot send reply: User email not found.');
        }

        // Send email directly
        Mail::to($feedback->user->email)
            ->send(new FeedbackReplyMail($feedback, $request->validated()['reply_message']));

        // Mark as replied
        $feedback->update(['replied' => true]);

        Cache::tags(['feedback'])->flush();

        return back()->with('success', 'Reply sent successfully!');
    }
}
