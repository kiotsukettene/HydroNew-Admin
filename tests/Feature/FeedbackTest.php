<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Device;
use App\Models\Feedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_feedback_page()
    {
        $response = $this->get(route('feedback.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_access_feedback_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('feedback.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('feedback/index')
                ->has('feedback')
                ->has('filters')
            );
    }

    public function test_feedback_page_displays_unreplied_feedback_by_default()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        Feedback::factory()->count(3)->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);
        Feedback::factory()->count(2)->replied()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
        ]);

        $response = $this->actingAs($admin)->get(route('feedback.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('feedback.data', 3) // Only unreplied feedback
            );
    }

    public function test_feedback_can_be_filtered_by_replied_category()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        Feedback::factory()->count(3)->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);
        Feedback::factory()->count(2)->replied()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
        ]);

        $response = $this->actingAs($admin)->get(route('feedback.index', ['category' => 'replied']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('feedback.data', 2) // Only replied feedback
            );
    }

    public function test_feedback_can_be_filtered_by_specific_category()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        Feedback::factory()->count(2)->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'category' => 'Bug Report',
            'replied' => false,
        ]);
        Feedback::factory()->count(3)->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'category' => 'Feature Request',
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('feedback.index', ['category' => 'Bug Report']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('feedback.data', 2) // Only Bug Report feedback
            );
    }

    public function test_feedback_can_be_filtered_by_device_id()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device1 = Device::factory()->create();
        $device2 = Device::factory()->create();

        Feedback::factory()->count(2)->create([
            'user_id' => $user->id,
            'device_id' => $device1->id,
            'replied' => false,
        ]);
        Feedback::factory()->count(3)->create([
            'user_id' => $user->id,
            'device_id' => $device2->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('feedback.index', ['device_id' => $device1->id]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('feedback.data', 2) // Only device1 feedback
            );
    }

    public function test_feedback_can_be_searched()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'subject' => 'Important Bug',
            'message' => 'This is a critical issue',
            'replied' => false,
        ]);
        Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'subject' => 'Feature Request',
            'message' => 'Please add this feature',
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('feedback.index', ['search' => 'Bug']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('feedback.data', 1)
            );
    }

    public function test_feedback_includes_pagination()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        Feedback::factory()->count(25)->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('feedback.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('feedback.data', 20) // Default pagination: 20 per page
                ->has('feedback.links')
            );
    }

    public function test_admin_can_reply_to_feedback()
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user', 'email' => 'user@example.com']);
        $device = Device::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('feedback.reply', $feedback->id), [
            'reply_message' => 'Thank you for your feedback. We will look into this issue.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Reply sent successfully!');

        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
            'replied' => true,
        ]);

        Mail::assertSent(\App\Mail\FeedbackReplyMail::class);
    }

    public function test_feedback_reply_validates_reply_message_required()
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('feedback.reply', $feedback->id), [
            'reply_message' => '',
        ]);

        $response->assertSessionHasErrors('reply_message');
    }

    public function test_feedback_reply_validates_reply_message_min_length()
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('feedback.reply', $feedback->id), [
            'reply_message' => 'Short',
        ]);

        $response->assertSessionHasErrors('reply_message');
    }

    public function test_feedback_reply_validates_reply_message_max_length()
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('feedback.reply', $feedback->id), [
            'reply_message' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors('reply_message');
    }

    public function test_feedback_reply_fails_when_user_has_no_email()
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user', 'email' => '']);
        $device = Device::factory()->create();

        $feedback = Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('feedback.reply', $feedback->id), [
            'reply_message' => 'Thank you for your feedback. We will look into this issue.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot send reply: User email not found.');

        $this->assertDatabaseHas('feedback', [
            'id' => $feedback->id,
            'replied' => false, // Should not be marked as replied
        ]);

        Mail::assertNotSent(\App\Mail\FeedbackReplyMail::class);
    }

    public function test_feedback_includes_user_and_device_relationships()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $device = Device::factory()->create();

        Feedback::factory()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'replied' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('feedback.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('feedback.data.0.user')
                ->has('feedback.data.0.device')
            );
    }
}
