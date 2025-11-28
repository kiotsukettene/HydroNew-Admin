<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_cannot_be_rendered()
    {
        // Registration is disabled for admin-only system
        $this->expectException(\Symfony\Component\Routing\Exception\RouteNotFoundException::class);
        $this->get(route('register'));
    }

    public function test_new_users_cannot_register()
    {
        // Registration is disabled for admin-only system
        $this->expectException(\Symfony\Component\Routing\Exception\RouteNotFoundException::class);
        $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
    }
}
