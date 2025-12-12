<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DevicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_devices_page()
    {
        $response = $this->get(route('devices.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_devices_page()
    {
        $admin = User::factory()->create(['roles' => 'admin']);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('devices/index')
            );
    }

    public function test_devices_page_displays_devices_with_user_relationship()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $user = User::factory()->create(['roles' => 'user']);
        Device::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('devices/index')
                ->has('devices.data', 3)
                ->has('devices.data.0.user') // Check user relationship is loaded
            );
    }

    public function test_devices_page_excludes_archived_devices()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->count(4)->create(['is_archived' => false]);
        Device::factory()->count(2)->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 4) // Only non-archived devices
            );
    }

    public function test_devices_can_be_searched_by_name()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->create(['name' => 'pH Sensor 001']);
        Device::factory()->create(['name' => 'TDS Meter 002']);

        $response = $this->actingAs($admin)->get(route('devices.index', ['search' => 'pH Sensor']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 1)
                ->where('devices.data.0.name', 'pH Sensor 001')
            );
    }

    public function test_devices_can_be_searched_by_serial_number()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->create(['serial_number' => 'ABC-1234-XYZAB']);
        Device::factory()->create(['serial_number' => 'DEF-5678-VWXYZ']);

        $response = $this->actingAs($admin)->get(route('devices.index', ['search' => 'ABC-1234']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 1)
                ->where('devices.data.0.serial_number', 'ABC-1234-XYZAB')
            );
    }

    public function test_devices_can_be_searched_by_owner_name()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $owner = User::factory()->create([
            'roles' => 'user',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        Device::factory()->create(['user_id' => $owner->id]);
        Device::factory()->create(); // Another device with different owner

        $response = $this->actingAs($admin)->get(route('devices.index', ['search' => 'John']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 1)
                ->where('devices.data.0.user.first_name', 'John')
            );
    }

    public function test_devices_can_be_filtered_by_status()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->count(3)->create(['status' => 'connected']);
        Device::factory()->count(2)->create(['status' => 'not connected']);

        $response = $this->actingAs($admin)->get(route('devices.index', ['status' => 'connected']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 3)
            );
    }

    public function test_devices_filter_shows_all_when_status_is_all()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->count(3)->create(['status' => 'connected']);
        Device::factory()->count(2)->create(['status' => 'not connected']);

        $response = $this->actingAs($admin)->get(route('devices.index', ['status' => 'all']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 5) // All devices
            );
    }

    public function test_device_can_be_updated()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $device = Device::factory()->create([
            'name' => 'Old Name',
            'serial_number' => 'ABC-1234-XYZAB'
        ]);

        $response = $this->actingAs($admin)->patch(route('devices.update', $device->id), [
            'name' => 'Updated Device Name',
            'serial_number' => 'ABC-1234-XYZAB', // Serial number shouldn't change
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'name' => 'Updated Device Name',
            'serial_number' => 'ABC-1234-XYZAB', // Should remain the same
        ]);
    }

    public function test_device_serial_number_cannot_be_changed()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $device = Device::factory()->create(['serial_number' => 'ORIGINAL-123']);

        $response = $this->actingAs($admin)->patch(route('devices.update', $device->id), [
            'name' => 'Updated Name',
            'serial_number' => 'CHANGED-456', // Attempt to change
        ]);

        // The controller should ignore the serial_number update
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'serial_number' => 'ORIGINAL-123', // Should remain unchanged
        ]);
    }

    public function test_device_can_be_archived()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $device = Device::factory()->create(['is_archived' => false]);

        $response = $this->actingAs($admin)->patch(route('devices.archive', $device->id));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'is_archived' => true,
        ]);
    }

    public function test_device_can_be_unarchived()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        $device = Device::factory()->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->patch(route('devices.unarchive', $device->id));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'is_archived' => false,
        ]);
    }

    public function test_archived_devices_page_shows_only_archived_devices()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->count(3)->create(['is_archived' => false]);
        Device::factory()->count(2)->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('devices.archived'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('devices/archive-devices')
                ->has('devices.data', 2) // Only archived devices
            );
    }

    public function test_archived_devices_can_be_searched()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->create([
            'name' => 'Archived pH Sensor',
            'is_archived' => true
        ]);
        Device::factory()->create([
            'name' => 'Archived TDS Meter',
            'is_archived' => true
        ]);

        $response = $this->actingAs($admin)->get(route('devices.archived', ['search' => 'pH Sensor']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 1)
                ->where('devices.data.0.name', 'Archived pH Sensor')
            );
    }

    public function test_devices_page_includes_pagination_data()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 10) // Default pagination: 10 per page
                ->has('devices.links')
                ->has('devices.total')
            );
    }

    public function test_devices_are_ordered_by_created_at_desc()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        
        $oldDevice = Device::factory()->create(['created_at' => now()->subDays(5)]);
        $newDevice = Device::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('devices.data.0.id', $newDevice->id) // Newest first
            );
    }

    public function test_device_page_includes_device_count()
    {
        $admin = User::factory()->create(['roles' => 'admin']);
        Device::factory()->count(7)->create(['is_archived' => false]);
        Device::factory()->count(3)->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('deviceCount', 7) // Only non-archived count
            );
    }
}
