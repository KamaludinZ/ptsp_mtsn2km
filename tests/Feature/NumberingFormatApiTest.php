<?php

namespace Tests\Feature;

use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\NomorFormat;
use App\Support\NomorFormatSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** /api/persuratan/penomoran: baca dan simpan format nomor per jenis surat. */
class NumberingFormatApiTest extends TestCase
{
    use RefreshDatabase;

    private PersuratanMaster $jenis;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();
    }

    public function test_staff_read_formats_with_examples_and_the_token_guide(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/persuratan/penomoran')->assertOk()
            ->assertJsonPath('format_bawaan', NomorFormat::DEFAULT)
            ->assertJsonPath('pilihan_cepat', NomorFormat::QUICK_FORMATS)
            ->assertJsonFragment(['token' => '{N}', 'arti' => 'Nomor urut'])
            ->assertJsonFragment(['id' => $this->jenis->id, 'memakai_bawaan' => true, 'format_berlaku' => NomorFormat::DEFAULT]);

        $this->getJson('/api/persuratan/penomoran/' . $this->jenis->id)->assertOk()->assertJsonPath('jenis_surat', $this->jenis->nama);
        $other = PersuratanMaster::where('type', 'tembusan')->firstOrFail();
        $this->getJson('/api/persuratan/penomoran/' . $other->id)->assertNotFound();
    }

    public function test_managers_save_a_format_and_go_back_to_default(): void
    {
        Sanctum::actingAs(User::role('admin')->firstOrFail());

        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{N}/{S}/{k}/{M}/{Y}', 'mode_bulan' => 'romawi'])->assertOk()
            ->assertJsonPath('format', '{N}/{S}/{k}/{M}/{Y}')
            ->assertJsonPath('mode_bulan', 'romawi')
            ->assertJsonPath('contoh', NomorFormat::preview('{N}/{S}/{k}/{M}/{Y}', 'romawi'));
        $this->assertSame('{N}/{S}/{k}/{M}/{Y}', $this->jenis->fresh()->format_nomor);
        $this->assertTrue(\Spatie\Activitylog\Models\Activity::where('description', 'Mengubah format penomoran ' . $this->jenis->nama)->exists());

        // The archive classification for {k}/{K} is saved with the format.
        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{N}/{k}/{Y}', 'klasifikasi_arsip' => ' pp.00 '])->assertOk()
            ->assertJsonPath('klasifikasi_arsip', 'PP.00');
        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{N}/{k}/{Y}', 'klasifikasi_arsip' => 'PP/00'])->assertUnprocessable()
            ->assertJsonValidationErrors(['klasifikasi_arsip' => 'Gunakan kode klasifikasi seperti PP.00 (tanpa garis miring).']);
        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{N}/{S}/{k}/{M}/{Y}', 'mode_bulan' => 'romawi'])->assertOk()
            ->assertJsonPath('klasifikasi_arsip', 'PP.00'); // untouched when not sent

        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => null])->assertOk()
            ->assertJsonPath('memakai_bawaan', true)
            ->assertJsonPath('mode_bulan', 'romawi');
        $this->assertNull($this->jenis->fresh()->format_nomor);
    }

    public function test_unit_abbreviation_comes_from_the_settings_unless_overridden(): void
    {
        Sanctum::actingAs(User::role('admin')->firstOrFail());
        \App\Models\AppSetting::set(\App\Support\SuratKeluarNumber::SETTING_KODE_SATKER, 'MTsN.2');
        $url = '/api/persuratan/penomoran/' . $this->jenis->id;

        $this->getJson($url)->assertOk()
            ->assertJsonPath('singkatan', null)
            ->assertJsonPath('singkatan_berlaku', 'MTsN.2')
            ->assertJsonPath('singkatan_dari_pengaturan', true);

        $this->putJson($url, ['format' => '{N}/{S}/{Y}', 'singkatan' => 'TU.MTsN2'])->assertOk()
            ->assertJsonPath('singkatan_berlaku', 'TU.MTsN2')
            ->assertJsonPath('contoh', '12/TU.MTsN2/' . now()->year);

        // Saving the format alone keeps the override; null hands {S} back to the settings.
        $this->putJson($url, ['format' => '{N}/{S}/{M}/{Y}'])->assertOk()->assertJsonPath('singkatan', 'TU.MTsN2');
        $this->putJson($url, ['format' => '{N}/{S}/{M}/{Y}', 'singkatan' => null])->assertOk()->assertJsonPath('singkatan_berlaku', 'MTsN.2');

        $this->putJson($url, ['format' => '{N}/{Y}', 'singkatan' => 'TU/MTsN'])->assertUnprocessable()->assertJsonValidationErrors('singkatan');
    }

    public function test_abbreviation_endpoint_saves_only_the_abbreviation(): void
    {
        Sanctum::actingAs(User::role('admin')->firstOrFail());
        \App\Models\AppSetting::set(\App\Support\SuratKeluarNumber::SETTING_KODE_SATKER, 'MTsN.2');
        NomorFormatSettings::save($this->jenis, '{N}/{S}/{M}/{Y}', 'romawi');
        $url = '/api/persuratan/penomoran/' . $this->jenis->id . '/singkatan';

        $this->putJson($url, ['singkatan' => ' TU.MTsN2 '])->assertOk()
            ->assertJsonPath('singkatan', 'TU.MTsN2')
            ->assertJsonPath('singkatan_berlaku', 'TU.MTsN2')
            ->assertJsonPath('format', '{N}/{S}/{M}/{Y}')
            ->assertJsonPath('mode_bulan', 'romawi');
        $this->assertTrue(\Spatie\Activitylog\Models\Activity::where('description', 'Mengubah singkatan unit kerja ' . $this->jenis->nama)->exists());

        $this->putJson($url, ['singkatan' => null])->assertOk()->assertJsonPath('singkatan_dari_pengaturan', true)->assertJsonPath('singkatan_berlaku', 'MTsN.2');
        $this->putJson($url, ['singkatan' => 'TU MTsN'])->assertUnprocessable()->assertJsonValidationErrors('singkatan');
        $this->putJson($url, [])->assertUnprocessable()->assertJsonValidationErrors('singkatan');
        $this->putJson('/api/persuratan/penomoran/' . PersuratanMaster::where('type', 'tembusan')->firstOrFail()->id . '/singkatan', ['singkatan' => 'X'])->assertNotFound();

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->putJson($url, ['singkatan' => 'X'])->assertForbidden();
        $this->assertNull($this->jenis->fresh()->singkatan_unit_kerja);
    }

    public function test_preview_endpoint_builds_an_example_with_the_abbreviation_in_force(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        \App\Models\AppSetting::set(\App\Support\SuratKeluarNumber::SETTING_KODE_SATKER, 'MTsN.2');
        $this->travelTo(\Illuminate\Support\Carbon::create(2026, 10, 7));
        $url = '/api/persuratan/penomoran/pratinjau?';

        $this->getJson($url . http_build_query(['format' => '{N}/{S}/{M}/{Y}', 'mode_bulan' => 'romawi']))->assertOk()
            ->assertJsonPath('contoh', '12/MTsN.2/X/2026')
            ->assertJsonPath('sumber_singkatan', 'pengaturan_aplikasi');
        $this->getJson($url . http_build_query(['format' => '{N}/{S}/{Y}', 'singkatan' => 'KEU']))->assertOk()
            ->assertJsonPath('contoh', '12/KEU/2026')
            ->assertJsonPath('sumber_singkatan', 'isian');

        // A letter type supplies its own format, month mode, and override.
        NomorFormatSettings::save($this->jenis, '{N}/{S}/{M}/{Y}', 'romawi', 'TU.MTsN2');
        $this->getJson($url . http_build_query(['jenis_surat_id' => $this->jenis->id]))->assertOk()
            ->assertJsonPath('contoh', '12/TU.MTsN2/X/2026')
            ->assertJsonPath('sumber_singkatan', 'jenis_surat');

        $this->getJson($url . http_build_query(['format' => '{S}/{Y}']))->assertUnprocessable()->assertJsonValidationErrors('format');
        $this->getJson($url . http_build_query(['format' => '{N}', 'singkatan' => 'A/B']))->assertUnprocessable()->assertJsonValidationErrors('singkatan');
        $this->getJson($url . http_build_query(['jenis_surat_id' => PersuratanMaster::where('type', 'tembusan')->firstOrFail()->id]))->assertUnprocessable();
    }

    public function test_variable_definition_crud(): void
    {
        Sanctum::actingAs(User::role('admin')->firstOrFail());
        $url = '/api/persuratan/penomoran/' . $this->jenis->id . '/variabel';

        $this->getJson($url)->assertOk()->assertJsonPath('variabel', null)->assertJsonPath('dipakai_di_format', false);
        $this->putJson($url, ['keterangan' => 'x'])->assertUnprocessable()->assertJsonValidationErrors(['label' => 'Nama variabel wajib diisi.']);

        $this->putJson($url, ['label' => 'Kelas', 'keterangan' => 'Rombel, mis. IX-A'])->assertCreated()
            ->assertJsonPath('variabel', ['label' => 'Kelas', 'keterangan' => 'Rombel, mis. IX-A', 'wajib' => true]);
        $this->assertTrue(\Spatie\Activitylog\Models\Activity::where('description', 'Menambah variabel tambahan ' . $this->jenis->nama)->exists());

        NomorFormatSettings::save($this->jenis, '{N}/{V}/{Y}');
        $this->putJson($url, ['label' => 'Rombel', 'wajib' => false])->assertOk()
            ->assertJsonPath('variabel.wajib', false)
            ->assertJsonPath('variabel.keterangan', null)
            ->assertJsonPath('dipakai_di_format', true);
        $this->getJson('/api/persuratan/penomoran/' . $this->jenis->id)->assertJsonPath('variabel.label', 'Rombel');

        $this->deleteJson($url)->assertOk()->assertJsonPath('variabel', null);
        $this->deleteJson($url)->assertNotFound();
        $this->assertTrue(\Spatie\Activitylog\Models\Activity::where('description', 'Menghapus variabel tambahan ' . $this->jenis->nama)->exists());

        $this->getJson('/api/persuratan/penomoran/' . PersuratanMaster::where('type', 'tembusan')->firstOrFail()->id . '/variabel')->assertNotFound();
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson($url)->assertOk();
        $this->putJson($url, ['label' => 'Kelas'])->assertForbidden();
        $this->deleteJson($url)->assertForbidden();
    }

    public function test_saved_variable_loads_by_letter_type_name(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas', 'Rombel, mis. IX-A');

        $this->getJson('/api/persuratan/penomoran/variabel?jenis_surat=' . urlencode(' ' . mb_strtoupper($this->jenis->nama)))->assertOk()
            ->assertJsonPath('id', $this->jenis->id)
            ->assertJsonPath('variabel.label', 'Kelas')
            ->assertJsonPath('variabel.wajib', true);
        $this->getJson('/api/persuratan/penomoran/variabel?jenis_surat=Surat%20Lain')->assertOk()->assertJsonPath('variabel', null)->assertJsonPath('id', null);
        $this->getJson('/api/persuratan/penomoran/variabel')->assertUnprocessable()->assertJsonValidationErrors('jenis_surat');
    }

    public function test_invalid_formats_and_unauthorised_changes_are_refused(): void
    {
        Sanctum::actingAs(User::role('admin')->firstOrFail());
        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{N}/{X}'])->assertUnprocessable()
            ->assertJsonValidationErrors(['format' => 'Token tidak dikenal: {X}.']);
        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{S}/{Y}'])->assertUnprocessable()->assertJsonValidationErrors('format');
        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{N}/{Y}', 'mode_bulan' => 'lain'])->assertUnprocessable()->assertJsonValidationErrors('mode_bulan');

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->putJson('/api/persuratan/penomoran/' . $this->jenis->id, ['format' => '{N}/{Y}'])->assertForbidden();
        $this->assertNull($this->jenis->fresh()->format_nomor);
    }
}
