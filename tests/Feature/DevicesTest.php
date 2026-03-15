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
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('devices/index')
            );
    }

    public function test_devices_page_displays_devices_with_users_relationship()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();
        $device->users()->attach($user);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('devices/index')
                ->has('devices.data', 1)
                ->has('devices.data.0.users') // Check users relationship is loaded
            );
    }

    public function test_devices_page_excludes_archived_devices()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Device::factory()->count(4)->create(['is_archived' => false]);
        Device::factory()->count(2)->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 4) // Only non-archived devices
            );
    }

    public function test_devices_can_be_searched_by_device_name()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Device::factory()->create(['device_name' => 'pH Sensor 001']);
        Device::factory()->create(['device_name' => 'TDS Meter 002']);

        $response = $this->actingAs($admin)->get(route('devices.index', ['search' => 'pH Sensor']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 1)
                ->where('devices.data.0.device_name', 'pH Sensor 001')
            );
    }

    public function test_devices_can_be_searched_by_serial_number()
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create([
            'role' => 'user',
            'first_name' => 'John',
            'last_name' => 'Doe'
        ]);
        $device = Device::factory()->create();
        $device->users()->attach($owner);
        
        $otherDevice = Device::factory()->create();

        $response = $this->actingAs($admin)->get(route('devices.index', ['search' => 'John']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 1)
                ->where('devices.data.0.users.0.first_name', 'John')
            );
    }

    public function test_devices_can_be_filtered_by_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Device::factory()->count(3)->create(['status' => 'online']);
        Device::factory()->count(2)->create(['status' => 'offline']);

        $response = $this->actingAs($admin)->get(route('devices.index', ['status' => 'online']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 3)
            );
    }

    public function test_devices_filter_shows_all_when_status_is_all()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Device::factory()->count(3)->create(['status' => 'online']);
        Device::factory()->count(2)->create(['status' => 'offline']);

        $response = $this->actingAs($admin)->get(route('devices.index', ['status' => 'all']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 5) // All devices
            );
    }

    public function test_device_can_be_updated()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = Device::factory()->create([
            'device_name' => 'Old Name',
            'serial_number' => 'ABC-1234-XYZAB'
        ]);

        $response = $this->actingAs($admin)->patch(route('devices.update', $device->id), [
            'device_name' => 'Updated Device Name',
            'serial_number' => 'ABC-1234-XYZAB',
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'device_name' => 'Updated Device Name',
            'serial_number' => 'ABC-1234-XYZAB',
        ]);
    }

    public function test_device_serial_number_cannot_be_changed()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = Device::factory()->create(['serial_number' => 'ORIGINAL-123']);

        $response = $this->actingAs($admin)->patch(route('devices.update', $device->id), [
            'device_name' => 'Updated Name',
            'serial_number' => 'CHANGED-456',
        ]);

        // The controller should ignore the serial_number update
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'serial_number' => 'ORIGINAL-123',
        ]);
    }

    public function test_device_can_be_archived()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = Device::factory()->offline()->create(['is_archived' => false]);

        $response = $this->actingAs($admin)->patch(route('devices.archive', $device->id));

        $response->assertRedirect();
        
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'is_archived' => true,
        ]);
    }

    public function test_online_device_cannot_be_archived()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $device = Device::factory()->online()->create(['is_archived' => false]);

        $response = $this->actingAs($admin)
            ->patch(route('devices.archive', $device->id), [], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'is_archived' => false,
        ]);
    }

    public function test_device_can_be_unarchived()
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
        $admin = User::factory()->create(['role' => 'admin']);
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
        $admin = User::factory()->create(['role' => 'admin']);
        Device::factory()->create([
            'device_name' => 'Archived pH Sensor',
            'is_archived' => true
        ]);
        Device::factory()->create([
            'device_name' => 'Archived TDS Meter',
            'is_archived' => true
        ]);

        $response = $this->actingAs($admin)->get(route('devices.archived', ['search' => 'pH Sensor']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('devices.data', 1)
                ->where('devices.data.0.device_name', 'Archived pH Sensor')
            );
    }

    public function test_devices_page_includes_pagination_data()
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
        $admin = User::factory()->create(['role' => 'admin']);
        
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
        $admin = User::factory()->create(['role' => 'admin']);
        Device::factory()->count(7)->create(['is_archived' => false]);
        Device::factory()->count(3)->create(['is_archived' => true]);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('deviceCount', 7) // Only non-archived count
            );
    }

    public function test_devices_can_be_bulk_archived()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $devices = Device::factory()->count(3)->offline()->create(['is_archived' => false]);

        $deviceIds = $devices->pluck('id')->toArray();

        $response = $this->actingAs($admin)->patch(route('devices.bulk-archive'), [
            'ids' => $deviceIds,
        ]);

        $response->assertRedirect();

        foreach ($devices as $device) {
            $this->assertDatabaseHas('devices', [
                'id' => $device->id,
                'is_archived' => true,
            ]);
        }
    }

    public function test_devices_can_be_bulk_unarchived()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $devices = Device::factory()->count(3)->create(['is_archived' => true]);

        $deviceIds = $devices->pluck('id')->toArray();

        $response = $this->actingAs($admin)->patch(route('devices.bulk-unarchive'), [
            'ids' => $deviceIds,
        ]);

        $response->assertRedirect();

        foreach ($devices as $device) {
            $this->assertDatabaseHas('devices', [
                'id' => $device->id,
                'is_archived' => false,
            ]);
        }
    }
}
