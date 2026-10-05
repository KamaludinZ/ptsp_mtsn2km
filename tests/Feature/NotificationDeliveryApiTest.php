<?php

namespace Tests\Feature;

use App\Models\NotificationDelivery;
use App\Models\NotificationSetting;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Riwayat pengiriman notifikasi through the API (admin only). */
class NotificationDeliveryApiTest extends TestCase
{
    use RefreshDatabase;

    private NotificationDelivery $failed;

    private NotificationDelivery $sent;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        NotificationDelivery::query()->delete();

        $ticket = Ticket::firstOrFail();
        $this->failed = NotificationDelivery::create(['event' => 'ticket_created', 'channel' => 'whatsapp', 'ticket_id' => $ticket->id, 'recipient' => '6281299990000', 'body' => 'Halo', 'status' => 'failed', 'error' => 'HTTP 500', 'attempts' => 1]);
        $this->sent = NotificationDelivery::create(['event' => 'ticket_completed', 'channel' => 'email', 'ticket_id' => $ticket->id, 'recipient' => 'siti@example.test', 'subject' => 'Selesai', 'body' => 'Isi', 'status' => 'sent', 'attempts' => 1]);
    }

    public function test_admin_lists_and_filters_the_history(): void
    {
        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/riwayat-notifikasi')->assertOk()->assertJsonPath('meta.total', 2)->assertJsonMissingPath('data.0.isi');
        $this->getJson('/api/riwayat-notifikasi?status=failed')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.penerima', '6281299990000');
        $this->getJson('/api/riwayat-notifikasi?kanal=email&pemicu=ticket_completed')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/riwayat-notifikasi?q=siti')->assertOk()->assertJsonPath('data.0.id', $this->sent->id);
        $this->getJson('/api/riwayat-notifikasi?kanal=merpati')->assertJsonValidationErrors('kanal');
        $this->getJson('/api/riwayat-notifikasi/' . $this->sent->id)->assertOk()->assertJsonPath('isi', 'Isi')->assertJsonPath('subjek', 'Selesai');
    }

    public function test_admin_resends_a_failed_message(): void
    {
        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        NotificationSetting::store('whatsapp', true, ['api_url' => 'https://wa.test/send', 'api_token' => 't', 'sender_id' => '628000']);
        Http::swap(new HttpFactory());
        Http::fake(['wa.test/*' => Http::response(['ok' => true])]);

        $this->postJson('/api/riwayat-notifikasi/' . $this->sent->id . '/kirim-ulang')->assertUnprocessable();
        $this->postJson('/api/riwayat-notifikasi/' . $this->failed->id . '/kirim-ulang')
            ->assertOk()
            ->assertJsonPath('status', 'sent')
            ->assertJsonPath('percobaan', 2);
    }

    public function test_other_staff_cannot_read_the_history(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/riwayat-notifikasi')->assertForbidden();
        $this->postJson('/api/riwayat-notifikasi/' . $this->failed->id . '/kirim-ulang')->assertForbidden();
    }
}
