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
        $filters = $request->only(['search', 'sort', 'direction', 'status', 'per_page']);

        $query = User::where('role', '=', 'user')
                     ->where('is_archived', false)
                     ->with(['login_histories' => function ($q) {
                         $q->latest()->limit(1);
                     }]);

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

        // Get per_page value from request, default to 10, allow only [10, 25, 50, 100]
        $perPage = in_array($filters['per_page'] ?? 10, [10, 25, 50, 100])
            ? ($filters['per_page'] ?? 10)
            : 10;

        $users = $query->paginate($perPage);
        
        // Add last_login_at from login_histories to each user
        $users->getCollection()->transform(function ($user) {
            $user->last_login_at = $user->login_histories->first()?->created_at;
            unset($user->login_histories);
            return $user;
        });
        
        $filteredCount = $users->total();

        // Only query total count if filters are applied, otherwise use filtered count
        $hasFilters = !empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'all');
        $userCount = $hasFilters
            ? User::where('role', '=', 'user')->where('is_archived', false)->count()
            : $filteredCount;

        return Inertia::render('users/index',[
            'users' => $users,
            'userCount' => $userCount,
            'filteredCount' => $filteredCount,
            'filters' => $filters,
        ]);
    }

    /**
     * Display archived (Users)
     */
    public function archived(Request $request)
    {
        $filters = $request->only(['search', 'sort', 'direction', 'per_page']);

        $query = User::where('role', '=', 'user')
                    ->where('is_archived', true)
                    ->with(['login_histories' => function ($q) {
                        $q->latest()->limit(1);
                    }]);

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%');
            });
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

        // Get per_page value from request, default to 10, allow only [10, 25, 50, 100]
        $perPage = in_array($filters['per_page'] ?? 10, [10, 25, 50, 100])
            ? ($filters['per_page'] ?? 10)
            : 10;

        $users = $query->paginate($perPage);
        
        // Add last_login_at from login_histories to each user
        $users->getCollection()->transform(function ($user) {
            $user->last_login_at = $user->login_histories->first()?->created_at;
            unset($user->login_histories);
            return $user;
        });
        
        $filteredArchivedCount = $users->total();

        // Only query total count if filters are applied, otherwise use filtered count
        $hasFilters = !empty($filters['search']);
        $archivedCount = $hasFilters
            ? User::where('role', '=', 'user')->where('is_archived', true)->count()
            : $filteredArchivedCount;

        return Inertia::render('users/archive-user', [
            'users' => $users,
            'archivedCount' => $archivedCount,
            'filteredArchivedCount' => $filteredArchivedCount,
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

            // Validate archive conditions: user must be inactive
            if ($user->status !== 'inactive') {
                return redirect()->back()->withErrors(['error' => 'Only inactive users can be archived.']);
            }

            // Get the most recent login from login_histories
            $lastLogin = $user->login_histories()->latest()->first();
            
            // Validate archive conditions: user must be inactive for at least 6 months
            if ($lastLogin) {
                $sixMonthsAgo = now()->subMonths(6);
                if ($lastLogin->created_at > $sixMonthsAgo) {
                    return redirect()->back()->withErrors(['error' => 'User must be inactive for at least 6 months before archiving.']);
                }
            }

            $user->is_archived = true;
            $user->save();

            return redirect()->back()->with('success', 'User archived successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to archive user.']);
        }
    }

    /**
     * Bulk archive users
     */
    public function bulkArchive(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $sixMonthsAgo = now()->subMonths(6);

        // Find users that meet archive conditions
        // Use a subquery to get the latest login from login_histories
        $eligibleUsers = User::whereIn('id', $validated['ids'])
            ->where('is_archived', false)
            ->where('status', 'inactive')
            ->where(function ($q) use ($sixMonthsAgo) {
                // Check if user has no login history
                $q->whereDoesntHave('login_histories')
                  // Or if the most recent login is older than 6 months
                  ->orWhereHas('login_histories', function ($subQuery) use ($sixMonthsAgo) {
                      $subQuery->whereIn('id', function ($nestedQuery) {
                          $nestedQuery->selectRaw('MAX(id)')
                              ->from('login_histories')
                              ->whereColumn('user_id', 'users.id');
                      })
                      ->where('created_at', '<=', $sixMonthsAgo);
                  });
            })
            ->get();

        if ($eligibleUsers->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'No users meet the archive requirements (inactive for at least 6 months).']);
        }

        $count = $eligibleUsers->count();
        $ineligibleCount = count($validated['ids']) - $count;

        // Archive eligible users
        User::whereIn('id', $eligibleUsers->pluck('id'))->update(['is_archived' => true]);

        $message = "{$count} user(s) archived successfully.";
        if ($ineligibleCount > 0) {
            $message .= " {$ineligibleCount} user(s) did not meet archive requirements and were skipped.";
        }

        return redirect()->back()->with('success', $message);
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
     * Bulk unarchive users
     */
    public function bulkUnarchive(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:users,id',
        ]);

        $count = User::whereIn('id', $validated['ids'])
            ->where('is_archived', true)
            ->update(['is_archived' => false]);

        return redirect()->back()->with('success', "{$count} user(s) restored successfully.");
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
