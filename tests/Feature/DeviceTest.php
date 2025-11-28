<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    use RefreshDatabase;

    // ===== INDEX TESTS =====
    public function test_guests_cannot_view_devices_list()
    {
        $this->get(route('devices.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_view_devices_list()
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user)->get(route('devices.index'))->assertForbidden();
    }

    public function test_admin_users_can_view_devices_list()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('devices.index'))->assertInertia(fn ($page) => $page
            ->component('devices/index')
        );
    }

    public function test_admin_sees_all_devices()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();

        $adminDevice = Device::factory()->create(['user_id' => $admin->id]);
        $userDevice = Device::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get(route('devices.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('devices/index')
        );
    }

    // ===== CREATE TESTS =====
    public function test_guests_cannot_view_create_device_form()
    {
        $this->get(route('devices.create'))->assertRedirect(route('login'));
    }

    public function test_non_admin_users_cannot_view_create_device_form()
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user)->get(route('devices.create'))->assertForbidden();
    }

    public function test_admin_users_can_view_create_device_form()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('devices.create'))->assertInertia(fn ($page) => $page
            ->component('devices/create')
        );
    }

    // ===== STORE TESTS =====
    public function test_guests_cannot_create_device()
    {
        $this->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ])->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_create_device()
    {
        $user = User::factory()->user()->create();

        $this->actingAs($user)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ])->assertForbidden();
    }

    public function test_admin_user_can_create_device()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $this->assertDatabaseHas('devices', [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $response->assertRedirect();
    }

    public function test_device_name_is_required()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('devices.store'), [
            'name' => '',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_device_serial_number_is_required()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => '',
            'status' => 'connected',
        ]);

        $response->assertSessionHasErrors('serial_number');
    }

    public function test_device_status_is_required()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => '',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_serial_number_must_be_unique()
    {
        $admin = User::factory()->admin()->create();
        Device::factory()->create(['serial_number' => 'SN-12345']);

        $response = $this->actingAs($admin)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $response->assertSessionHasErrors('serial_number');
    }

    public function test_device_status_must_be_valid()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    // ===== SHOW TESTS =====
    public function test_guests_cannot_view_device_details()
    {
        $device = Device::factory()->create();

        $this->get(route('devices.show', $device))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_view_device()
    {
        $user = User::factory()->user()->create();
        $device = Device::factory()->create();

        $this->actingAs($user)->get(route('devices.show', $device))->assertForbidden();
    }

    public function test_admin_user_can_view_device()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->get(route('devices.show', $device));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('devices/show')
        );
    }

    public function test_non_admin_user_cannot_view_other_device()
    {
        $user = User::factory()->user()->create();
        $otherUser = User::factory()->user()->create();
        $device = Device::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('devices.show', $device));

        $response->assertForbidden();
    }

    // ===== EDIT TESTS =====
    public function test_guests_cannot_view_edit_device_form()
    {
        $device = Device::factory()->create();

        $this->get(route('devices.edit', $device))->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_view_edit_form()
    {
        $user = User::factory()->user()->create();
        $device = Device::factory()->create();

        $response = $this->actingAs($user)->get(route('devices.edit', $device));

        $response->assertForbidden();
    }

    public function test_admin_user_can_view_edit_form()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->get(route('devices.edit', $device));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('devices/edit')
        );
    }

    // ===== UPDATE TESTS =====
    public function test_guests_cannot_update_device()
    {
        $device = Device::factory()->create();

        $this->put(route('devices.update', $device), [
            'name' => 'Updated Device',
            'serial_number' => $device->serial_number,
            'status' => 'not connected',
        ])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_their_device()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('devices.update', $device), [
            'name' => 'Updated Device',
            'serial_number' => $device->serial_number,
            'status' => 'not connected',
        ]);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'name' => 'Updated Device',
            'status' => 'not connected',
        ]);

        $response->assertRedirect();
    }

    public function test_non_admin_user_cannot_update_device()
    {
        $user = User::factory()->user()->create();
        $device = Device::factory()->create();

        $response = $this->actingAs($user)->put(route('devices.update', $device), [
            'name' => 'Updated Device',
            'serial_number' => $device->serial_number,
            'status' => 'not connected',
        ]);

        $response->assertForbidden();
    }

    public function test_update_requires_valid_data()
    {
        $admin = User::factory()->admin()->create();
        $device = Device::factory()->create();

        $response = $this->actingAs($admin)->put(route('devices.update', $device), [
            'name' => '',
            'serial_number' => $device->serial_number,
            'status' => 'not connected',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_serial_number_must_be_unique()
    {
        $user = User::factory()->create();
        $device1 = Device::factory()->create(['user_id' => $user->id]);
        $device2 = Device::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('devices.update', $device1), [
            'name' => 'Updated Device',
            'serial_number' => $device2->serial_number,
            'status' => 'connected',
        ]);

        $response->assertSessionHasErrors('serial_number');
    }

    // ===== DELETE TESTS =====
    public function test_guests_cannot_delete_device()
    {
        $device = Device::factory()->create();

        $this->delete(route('devices.destroy', $device))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_delete_their_device()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('devices.destroy', $device));

        $this->assertDatabaseMissing('devices', ['id' => $device->id]);

        $response->assertRedirect();
    }

    public function test_non_admin_user_cannot_delete_device()
    {
        $user = User::factory()->user()->create();
        $device = Device::factory()->create();

        $response = $this->actingAs($user)->delete(route('devices.destroy', $device));

        $response->assertForbidden();

        $this->assertDatabaseHas('devices', ['id' => $device->id]);
    }
}
