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
        $filters = $request->only(['search', 'sort', 'direction', 'status']);

        $query = User::where('role', '=', 'user')
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

        $userCount = User::where('role', '=', 'user')->where('is_archived', false)->count();

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

        $query = User::where('role', '=', 'user')
                    ->where('is_archived', true);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
        }

        $users = $query->paginate(10);

        $archivedCount = User::where('role', '=', 'user')
                            ->where('is_archived', true)
                            ->count();

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
        try {
            $user = User::findOrFail($id);
            
            if ($user->is_archived) {
                return redirect()->back()->withErrors(['error' => 'User is already archived.']);
            }
            
            $user->is_archived = true;
            $user->save();

            return redirect()->back()->with('success', 'User archived successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to archive user.']);
        }
    }

    /**
     * Unarchive a user
     */
    public function unarchive(string $id)
    {
        try {
            $user = User::findOrFail($id);
            
            if (!$user->is_archived) {
                return redirect()->back()->withErrors(['error' => 'User is not archived.']);
            }
            
            $user->is_archived = false;
            $user->save();

            return redirect()->back()->with('success', 'User unarchived successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to restore user.']);
        }
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
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $id,
                'address' => 'nullable|string|max:500',
            ]);

            $user = User::findOrFail($id);
            $user->update($validated);

            return redirect()->back()->with('success', 'User updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to update user.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
