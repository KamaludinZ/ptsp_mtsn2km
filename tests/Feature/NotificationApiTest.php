<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Support\RoleAccess;
use App\Support\TicketNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** /api/notifikasi: the signed-in user's in-app notifications. */
class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        RoleAccess::sync();
        $this->officer = User::factory()->create(['user_type' => 'pegawai'])->assignRole('back_office');
    }

    public function test_officer_lists_their_notifications_with_filters(): void
    {
        [$a, $b] = Ticket::factory()->count(2)->create();
        TicketNotification::send($this->officer, $a, 'Permohonan baru masuk', "{$a->ticket_number} masuk.");
        TicketNotification::send($this->officer, $b, 'Status berubah', 'Diproses.');
        TicketNotification::send(User::factory()->create(), $a, 'Milik orang lain');
        $this->officer->notifications()->whereRaw("data->>'title' = ?", ['Status berubah'])->first()->markAsRead();
        Sanctum::actingAs($this->officer);

        $this->getJson('/api/notifikasi')->assertOk()
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('belum_dibaca', 1)
            ->assertJsonMissing(['judul' => 'Milik orang lain']);

        $this->getJson('/api/notifikasi?status=belum-dibaca')->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.judul', 'Permohonan baru masuk')
            ->assertJsonPath('data.0.dibaca', false)
            ->assertJsonPath('data.0.tiket.nomor', $a->ticket_number)
            ->assertJsonPath('data.0.tautan', \App\Filament\Resources\TicketResource::getUrl('view', ['record' => $a], panel: 'admin'));
        $this->getJson('/api/notifikasi?tiket=' . $b->ticket_number)->assertJsonPath('meta.total', 1)->assertJsonPath('data.0.dibaca', true);
        $this->getJson('/api/notifikasi?q=diproses')->assertJsonPath('meta.total', 1);
        $this->getJson('/api/notifikasi/jumlah')->assertExactJson(['belum_dibaca' => 1]);
        $this->getJson('/api/notifikasi?status=lain')->assertStatus(422);
    }

    public function test_guests_get_nothing(): void
    {
        $this->getJson('/api/notifikasi')->assertUnauthorized();
    }

    public function test_marking_one_several_by_ticket_and_all_as_read(): void
    {
        [$a, $b] = Ticket::factory()->count(2)->create();
        foreach (['Satu', 'Dua'] as $title) {
            TicketNotification::send($this->officer, $a, $title);
        }
        TicketNotification::send($this->officer, $b, 'Tiga');
        TicketNotification::send($this->officer, $b, 'Empat');
        Sanctum::actingAs($this->officer);
        $id = fn (string $title) => $this->officer->notifications()->whereRaw("data->>'title' = ?", [$title])->value('id');

        $this->patchJson('/api/notifikasi/' . $id('Satu') . '/dibaca')->assertOk()
            ->assertJsonPath('data.dibaca', true)->assertJsonPath('belum_dibaca', 3);
        $this->patchJson('/api/notifikasi/' . $id('Satu') . '/dibaca', ['dibaca' => false])->assertOk()
            ->assertJsonPath('data.dibaca', false)->assertJsonPath('belum_dibaca', 4);

        $this->postJson('/api/notifikasi/dibaca', ['tiket' => $a->ticket_number])->assertOk()->assertJsonPath('ditandai', 2)->assertJsonPath('belum_dibaca', 2);
        $this->postJson('/api/notifikasi/dibaca', ['id' => [$id('Tiga')]])->assertOk()->assertJsonPath('ditandai', 1);
        $this->postJson('/api/notifikasi/dibaca')->assertOk()->assertJsonPath('ditandai', 1)->assertJsonPath('belum_dibaca', 0);
    }

    public function test_nobody_marks_another_users_notification(): void
    {
        $other = User::factory()->create();
        TicketNotification::send($other, Ticket::factory()->create(), 'Rahasia');
        $foreign = $other->notifications()->value('id');
        Sanctum::actingAs($this->officer);

        $this->patchJson("/api/notifikasi/{$foreign}/dibaca")->assertNotFound();
        $this->postJson('/api/notifikasi/dibaca', ['id' => [$foreign]])->assertOk()->assertJsonPath('ditandai', 0);
        $this->assertNull($other->notifications()->value('read_at'));
    }
}
