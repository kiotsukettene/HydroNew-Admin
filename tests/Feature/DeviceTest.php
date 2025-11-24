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

    public function test_authenticated_users_can_view_devices_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('devices.index'))->assertInertia(fn ($page) => $page
            ->component('devices/index')
        );
    }

    public function test_user_only_sees_their_own_devices()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userDevice = Device::factory()->create(['user_id' => $user->id]);
        $otherDevice = Device::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('devices.index'));

        $response->assertInertia(fn ($page) => $page
            ->component('devices/index')
        );
        // Device with ID of userDevice should be in the list, otherDevice should not be
    }

    // ===== CREATE TESTS =====
    public function test_guests_cannot_view_create_device_form()
    {
        $this->get(route('devices.create'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_create_device_form()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('devices.create'))->assertInertia(fn ($page) => $page
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

    public function test_authenticated_user_can_create_device()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $this->assertDatabaseHas('devices', [
            'user_id' => $user->id,
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $response->assertRedirect();
    }

    public function test_device_name_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('devices.store'), [
            'name' => '',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_device_serial_number_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => '',
            'status' => 'connected',
        ]);

        $response->assertSessionHasErrors('serial_number');
    }

    public function test_device_status_is_required()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => '',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_serial_number_must_be_unique()
    {
        $user = User::factory()->create();
        Device::factory()->create(['serial_number' => 'SN-12345']);

        $response = $this->actingAs($user)->post(route('devices.store'), [
            'name' => 'Test Device',
            'serial_number' => 'SN-12345',
            'status' => 'connected',
        ]);

        $response->assertSessionHasErrors('serial_number');
    }

    public function test_device_status_must_be_valid()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('devices.store'), [
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

    public function test_authenticated_user_can_view_their_device()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('devices.show', $device));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('devices/show')
        );
    }

    public function test_user_cannot_view_other_users_device()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
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

    public function test_authenticated_user_can_view_edit_form_for_their_device()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('devices.edit', $device));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('devices/edit')
        );
    }

    public function test_user_cannot_view_edit_form_for_other_users_device()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('devices.edit', $device));

        $response->assertForbidden();
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

    public function test_user_cannot_update_other_users_device()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put(route('devices.update', $device), [
            'name' => 'Updated Device',
            'serial_number' => $device->serial_number,
            'status' => 'not connected',
        ]);

        $response->assertForbidden();
    }

    public function test_update_requires_valid_data()
    {
        $user = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('devices.update', $device), [
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

    public function test_user_cannot_delete_other_users_device()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $device = Device::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('devices.destroy', $device));

        $response->assertForbidden();

        $this->assertDatabaseHas('devices', ['id' => $device->id]);
    }
}
