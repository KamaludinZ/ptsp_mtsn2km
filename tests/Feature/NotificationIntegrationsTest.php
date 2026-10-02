<?php

namespace Tests\Feature;

use App\Filament\Pages\System\NotificationIntegrations;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\NotificationGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Pengaturan integrasi Email & WhatsApp (admin only). */
class NotificationIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function form(array $overrides = []): array
    {
        return array_replace_recursive([
            'email' => ['is_enabled' => true, 'host' => 'smtp.example.test', 'port' => 587, 'encryption' => 'tls', 'username' => 'ptsp', 'password' => 'rahasia-smtp', 'from_address' => 'ptsp@example.test', 'from_name' => 'PTSP'],
            'whatsapp' => ['is_enabled' => false, 'api_url' => 'https://wa.example.test/send', 'api_token' => 'token-wa', 'sender_id' => '628111'],
        ], $overrides);
    }

    public function test_only_admins_open_the_page(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'))->get(NotificationIntegrations::getUrl())->assertOk();
    }

    public function test_other_staff_cannot_open_the_page(): void
    {
        $this->actingAs($this->user('katu@mtsn2malang.sch.id'))->get(NotificationIntegrations::getUrl())->assertForbidden();
    }

    public function test_admin_saves_channels_with_encrypted_secrets(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(NotificationIntegrations::class)
            ->fillForm($this->form())
            ->call('submit')
            ->assertHasNoFormErrors()
            ->assertNotified('Pengaturan integrasi disimpan.')
            ->assertFormSet(['email.password' => null]);

        $email = NotificationSetting::for('email');
        $this->assertTrue($email->is_enabled);
        $this->assertSame('smtp.example.test', $email->value('host'));
        $this->assertSame('rahasia-smtp', $email->value('password'));
        $this->assertFalse(NotificationSetting::for('whatsapp')->is_enabled);

        $raw = DB::table('notification_settings')->where('channel', 'email')->value('config');
        $this->assertStringNotContainsString('rahasia-smtp', $raw);
        $this->assertStringNotContainsString('smtp.example.test', $raw);
    }

    public function test_blank_secret_keeps_the_saved_one(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        Livewire::test(NotificationIntegrations::class)->fillForm($this->form())->call('submit');

        Livewire::test(NotificationIntegrations::class)
            ->assertDontSee('rahasia-smtp')
            ->fillForm($this->form(['email' => ['password' => null, 'host' => 'smtp2.example.test'], 'whatsapp' => ['api_token' => '']]))
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertSame('rahasia-smtp', NotificationSetting::for('email')->value('password'));
        $this->assertSame('smtp2.example.test', NotificationSetting::for('email')->value('host'));
        $this->assertSame('token-wa', NotificationSetting::for('whatsapp')->value('api_token'));
    }

    public function test_enabled_channel_requires_its_gateway(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(NotificationIntegrations::class)
            ->fillForm($this->form(['whatsapp' => ['is_enabled' => true, 'api_url' => null]]))
            ->call('submit')
            ->assertHasFormErrors(['whatsapp.api_url' => 'required']);
    }

    public function test_test_email_reports_success_and_failure(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        NotificationSetting::for('email')->fill(['is_enabled' => true, 'config' => ['host' => '127.0.0.1', 'port' => 1, 'encryption' => 'none', 'from_address' => 'ptsp@example.test']])->save();

        // Nothing listens on port 1: the admin sees the failure.
        Livewire::test(NotificationIntegrations::class)
            ->callAction('testEmail', ['recipient' => 'admin@example.test'])
            ->assertNotified('Pengiriman uji gagal');

        $this->mock(NotificationGateway::class)->shouldReceive('testEmail')->once()->with('admin@example.test')->andReturnNull();
        Livewire::test(NotificationIntegrations::class)
            ->callAction('testEmail', ['recipient' => 'admin@example.test'])
            ->assertNotified('Email uji terkirim ke admin@example.test.');
    }

    public function test_test_whatsapp_posts_to_the_gateway(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        Livewire::test(NotificationIntegrations::class)
            ->callAction('testWhatsApp', ['number' => '628111'])
            ->assertNotified('Pengiriman uji gagal');

        NotificationSetting::for('whatsapp')->fill(['is_enabled' => true, 'config' => ['api_url' => 'https://wa.example.test/send', 'api_token' => 'token-wa', 'sender_id' => '628000']])->save();
        Http::fake(['wa.example.test/*' => Http::response(['status' => true])]);

        Livewire::test(NotificationIntegrations::class)
            ->callAction('testWhatsApp', ['number' => '628111'])
            ->assertNotified('Pesan uji terkirim ke 628111.');

        Http::assertSent(fn ($request) => $request->url() === 'https://wa.example.test/send'
            && $request->hasHeader('Authorization', 'Bearer token-wa')
            && $request['number'] === '628111'
            && $request['sender'] === '628000');
    }
}
