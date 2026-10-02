<?php

namespace Tests\Feature;

use App\Filament\Resources\NotificationTemplateResource;
use App\Filament\Resources\NotificationTemplateResource\Pages\EditNotificationTemplate;
use App\Filament\Resources\NotificationTemplateResource\Pages\ListNotificationTemplates;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Support\NotificationTemplates;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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
}
