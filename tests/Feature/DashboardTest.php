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
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_dashboard_displays_correct_statistics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Create test data
        $users = User::factory()->count(5)->create(['role' => 'user']);
        
        // Create devices and attach to users
        $devices = Device::factory()->count(3)->create(['is_archived' => false]);
        
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

    public function test_dashboard_includes_water_treatment_stats()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->has('waterTreatmentStats')
                ->has('waterTreatmentStats.totalCycles')
                ->has('waterTreatmentStats.successRate')
                ->has('waterTreatmentStats.averageDuration')
                ->has('waterTreatmentStats.successfulCycles')
                ->has('waterTreatmentStats.failedCycles')
            );
    }

    public function test_dashboard_counts_only_non_archived_devices()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
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
        $admin = User::factory()->create(['role' => 'admin']);
        
        User::factory()->count(10)->create(['role' => 'user']);
        User::factory()->count(3)->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('stats.totalUsers', 10) // Only users with 'user' role
            );
    }

    public function test_get_sensor_systems_requires_device_id()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard.sensor-systems'));

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Device ID is required',
            ]);
    }

    public function test_get_sensor_systems_returns_systems_for_device()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->get(route('dashboard.sensor-systems', ['device_id' => $device->id]));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    public function test_get_ph_tds_readings_requires_sensor_system_id()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard.ph-tds-readings'));

        $response->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Sensor system ID is required',
            ]);
    }

    public function test_get_ph_tds_readings_returns_readings_for_sensor_system()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('dashboard.ph-tds-readings', ['sensor_system_id' => 1]));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }
}


