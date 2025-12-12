<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\HydroponicYield;
use App\Models\HydroponicSetup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create(['roles' => 'admin']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_displays_correct_statistics()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        
        // Create test data
        $users = User::factory()->count(5)->create(['roles' => 'user']);
        Device::factory()->count(3)->create(['user_id' => $users->first()->id]);
        
        // Create a setup and yields using the same user
        $setup = HydroponicSetup::factory()->create(['user_id' => $users->first()->id]);
        HydroponicYield::factory()->count(2)->create(['hydroponic_setup_id' => $setup->id]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('stats')
                ->where('stats.totalUsers', 5) // Only users with 'user' role
                ->where('stats.totalDevices', 3)
                ->where('stats.totalHarvestedCrops', 2)
            );
    }

    public function test_dashboard_includes_harvest_status()
    {
        $admin = User::factory()->create(['roles' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('harvestStatus')
                ->has('harvestStatus.waterTankLevel')
                ->has('harvestStatus.currentGrowthStage')
            );
    }

    public function test_dashboard_counts_only_non_archived_devices()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        
        Device::factory()->count(5)->create(['is_archived' => false]);
        Device::factory()->count(3)->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.totalDevices', 5) // Only non-archived devices
            );
    }

    public function test_dashboard_counts_only_regular_users()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        
        User::factory()->count(10)->create(['roles' => 'user']);
        User::factory()->count(3)->create(['roles' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.totalUsers', 10) // Only users with 'user' role
            );
    }
}


