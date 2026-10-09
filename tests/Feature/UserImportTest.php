<?php

namespace Tests\Feature;

use App\Imports\UserImport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Import akun masal dari berkas CSV/Excel. */
class UserImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \App\Support\RoleAccess::sync();
        foreach (['guru', 'siswa', 'umum'] as $role) {
            \Spatie\Permission\Models\Role::findOrCreate($role, 'web');
        }
    }

    private function csv(array $rows): UploadedFile
    {
        $lines = array_map(fn (array $row) => implode(',', $row), [['nama', 'email', 'nomor_whatsapp', 'tipe_pengguna', 'kode_registrasi', 'role'], ...$rows]);

        return UploadedFile::fake()->createWithContent('akun.csv', implode("\n", $lines) . "\n");
    }

    public function test_valid_rows_become_accounts_and_invalid_rows_are_reported(): void
    {
        Mail::fake();
        Notification::fake();
        User::factory()->create(['email' => 'lama@contoh.sch.id']);

        $import = new UserImport;
        Excel::import($import, $this->csv([
            ['Bu Sari', 'Sari@Contoh.sch.id', '0812-3456-7890', 'guru', 'GURU-01', '"guru, back_office"'],
            ['Andi', 'andi@contoh.sch.id', '81298765432', 'siswa', '', ''],
            ['Lama', 'lama@contoh.sch.id', '', 'guru', '', 'guru'],
            ['Salah', 'bukan-email', '', 'guru', '', 'guru'],
            ['Kembar', 'sari@contoh.sch.id', '', 'guru', '', 'guru'],
            ['Peran', 'peran@contoh.sch.id', '', 'guru', '', 'operator'],
            ['Tipe', 'tipe@contoh.sch.id', '', 'tamu', '', ''],
            ['', '', '', '', '', ''],
        ]));

        $this->assertSame(2, $import->report()['created']);
        $this->assertSame([
            ['row' => 4, 'email' => 'lama@contoh.sch.id', 'reason' => 'Email sudah terdaftar.'],
            ['row' => 5, 'email' => 'bukan-email', 'reason' => 'Format email tidak valid.'],
            ['row' => 6, 'email' => 'sari@contoh.sch.id', 'reason' => 'Email sudah terdaftar.'],
            ['row' => 7, 'email' => 'peran@contoh.sch.id', 'reason' => 'Role "operator" tidak dikenal.'],
            ['row' => 8, 'email' => 'tipe@contoh.sch.id', 'reason' => 'Tipe pengguna "tamu" tidak dikenal.'],
        ], $import->report()['failed']);

        $sari = User::where('email', 'sari@contoh.sch.id')->firstOrFail();
        $this->assertSame('081234567890', $sari->whatsapp_number);
        $this->assertSame('GURU-01', $sari->registration_code);
        $this->assertTrue($sari->is_active);
        $this->assertTrue($sari->must_change_password);
        $this->assertNotNull($sari->email_verified_at);
        $this->assertEqualsCanonicalizing(['guru', 'back_office'], $sari->getRoleNames()->all());

        $andi = User::where('email', 'andi@contoh.sch.id')->firstOrFail();
        $this->assertSame('081298765432', $andi->whatsapp_number);
        $this->assertSame(['siswa'], $andi->getRoleNames()->all());

        // Tanpa email undangan atau kata sandi yang dikirim.
        Mail::assertNothingSent();
        Notification::assertNothingSent();
    }

    public function test_each_account_gets_its_own_unknown_password_and_whatsapp_number(): void
    {
        $import = new UserImport;
        Excel::import($import, $this->csv([
            ['Satu', 'satu@contoh.id', '+62 812 1111 2222', 'umum', '', ''],
            ['Dua', 'dua@contoh.id', '', 'umum', '', ''],
        ]));

        [$satu, $dua] = [User::where('email', 'satu@contoh.id')->firstOrFail(), User::where('email', 'dua@contoh.id')->firstOrFail()];

        $this->assertSame('+6281211112222', $satu->whatsapp_number);
        $this->assertNull($dua->whatsapp_number);
        // Kata sandi acak dan tersimpan sebagai hash; bukan kata sandi bawaan yang bisa ditebak.
        $this->assertNotSame($satu->password, $dua->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::isHashed($satu->password));
        foreach (['password', 'satu@contoh.id', '081211112222', ''] as $guess) {
            $this->assertFalse(\Illuminate\Support\Facades\Hash::check($guess, $satu->password));
        }
    }

    public function test_file_without_the_template_columns_is_rejected_as_a_whole(): void
    {
        $import = new UserImport;
        Excel::import($import, UploadedFile::fake()->createWithContent('akun.csv', "name,mail\nBudi,budi@contoh.id\n"));

        $this->assertSame(0, $import->report()['created']);
        $this->assertCount(1, $import->report()['failed']);
        $this->assertSame(1, $import->report()['failed'][0]['row']);
        $this->assertStringContainsString('Kolom nama, email, tipe_pengguna tidak ditemukan', $import->report()['failed'][0]['reason']);
    }

    public function test_rows_past_the_limit_are_reported_and_skipped(): void
    {
        $rows = array_map(fn (int $i) => ["Akun $i", "akun$i@contoh.id", '', 'umum', '', ''], range(1, UserImport::MAX_ROWS + 2));

        $import = new UserImport;
        Excel::import($import, $this->csv($rows));

        $this->assertSame(UserImport::MAX_ROWS, $import->report()['created']);
        $this->assertSame(UserImport::MAX_ROWS + 2, $import->report()['failed'][0]['row']);
        $this->assertFalse(User::where('email', 'akun' . (UserImport::MAX_ROWS + 1) . '@contoh.id')->exists());
    }
}
