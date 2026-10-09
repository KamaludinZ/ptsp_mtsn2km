<?php

namespace Tests\Feature;

use App\Exports\UserExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/** Berkas Excel data akun: susunan kolom dan isinya. */
class UserExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_workbook_has_readable_columns_without_passwords(): void
    {
        \Spatie\Permission\Models\Role::findOrCreate('guru', 'web');
        $user = User::factory()->create(['name' => 'Bu Sari', 'email' => 'sari@contoh.id', 'whatsapp_number' => '081234567890', 'user_type' => 'guru', 'registration_code' => 'G-01']);
        $user->forceFill(['must_change_password' => true])->save();
        $user->assignRole('guru');

        $path = tempnam(sys_get_temp_dir(), 'exp') . '.xlsx';
        file_put_contents($path, Excel::raw(new UserExport(User::query()->whereKey($user->id), 'Semua'), \Maatwebsite\Excel\Excel::XLSX));
        $sheet = IOFactory::load($path)->getActiveSheet();
        @unlink($path);

        $rows = $sheet->toArray();
        $this->assertSame(['No', 'Nama', 'Email', 'Nomor WhatsApp', 'Tipe Pengguna', 'Kode Registrasi', 'Role', 'Status', 'Wajib Ganti Kata Sandi', 'Kata Sandi Diganti', 'Terakhir Masuk', 'Dibuat'], $rows[0]);
        $this->assertSame(['1', 'Bu Sari', 'sari@contoh.id', '081234567890', 'Guru', 'G-01', 'Guru', 'Aktif', 'Ya'], array_slice($rows[1], 0, 9));
        $this->assertSame('Akun Semua', $sheet->getTitle());
        $this->assertSame('A1:L1', $sheet->getAutoFilter()->getRange());
        $this->assertNotContains($user->password, $rows[1]);
    }
}
