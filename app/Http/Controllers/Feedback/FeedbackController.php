<?php

namespace App\Http\Controllers\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
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

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category', $filters['category']);
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
}
