<?php

namespace Tests\Feature;

use App\Filament\Resources\NotificationTemplateResource;
use App\Filament\Resources\NotificationTemplateResource\Pages\EditNotificationTemplate;
use App\Filament\Resources\NotificationTemplateResource\Pages\ListNotificationTemplates;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Support\NotificationTemplates;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Manajemen template notifikasi (admin only). */
class NotificationTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
    }

    public function test_every_event_has_an_email_and_a_whatsapp_template(): void
    {
        foreach (array_keys(NotificationTemplates::EVENTS) as $key) {
            foreach (['email', 'whatsapp'] as $channel) {
                $this->assertDatabaseHas('notification_templates', ['key' => $key, 'channel' => $channel, 'is_active' => true]);
            }
        }
    }

    public function test_admin_lists_and_filters_templates(): void
    {
        $this->actingAs($this->admin())->get(NotificationTemplateResource::getUrl('index'))->assertOk()->assertSee('Permohonan diterima');

        $wa = NotificationTemplate::where('channel', 'whatsapp')->get();
        $email = NotificationTemplate::where('channel', 'email')->get();

        Livewire::test(ListNotificationTemplates::class)
            ->filterTable('channel', 'whatsapp')
            ->assertCanSeeTableRecords($wa)
            ->assertCanNotSeeTableRecords($email);
    }

    public function test_other_staff_cannot_open_templates(): void
    {
        $this->actingAs(User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail())
            ->get(NotificationTemplateResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_templates_are_never_created_or_deleted(): void
    {
        $this->actingAs($this->admin());

        $this->assertFalse(NotificationTemplateResource::canCreate());
        $this->assertFalse(NotificationTemplateResource::canDelete(NotificationTemplate::firstOrFail()));
    }

    public function test_placeholders_are_filled_in(): void
    {
        $this->assertSame(
            'Halo Budi, tiket T-1 {tidak_dikenal}',
            NotificationTemplates::render('Halo {nama}, tiket {nomor_tiket} {tidak_dikenal}', ['nama' => 'Budi', 'nomor_tiket' => 'T-1']),
        );
    }

    public function test_templates_can_be_searched_and_filtered_by_status(): void
    {
        $this->actingAs($this->admin());
        $off = NotificationTemplate::where('key', 'ticket_rejected')->where('channel', 'whatsapp')->firstOrFail();
        $off->update(['is_active' => false]);
        $on = NotificationTemplate::where('key', 'ticket_created')->where('channel', 'email')->firstOrFail();

        Livewire::test(ListNotificationTemplates::class)->filterTable('is_active', false)
            ->assertCanSeeTableRecords([$off])->assertCanNotSeeTableRecords([$on]);
        Livewire::test(ListNotificationTemplates::class)->searchTable('tidak dapat diproses')
            ->assertCanSeeTableRecords([$off])->assertCanNotSeeTableRecords([$on]);
    }

    public function test_admin_edits_a_template_with_live_preview(): void
    {
        $this->actingAs($this->admin());
        $template = NotificationTemplate::where('key', 'ticket_completed')->where('channel', 'email')->firstOrFail();

        $this->get(NotificationTemplateResource::getUrl('edit', ['record' => $template]))->assertOk()->assertSee('{nomor_tiket}');

        Livewire::test(EditNotificationTemplate::class, ['record' => $template->getRouteKey()])
            ->fillForm(['subject' => 'Selesai: {nomor_tiket}', 'body' => 'Halo {nama}, hasil {layanan} siap.', 'is_active' => false])
            ->assertSee('Halo Budi Santoso, hasil Legalisir Ijazah siap.')
            ->call('save')
            ->assertHasNoFormErrors();

        $template->refresh();
        $this->assertSame('Halo {nama}, hasil {layanan} siap.', $template->body);
        $this->assertFalse($template->is_active);
        $this->assertSame($this->admin()->id, $template->updated_by);

        $wa = NotificationTemplate::where('key', 'ticket_completed')->where('channel', 'whatsapp')->firstOrFail();
        Livewire::test(EditNotificationTemplate::class, ['record' => $wa->getRouteKey()])
            ->fillForm(['body' => ''])
            ->call('save')
            ->assertHasFormErrors(['body' => 'required']);
    }

    public function test_placeholder_panel_inserts_into_the_message_body(): void
    {
        $this->actingAs($this->admin());
        $template = NotificationTemplate::where('channel', 'whatsapp')->firstOrFail();

        $html = $this->get(NotificationTemplateResource::getUrl('edit', ['record' => $template]))->assertOk()->getContent();

        $this->assertStringContainsString('id="data.body"', $html);
        foreach (array_keys(NotificationTemplates::PLACEHOLDERS) as $key) {
            $this->assertStringContainsString('>{' . $key . '}</button>', $html);
        }
        $this->assertStringContainsString("getElementById('data.body')", html_entity_decode($html, ENT_QUOTES));
    }

    public function test_trigger_can_be_switched_from_the_list(): void
    {
        $this->actingAs($this->admin());
        $template = NotificationTemplate::where('key', 'ticket_created')->where('channel', 'whatsapp')->firstOrFail();

        Livewire::test(ListNotificationTemplates::class)
            ->call('updateTableColumnState', 'is_active', (string) $template->getKey(), false);

        $this->assertFalse($template->fresh()->is_active);
        $this->assertSame($this->admin()->id, $template->fresh()->updated_by);
    }

    public function test_each_change_keeps_the_previous_wording(): void
    {
        $this->actingAs($this->admin());
        $template = NotificationTemplate::where('key', 'ticket_created')->where('channel', 'email')->firstOrFail();
        $original = $template->body;

        Livewire::test(EditNotificationTemplate::class, ['record' => $template->getRouteKey()])
            ->fillForm(['subject' => 'Diterima: {nomor_tiket}', 'body' => 'Versi baru {nama}', 'is_active' => true])
            ->call('save')
            ->assertHasNoFormErrors();

        $revision = $template->revisions()->firstOrFail();
        $this->assertSame($original, $revision->body);
        $this->assertSame($this->admin()->id, $revision->changed_by);

        $template->refresh()->update(['updated_by' => $this->admin()->id]); // no wording change
        $this->assertSame(1, $template->revisions()->count());
    }

    public function test_seeder_adds_missing_templates_without_overwriting_edits(): void
    {
        $edited = NotificationTemplate::where('key', 'ticket_created')->where('channel', 'email')->firstOrFail();
        $edited->update(['body' => 'Teks buatan admin']);
        NotificationTemplate::where('key', 'ticket_rejected')->where('channel', 'whatsapp')->delete();

        $this->seed(NotificationTemplateSeeder::class);

        $this->assertSame('Teks buatan admin', $edited->fresh()->body);
        $this->assertDatabaseHas('notification_templates', ['key' => 'ticket_rejected', 'channel' => 'whatsapp']);
        $this->assertSame(count(NotificationTemplates::EVENTS) * 2, NotificationTemplate::count());
    }

    public function test_templates_are_listed_and_shown_through_the_api(): void
    {
        Sanctum::actingAs($this->admin());
        $template = NotificationTemplate::where('key', 'ticket_completed')->where('channel', 'whatsapp')->firstOrFail();
        $template->update(['body' => 'Halo {nama}, {layanan} selesai.']);

        $this->getJson('/api/template-notifikasi?kanal=whatsapp')->assertOk()
            ->assertJsonCount(count(NotificationTemplates::EVENTS), 'data')
            ->assertJsonPath('placeholder.nama', 'Nama penerima');

        $this->getJson('/api/template-notifikasi/' . $template->id)->assertOk()
            ->assertJsonPath('pratinjau.isi', 'Halo Budi Santoso, Legalisir Ijazah selesai.')
            ->assertJsonCount(1, 'riwayat');

        Sanctum::actingAs(User::where('email', 'katu@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/template-notifikasi')->assertForbidden();
    }

    public function test_template_wording_is_updated_through_the_api(): void
    {
        Sanctum::actingAs($this->admin());
        $email = NotificationTemplate::where('key', 'ticket_created')->where('channel', 'email')->firstOrFail();

        $this->putJson('/api/template-notifikasi/' . $email->id, ['subjek' => 'Tiket {nomor_tiket}', 'isi' => 'Halo {nama}'])
            ->assertOk()->assertJsonPath('isi', 'Halo {nama}')->assertJsonCount(1, 'riwayat');
        $this->putJson('/api/template-notifikasi/' . $email->id, ['isi' => 'Halo {nama}'])->assertJsonValidationErrors('subjek');
        $this->putJson('/api/template-notifikasi/' . $email->id, ['subjek' => 'x', 'isi' => 'Nomor {nomer_tiket}'])
            ->assertJsonValidationErrors(['isi' => 'Placeholder tidak dikenal: {nomer_tiket}.']);
    }

    public function test_editor_refuses_unknown_placeholders(): void
    {
        $this->actingAs($this->admin());
        $template = NotificationTemplate::where('channel', 'whatsapp')->firstOrFail();

        Livewire::test(EditNotificationTemplate::class, ['record' => $template->getRouteKey()])
            ->fillForm(['body' => 'Halo {nma}'])
            ->call('save')
            ->assertHasFormErrors(['body']);
    }

    public function test_template_trigger_is_switched_through_the_api(): void
    {
        Sanctum::actingAs($this->admin());
        $template = NotificationTemplate::where('key', 'ticket_status_changed')->where('channel', 'whatsapp')->firstOrFail();

        $this->patchJson('/api/template-notifikasi/' . $template->id . '/aktif', ['aktif' => false])->assertOk()->assertJsonPath('aktif', false);
        $this->assertFalse($template->fresh()->is_active);
        $this->assertTrue($template->revisions()->firstOrFail()->is_active); // the revision keeps the earlier (active) state
        $this->patchJson('/api/template-notifikasi/' . $template->id . '/aktif', [])->assertJsonValidationErrors('aktif');
    }

    public function test_template_changes_are_in_the_audit_trail(): void
    {
        Sanctum::actingAs($this->admin());
        $template = NotificationTemplate::where('key', 'ticket_completed')->where('channel', 'email')->firstOrFail();

        $this->putJson('/api/template-notifikasi/' . $template->id, ['subjek' => 'Selesai {nomor_tiket}', 'isi' => 'Halo {nama}'])->assertOk();

        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'audit',
            'description' => 'Mengubah template notifikasi Permohonan selesai (email)',
            'causer_id' => $this->admin()->id,
            'subject_id' => $template->id,
        ]);
    }
}
