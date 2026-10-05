<?php

namespace Tests\Feature;

use App\Filament\Pages\System\NotificationIntegrations;
use App\Models\NotificationSetting;
use App\Models\User;
use App\Services\NotificationGateway;
use App\Services\WhatsAppService;
use App\Support\IntegrationRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
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

    public function test_admin_reads_and_saves_email_settings_through_the_api(): void
    {
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->putJson('/api/integrasi/email', ['aktif' => true, 'host' => 'smtp.example.test', 'port' => 587, 'encryption' => 'tls', 'password' => 'rahasia-api', 'from_address' => 'ptsp@example.test'])
            ->assertOk()
            ->assertJsonPath('aktif', true)
            ->assertJsonPath('konfigurasi.password_tersimpan', true)
            ->assertJsonMissingPath('konfigurasi.password');

        // Blank password keeps the stored one; the secret is never returned.
        $read = $this->putJson('/api/integrasi/email', ['aktif' => true, 'host' => 'smtp2.example.test', 'port' => 465, 'from_address' => 'ptsp@example.test'])->assertOk();
        $this->assertStringNotContainsString('rahasia-api', $read->getContent());
        $this->assertSame('rahasia-api', NotificationSetting::for('email')->value('password'));
        $this->getJson('/api/integrasi/email')->assertOk()->assertJsonPath('konfigurasi.host', 'smtp2.example.test');

        $this->putJson('/api/integrasi/email', ['aktif' => true])->assertJsonValidationErrors(['host', 'port', 'from_address']);

        Sanctum::actingAs($this->user('katu@mtsn2malang.sch.id'));
        $this->getJson('/api/integrasi/email')->assertForbidden();
    }

    public function test_gateways_are_tested_through_the_api(): void
    {
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->mock(NotificationGateway::class, function ($mock) {
            $mock->shouldReceive('testEmail')->once()->with('admin@example.test')->andReturnNull();
            $mock->shouldReceive('testWhatsApp')->once()->with('6281234567')->andReturn('Gateway menjawab HTTP 401.');
        });

        $this->postJson('/api/integrasi/email/uji', ['penerima' => 'admin@example.test'])->assertOk()->assertJsonPath('berhasil', true);
        $this->postJson('/api/integrasi/whatsapp/uji', ['nomor' => '6281234567'])->assertUnprocessable()->assertJsonPath('pesan', 'Gateway menjawab HTTP 401.');
        $this->postJson('/api/integrasi/email/uji', [])->assertJsonValidationErrors('penerima');
    }

    public function test_admin_reads_and_saves_whatsapp_settings_through_the_api(): void
    {
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->putJson('/api/integrasi/whatsapp', ['aktif' => true, 'api_url' => 'https://wa.example.test/send', 'api_token' => 'token-rahasia', 'sender_id' => '628000'])
            ->assertOk()
            ->assertJsonPath('konfigurasi.api_token_tersimpan', true)
            ->assertJsonPath('konfigurasi.sender_id', '628000')
            ->assertJsonMissingPath('konfigurasi.password_tersimpan');
        $this->assertStringNotContainsString('token-rahasia', $this->getJson('/api/integrasi/whatsapp')->getContent());

        $this->putJson('/api/integrasi/whatsapp', ['aktif' => true, 'api_url' => 'bukan-url'])->assertJsonValidationErrors(['api_url', 'sender_id']);
        $this->putJson('/api/integrasi/whatsapp', ['aktif' => false])->assertOk()->assertJsonPath('aktif', false);
        $this->assertSame('token-rahasia', NotificationSetting::for('whatsapp')->value('api_token'));
    }

    public function test_channels_are_switched_on_only_when_configured(): void
    {
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->patchJson('/api/integrasi/whatsapp/aktif', ['aktif' => true])->assertUnprocessable()->assertJsonPath('message', 'Lengkapi pengaturan gateway terlebih dahulu: api_url, api_token, sender_id.');

        NotificationSetting::store('whatsapp', false, ['api_url' => 'https://wa.example.test', 'api_token' => 't', 'sender_id' => '628']);
        $this->patchJson('/api/integrasi/whatsapp/aktif', ['aktif' => true])->assertOk()->assertJsonPath('aktif', true);
        $this->patchJson('/api/integrasi/whatsapp/aktif', ['aktif' => false])->assertOk()->assertJsonPath('aktif', false);
        $this->assertDatabaseHas('activity_log', ['description' => 'Menonaktifkan notifikasi whatsapp']);
    }

    public function test_saved_email_settings_apply_without_a_restart(): void
    {
        IntegrationRuntime::apply();
        $this->assertNotSame('smtp.saved.test', config('mail.mailers.smtp.host'));

        NotificationSetting::store('email', true, ['host' => 'smtp.saved.test', 'port' => 2525, 'encryption' => 'none', 'from_address' => 'ptsp@saved.test', 'from_name' => 'PTSP Uji']);
        IntegrationRuntime::apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.saved.test', config('mail.mailers.smtp.host'));
        $this->assertSame(2525, config('mail.mailers.smtp.port'));
        $this->assertNull(config('mail.mailers.smtp.encryption'));
        $this->assertSame('ptsp@saved.test', config('mail.from.address'));

        // Switched off: .env stays in charge again on the next request.
        NotificationSetting::store('email', false, []);
        config(['mail.mailers.smtp.host' => 'env-host']);
        IntegrationRuntime::apply();
        $this->assertSame('env-host', config('mail.mailers.smtp.host'));
    }

    public function test_whatsapp_service_uses_saved_settings_and_respects_the_switch(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::fake(['wa.saved.test/*' => Http::response(['ok' => true])]);
        NotificationSetting::store('whatsapp', true, ['api_url' => 'https://wa.saved.test/send', 'api_token' => 'tkn', 'sender_id' => '628000']);

        $this->assertTrue((new WhatsAppService())->sendMessage('6281234567', 'Halo'));
        Http::assertSent(fn ($request) => $request->url() === 'https://wa.saved.test/send' && $request->hasHeader('Authorization', 'Bearer tkn'));

        NotificationSetting::store('whatsapp', false, []);
        $this->assertFalse((new WhatsAppService())->sendMessage('6281234567', 'Halo'));
        Http::assertSentCount(1);
    }
}
