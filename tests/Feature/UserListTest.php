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

    public function test_applicant_tab_explains_the_applicant_roles_and_drops_the_legacy_type_column(): void
    {
        Livewire::test(ListUsers::class)
            ->assertTableColumnVisible('user_type')
            ->assertDontSee('Pemohon adalah akun berperan')
            ->set('activeTab', 'pemohon')
            ->assertSee('Pemohon adalah akun berperan Siswa, Wali Murid, Alumni, Masyarakat Umum, Instansi/Perusahaan, Guru, atau Pegawai.')
            ->assertTableColumnHidden('user_type')
            ->assertTableColumnVisible('roles.name');
    }

    public function test_applicant_tab_lists_accounts_by_applicant_role(): void
    {
        $teacherWaka = User::factory()->create(['name' => 'Guru Merangkap Waka', 'user_type' => 'guru']);
        $teacherWaka->syncRoles(['waka_humas', 'guru']);
        $walkIn = User::factory()->create(['name' => 'Walk-in Lama', 'user_type' => 'alumni']);
        \Spatie\Permission\Models\Role::findOrCreate('tamu_khusus');
        $otherRole = User::factory()->create(['name' => 'Peran Lain', 'user_type' => 'umum']);
        $otherRole->assignRole('tamu_khusus');
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();

        $ids = Livewire::test(ListUsers::class)->set('activeTab', 'pemohon')->instance()->getFilteredTableQuery()->pluck('id');

        $this->assertTrue($ids->contains($teacherWaka->id) && $ids->contains($walkIn->id));
        $this->assertFalse($ids->contains($otherRole->id) || $ids->contains($staff->id));

        $this->assertSame(User::applicants()->count(), (new ListUsers)->getTabs()['pemohon']->getBadge());
    }

    public function test_applicant_role_filter_follows_the_official_rule(): void
    {
        $student = User::factory()->create(['name' => 'Filter Siswa', 'user_type' => 'umum']);
        $student->assignRole('siswa');
        $legacy = User::factory()->create(['name' => 'Filter Walk-in', 'user_type' => 'siswa']);
        $staff = User::factory()->create(['name' => 'Filter Petugas', 'user_type' => 'siswa']);
        $staff->assignRole('back_office');

        $ids = Livewire::test(ListUsers::class)
            ->set('activeTab', 'pemohon')
            ->filterTable('applicant_role', 'siswa')
            ->instance()->getFilteredTableQuery()->pluck('id');

        $this->assertTrue($ids->contains($student->id) && $ids->contains($legacy->id));
        $this->assertFalse($ids->contains($staff->id));
        $this->assertTrue($ids->every(fn (int $id) => User::find($id)->isApplicant()));
    }

    public function test_applicant_tab_shows_identity_columns_and_finds_numbers_in_any_format(): void
    {
        $walkIn = User::factory()->create(['name' => 'Identitas Loket', 'email' => '081255557777@walkin.local', 'whatsapp_number' => '0812-5555-7777', 'user_type' => 'umum']);
        $walkIn->assignRole('umum');
        \App\Models\Ticket::factory()->count(2)->create(['user_id' => $walkIn->id]);
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();

        $page = Livewire::test(ListUsers::class)
            ->set('activeTab', 'pemohon')
            ->assertTableColumnVisible('tickets_count')
            ->searchTable('6281255557777')
            ->assertCanSeeTableRecords([$walkIn])
            ->assertSee('Akun loket, belum ada email')
            ->assertDontSee('081255557777@walkin.local');
        $this->assertSame(2, (int) $page->instance()->getTableRecords()->firstWhere('id', $walkIn->id)->tickets_count);

        $page->searchTable('5555-77')->assertCanSeeTableRecords([$walkIn]);

        // Kolom permohonan khusus tab Pemohon; pencarian nomor tetap berlaku untuk petugas.
        Livewire::test(ListUsers::class)
            ->set('activeTab', 'petugas')
            ->assertTableColumnHidden('tickets_count')
            ->searchTable((string) $staff->whatsapp_number ?: $staff->name)
            ->assertCanSeeTableRecords([$staff]);
    }

    public function test_applicant_account_opens_for_fixing_from_the_applicant_tab(): void
    {
        $applicant = User::factory()->create(['name' => 'Perlu Diperbaiki', 'user_type' => 'umum', 'whatsapp_number' => '081200001234']);
        $applicant->assignRole('umum');
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();

        // Baris tab Pemohon membuka halaman ubah akun.
        Livewire::test(ListUsers::class)
            ->set('activeTab', 'pemohon')
            ->searchTable('Perlu Diperbaiki')
            ->assertSee(UserResource::getUrl('edit', ['record' => $applicant]), false);

        $this->get(UserResource::getUrl('edit', ['record' => $applicant]))->assertOk()
            ->assertSee('perbaikan di sini langsung berlaku di loket')
            ->assertSee('Kembali ke tab Pemohon');
        $this->get(UserResource::getUrl('edit', ['record' => $staff]))->assertOk()
            ->assertDontSee('Kembali ke tab Pemohon');

        Livewire::test(\App\Filament\Resources\UserResource\Pages\EditUser::class, ['record' => $applicant->getKey()])
            ->fillForm(['whatsapp_number' => '081200009999'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('081200009999', $applicant->fresh()->whatsapp_number);
        $this->assertTrue($applicant->fresh()->isApplicant());
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
            ->assertActionHasLabel('excel', 'Unduh Excel')
            ->set('activeTab', 'petugas')
            ->assertActionHasLabel('excel', 'Unduh Excel (Petugas)')
            ->callAction('excel')
            ->assertNotified();

        Livewire::test(ListUsers::class)
            ->searchTable('tidak-ada-akun-seperti-ini')
            ->callAction('excel')
            ->assertNotified('Tidak ada akun untuk diunduh')
            ->assertNoFileDownloaded();

        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('/^akun-pengguna-petugas-\d{8}-\d{6}\.xlsx$/', function (\App\Exports\UserExport $export) use ($staff) {
            $emails = $export->query()->pluck('email');

            return $export->title() === 'Akun Petugas'
                && $emails->contains($staff->email)
                && ! $emails->contains('budi.santoso@email.com')
                && $export->map($staff->fresh())[7] === 'Aktif'
                && ! in_array($staff->password, $export->map($staff->fresh()), true);
        });

        $log = \Spatie\Activitylog\Models\Activity::where('log_name', 'audit')->where('description', 'Mengekspor data akun pengguna')->latest('id')->firstOrFail();
        $this->assertSame(auth()->id(), $log->causer_id);
        $this->assertSame('Petugas', $log->properties['tab']);

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

        $log = \Spatie\Activitylog\Models\Activity::where('log_name', 'audit')->where('description', 'Mengimport akun pengguna')->latest('id')->firstOrFail();
        $this->assertSame(auth()->id(), $log->causer_id);
        $this->assertSame(1, $log->properties['created']);
        $this->assertSame([['row' => 3, 'reason' => 'Email sudah terdaftar.']], $log->properties['failed']);
    }

    public function test_export_query_matches_every_tab(): void
    {
        \Maatwebsite\Excel\Facades\Excel::fake();
        \Maatwebsite\Excel\Facades\Excel::matchByRegex();
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $applicant = User::where('email', 'budi.santoso@email.com')->firstOrFail();
        $inactive = User::factory()->create(['email' => 'lama@contoh.id', 'is_active' => false]);

        foreach ([
            'semua' => [[$staff, $applicant, $inactive], []],
            'petugas' => [[$staff], [$applicant]],
            'pemohon' => [[$applicant], [$staff]],
            'nonaktif' => [[$inactive], [$staff, $applicant]],
        ] as $tab => [$in, $out]) {
            Livewire::test(ListUsers::class)->set('activeTab', $tab)->callAction('excel');

            \Maatwebsite\Excel\Facades\Excel::assertDownloaded('/^akun-pengguna-' . $tab . '-/', function (\App\Exports\UserExport $export) use ($in, $out) {
                $emails = $export->query()->pluck('email');

                return collect($in)->every(fn (User $u) => $emails->contains($u->email))
                    && collect($out)->every(fn (User $u) => ! $emails->contains($u->email));
            });
        }
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
