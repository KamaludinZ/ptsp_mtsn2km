<?php

namespace Tests\Feature;

use App\Filament\Pages\Services\DispositionSettings;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Filament\Resources\TicketResource;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Support\ServiceDisposition;
use App\Support\ServiceSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Pengaturan disposisi per layanan (admin only). */
class DispositionSettingsTest extends TestCase
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

    public function test_only_admins_can_open_the_page(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'))->get(DispositionSettings::getUrl())->assertOk();
    }

    public function test_other_staff_cannot_open_the_page(): void
    {
        $this->actingAs($this->user('kepsek@mtsn2malang.sch.id'))->get(DispositionSettings::getUrl())->assertForbidden();
    }

    public function test_mode_maps_onto_the_approval_fields(): void
    {
        $service = Service::firstOrFail();

        $service->update(['approval_required' => true, 'approval_roles' => ['kepala_tu'], 'approval_users' => null]);
        $this->assertSame('tu', $service->fresh()->disposition_mode);

        $service->update(['approval_roles' => null]);
        $this->assertSame('kepsek_tu', $service->fresh()->disposition_mode);

        $service->update(['approval_roles' => ['admin']]);
        $this->assertSame('custom', $service->fresh()->disposition_mode);

        $service->update(['approval_required' => false]);
        $this->assertSame('none', $service->fresh()->disposition_mode);
    }

    public function test_admin_saves_mode_recipients_and_signature_recommendation(): void
    {
        $service = Service::firstOrFail();
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(DispositionSettings::class)
            ->searchTable($service->name)
            ->assertCanSeeTableRecords([$service])
            ->callTableAction('edit', $service, data: [
                'disposition_mode' => 'kepsek',
                'disposition_roles' => ['waka_kurikulum', 'tata_usaha'],
                'signature_recommendation' => 'tte',
            ])
            ->assertHasNoTableActionErrors();

        $service->refresh();
        $this->assertTrue($service->approval_required);
        $this->assertSame(['kepala_sekolah'], $service->approval_roles);
        $this->assertSame('kepsek', $service->disposition_mode);
        $this->assertSame(['waka_kurikulum', 'tata_usaha'], $service->disposition_roles);
        $this->assertSame('tte', $service->signature_recommendation);
        $this->assertSame('Waka Kurikulum, Tata Usaha', ServiceDisposition::recipients($service->disposition_roles));

        Livewire::test(DispositionSettings::class)
            ->callTableAction('edit', $service, data: [
                'disposition_mode' => 'none',
                'disposition_roles' => ['waka_kurikulum'],
                'signature_recommendation' => 'none',
            ])
            ->assertHasNoTableActionErrors();

        $service->refresh();
        $this->assertFalse($service->approval_required);
        $this->assertNull($service->disposition_roles);
        $this->assertNull($service->signature_recommendation);
    }

    public function test_catalog_and_request_summary_show_the_disposition_rule(): void
    {
        $service = Service::firstOrFail();
        $service->update(['approval_required' => true, 'approval_roles' => ['kepala_sekolah'], 'approval_users' => null, 'signature_recommendation' => 'ttd']);
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(ListServices::class)
            ->searchTable($service->name)
            ->assertSee('Kepala Madrasah saja')
            ->assertSee('Anjuran TTD');

        $this->assertStringContainsString('Memerlukan disposisi Kepala Madrasah saja.', (string) ServiceSummary::html($service->fresh()));
    }

    public function test_ticket_documents_show_the_signature_recommendation(): void
    {
        $ticket = Ticket::with('service')->firstOrFail();
        $ticket->service->update(['signature_recommendation' => 'tte']);

        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'))
            ->get(TicketResource::getUrl('view', ['record' => $ticket]))
            ->assertOk()
            ->assertSee('Anjuran layanan ini: TTE (tanda tangan elektronik).');
    }

    public function test_services_without_a_rule_are_flagged(): void
    {
        [$unset, $set] = Service::orderBy('id')->take(2)->get();
        $unset->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        $set->update(['approval_required' => true, 'approval_roles' => ['kepala_tu'], 'approval_users' => null]);
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->assertTrue(ServiceDisposition::usesDefault($unset->fresh()));
        $this->assertFalse(ServiceDisposition::usesDefault($set->fresh()));

        Livewire::test(DispositionSettings::class)
            ->filterTable('unset', true)
            ->assertCanNotSeeTableRecords([$set])
            ->searchTable($unset->name)
            ->assertCanSeeTableRecords([$unset])
            ->assertSee('Belum diatur — memakai aturan bawaan');
    }

    public function test_recipient_checklist_shows_who_holds_each_unit(): void
    {
        $service = Service::firstOrFail();
        $waka = $this->user('waka.kesiswaan@mtsn2malang.sch.id');
        $waka->assignRole(\Spatie\Permission\Models\Role::findOrCreate('waka_kesiswaan', 'web'));
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(DispositionSettings::class)
            ->mountTableAction('edit', $service)
            ->assertSee('Dipegang: Waka Kesiswaan')
            ->assertSee('Belum ada pemegang');
    }

    public function test_services_can_be_filtered_by_recipient(): void
    {
        [$sarpras, $other] = Service::query()->limit(2)->get()->all();
        $sarpras->update(['disposition_roles' => ['waka_sarpras']]);
        $other->update(['disposition_roles' => ['tata_usaha']]);
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(DispositionSettings::class)
            ->filterTable('recipient', 'waka_sarpras')
            ->assertCanSeeTableRecords([$sarpras])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_staff_read_disposition_settings_as_json(): void
    {
        $service = Service::firstOrFail();
        $service->update(['approval_required' => true, 'approval_roles' => ['kepala_tu'], 'approval_users' => null, 'disposition_roles' => ['tata_usaha'], 'signature_recommendation' => 'tte']);
        Sanctum::actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $this->getJson('/api/pengaturan-disposisi/' . $service->slug)
            ->assertOk()
            ->assertJson([
                'slug' => $service->slug,
                'mode' => 'tu',
                'mode_label' => 'Kepala TU saja',
                'aturan_bawaan' => false,
                'penerima' => ['tata_usaha'],
                'anjuran_tanda_tangan' => 'tte',
            ]);

        $this->getJson('/api/pengaturan-disposisi')->assertOk()->assertJsonCount(Service::count(), 'data');

        Sanctum::actingAs($this->user('budi.santoso@email.com'));
        $this->getJson('/api/pengaturan-disposisi')->assertForbidden();
    }

    public function test_admin_saves_disposition_settings_through_the_api(): void
    {
        $service = Service::firstOrFail();
        Sanctum::actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->putJson('/api/pengaturan-disposisi/' . $service->slug, ['mode' => 'kepsek', 'penerima' => ['waka_humas'], 'anjuran_tanda_tangan' => 'ttd'])
            ->assertOk()
            ->assertJsonPath('mode', 'kepsek')
            ->assertJsonPath('penerima', ['waka_humas']);

        $service->refresh();
        $this->assertSame(['kepala_sekolah'], $service->approval_roles);
        $this->assertSame('ttd', $service->signature_recommendation);
        $this->assertDatabaseHas('activity_log', ['description' => 'Mengubah pengaturan disposisi']);

        $this->putJson('/api/pengaturan-disposisi/' . $service->slug, ['mode' => 'semua', 'penerima' => ['kepala_dinas']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['mode', 'penerima.0']);

        Sanctum::actingAs($this->user('katu@mtsn2malang.sch.id'));
        $this->putJson('/api/pengaturan-disposisi/' . $service->slug, ['mode' => 'none'])->assertForbidden();
    }
}
