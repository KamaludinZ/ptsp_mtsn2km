<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Daftar pengguna (admin). */
class UserListTest extends TestCase
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

    public function test_tabs_split_staff_applicants_and_deactivated_accounts(): void
    {
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $inactive = User::factory()->create(['name' => 'Akun Lama', 'is_active' => false]);

        $this->get(UserResource::getUrl())->assertOk()->assertSee('Akun petugas dan pemohon');

        Livewire::test(ListUsers::class)
            ->set('activeTab', 'petugas')->assertCanSeeTableRecords([$staff])->assertCanNotSeeTableRecords([$applicant])
            ->set('activeTab', 'pemohon')->assertCanSeeTableRecords([$applicant])->assertCanNotSeeTableRecords([$staff])
            ->set('activeTab', 'nonaktif')->assertCanSeeTableRecords([$inactive])->assertCanNotSeeTableRecords([$staff, $applicant]);
    }

    public function test_import_action_opens_upload_modal_and_requires_a_file(): void
    {
        $this->get(UserResource::getUrl())->assertOk()->assertSee('Unggah berkas akun');

        Livewire::test(ListUsers::class)
            ->assertActionExists('import')
            ->mountAction('import')
            ->assertSee('Import akun dari berkas')
            ->assertSee(['nomor_whatsapp', 'back_office', 'Penjamin Mutu', 'walimurid'])
            ->callMountedAction()
            ->assertHasActionErrors(['file' => 'required']);

        Livewire::test(ListUsers::class)
            ->mountAction('import')
            // The template button runs at once (no modal), leaving the import modal open.
            ->call('mountAction', 'template')
            ->assertFileDownloaded('template-import-akun.xlsx');
    }

    public function test_excel_button_follows_the_active_tab(): void
    {
        \Maatwebsite\Excel\Facades\Excel::fake();
        \Maatwebsite\Excel\Facades\Excel::matchByRegex();
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();

        Livewire::test(ListUsers::class)
            ->assertActionExists('excel')
            ->set('activeTab', 'petugas')
            ->callAction('excel');

        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('/^akun-pengguna-petugas-\d{8}-\d{6}\.xlsx$/', function (\App\Exports\UserExport $export) use ($staff) {
            $emails = $export->query()->pluck('email');

            return $export->title() === 'Akun Petugas'
                && $emails->contains($staff->email)
                && ! $emails->contains('budi.santoso@email.com')
                && $export->map($staff->fresh())[6] === 'Aktif'
                && ! in_array($staff->password, $export->map($staff->fresh()), true);
        });

        $this->assertSame('Petugas', Livewire::test(ListUsers::class)->set('activeTab', 'petugas')->instance()->activeTabLabel());
    }

    public function test_uploaded_file_creates_accounts_and_shows_the_report(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('akun.csv', implode("\n", [
            'nama,email,nomor_whatsapp,tipe_pengguna,kode_registrasi,role',
            'Guru Import,guru.import@contoh.sch.id,081234567890,guru,G-77,guru',
            'Ganda,staff1@mtsn2malang.sch.id,,pegawai,,',
        ]) . "\n");

        Livewire::test(ListUsers::class)
            ->callAction('import', data: ['file' => $file])
            ->assertHasNoActionErrors()
            ->assertSet('mountedActions', ['importReport'])
            ->assertSee(['Hasil import akun', 'staff1@mtsn2malang.sch.id', 'Email sudah terdaftar.']);

        $this->assertTrue(User::where('email', 'guru.import@contoh.sch.id')->firstOrFail()->must_change_password);
        $this->assertSame([], \Illuminate\Support\Facades\Storage::disk('local')->allFiles('imports'));
    }

    public function test_import_report_lists_created_count_and_failed_rows(): void
    {
        Livewire::test(ListUsers::class)
            ->mountAction('importReport', ['report' => ['created' => 5, 'failed' => [
                ['row' => 3, 'email' => 'dobel@contoh.sch.id', 'reason' => 'Email sudah terdaftar.'],
            ]]])
            ->assertSee(['Hasil import akun', 'Akun dibuat', 'dobel@contoh.sch.id', 'Email sudah terdaftar.'])
            ->assertDontSee('Contoh tampilan');
    }

    public function test_last_sign_in_and_role_filters(): void
    {
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $never = User::factory()->create(['name' => 'Belum Masuk']);
        $staff->forceFill(['last_login_at' => now()->subHours(3)])->save();
        $never->forceFill(['last_login_at' => null])->save();

        Livewire::test(ListUsers::class)
            ->searchTable('staff1')
            ->assertTableColumnStateSet('last_login_at', $staff->fresh()->last_login_at, (string) $staff->getKey())
            ->assertSee('3 jam yang lalu');

        Livewire::test(ListUsers::class)
            ->filterTable('never_logged_in', true)
            ->assertCanSeeTableRecords([$never])
            ->assertCanNotSeeTableRecords([$staff])
            ->resetTableFilters()
            ->filterTable('roles', [\Spatie\Permission\Models\Role::findByName('back_office')->id])
            ->assertCanSeeTableRecords([$staff])
            ->assertCanNotSeeTableRecords([$never]);
    }
}
