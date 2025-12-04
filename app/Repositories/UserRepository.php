<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;

class UserRepository
{
    /**
     * Get paginated users with filters, search, and sorting
     */
    public function getPaginated(
        string $search = '',
        string $status = '',
        string $verified = '',
        string $sortBy = 'created_at',
        string $sortOrder = 'desc',
        int $perPage = 15
    ): Paginator {
        return User::query()
            ->search($search)
            ->filterByStatus($status)
            ->filterByVerified($verified)
            ->sort($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * Get all users with filters and sorting (non-paginated)
     */
    public function getAll(
        string $search = '',
        string $status = '',
        string $verified = '',
        string $sortBy = 'created_at',
        string $sortOrder = 'desc'
    ) {
        return User::query()
            ->search($search)
            ->filterByStatus($status)
            ->filterByVerified($verified)
            ->sort($sortBy, $sortOrder)
            ->get();
    }

    /**
     * Get user count with optional filters
     */
    public function getCount(
        string $search = '',
        string $status = '',
        string $verified = ''
    ): int {
        return User::query()
            ->search($search)
            ->filterByStatus($status)
            ->filterByVerified($verified)
            ->count();
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role, string $sortBy = 'created_at', string $sortOrder = 'desc')
    {
        return User::query()
            ->where('role', $role)
            ->sort($sortBy, $sortOrder)
            ->get();
    }

    /**
     * Get active users
     */
    public function getActive(string $sortBy = 'created_at', string $sortOrder = 'desc')
    {
        return User::query()
            ->where('status', 'active')
            ->sort($sortBy, $sortOrder)
            ->get();
    }

    /**
     * Get verified users
     */
    public function getVerified(string $sortBy = 'created_at', string $sortOrder = 'desc')
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->sort($sortBy, $sortOrder)
            ->get();
    }

    /**
     * Get unverified users
     */
    public function getUnverified(string $sortBy = 'created_at', string $sortOrder = 'desc')
    {
        return User::query()
            ->whereNull('email_verified_at')
            ->sort($sortBy, $sortOrder)
            ->get();
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }

    /**
     * Search users by name or email
     */
    public function search(string $query, string $sortBy = 'created_at', string $sortOrder = 'desc')
    {
        return User::query()
            ->search($query)
            ->sort($sortBy, $sortOrder)
            ->get();
    }
}
