<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_users_page()
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_users_page()
    {
        $admin = User::factory()->create(['roles' => 'admin']);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/index')
            );
    }

    public function test_users_page_displays_only_regular_users()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        User::factory()->count(5)->create(['roles' => 'user']);
        User::factory()->count(2)->create(['roles' => 'admin']);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/index')
                ->has('users.data', 5) // Only regular users
            );
    }

    public function test_users_page_excludes_archived_users()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        User::factory()->count(3)->create(['roles' => 'user', 'is_archived' => false]);
        User::factory()->count(2)->create(['roles' => 'user', 'is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 3) // Only non-archived users
            );
    }

    public function test_users_can_be_searched()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        User::factory()->create([
            'roles' => 'user',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com'
        ]);
        User::factory()->create([
            'roles' => 'user',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com'
        ]);

        $response = $this->actingAs($admin)->get(route('users.index', ['search' => 'John']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 1)
                ->where('users.data.0.first_name', 'John')
            );
    }

    public function test_user_can_be_updated()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $user = User::factory()->create(['roles' => 'user']);

        $response = $this->actingAs($admin)->patch(route('users.update', $user->id), [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
            'address' => '123 Updated Street',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_be_archived()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $user = User::factory()->create(['roles' => 'user', 'is_archived' => false]);

        $response = $this->actingAs($admin)->patch(route('users.archive', $user->id));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_archived' => true,
        ]);
    }

    public function test_user_can_be_unarchived()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $user = User::factory()->create(['roles' => 'user', 'is_archived' => true]);

        $response = $this->actingAs($admin)->patch(route('users.unarchive', $user->id));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_archived' => false,
        ]);
    }

    public function test_archived_users_page_shows_only_archived_users()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        User::factory()->count(3)->create(['roles' => 'user', 'is_archived' => false]);
        User::factory()->count(2)->create(['roles' => 'user', 'is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('users.archived'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('users/archive-user')
                ->has('users.data', 2) // Only archived users
            );
    }

    public function test_archived_users_can_be_searched()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        User::factory()->create([
            'roles' => 'user',
            'first_name' => 'Archived',
            'last_name' => 'User',
            'email' => 'archived@example.com',
            'is_archived' => true
        ]);
        User::factory()->create([
            'roles' => 'user',
            'first_name' => 'Another',
            'last_name' => 'Archived',
            'email' => 'another@example.com',
            'is_archived' => true
        ]);

        $response = $this->actingAs($admin)->get(route('users.archived', ['search' => 'Archived']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 2) // Both users have "Archived" in their name
            );
    }

    public function test_users_page_includes_pagination_data()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        User::factory()->count(15)->create(['roles' => 'user']);

        $response = $this->actingAs($admin)->get(route('users.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 10) // Default pagination: 10 per page
                ->has('users.links')
                ->has('users.total')
            );
    }

    public function test_user_update_validates_email_uniqueness()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $user1 = User::factory()->create(['roles' => 'user', 'email' => 'existing@example.com']);
        $user2 = User::factory()->create(['roles' => 'user', 'email' => 'user2@example.com']);

        $response = $this->actingAs($admin)->patch(route('users.update', $user2->id), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'existing@example.com', // Duplicate email
            'address' => '123 Street',
        ]);

        $response->assertSessionHasErrors('email');
    }
}

