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
}
