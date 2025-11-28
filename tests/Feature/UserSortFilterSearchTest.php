<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSortFilterSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup test data (without creating extra admin)
     */
    private function setupTestUsers(User $admin): void
    {
        // Create test users with various statuses and verification states
        // Don't create a separate admin, use the one passed in
        User::factory()->user()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        User::factory()->user()->create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'status' => 'active',
            'email_verified_at' => null,
        ]);

        User::factory()->user()->create([
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'email' => 'bob@example.com',
            'status' => 'inactive',
            'email_verified_at' => now(),
        ]);

        User::factory()->user()->create([
            'first_name' => 'Alice',
            'last_name' => 'Williams',
            'email' => 'alice@example.com',
            'status' => 'inactive',
            'email_verified_at' => null,
        ]);
    }

    // ===== SEARCH TESTS =====

    public function test_users_list_can_be_searched_by_first_name()
    {
        $admin = User::factory()->admin()->create(['first_name' => 'Admin', 'last_name' => 'User']);
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?search=john');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.search', 'john')
                ->has('users.data', 2)  // John Doe + Bob Johnson (matches last name)
            );
    }

    public function test_users_list_can_be_searched_by_last_name()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?search=smith');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.search', 'smith')
                ->has('users.data', 1)
            );
    }

    public function test_users_list_can_be_searched_by_email()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?search=alice@example.com');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.search', 'alice@example.com')
                ->has('users.data', 1)
            );
    }

    public function test_search_is_case_insensitive()
    {
        $admin = User::factory()->admin()->create(['first_name' => 'Admin', 'last_name' => 'User']);
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?search=JOHN');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 2)  // John Doe + Bob Johnson (matches last name)
            );
    }

    public function test_search_returns_empty_results_when_no_match()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?search=nonexistent');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 0)
            );
    }

    // ===== FILTER BY STATUS TESTS =====

    public function test_users_list_can_be_filtered_by_active_status()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?status=active');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.status', 'active')
                ->has('users.data', 3) // Admin + 2 active users
            );
    }

    public function test_users_list_can_be_filtered_by_inactive_status()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?status=inactive');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.status', 'inactive')
                ->has('users.data', 2) // 2 inactive users
            );
    }

    public function test_invalid_status_filter_is_ignored()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?status=invalid');

        // Should return all users when invalid status
        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 5) // All users
            );
    }

    // ===== FILTER BY VERIFICATION TESTS =====

    public function test_users_list_can_be_filtered_by_verified_yes()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?verified=yes');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.verified', 'yes')
                ->has('users.data', 3) // Admin + 2 verified users
            );
    }

    public function test_users_list_can_be_filtered_by_verified_no()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?verified=no');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.verified', 'no')
                ->has('users.data', 2) // 2 unverified users
            );
    }

    // ===== SORT TESTS =====

    public function test_users_list_can_be_sorted_by_first_name_ascending()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=first_name&sortOrder=asc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'first_name')
                ->where('sorts.sortOrder', 'asc')
            );
    }

    public function test_users_list_can_be_sorted_by_first_name_descending()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=first_name&sortOrder=desc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'first_name')
                ->where('sorts.sortOrder', 'desc')
            );
    }

    public function test_users_list_can_be_sorted_by_email()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=email&sortOrder=asc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'email')
                ->where('sorts.sortOrder', 'asc')
            );
    }

    public function test_users_list_can_be_sorted_by_status()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=status&sortOrder=desc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'status')
            );
    }

    public function test_users_list_can_be_sorted_by_email_verified_at()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=email_verified_at&sortOrder=desc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'email_verified_at')
            );
    }

    public function test_users_list_can_be_sorted_by_created_at()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=created_at&sortOrder=asc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'created_at')
                ->where('sorts.sortOrder', 'asc')
            );
    }

    public function test_invalid_sort_column_defaults_to_created_at()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=invalid_column&sortOrder=asc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'created_at')
            );
    }

    public function test_invalid_sort_order_defaults_to_desc()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?sortBy=first_name&sortOrder=invalid');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortOrder', 'desc')
            );
    }

    // ===== COMBINED FILTER AND SORT TESTS =====

    public function test_users_can_be_filtered_and_sorted_together()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?status=active&sortBy=first_name&sortOrder=asc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.status', 'active')
                ->where('sorts.sortBy', 'first_name')
                ->where('sorts.sortOrder', 'asc')
                ->has('users.data', 3)
            );
    }

    public function test_users_can_be_searched_filtered_and_sorted_together()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users?search=john&status=active&sortBy=email&sortOrder=asc');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('filters.search', 'john')
                ->where('filters.status', 'active')
                ->where('sorts.sortBy', 'email')
            );
    }

    public function test_non_admin_users_cannot_view_user_list()
    {
        $user = User::factory()->user()->create();

        $response = $this->actingAs($user)->get('/users?search=john');

        $response->assertForbidden();
    }

    public function test_guests_cannot_view_user_list_with_filters()
    {
        $response = $this->get('/users?search=john');

        $response->assertRedirect('/login');
    }

    // ===== PAGINATION TESTS =====

    public function test_users_list_is_paginated()
    {
        $admin = User::factory()->admin()->create();

        // Create more than 15 users
        User::factory()->user()->count(20)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 15)
                ->has('users.links')
            );
    }

    public function test_users_list_respects_per_page_parameter()
    {
        $admin = User::factory()->admin()->create();

        // Create more than 5 users
        User::factory()->user()->count(10)->create();

        $response = $this->actingAs($admin)->get('/users?perPage=5');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->has('users.data', 5)
            );
    }

    public function test_default_sort_is_created_at_descending()
    {
        $admin = User::factory()->admin()->create();
        $this->setupTestUsers($admin);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => $page
                ->where('sorts.sortBy', 'created_at')
                ->where('sorts.sortOrder', 'desc')
            );
    }
}
