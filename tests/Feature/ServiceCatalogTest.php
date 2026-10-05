<?php

namespace Tests\Feature;

use App\Filament\Resources\ServiceResource;
use App\Filament\Resources\ServiceResource\Pages\ListServices;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Katalog Layanan list for the admin. */
class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
    }

    public function test_catalog_shows_counts_and_active_tabs(): void
    {
        $service = Service::where('is_active', true)->firstOrFail();
        $inactive = Service::where('id', '!=', $service->id)->firstOrFail();
        $inactive->update(['is_active' => false]);
        Ticket::factory()->create(['service_id' => $service->id, 'status' => 'in_process']);

        $this->get(ServiceResource::getUrl())->assertOk()->assertSee('Katalog Layanan')->assertSee('Permohonan berjalan');

        Livewire::test(ListServices::class)
            ->searchTable($service->name)
            ->assertTableColumnStateSet('open_tickets_count', Ticket::where('service_id', $service->id)->open()->count(), (string) $service->getKey());

        Livewire::test(ListServices::class)
            ->assertCountTableRecords(Service::count())
            ->set('activeTab', 'nonaktif')
            ->assertCanSeeTableRecords([$inactive])
            ->assertCanNotSeeTableRecords([$service])
            ->set('activeTab', 'aktif')
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_service_is_switched_on_and_off_from_the_list(): void
    {
        $service = Service::where('is_active', true)->firstOrFail();

        Ticket::factory()->create(['service_id' => $service->id, 'status' => 'in_process']);

        Livewire::test(ListServices::class)
            ->mountTableAction('toggleActive', $service)
            ->assertSee('permohonan berjalan; permohonan itu tetap diproses')
            ->callMountedTableAction()
            ->assertNotified('Layanan dinonaktifkan');
        $this->assertFalse($service->fresh()->is_active);
        $this->assertDatabaseHas('activity_log', ['description' => 'Menonaktifkan layanan ' . $service->name]);

        Livewire::test(\App\Filament\Resources\ServiceResource\Pages\ViewService::class, ['record' => $service->getRouteKey()])
            ->assertActionHasLabel('toggleActive', 'Aktifkan')
            ->callAction('toggleActive')
            ->assertNotified('Layanan diaktifkan');
        $this->assertTrue($service->fresh()->is_active);

        $two = Service::where('is_active', true)->limit(2)->get();
        Livewire::test(ListServices::class)->callTableBulkAction('deactivate', $two);
        $this->assertSame(0, Service::whereIn('id', $two->pluck('id'))->where('is_active', true)->count());
    }

    public function test_filters_by_applicant_type_and_mode(): void
    {
        $forStudents = Service::factory()->create(['name' => 'Khusus Siswa', 'user_types_allowed' => ['siswa'], 'mode' => 'offline']);
        $forAgencies = Service::factory()->create(['name' => 'Khusus Instansi', 'user_types_allowed' => ['instansi'], 'mode' => 'online']);

        Livewire::test(ListServices::class)
            ->filterTable('user_type', 'siswa')
            ->assertCanSeeTableRecords([$forStudents])
            ->assertCanNotSeeTableRecords([$forAgencies])
            ->resetTableFilters()
            ->filterTable('mode', 'online')
            ->assertCanSeeTableRecords([$forAgencies])
            ->assertCanNotSeeTableRecords([$forStudents]);
    }

    public function test_admin_adds_a_service_with_category_applicants_and_disposition(): void
    {
        $category = \App\Models\ServiceCategory::firstOrFail();

        Livewire::test(\App\Filament\Resources\ServiceResource\Pages\CreateService::class)
            ->fillForm([
                'name' => 'Surat Keterangan Lulus',
                'code' => 'SKL',
                'categories' => [$category->id],
                'mode' => 'hybrid',
                'processing_time' => '1-2 hari kerja',
                'user_types_allowed' => ['alumni', 'walimurid'],
                'disposition_mode' => 'tu',
                'disposition_roles' => ['tata_usaha'],
                'signature_recommendation' => 'tte',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::where('code', 'SKL')->firstOrFail();
        $this->assertSame([$category->id], $service->categories->pluck('id')->all());
        $this->assertSame(['alumni', 'walimurid'], $service->user_types_allowed);
        $this->assertSame(['tu', ['tata_usaha'], 'tte'], [$service->disposition_mode, $service->disposition_roles, $service->signature_recommendation]);
        $this->assertTrue($service->approval_required);
        $this->assertNotEmpty($service->slug);
    }

    public function test_service_code_must_be_unique_and_mode_none_needs_no_disposition(): void
    {
        $existing = Service::firstOrFail();

        Livewire::test(\App\Filament\Resources\ServiceResource\Pages\CreateService::class)
            ->fillForm(['name' => 'Duplikat', 'code' => $existing->code, 'mode' => 'online', 'disposition_mode' => 'kepsek_tu'])
            ->call('create')
            ->assertHasFormErrors(['code' => 'unique']);

        Livewire::test(\App\Filament\Resources\ServiceResource\Pages\EditService::class, ['record' => $existing->getRouteKey()])
            ->fillForm(['disposition_mode' => 'none', 'signature_recommendation' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $existing->refresh();
        $this->assertFalse($existing->approval_required);
        $this->assertNull($existing->signature_recommendation);
    }

    public function test_detail_page_shows_requirements_flow_templates_and_disposition(): void
    {
        $service = Service::factory()->create([
            'name' => 'Legalisir Rapor', 'requirements' => '<p>Rapor asli dan fotokopi</p>', 'mechanism' => '<p>Serahkan di loket</p>',
            'fee' => 0, 'user_types_allowed' => ['siswa'], 'is_active' => true,
        ]);
        \App\Support\ServiceDisposition::apply($service, 'tu', ['tata_usaha'], 'ttd');
        $service->requirements()->create(['requirement_name' => 'Fotokopi rapor', 'is_required' => true]);
        $workflow = \App\Models\Workflow::create(['service_id' => $service->id, 'name' => 'Alur legalisir', 'is_active' => true]);
        $workflow->steps()->create(['step_number' => 2, 'name' => 'Tanda tangan Kepala', 'estimated_duration_days' => 1]);
        $workflow->steps()->create(['step_number' => 1, 'name' => 'Verifikasi berkas', 'estimated_duration_days' => 1]);

        $this->get(ServiceResource::getUrl('view', ['record' => $service]))->assertOk()
            ->assertSee('Rapor asli dan fotokopi')
            ->assertSee('Fotokopi rapor')
            ->assertSeeInOrder(['1. Verifikasi berkas', '2. Tanda tangan Kepala'])
            ->assertSee('Gratis')
            ->assertSee('Siswa')
            ->assertSee('Kepala TU saja')
            ->assertSee('Tata Usaha')
            ->assertSee('TTD (tanda tangan basah)')
            ->assertSee('Belum ada template berkas.');
    }

    public function test_opening_a_request_starts_the_service_workflow_at_its_first_step(): void
    {
        $service = Service::factory()->create(['approval_required' => false]);
        $workflow = \App\Models\Workflow::create(['service_id' => $service->id, 'name' => 'Alur', 'is_active' => true]);
        $second = $workflow->steps()->create(['step_number' => 2, 'name' => 'Proses', 'estimated_duration_days' => 1]);
        $first = $workflow->steps()->create(['step_number' => 1, 'name' => 'Verifikasi', 'estimated_duration_days' => 1]);
        $applicant = User::factory()->create();

        $ticket = app(\App\Services\TicketService::class)->open($service, $applicant, $applicant, 'online', 'Permohonan uji.');

        $this->assertSame($first->id, $ticket->ticketWorkflows()->first()->current_step_id);
    }

    public function test_public_catalog_has_a_no_results_state_for_filters(): void
    {
        auth()->logout();

        $this->get(route('onlineportal.service.catalog'))->assertOk()
            ->assertSee('id="noResults"', false)
            ->assertSee('Tidak ada layanan yang cocok')
            ->assertSee('Tampilkan semua layanan')
            ->assertDontSee('Katalog layanan gagal dimuat');
    }

    public function test_public_catalog_explains_when_no_service_is_available(): void
    {
        auth()->logout();
        Service::query()->update(['is_active' => false]);

        $this->get(route('onlineportal.service.catalog'))->assertOk()
            ->assertSee('Belum ada layanan tersedia')
            ->assertSee('untuk melihat layanan khusus sesuai peran Anda');
    }

    public function test_public_catalog_offers_a_retry_when_loading_fails(): void
    {
        auth()->logout();
        \Illuminate\Support\Facades\DB::listen(function ($query) {
            if (str_contains($query->sql, 'from "services"')) {
                throw new \Illuminate\Database\QueryException('pgsql', $query->sql, [], new \Exception('server closed the connection'));
            }
        });

        $this->get(route('onlineportal.service.catalog'))->assertStatus(503)
            ->assertSee('Katalog layanan gagal dimuat')
            ->assertSee('Coba lagi');
    }
}
