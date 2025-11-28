<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * User repository instance
     */
    protected UserRepository $userRepository;

    /**
     * Constructor
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        // Get sort parameters from request, default to created_at descending
        $sortBy = $request->get('sortBy', 'created_at');
        $sortOrder = $request->get('sortOrder', 'desc');

        // Get filter and search parameters
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $verified = $request->get('verified', '');
        $perPage = $request->get('perPage', 15);

        // Validate sort parameters
        $allowedColumns = ['id', 'first_name', 'last_name', 'email', 'status', 'role', 'email_verified_at', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'created_at';
        }
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        // Get paginated users using repository
        $users = $this->userRepository->getPaginated(
            search: $search,
            status: $status,
            verified: $verified,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
            perPage: (int) $perPage
        );

        return Inertia::render('users/index', [
            'users' => $users,
            'filters' => $request->only(['search', 'status', 'verified', 'sortBy', 'sortOrder']),
            'sorts' => [
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return Inertia::render('users/create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return Inertia::render('users/show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return Inertia::render('users/edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validated['password'] ?? false) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
