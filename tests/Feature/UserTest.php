<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    // ===== INDEX TESTS =====
    public function test_guests_cannot_view_users_list()
    {
        $this->get('/users')->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_view_users_list()
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user)->get('/users')->assertForbidden();
    }

    public function test_admin_users_can_view_users_list()
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)->get('/users')->assertInertia(fn ($page) => $page
            ->component('users/index')
        );
    }

    public function test_admin_users_list_displays_all_users()
    {
        $admin = User::factory()->admin()->create();
        $otherUsers = User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/users');

        $response->assertInertia(fn ($page) => $page
            ->component('users/index')
            ->has('users.data', 4)
        );
    }

    public function test_users_list_can_be_filtered_by_search()
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['first_name' => 'John', 'email' => 'john@test.com']);
        User::factory()->create(['first_name' => 'Jane', 'email' => 'jane@test.com']);

        $response = $this->actingAs($admin)->get('/users?search=john');

        $response->assertInertia(fn ($page) => $page
            ->where('filters.search', 'john')
        );
    }

    public function test_users_list_can_be_filtered_by_status()
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['status' => 'active']);
        User::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($admin)->get('/users?status=active');

        $response->assertInertia(fn ($page) => $page
            ->where('filters.status', 'active')
        );
    }

    public function test_users_list_can_be_filtered_by_verified()
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['email_verified_at' => now()]);
        User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($admin)->get('/users?verified=yes');

        $response->assertInertia(fn ($page) => $page
            ->where('filters.verified', 'yes')
        );
    }

    // ===== CREATE TESTS =====
    public function test_guests_cannot_view_create_user_form()
    {
        $this->get('/users/create')->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_view_create_user_form()
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user)->get('/users/create')->assertForbidden();
    }

    public function test_admin_users_can_view_create_user_form()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/users/create')->assertInertia(fn ($page) => $page
            ->component('users/create')
        );
    }

    // ===== STORE TESTS =====
    public function test_guests_cannot_create_user()
    {
        $this->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ])->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_create_user()
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ])->assertForbidden();
    }

    public function test_admin_user_can_create_user()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'status' => 'active',
        ]);

        $response->assertRedirect('/users');
    }

    public function test_first_name_is_required()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'first_name' => '',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_last_name_is_required()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'first_name' => 'John',
            'last_name' => '',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_email_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_email_must_be_unique()
    {
        $user = User::factory()->create();
        $existingUser = User::factory()->create(['email' => 'john@test.com']);

        $response = $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => '',
            'password_confirmation' => '',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_be_at_least_8_characters()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_password_must_be_confirmed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password456',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_status_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => '',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_status_must_be_active_or_inactive()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'pending',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ===== SHOW TESTS =====
    public function test_guests_cannot_view_user_detail()
    {
        $user = User::factory()->create();

        $this->get("/users/{$user->id}")->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_user_detail()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($user)->get("/users/{$targetUser->id}");

        $response->assertInertia(fn ($page) => $page
            ->component('users/show')
            ->where('user.id', $targetUser->id)
            ->where('user.email', $targetUser->email)
        );
    }

    // ===== EDIT TESTS =====
    public function test_guests_cannot_view_edit_user_form()
    {
        $user = User::factory()->create();

        $this->get("/users/{$user->id}/edit")->assertRedirect(route('login'));
    }

    public function test_user_can_view_edit_form_for_own_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/users/{$user->id}/edit");

        $response->assertInertia(fn ($page) => $page
            ->component('users/edit')
            ->where('user.id', $user->id)
        );
    }

    public function test_user_can_view_edit_form_for_other_users()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->get("/users/{$otherUser->id}/edit");

        $response->assertInertia(fn ($page) => $page
            ->component('users/edit')
            ->where('user.id', $otherUser->id)
        );
    }

    // ===== UPDATE TESTS =====
    public function test_guests_cannot_update_user()
    {
        $user = User::factory()->create();

        $this->put("/users/{$user->id}", [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@test.com',
            'status' => 'inactive',
        ])->assertRedirect(route('login'));
    }

    public function test_user_can_update_own_profile()
    {
        $user = User::factory()->create(['first_name' => 'John']);

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => 'Jane',
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Jane',
        ]);

        $response->assertRedirect('/users');
    }

    public function test_user_can_update_other_user()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create(['first_name' => 'John']);

        $response = $this->actingAs($user)->put("/users/{$targetUser->id}", [
            'first_name' => 'Jane',
            'last_name' => $targetUser->last_name,
            'email' => $targetUser->email,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'first_name' => 'Jane',
        ]);

        $response->assertRedirect('/users');
    }

    public function test_update_requires_valid_first_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => '',
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('first_name');
    }

    public function test_update_requires_valid_last_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => '',
            'email' => $user->email,
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('last_name');
    }

    public function test_update_requires_valid_email()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'invalid-email',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_email_must_be_unique()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create(['email' => 'other@test.com']);

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => 'other@test.com',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_update_requires_valid_status()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'status' => 'invalid',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_update_password_is_optional()
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => 'Updated',
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
        ]);

        // Password should not have changed
        $this->assertEquals($originalPassword, User::find($user->id)->password);
    }

    public function test_update_password_if_provided()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
            'status' => 'active',
        ]);

        $updatedUser = User::find($user->id);
        $this->assertTrue(\Hash::check('newpassword123', $updatedUser->password));
    }

    public function test_update_password_must_be_confirmed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword123',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_update_password_must_be_at_least_8_characters()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put("/users/{$user->id}", [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('password');
    }

    // ===== DELETE TESTS =====
    public function test_guests_cannot_delete_user()
    {
        $user = User::factory()->create();

        $this->delete("/users/{$user->id}")->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_delete_own_account()
    {
        $user = User::factory()->user()->create();

        $response = $this->actingAs($user)->delete("/users/{$user->id}");

        $response->assertForbidden();
    }

    public function test_admin_user_can_delete_user()
    {
        $admin = User::factory()->admin()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/users/{$targetUser->id}");

        $this->assertDatabaseMissing('users', [
            'id' => $targetUser->id,
        ]);

        $response->assertRedirect('/users');
    }

    public function test_delete_removes_user_permanently()
    {
        $user = User::factory()->create();
        $targetUser = User::factory()->create();

        $this->assertDatabaseHas('users', ['id' => $targetUser->id]);

        $this->actingAs($user)->delete("/users/{$targetUser->id}");

        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }
}
