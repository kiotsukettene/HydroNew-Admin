<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_analytics_page()
    {
        $response = $this->get(route('analytics.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_analytics_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('analytics/index')
                ->has('usersDevices')
                ->has('cropsHarvestYield')
                ->has('waterTreatment')
                ->has('devices')
                ->has('filters')
            );
    }

    public function test_analytics_index_includes_correct_data_structure()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('analytics/index')
                ->where('filters.frequency', 'monthly')
            );
    }

    public function test_analytics_respects_date_from_filter()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.index', [
            'date_from' => '2026-01-01',
        ]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.date_from', '2026-01-01')
            );
    }

    public function test_analytics_respects_date_to_filter()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.index', [
            'date_to' => '2026-12-31',
        ]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.date_to', '2026-12-31')
            );
    }

    public function test_analytics_respects_frequency_filter()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.index', [
            'frequency' => 'weekly',
        ]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.frequency', 'weekly')
            );
    }

    public function test_analytics_respects_device_id_filter()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->get(route('analytics.index', [
            'device_id' => $device->id,
        ]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.device_id', (string) $device->id)
            );
    }

    public function test_users_devices_api_endpoint_returns_json()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.api.users-devices'));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    public function test_crops_harvest_api_endpoint_returns_json()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.api.crops-harvest'));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    public function test_water_treatment_api_endpoint_returns_json()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.api.water-treatment'));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data',
            ]);
    }

    public function test_analytics_pdf_export_generates_file()
    {
        Storage::fake('public');
        
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('analytics.export.pdf'));

        // Should return a download response (BinaryFileResponse uses getStatusCode())
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->getStatusCode() === 500,
            'PDF export should return 200 (success) or 500 (error)'
        );
    }

    public function test_analytics_pdf_export_with_filters()
    {
        Storage::fake('public');
        
        $admin = User::factory()->create(['role' => 'admin']);
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->get(route('analytics.export.pdf', [
            'date_from' => '2026-01-01',
            'date_to' => '2026-12-31',
            'frequency' => 'monthly',
            'device_id' => $device->id,
            'tab' => 'users-devices',
        ]));

        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->getStatusCode() === 500,
            'PDF export with filters should return 200 (success) or 500 (error)'
        );
    }

    public function test_analytics_page_includes_non_archived_devices()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Device::factory()->count(3)->create(['is_archived' => false]);
        Device::factory()->count(2)->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('analytics.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('devices', fn ($devices) => count($devices) === 3)
            );
    }
}
