<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource (User).
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'sort', 'direction', 'status', 'verified']);

        $query = User::where('roles', '=', 'user')
                    ->where('is_archived', false);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Apply status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Apply verified filter
        if (!empty($filters['verified']) && $filters['verified'] !== 'all') {
            if ($filters['verified'] === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($filters['verified'] === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        
        // Handle name sorting (concat first_name and last_name)
        if ($sortField === 'name') {
            $query->orderByRaw("CONCAT(first_name, ' ', last_name) {$sortDirection}");
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        $users = $query->paginate(10);
        
        $userCount = User::where('roles', '=', 'user')->where('is_archived', false)->count();
        
        return Inertia::render('users/index',[
            'users' => $users,
            'userCount' => $userCount,
            'filters' => $filters,
        ]);
    }

    /**
     * Display archived (Users)
     */
    public function archived(Request $request)
    {
        $filters = $request->only(['search']);

        $users = User::where('roles', '=', 'user')
                    ->where('is_archived', true)
                    ->when($filters['search'] ?? null, function ($query, $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('first_name', 'like', '%' . $search . '%')
                              ->orWhere('last_name', 'like', '%' . $search . '%')
                              ->orWhere('email', 'like', '%' . $search . '%');
                        });
                    })
                    ->paginate(10);

        $archivedCount = User::where('roles', '=', 'user')->where('is_archived', true)->count();

        return Inertia::render('users/archive-user', [
            'users' => $users,
            'archivedCount' => $archivedCount,
            'filters' => $filters,
        ]);
    }

    /**
     * Archive a user
     */
    public function archive(string $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_archived' => true]);

        return redirect()->back()->with('success', 'User archived successfully.');
    }

    /**
     * Unarchive a user
     */
    public function unarchive(string $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_archived' => false]);

        return redirect()->back()->with('success', 'User unarchived successfully.');
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
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'address' => 'nullable|string|max:500',
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return redirect()->back()->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
