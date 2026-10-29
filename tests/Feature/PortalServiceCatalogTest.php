<?php

namespace Tests\Feature;

use App\Filament\Portal\Pages\ApplyService;
use App\Filament\Portal\Pages\ServiceCatalog;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceRichTextSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** /portal/layanan: the portal's own service catalogue. */
class PortalServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    private User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->seed(ServiceRichTextSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('portal'));
        $this->applicant = User::where('email', 'ahmad.rizki.parent@gmail.com')->firstOrFail(); // walimurid
        $this->actingAs($this->applicant);
    }

    private function offered()
    {
        return Service::where('is_active', true)->availableFor('walimurid')->orderBy('name')->get();
    }

    public function test_the_portal_menu_opens_its_own_catalogue(): void
    {
        $this->get(ServiceCatalog::getUrl())->assertOk()
            ->assertSee('Katalog Layanan')
            ->assertSee('href="' . ServiceCatalog::getUrl() . '"', false)
            ->assertDontSee('href="' . route('onlineportal.service.catalog') . '"', false);

        $component = Livewire::test(ServiceCatalog::class);
        $this->assertSame($this->offered()->pluck('id')->all(), $component->instance()->services->pluck('id')->all());
    }

    public function test_an_online_service_is_applied_for_from_its_card_and_an_offline_one_at_the_counter(): void
    {
        $online = $this->offered()->firstWhere('mode', 'online');
        $offline = $this->offered()->firstWhere('mode', 'offline');

        Livewire::test(ServiceCatalog::class)
            ->assertSeeHtml('href="' . ApplyService::getUrl(['layanan' => $online->slug]) . '"')
            ->assertDontSeeHtml('href="' . ApplyService::getUrl(['layanan' => $offline->slug]) . '"')
            ->assertSee('Ajukan di loket PTSP');
    }

    public function test_each_card_says_what_to_prepare(): void
    {
        $service = $this->offered()->first(fn (Service $s) => $s->requirements()->count() > 0);
        $required = $service->requirements()->count();
        \App\Models\ServiceTemplate::create(['service_id' => $service->id, 'nama' => 'Formulir permohonan', 'file_path' => 'service-templates/formulir.pdf',
            'file_name' => 'formulir.pdf', 'mime_type' => 'application/pdf', 'file_size' => 1024, 'is_required' => true, 'sort' => 1]);

        $html = Livewire::test(ServiceCatalog::class)->html();
        $card = substr($html, strpos($html, 'data-service-card="' . $service->slug . '"'));
        $card = substr($card, 0, strpos($card, 'Standar pelayanan'));

        $this->assertStringContainsString("{$required} berkas persyaratan", $card);
        $this->assertStringContainsString('1 template berkas', $card);
        $this->assertStringContainsString('Waktu proses', $card);
    }

    public function test_the_page_s_button_picks_an_online_service_and_opens_its_form(): void
    {
        $online = $this->offered()->firstWhere('mode', 'online');
        $offline = $this->offered()->firstWhere('mode', 'offline');

        Livewire::test(ServiceCatalog::class)
            ->mountAction('apply')
            ->assertActionMounted('apply')
            ->assertFormFieldExists('service', 'mountedActionForm', fn ($field) => array_key_exists($online->slug, $field->getOptions()) && ! array_key_exists($offline->slug, $field->getOptions()))
            ->callMountedAction(['service' => null])
            ->assertHasActionErrors(['service' => 'required']);

        Livewire::test(ServiceCatalog::class)
            ->callAction('apply', ['service' => $online->slug])
            ->assertRedirect(ApplyService::getUrl(['layanan' => $online->slug]));
    }

    public function test_a_card_mentions_the_applicant_s_request_still_in_progress(): void
    {
        $service = $this->offered()->firstWhere('mode', 'online');
        $ticket = app(\App\Services\TicketService::class)->open($service, $this->applicant, $this->applicant, 'online', 'Untuk keperluan sekolah.');

        $html = Livewire::test(ServiceCatalog::class)->html();
        $card = substr($html, strpos($html, 'data-service-card="' . $service->slug . '"'));
        $card = substr($card, 0, strpos($card, 'Standar pelayanan'));
        $this->assertStringContainsString('Permohonan Anda masih berjalan', $card);
        $this->assertStringContainsString($ticket->ticket_number, $card);
        $this->assertStringContainsString('href="' . ApplyService::getUrl(['layanan' => $service->slug]) . '"', $card); // still allowed

        // Finished requests are not mentioned.
        $ticket->forceFill(['status' => 'completed'])->saveQuietly();
        $this->assertStringNotContainsString($ticket->ticket_number, Livewire::test(ServiceCatalog::class)->html());
    }

    public function test_an_account_with_several_roles_sees_each_role_s_services(): void
    {
        $person = User::factory()->create(['user_type' => 'guru']);
        $person->assignRole('guru', 'walimurid'); // a teacher who is also a parent
        $this->actingAs($person);
        $forTeachers = Service::factory()->create(['name' => 'Cuti Guru', 'is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['guru']]);
        $forParents = Service::factory()->create(['name' => 'Izin Anak', 'is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['walimurid']]);
        $forStudents = Service::factory()->create(['name' => 'Kartu Pelajar', 'is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['siswa']]);
        $ids = fn ($component) => $component->instance()->services->pluck('id');

        $component = Livewire::test(ServiceCatalog::class)
            ->assertSee('Semua peran saya')
            ->assertSeeHtml('data-service-roles');
        $this->assertSame(['walimurid', 'guru'], $component->instance()->roles);
        $this->assertTrue($ids($component)->contains($forTeachers->id) && $ids($component)->contains($forParents->id));
        $this->assertFalse($ids($component)->contains($forStudents->id));
        $this->assertSame(['guru'], $component->instance()->rolesFor($forTeachers));

        $component->set('role', 'walimurid');
        $this->assertFalse($ids($component)->contains($forTeachers->id));
        $this->assertTrue($ids($component)->contains($forParents->id));

        $component->set('role', 'siswa'); // not theirs: every role of theirs
        $this->assertTrue($ids($component)->contains($forTeachers->id) && $ids($component)->contains($forParents->id));
    }

    public function test_an_account_with_one_role_has_no_role_to_pick(): void
    {
        Livewire::test(ServiceCatalog::class)
            ->assertDontSee('Semua peran saya')
            ->assertDontSeeHtml('data-service-roles');
    }

    public function test_search_and_category_narrow_the_cards(): void
    {
        $legalisir = Service::where('code', 'LDK-023')->firstOrFail();
        $category = $legalisir->categories()->firstOrFail();

        Livewire::test(ServiceCatalog::class)
            ->set('search', 'kelulusan')
            ->assertSet('search', 'kelulusan')
            ->tap(fn ($c) => $this->assertSame([$legalisir->id], $c->instance()->services->pluck('id')->all()))
            ->set('search', 'LDK-0')
            ->tap(fn ($c) => $this->assertSame([$legalisir->id], $c->instance()->services->pluck('id')->all()))
            ->set('search', 'tidak ada layanan begini')
            ->assertSee('Tidak ada layanan yang cocok')
            ->call('resetFilters')
            ->set('category', (string) $category->id)
            ->tap(fn ($c) => $this->assertTrue($c->instance()->services->every(fn (Service $s) => $s->categories->contains('id', $category->id))));
    }

    public function test_the_service_standard_opens_in_the_portal(): void
    {
        $service = Service::where('code', 'SKP-021')->firstOrFail();

        Livewire::test(ServiceCatalog::class)
            ->mountAction('standard', ['service' => $service->id])
            ->assertActionMounted('standard')
            ->assertSee($service->name)
            ->assertSee('Penerbitan surat keterangan pindah');
    }

    public function test_the_ajukan_layanan_menu_asks_for_the_service_first(): void
    {
        $online = $this->offered()->firstWhere('mode', 'online');
        $offline = $this->offered()->firstWhere('mode', 'offline');
        $notTheirs = Service::factory()->create(['is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['siswa']]);

        $this->get(ServiceCatalog::getUrl())->assertOk()
            ->assertSee('Ajukan Layanan')
            ->assertSee('href="' . ApplyService::getUrl() . '"', false);

        $picker = Livewire::test(ApplyService::class)
            ->assertNoRedirect()
            ->assertSee('Pilih layanan yang ingin Anda ajukan')
            ->assertFormFieldExists('service', fn ($field) => array_key_exists($online->slug, $field->getOptions())
                && ! array_key_exists($offline->slug, $field->getOptions())
                && ! array_key_exists($notTheirs->slug, $field->getOptions()))
            ->assertFormFieldIsHidden('description');

        // The picker's state exists from the start (the browser's select is bound to it).
        $this->assertArrayHasKey('service', $picker->get('data'));
        $picker->call('submit')->assertHasFormErrors(['service' => 'required']);
        $this->assertSame('Pilih layanan yang ingin Anda ajukan.', $picker->errors()->first('data.service'));
        $this->assertDatabaseCount('tickets', \App\Models\Ticket::count());

        $picker->fillForm(['service' => $online->slug])->call('submit')->assertRedirect(ApplyService::getUrl(['layanan' => $online->slug]));
    }

    public function test_the_form_of_a_service_can_switch_to_another(): void
    {
        $online = $this->offered()->firstWhere('mode', 'online');

        Livewire::withQueryParams(['layanan' => $online->slug])->test(ApplyService::class)
            ->assertFormFieldIsVisible('description')
            ->assertFormFieldIsHidden('service')
            ->assertSee('Ganti layanan')
            ->assertSeeHtml('href="' . ApplyService::getUrl() . '"');
    }

    public function test_the_api_lists_what_the_portal_catalogue_shows(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->applicant);
        $page = Livewire::test(ServiceCatalog::class)->instance();
        $online = $page->offered->filter(fn (Service $s) => in_array($s->mode, ['online', 'hybrid'], true));

        // By default, as before: the services requested online.
        $this->assertSame($online->pluck('slug')->values()->all(), collect($this->getJson('/api/permohonan/layanan')->assertOk()->json('data'))->pluck('slug')->all());

        $all = $this->getJson('/api/permohonan/layanan?jalur=semua')->assertOk();
        $this->assertSame($page->offered->pluck('slug')->all(), collect($all->json('data'))->pluck('slug')->all());
        $this->assertSame($page->offered->count(), $all->json('total'));
        $offline = collect($all->json('data'))->firstWhere('jalur', 'offline');
        $this->assertFalse($offline['bisa_diajukan_online']);
        $this->assertSame($page->categories->count(), count($all->json('kategori')));

        $legalisir = Service::where('code', 'LDK-023')->firstOrFail();
        $this->getJson('/api/permohonan/layanan?jalur=semua&q=ldk-0')->assertJsonPath('total', 1)->assertJsonPath('data.0.slug', $legalisir->slug)
            ->assertJsonPath('data.0.kode', 'LDK-023')
            ->assertJsonPath('data.0.ringkasan', 'Legalisir ijazah, SKHUN, dan surat keterangan lulus untuk alumni.');
        $category = $legalisir->categories()->firstOrFail();
        $this->assertTrue(collect($this->getJson("/api/permohonan/layanan?jalur=semua&kategori={$category->id}")->json('data'))->every(fn ($row) => in_array($category->name, $row['kategori'], true)));
        $this->getJson('/api/permohonan/layanan?jalur=lain')->assertJsonValidationErrors('jalur');
    }

    public function test_the_api_says_which_service_has_a_request_in_progress(): void
    {
        $service = $this->offered()->firstWhere('mode', 'online');
        $ticket = app(\App\Services\TicketService::class)->open($service, $this->applicant, $this->applicant, 'online', 'Untuk keperluan sekolah.');
        \Laravel\Sanctum\Sanctum::actingAs($this->applicant);

        $row = collect($this->getJson('/api/permohonan/layanan')->json('data'))->firstWhere('slug', $service->slug);
        $this->assertSame($ticket->ticket_number, $row['permohonan_berjalan']);
        $this->assertIsInt($row['jumlah_persyaratan']);
    }

    public function test_the_api_filters_by_one_of_the_account_s_roles(): void
    {
        $person = User::factory()->create(['user_type' => 'guru']);
        $person->assignRole('guru', 'walimurid');
        $forTeachers = Service::factory()->create(['name' => 'Cuti Guru', 'is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['guru']]);
        $forParents = Service::factory()->create(['name' => 'Izin Anak', 'is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['walimurid']]);
        \Laravel\Sanctum\Sanctum::actingAs($person);

        $all = $this->getJson('/api/permohonan/layanan')->assertOk()
            ->assertJsonPath('peran_saya', [['peran' => 'walimurid', 'label' => 'Wali Murid'], ['peran' => 'guru', 'label' => 'Guru']]);
        $rows = collect($all->json('data'))->keyBy('slug');
        $this->assertSame([['peran' => 'guru', 'label' => 'Guru']], $rows[$forTeachers->slug]['untuk']);
        $this->assertTrue($rows->has($forParents->slug));

        $parents = collect($this->getJson('/api/permohonan/layanan?peran=walimurid')->assertOk()->json('data'))->pluck('slug');
        $this->assertContains($forParents->slug, $parents);
        $this->assertNotContains($forTeachers->slug, $parents);

        $this->getJson('/api/permohonan/layanan?peran=siswa')
            ->assertJsonValidationErrors(['peran' => 'Peran itu bukan peran pemohon akun Anda.']);
    }

    public function test_an_account_applies_for_what_any_of_its_roles_may_request(): void
    {
        $person = User::factory()->create(['user_type' => 'guru']);
        $person->assignRole('guru', 'walimurid'); // the type says guru; the account is a parent too
        $forParents = Service::factory()->create(['name' => 'Izin Anak', 'is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['walimurid']]);
        $forStudents = Service::factory()->create(['name' => 'Kartu Pelajar', 'is_active' => true, 'mode' => 'online', 'user_types_allowed' => ['siswa']]);

        $this->assertTrue($forParents->acceptsRequests($person));
        $this->assertFalse($forStudents->acceptsRequests($person));

        // The portal's form opens it, and the ticket is for that service.
        $this->actingAs($person);
        Livewire::withQueryParams(['layanan' => $forParents->slug])->test(ApplyService::class)
            ->assertNoRedirect()
            ->fillForm(['description' => 'Izin acara keluarga.', 'priority' => 'normal'])
            ->call('submit')
            ->assertHasNoFormErrors();
        $this->assertSame($forParents->id, \App\Models\Ticket::where('user_id', $person->id)->latest('id')->value('service_id'));
        Livewire::withQueryParams(['layanan' => $forStudents->slug])->test(ApplyService::class)->assertRedirect(ServiceCatalog::getUrl());

        // And the API, likewise.
        \Laravel\Sanctum\Sanctum::actingAs($person);
        $this->postJson('/api/permohonan', ['layanan' => $forParents->slug, 'keterangan' => 'Lewat aplikasi.'])->assertCreated();
        $this->assertSame(2, \App\Models\Ticket::where('user_id', $person->id)->where('service_id', $forParents->id)->count());
        $this->postJson('/api/permohonan', ['layanan' => $forStudents->slug, 'keterangan' => 'Coba.'])->assertStatus(422);
    }

    public function test_the_request_form_goes_back_to_the_portal_catalogue(): void
    {
        $other = Service::where('is_active', true)->whereJsonContains('user_types_allowed', 'pegawai')->whereJsonDoesntContain('user_types_allowed', 'walimurid')->firstOrFail();

        Livewire::withQueryParams(['layanan' => $other->slug])->test(ApplyService::class)->assertRedirect(ServiceCatalog::getUrl());
    }
}
