<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Http::fake();
        Role::findOrCreate('umum');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp_number' => '081234567890',
            'user_type' => 'umum',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_whatsapp_welcome_waits_for_email_verification(): void
    {
        \Illuminate\Support\Facades\Queue::fake();
        Role::findOrCreate('umum');

        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp_number' => '081234567890',
            'user_type' => 'umum',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // The public sign-up form never makes the school's number message a phone.
        \Illuminate\Support\Facades\Queue::assertNotPushed(\App\Jobs\SendWhatsAppMessage::class);

        $user = \App\Models\User::where('email', 'test@example.com')->firstOrFail();
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $this->actingAs($user)->get($url);

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\SendWhatsAppMessage::class, fn ($job) => $job->recipient === '081234567890'
            && ! str_contains($job->message, 'Test User'));
    }
}
