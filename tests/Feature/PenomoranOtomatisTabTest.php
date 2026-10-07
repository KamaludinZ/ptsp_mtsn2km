<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\NomorFormat;
use App\Support\NomorFormatSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Master Persuratan: tab Penomoran Otomatis dan perangkai format token. */
class PenomoranOtomatisTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_format_tokens_render_into_a_number(): void
    {
        $date = Carbon::create(2026, 10, 7);

        $this->assertSame('B/12/MTSN2/PP.00/10/2026', NomorFormat::render('{KS}/{N}/{S}/{k}/{M}/{Y}', ['N' => 12, 'S' => 'MTSN2', 'k' => 'PP.00', 'tanggal' => $date]));
        $this->assertSame('12/MTSN2/X/2026', NomorFormat::render('{N}/{S}/{k}/{M}/{Y}', ['N' => 12, 'S' => 'MTSN2', 'k' => '', 'tanggal' => $date, 'mode_bulan' => 'romawi']));
        $this->assertSame('5/IX-A/hm.01/HM.01', NomorFormat::render('{N}/{V}/{k}/{K}', ['N' => 5, 'v' => 'ix-a', 'k' => 'hm.01', 'tanggal' => $date]));
        $this->assertSame(['KS', 'N', 'S', 'k', 'M', 'Y'], NomorFormat::tokens('{KS}/{N}/{S}/{k}/{M}/{Y}'));
        $this->assertSame(['X'], NomorFormat::unknownTokens('{N}/{X}/{Y}'));

        // {M}: every month in Arabic (two digits, as the earlier numbering) and Roman numerals.
        foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'] as $i => $roman) {
            $month = Carbon::create(2026, $i + 1, 1);
            $this->assertSame(sprintf('%02d/2026', $i + 1), NomorFormat::render('{M}/{Y}', ['N' => 1, 'tanggal' => $month, 'mode_bulan' => 'arab']));
            $this->assertSame($roman . '/2026', NomorFormat::render('{M}/{Y}', ['N' => 1, 'tanggal' => $month, 'mode_bulan' => 'romawi']));
        }
        // Through the service: the month mode of the jenis surat decides.
        $jenis = PersuratanMaster::create(['type' => 'jenis_surat', 'nama' => 'Surat Uji Bulan']);
        NomorFormatSettings::save($jenis, '{N}/{M}/{Y}', 'romawi');
        $this->assertSame('3/IV/2026', \App\Services\SuratKeluarService::composeNumber(3, Carbon::create(2026, 4, 2), null, 'Surat Uji Bulan'));
        NomorFormatSettings::save($jenis, '{N}/{M}/{Y}', 'arab');
        $this->assertSame('3/04/2026', \App\Services\SuratKeluarService::composeNumber(3, Carbon::create(2026, 4, 2), null, 'Surat Uji Bulan'));
        $this->assertSame('XII', NomorFormat::roman(12));
    }

    public function test_letter_type_rows_hold_their_numbering_format(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumns('persuratan_masters', ['format_nomor', 'mode_bulan']));

        $jenis = PersuratanMaster::create(['type' => 'jenis_surat', 'nama' => 'Surat Uji Penomoran', 'is_active' => true]);
        $this->assertNull($jenis->fresh()->format_nomor);
        $this->assertSame('arab', $jenis->fresh()->mode_bulan);

        $jenis->update(['format_nomor' => '{N}/{S}/{M}/{Y}', 'mode_bulan' => 'romawi']);
        $this->assertSame(['{N}/{S}/{M}/{Y}', 'romawi'], [$jenis->fresh()->format_nomor, $jenis->fresh()->mode_bulan]);
    }

    public function test_default_format_matches_the_existing_numbering(): void
    {
        $date = Carbon::create(2026, 10, 7);

        $this->assertSame(
            'B-12/' . \App\Support\SuratKeluarNumber::kodeSatker() . '/PP.00/10/2026', // the numbering used before formats existed
            NomorFormat::render(NomorFormat::DEFAULT, ['N' => 12, 'k' => 'PP.00', 'tanggal' => $date]),
        );
    }

    public function test_numbering_tab_lists_letter_types_with_format_and_example(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        $jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();
        $other = PersuratanMaster::where('type', 'tujuan_naskah')->firstOrFail();
        NomorFormatSettings::save($jenis, '{N}/{S}/{k}/{M}/{Y}', 'romawi');

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->assertSee('Penomoran Otomatis')
            ->assertCanSeeTableRecords([$jenis])
            ->assertCanNotSeeTableRecords([$other])
            ->assertTableColumnVisible('format_nomor')
            ->assertTableColumnStateSet('format_nomor', '{N}/{S}/{k}/{M}/{Y}', $jenis->getKey())
            ->assertTableColumnStateSet('contoh_nomor', NomorFormat::preview('{N}/{S}/{k}/{M}/{Y}', 'romawi'), $jenis->getKey());

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'tujuan_naskah'])
            ->assertTableColumnHidden('format_nomor')
            ->assertTableColumnHidden('contoh_nomor');
    }

    public function test_letter_type_rows_hold_the_numbering_settings(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumns('persuratan_masters', ['format_nomor', 'mode_bulan', 'singkatan_unit_kerja', 'klasifikasi_arsip']));

        // The abbreviation override is optional (empty = Pengaturan Aplikasi) and at most 30 characters.
        $jenis = PersuratanMaster::create(['type' => 'jenis_surat', 'nama' => 'Surat Uji Kolom']);
        $this->assertNull($jenis->fresh()->singkatan_unit_kerja);
        $this->assertSame('arab', $jenis->fresh()->mode_bulan);
        $this->assertNull($jenis->fresh()->klasifikasi_arsip);
        $jenis->update(['klasifikasi_arsip' => 'PP.00']);
        $this->assertSame('PP.00', $jenis->fresh()->klasifikasi_arsip);
        $jenis->update(['singkatan_unit_kerja' => str_repeat('A', 30)]);
        $this->assertSame(30, mb_strlen($jenis->fresh()->singkatan_unit_kerja));
        $this->assertSame('Singkatan paling panjang 30 karakter.', NomorFormatSettings::singkatanProblem(str_repeat('A', 31)));
    }

    public function test_live_preview_follows_the_format_and_month_mode(): void
    {
        $this->travelTo(Carbon::create(2026, 10, 7));
        $html = fn (string $format, string $mode = 'arab') => (string) \App\Filament\Actions\NumberingFormatAction::previewHtml($format, $mode);

        $this->assertStringContainsString(e(NomorFormat::preview('{N}/{S}/{M}/{Y}', 'arab')), $html('{N}/{S}/{M}/{Y}'));
        $this->assertStringContainsString('/X/2026', $html('{N}/{S}/{M}/{Y}', 'romawi'));
        $this->assertStringContainsString('Ada token yang tidak dikenal.', $html('{N}/{Q}'));
        $this->assertStringContainsString('Tambahkan {N}', $html('{S}/{Y}'));
        $this->assertStringContainsString('Tulis format', $html('  '));

        // The preview follows the abbreviation being typed, and says where {S} comes from.
        $withOverride = (string) \App\Filament\Actions\NumberingFormatAction::previewHtml('{N}/{S}/{Y}', 'arab', 'TU.MTsN2');
        $this->assertStringContainsString('12/TU.MTsN2/2026', $withOverride);
        $this->assertStringContainsString('(khusus jenis surat ini)', $withOverride);
        $this->assertStringContainsString('(Pengaturan Aplikasi)', $html('{N}/{S}/{Y}'));
        $this->assertStringNotContainsString('{S} =', $html('{N}/{Y}'));
    }

    public function test_saved_format_shows_in_the_table_and_can_go_back_to_default(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        $jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->callTableAction('numbering', $jenis, ['format' => '{N}/{S}/{k}/{M}/{Y}', 'mode_bulan' => 'romawi'])
            ->assertTableColumnStateSet('format_nomor', '{N}/{S}/{k}/{M}/{Y}', $jenis->getKey())
            ->assertTableColumnStateSet('contoh_nomor', NomorFormat::preview('{N}/{S}/{k}/{M}/{Y}', 'romawi'), $jenis->getKey())
            // Another letter type keeps the default.
            ->assertTableColumnStateSet('format_nomor', null, PersuratanMaster::where('type', 'jenis_surat')->whereKeyNot($jenis->id)->firstOrFail());

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->mountTableAction('numbering', $jenis)
            ->callMountedTableAction(['reset' => true])
            ->assertNotified($jenis->nama . ' memakai format bawaan')
            ->assertTableColumnStateSet('format_nomor', null, $jenis->getKey());

        $this->assertSame(['format' => null, 'mode_bulan' => 'arab', 'singkatan' => null], NomorFormatSettings::for($jenis->fresh()));
    }

    public function test_editor_overrides_the_unit_abbreviation_for_one_letter_type(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        \App\Models\AppSetting::set(\App\Support\SuratKeluarNumber::SETTING_KODE_SATKER, 'MTsN.2');
        $jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->mountTableAction('numbering', $jenis)
            ->assertSee('Dari Pengaturan Aplikasi')
            ->assertSeeHtml('data-default-abbreviation')
            ->assertTableActionDataSet(['timpa_singkatan' => false])
            ->setTableActionData(['format' => '{N}/{S}/{Y}', 'timpa_singkatan' => true, 'singkatan' => 'TU/X'])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['singkatan']);

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->callTableAction('numbering', $jenis, ['format' => '{N}/{S}/{Y}', 'mode_bulan' => 'arab', 'timpa_singkatan' => true, 'singkatan' => 'TU.MTsN2'])
            ->assertHasNoTableActionErrors()
            ->assertTableColumnStateSet('contoh_nomor', '12/TU.MTsN2/' . now()->year, $jenis->getKey());
        $this->assertSame('TU.MTsN2', $jenis->fresh()->singkatan_unit_kerja);

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->assertTableColumnStateSet('singkatan_unit_kerja', 'TU.MTsN2', $jenis->getKey())
            ->assertSee('khusus jenis surat ini')
            ->assertTableColumnStateSet('singkatan_unit_kerja', 'MTsN.2', PersuratanMaster::where('type', 'jenis_surat')->whereKeyNot($jenis->id)->firstOrFail()->getKey())
            ->assertSee('dari Pengaturan Aplikasi');

        // Toggle state in the editor: off clears the field, on starts from the settings value.
        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->mountTableAction('numbering', $jenis->fresh())
            ->assertTableActionDataSet(['timpa_singkatan' => true, 'singkatan' => 'TU.MTsN2'])
            ->assertSeeHtml('data-abbreviation-state="override"')
            ->set('mountedTableActionsData.0.timpa_singkatan', false)
            ->assertTableActionDataSet(['singkatan' => null])
            ->assertSeeHtml('data-abbreviation-state="default"')
            ->set('mountedTableActionsData.0.timpa_singkatan', true)
            ->assertTableActionDataSet(['singkatan' => 'MTsN.2']);

        // The preview reacts to every change of the abbreviation (no slashes, so the HTML is unescaped).
        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->mountTableAction('numbering', $jenis->fresh())
            ->set('mountedTableActionsData.0.format', '{N}-{S}-{Y}')
            ->assertSeeHtml('12-TU.MTsN2-' . now()->year)
            ->set('mountedTableActionsData.0.singkatan', 'KEU.MTsN2')
            ->assertSeeHtml('12-KEU.MTsN2-' . now()->year)
            ->set('mountedTableActionsData.0.timpa_singkatan', false)
            ->assertSeeHtml('12-MTsN.2-' . now()->year);

        // Switching the override off hands {S} back to the settings.
        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->callTableAction('numbering', $jenis, ['format' => '{N}/{S}/{Y}', 'mode_bulan' => 'arab', 'timpa_singkatan' => false])
            ->assertTableColumnStateSet('contoh_nomor', '12/MTsN.2/' . now()->year, $jenis->getKey());
        $this->assertNull($jenis->fresh()->singkatan_unit_kerja);
    }

    public function test_token_guide_explains_every_token(): void
    {
        $html = (string) \App\Filament\Actions\NumberingFormatAction::guideHtml();

        foreach (NomorFormat::TOKENS as $token => $meta) {
            $this->assertStringContainsString('data-token="' . $token . '"', $html);
            $this->assertStringContainsString(e($meta['label']), $html);
        }
        $this->assertStringContainsString('Nomor urut tahunan', $html);
    }

    public function test_editor_shows_the_preview_while_editing(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        $jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->mountTableAction('numbering', $jenis)
            ->assertSee('Contoh nomor')
            ->assertSeeHtml('data-number-preview')
            ->assertSee('Panduan token')
            ->setTableActionData(['format' => '{N}/{Q}'])
            ->assertSee('Ada token yang tidak dikenal.');
    }

    public function test_quick_format_buttons_fill_the_editor(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        $jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();

        $page = Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->mountTableAction('numbering', $jenis)
            ->assertSee('{KS}/{N}/{S}/{k}/{M}/{Y}')
            ->assertSee('{N}/{S}/{k}/{M}/{Y}')
            ->callFormComponentAction('format', 'quick1', formName: 'mountedTableActionForm')
            ->assertTableActionDataSet(['format' => '{KS}/{N}/{S}/{k}/{M}/{Y}'])
            ->callFormComponentAction('format', 'quick2', formName: 'mountedTableActionForm')
            ->assertTableActionDataSet(['format' => '{N}/{S}/{k}/{M}/{Y}']);

        $page->callMountedTableAction()->assertHasNoTableActionErrors();
        $this->assertSame('{N}/{S}/{k}/{M}/{Y}', NomorFormatSettings::for($jenis->fresh())['format']);
    }

    public function test_editor_saves_a_valid_format_and_rejects_unknown_tokens(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        $jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->assertTableActionVisible('numbering', $jenis)
            ->mountTableAction('numbering', $jenis)
            ->assertTableActionDataSet(['format' => NomorFormat::DEFAULT, 'mode_bulan' => 'arab'])
            ->setTableActionData(['format' => '{N}/{X}/{Y}'])
            ->callMountedTableAction()
            ->assertHasTableActionErrors(['format']);

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->callTableAction('numbering', $jenis, ['format' => '{S}/{M}/{Y}', 'mode_bulan' => 'arab'])
            ->assertHasTableActionErrors(['format']); // no {N}

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->callTableAction('numbering', $jenis, ['format' => ' {KS}/{N}/{S}/{k}/{M}/{Y} ', 'mode_bulan' => 'romawi'])
            ->assertHasNoTableActionErrors()
            ->assertNotified('Format ' . $jenis->nama . ' disimpan');

        $this->assertSame(['format' => '{KS}/{N}/{S}/{k}/{M}/{Y}', 'mode_bulan' => 'romawi', 'singkatan' => null], NomorFormatSettings::for($jenis->fresh()));

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'tujuan_naskah'])
            ->assertTableActionHidden('numbering', PersuratanMaster::where('type', 'tujuan_naskah')->firstOrFail());
    }

    public function test_override_button_changes_only_the_abbreviation(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        $jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();
        NomorFormatSettings::save($jenis, '{N}/{S}/{M}/{Y}', 'romawi');

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->assertTableActionVisible('abbreviation', $jenis)
            ->callTableAction('abbreviation', $jenis, ['singkatan' => 'TU/X'])
            ->assertHasTableActionErrors(['singkatan']);

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->callTableAction('abbreviation', $jenis, ['singkatan' => ' TU.MTsN2 '])
            ->assertHasNoTableActionErrors()
            ->assertNotified();
        $this->assertSame(['format' => '{N}/{S}/{M}/{Y}', 'mode_bulan' => 'romawi', 'singkatan' => 'TU.MTsN2'], NomorFormatSettings::for($jenis->fresh()));

        // Emptying it hands {S} back to Pengaturan Aplikasi.
        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->mountTableAction('abbreviation', $jenis->fresh())
            ->assertTableActionDataSet(['singkatan' => 'TU.MTsN2'])
            ->setTableActionData(['singkatan' => ''])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();
        $this->assertNull($jenis->fresh()->singkatan_unit_kerja);
        $this->assertSame('{N}/{S}/{M}/{Y}', $jenis->fresh()->format_nomor);

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'tujuan_naskah'])
            ->assertTableActionHidden('abbreviation', PersuratanMaster::where('type', 'tujuan_naskah')->firstOrFail());
    }
}
