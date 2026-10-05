<?php

namespace Tests\Feature;

use App\Filament\Pages\System\Settings;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Halaman Pengaturan dengan tab bagian. */
class SettingsPageTest extends TestCase
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

    public function test_page_shows_the_current_settings_in_tabs(): void
    {
        AppSetting::set('contact_phone', '0341-551234');

        $this->get(Settings::getUrl())->assertOk()
            ->assertSee(['Identitas', 'Kontak & Jam Layanan', 'Media Sosial & Tautan', 'Kop Surat', 'Situs']);

        Livewire::test(Settings::class)->assertFormSet(['contact_phone' => '0341-551234']);
    }

    public function test_saving_updates_what_the_site_and_documents_use(): void
    {
        Storage::fake('public');

        Livewire::test(Settings::class)
            ->fillForm([
                'app_name' => 'PTSP MTsN 2 MALANG',
                'app_name_full' => 'MTsN 2 Kota Malang',
                'contact_email' => 'ptsp@mtsn2malang.sch.id',
                'operating_hours_friday' => '07.00–11.00',
                'letterhead_line_2' => 'Kantor Kemenag Kota Malang',
                'enable_maintenance' => false,
                'app_logo' => UploadedFile::fake()->image('logo.png', 200, 200),
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Pengaturan disimpan');
        $this->assertDatabaseHas('activity_log', ['description' => 'Mengubah pengaturan aplikasi: Nama singkat, Nama lengkap instansi, Logo, Email, Jumat, Baris kop 2']);

        $this->assertSame('07.00–11.00', AppSetting::get('operating_hours_friday'));
        $this->assertFalse(filter_var(AppSetting::where('key', 'enable_maintenance')->value('value'), FILTER_VALIDATE_BOOLEAN));
        $logo = AppSetting::where('key', 'app_logo')->value('value');
        $this->assertStringStartsWith('storage/branding/', $logo);
        Storage::disk('public')->assertExists(substr($logo, 8));

        $this->assertContains('Kantor Kemenag Kota Malang', \App\Support\Letterhead::data()['lines']);
        auth()->logout();
        $this->getJson('/api/publik/profil')->assertOk()->assertJsonPath('jam_layanan.jumat', '07.00–11.00');
    }

    public function test_invalid_values_are_refused_and_page_is_admin_only(): void
    {
        Livewire::test(Settings::class)
            ->fillForm(['app_name' => '', 'contact_email' => 'bukan-email', 'social_facebook' => 'facebook'])
            ->call('save')
            ->assertHasFormErrors(['app_name', 'contact_email', 'social_facebook']);

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->get(Settings::getUrl())->assertForbidden();
    }

    public function test_institution_profile_feeds_the_report_signature(): void
    {
        Livewire::test(Settings::class)
            ->assertSee('Logo saat ini')
            ->assertSee('Belum ada logo.') // no logo setting yet (defaults)
            ->fillForm(['app_name' => 'PTSP', 'app_name_full' => 'MTsN 2 Kota Malang', 'institution_npsn' => '20533801',
                'headmaster_name' => 'Drs. H. Ahmad Fauzi, M.Pd.', 'headmaster_nip' => '196801011990031005'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['title' => 'Kepala Madrasah', 'name' => 'Drs. H. Ahmad Fauzi, M.Pd.', 'nip' => '196801011990031005'], \App\Support\Letterhead::signatory());
        $this->get(route('reports.monthly.print'))->assertOk()->assertSee('Drs. H. Ahmad Fauzi, M.Pd.')->assertSee('NIP. 196801011990031005');

        Livewire::test(Settings::class)
            ->fillForm(['app_name' => 'PTSP', 'app_name_full' => 'MTsN', 'institution_npsn' => '123', 'headmaster_nip' => 'abc'])
            ->call('save')
            ->assertHasFormErrors(['institution_npsn' => 'regex', 'headmaster_nip' => 'regex']);
    }

    public function test_number_patterns_and_date_format_with_preview(): void
    {
        Livewire::test(Settings::class)
            ->fillForm(['app_name' => 'PTSP', 'app_name_full' => 'MTsN 2', 'ticket_prefix' => 'lyn', 'surat_kode_satker' => 'MTSN.2', 'document_date_format' => 'd/m/Y'])
            ->assertSee('LYN-' . now()->format('Ym') . '-0007')
            ->assertSee('B-12/MTSN.2/PP.00/')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('LYN', \App\Support\Formats::ticketPrefix());
        $this->assertSame('d/m/Y', \App\Support\Formats::dateFormat());

        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $service = \App\Models\Service::requestable('umum', 'online')->firstOrFail();
        $ticket = app(\App\Services\TicketService::class)->open($service, $applicant, $applicant, 'online', 'Uji nomor.');
        $this->assertSame('LYN-' . now()->format('Ym') . '-0001', $ticket->ticket_number);
        $next = app(\App\Services\TicketService::class)->open($service, $applicant, $applicant, 'online', 'Uji nomor 2.');
        $this->assertSame('LYN-' . now()->format('Ym') . '-0002', $next->ticket_number);

        $this->get(route('tickets.receipt', $ticket))->assertOk()->assertSee(now()->format('d/m/Y'));
        $this->assertStringStartsWith('B-1/MTSN.2/', \App\Support\SuratKeluarNumber::format(1, now()));

        Livewire::test(Settings::class)
            ->fillForm(['app_name' => 'PTSP', 'app_name_full' => 'MTsN 2', 'ticket_prefix' => 'P1', 'surat_kode_satker' => 'a b'])
            ->call('save')
            ->assertHasFormErrors(['ticket_prefix' => 'regex', 'surat_kode_satker' => 'regex']);
    }

    public function test_ticket_numbers_continue_after_existing_ones_and_never_repeat(): void
    {
        $period = now()->format('Ym');
        \Illuminate\Support\Facades\DB::table('ticket_sequences')->delete(); // as before the sequence table existed
        \App\Models\Ticket::factory()->create(['ticket_number' => "PTSP-{$period}-9041"]);

        $numbers = collect(range(1, 3))->map(fn () => \App\Services\TicketService::nextTicketNumber());

        $this->assertSame(["PTSP-{$period}-9042", "PTSP-{$period}-9043", "PTSP-{$period}-9044"], $numbers->all());
        $this->assertSame(1, \Illuminate\Support\Facades\DB::table('ticket_sequences')->where('prefix', 'PTSP')->count());
    }

    public function test_save_feedback_and_role_gates(): void
    {
        Livewire::test(Settings::class)
            ->fillForm(['app_name' => '', 'contact_email' => 'x'])
            ->call('save')
            ->assertNotified('Pengaturan belum disimpan');

        AppSetting::set('app_name', 'PTSP');
        AppSetting::set('app_name_full', 'MTsN 2');
        Livewire::test(Settings::class)->call('save')->assertNotified('Tidak ada perubahan');

        $this->assertTrue(\Filament\Facades\Filament::getPanel('admin')->hasUnsavedChangesAlerts());
    }

    /**
     * One role per case: a 403 aborts during mount, so Livewire's redirector
     * would leak into a second request (and a user switch) in the same test.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('nonAdminStaff')]
    public function test_settings_screens_are_for_admins_only(string $email): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
        $this->get(Settings::getUrl())->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nonAdminStaff')]
    public function test_advanced_settings_are_for_admins_only(string $email): void
    {
        $this->actingAs(User::where('email', $email)->firstOrFail());
        $this->get(\App\Filament\Resources\AppSettingResource::getUrl())->assertForbidden();
    }

    public static function nonAdminStaff(): array
    {
        return [
            'front desk' => ['staff1@mtsn2malang.sch.id'],
            'kepala madrasah' => ['kepsek@mtsn2malang.sch.id'],
        ];
    }
}
