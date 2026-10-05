<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Messages from the public contact page are kept even when mail fails. */
class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    private array $message = ['name' => 'Bu Sari', 'email' => 'sari@example.test', 'subject' => 'Jam layanan', 'message' => 'Apakah Sabtu buka?'];

    public function test_message_is_stored_and_mailed(): void
    {
        Mail::fake();

        $this->post(route('public.contact.store'), $this->message)->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', ['email' => 'sari@example.test', 'subject' => 'Jam layanan', 'read_at' => null]);
        Mail::assertSent(ContactFormMail::class);
    }

    public function test_message_survives_a_mail_outage(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $this->post(route('public.contact.store'), $this->message)->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', ['email' => 'sari@example.test']);
    }

    public function test_invalid_message_is_refused(): void
    {
        $this->post(route('public.contact.store'), ['name' => '', 'email' => 'bukan-email'])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }
}
