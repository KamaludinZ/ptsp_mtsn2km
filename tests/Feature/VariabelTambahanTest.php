<?php

namespace Tests\Feature;

use App\Filament\Resources\PersuratanMasterResource\Pages\ListPersuratanMasters;
use App\Models\PersuratanMaster;
use App\Models\User;
use App\Support\NomorFormatSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Master Persuratan: tab Variabel Tambahan ({v}/{V}) per jenis surat. */
class VariabelTambahanTest extends TestCase
{
    use RefreshDatabase;

    private PersuratanMaster $jenis;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::role('admin')->firstOrFail());
        $this->jenis = PersuratanMaster::where('type', 'jenis_surat')->orderBy('id')->firstOrFail();
    }

    public function test_variable_tab_lists_letter_types_with_their_variable(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}/{S}/{V}/{Y}');
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas', 'Rombel siswa yang dimaksud surat', true);
        $other = PersuratanMaster::where('type', 'jenis_surat')->whereKeyNot($this->jenis->id)->firstOrFail();

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'variabel'])
            ->assertSuccessful()
            ->assertSee('Variabel Tambahan')
            ->assertSee('token {v}')
            ->assertCanSeeTableRecords(PersuratanMaster::where('type', 'jenis_surat')->get())
            ->assertCanNotSeeTableRecords(PersuratanMaster::where('type', 'tembusan')->get())
            ->assertTableColumnVisible('variabel_label')
            ->assertTableColumnHidden('format_nomor')
            ->assertTableColumnStateSet('variabel_label', 'Kelas', $this->jenis->getKey())
            ->assertTableColumnStateSet('variabel_wajib', 'Wajib', $this->jenis->getKey())
            ->assertTableColumnStateSet('variabel_di_format', 'Dipakai', $this->jenis->getKey())
            ->assertTableColumnStateSet('variabel_label', null, $other->getKey())
            ->assertTableColumnStateSet('variabel_di_format', 'Belum dipakai', $other->getKey())
            ->assertSee('Rombel siswa yang dimaksud surat');

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->assertTableColumnHidden('variabel_label');
    }

    public function test_admin_adds_edits_and_deletes_a_variable(): void
    {
        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'variabel'])
            ->assertTableActionVisible('variable', $this->jenis)
            ->assertTableActionHidden('deleteVariable', $this->jenis)
            ->callTableAction('variable', $this->jenis, ['label' => '', 'wajib' => true])
            ->assertHasTableActionErrors(['label' => 'required']);

        // The format does not use {v} yet: saved, with a warning.
        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'variabel'])
            ->callTableAction('variable', $this->jenis, ['label' => 'Kelas', 'keterangan' => 'Rombel, mis. IX-A', 'wajib' => true])
            ->assertHasNoTableActionErrors()
            ->assertNotified();
        $this->assertSame(['label' => 'Kelas', 'keterangan' => 'Rombel, mis. IX-A', 'wajib' => true], NomorFormatSettings::variable($this->jenis->fresh()));

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'variabel'])
            ->mountTableAction('variable', $this->jenis->fresh())
            ->assertTableActionDataSet(['label' => 'Kelas', 'wajib' => true])
            ->setTableActionData(['label' => 'Rombel', 'wajib' => false])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();
        $this->assertSame(['label' => 'Rombel', 'keterangan' => 'Rombel, mis. IX-A', 'wajib' => false], NomorFormatSettings::variable($this->jenis->fresh()));

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'variabel'])
            ->assertTableActionVisible('deleteVariable', $this->jenis->fresh())
            ->callTableAction('deleteVariable', $this->jenis->fresh())
            ->assertNotified('Variabel ' . $this->jenis->nama . ' dihapus');
        $this->assertNull($this->jenis->fresh()->variabel_nomor);

        Livewire::test(ListPersuratanMasters::class, ['activeTab' => 'penomoran'])
            ->assertTableActionHidden('variable', $this->jenis);
    }

    public function test_request_form_shows_the_variable_field_of_the_chosen_letter_type(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}-{V}-{Y}');
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas', 'Rombel siswa, mis. IX-A');
        $other = PersuratanMaster::where('type', 'jenis_surat')->whereKeyNot($this->jenis->id)->firstOrFail();

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->mountAction('reserve')
            ->assertFormFieldIsHidden('variabel', 'mountedActionForm')
            ->set('mountedActionsData.0.jenis_surat', $other->nama)
            ->assertFormFieldIsHidden('variabel', 'mountedActionForm')
            ->set('mountedActionsData.0.jenis_surat', mb_strtolower($this->jenis->nama))
            ->assertFormFieldIsVisible('variabel', 'mountedActionForm')
            ->assertSee('Kelas')
            ->assertSee('Rombel siswa, mis. IX-A')
            // The preview carries the value as it is typed ({V}: upper case).
            ->set('mountedActionsData.0.variabel', 'ix-a')
            ->assertSeeHtml(app(\App\Services\SuratKeluarService::class)->nextNumber(now()->year) . '-IX-A-' . now()->year);
    }

    public function test_request_form_puts_the_numbering_fields_first(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}-{S}-{Y}');

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->mountAction('reserve')
            ->assertSee('Penomoran')
            ->assertSee('Jenis di luar daftar memakai format bawaan.')
            ->set('mountedActionsData.0.jenis_surat', $this->jenis->nama)
            ->assertSeeHtml('Format: {N}-{S}-{Y}');
    }

    public function test_letter_type_summary_describes_the_numbering(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}-{S}-{V}-{M}-{Y}', 'romawi', 'TU.MTsN2');
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas');
        $page = \App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class;

        $html = (string) $page::letterTypeSummary(mb_strtoupper($this->jenis->nama));
        foreach (['{N}-{S}-{V}-{M}-{Y}', 'Angka Romawi', 'TU.MTsN2 (khusus)', 'Kelas (wajib)'] as $part) {
            $this->assertStringContainsString(e($part), $html);
        }
        $this->assertStringContainsString('data-letter-type-summary="unknown"', (string) $page::letterTypeSummary('Surat Lain'));

        Livewire::test($page)
            ->mountAction('reserve')
            ->assertDontSeeHtml('data-letter-type-summary')
            ->set('mountedActionsData.0.jenis_surat', $this->jenis->nama)
            ->assertSeeHtml('data-letter-type-summary="known"');
    }

    public function test_variable_value_resets_when_the_letter_type_changes(): void
    {
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas');
        $other = PersuratanMaster::where('type', 'jenis_surat')->whereKeyNot($this->jenis->id)->firstOrFail();
        NomorFormatSettings::saveVariable($other, 'Nama kegiatan');

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->mountAction('reserve')
            ->set('mountedActionsData.0.jenis_surat', $this->jenis->nama)
            ->set('mountedActionsData.0.variabel', 'IX-A')
            // Same jenis surat, typed differently: the value stays.
            ->set('mountedActionsData.0.jenis_surat', mb_strtoupper($this->jenis->nama))
            ->assertActionDataSet(['variabel' => 'IX-A'])
            ->set('mountedActionsData.0.jenis_surat', $other->nama)
            ->assertActionDataSet(['variabel' => null])
            ->assertSee('Nama kegiatan')
            ->set('mountedActionsData.0.jenis_surat', '')
            ->assertFormFieldIsHidden('variabel', 'mountedActionForm');
    }

    public function test_required_variable_must_be_filled_before_a_number_is_given(): void
    {
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas');
        $optional = PersuratanMaster::where('type', 'jenis_surat')->whereKeyNot($this->jenis->id)->firstOrFail();
        NomorFormatSettings::saveVariable($optional, 'Nama kegiatan', null, false);
        $page = \App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class;
        $before = \App\Models\SuratKeluar::count();

        Livewire::test($page)
            ->callAction('reserve', ['count' => 1, 'tanggal_surat' => now()->toDateString(), 'jenis_surat' => $this->jenis->nama])
            ->assertHasActionErrors(['variabel' => 'required'])
            ->assertSee('Kelas wajib diisi untuk jenis surat ini.');

        Livewire::test($page)
            ->callAction('reserve', ['count' => 1, 'tanggal_surat' => now()->toDateString(), 'jenis_surat' => $this->jenis->nama, 'variabel' => 'IX/A'])
            ->assertHasActionErrors(['variabel' => 'not_regex']);
        $this->assertSame($before, \App\Models\SuratKeluar::count());

        Livewire::test($page)
            ->callAction('reserve', ['count' => 1, 'tanggal_surat' => now()->toDateString(), 'jenis_surat' => $this->jenis->nama, 'variabel' => 'IX-A'])
            ->assertHasNoActionErrors();
        Livewire::test($page)
            ->callAction('reserve', ['count' => 1, 'tanggal_surat' => now()->toDateString(), 'jenis_surat' => $optional->nama])
            ->assertHasNoActionErrors();
        $this->assertSame($before + 2, \App\Models\SuratKeluar::count());
    }

    public function test_request_form_api_lists_letter_types_with_their_variable(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}/{V}/{Y}');
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas', 'Rombel, mis. IX-A');
        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $response = $this->getJson('/api/surat-keluar/nomor/form')->assertOk()
            ->assertJsonPath('maks_per_permintaan', \App\Services\SuratKeluarService::MAX_PER_REQUEST)
            ->assertJsonFragment(['id' => $this->jenis->id, 'format_berlaku' => '{N}/{V}/{Y}', 'variabel' => ['label' => 'Kelas', 'keterangan' => 'Rombel, mis. IX-A', 'wajib' => true]]);
        $this->assertSame(PersuratanMaster::ofType('jenis_surat')->count(), count($response->json('jenis_surat')));

        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/surat-keluar/nomor/form')->assertForbidden();
    }

    public function test_service_refuses_a_number_without_its_required_variable(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}/{V}/{Y}');
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas');
        $service = app(\App\Services\SuratKeluarService::class);
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $before = \App\Models\SuratKeluar::count();

        foreach ([null, '  ', 'IX/A'] as $value) {
            try {
                $service->reserve(1, now(), $officer, ['jenis_surat' => $this->jenis->nama, 'variabel' => $value]);
                $this->fail('A number was given without a usable variable.');
            } catch (\App\Exceptions\TicketActionException $e) {
                $this->assertStringContainsString($value === 'IX/A' ? 'garis miring' : 'Kelas wajib diisi', $e->getMessage());
            }
        }
        $this->assertSame($before, \App\Models\SuratKeluar::count());

        // Given the value, the number carries it and keeps it when the letter is completed.
        $letter = $service->reserve(1, now(), $officer, ['jenis_surat' => $this->jenis->nama, 'variabel' => 'ix-a'])->first();
        $this->assertSame("{$letter->nomor_urut}/IX-A/" . now()->year, $letter->nomor_surat);
        $this->assertSame('ix-a', $letter->variabel);
        $service->describe($letter, ['perihal' => 'Undangan wali kelas']);
        $this->assertSame("{$letter->nomor_urut}/IX-A/" . now()->year, $letter->fresh()->nomor_surat);

        // Completing a letter enforces the rule too, when its jenis surat or variable changes.
        $other = $service->reserve(1, now(), $officer, ['jenis_surat' => 'Surat Lain'])->first();
        try {
            $service->describe($other, ['jenis_surat' => $this->jenis->nama]);
            $this->fail('Letter type with a required variable accepted without it.');
        } catch (\App\Exceptions\TicketActionException $e) {
            $this->assertSame('Kelas wajib diisi untuk jenis surat ini.', $e->getMessage());
        }
        $this->assertSame('Surat Lain', $other->fresh()->jenis_surat);
        $service->describe($other->fresh(), ['jenis_surat' => $this->jenis->nama, 'variabel' => 'VII-B']);
        $this->assertSame("{$other->nomor_urut}/VII-B/" . now()->year, $other->fresh()->nomor_surat);
        $service->describe($other->fresh(), ['perihal' => 'Tanpa ubah jenis']); // untouched jenis: no re-check needed

        // Optional variable: no value is fine.
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas', null, false);
        $plain = $service->reserve(1, now(), $officer, ['jenis_surat' => $this->jenis->nama])->first();
        $this->assertSame("{$plain->nomor_urut}/" . now()->year, $plain->nomor_surat); // the empty part collapses
    }

    public function test_variable_value_fills_the_lower_and_upper_case_tokens(): void
    {
        $date = \Illuminate\Support\Carbon::create(2026, 10, 7);
        NomorFormatSettings::save($this->jenis, '{N}/{v}/{V}/{M}/{Y}', 'romawi');
        NomorFormatSettings::saveVariable($this->jenis, 'Kegiatan');

        $this->assertSame('8/Pentas-Seni/PENTAS-SENI/X/2026', \App\Services\SuratKeluarService::composeNumber(8, $date, null, $this->jenis->nama, ' Pentas-Seni '));

        // From the Minta Nomor form: the value typed by the officer ends up in the stored number.
        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->callAction('reserve', ['count' => 1, 'tanggal_surat' => $date->toDateString(), 'jenis_surat' => $this->jenis->nama, 'variabel' => 'pensi'])
            ->assertHasNoActionErrors();
        $letter = \App\Models\SuratKeluar::latest('id')->firstOrFail();
        $this->assertSame("{$letter->nomor_urut}/pensi/PENSI/X/2026", $letter->nomor_surat);
        $this->assertSame('pensi', $letter->variabel);
    }

    public function test_letter_edit_form_asks_for_the_variable(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}/{V}/{Y}');
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas');
        $letter = app(\App\Services\SuratKeluarService::class)->reserve(1, now(), auth()->user(), ['jenis_surat' => 'Surat Lain'])->first();
        $fields = ['tanggal_surat' => now()->toDateString(), 'tujuan_surat' => 'Wali kelas', 'perihal' => 'Undangan', 'jenis_surat' => $this->jenis->nama];

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->callTableAction('edit', $letter, $fields)
            ->assertHasTableActionErrors(['variabel' => 'required']);

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->callTableAction('edit', $letter, $fields + ['variabel' => 'ix-a'])
            ->assertHasNoTableActionErrors();
        $this->assertSame("{$letter->nomor_urut}/IX-A/" . now()->year, $letter->fresh()->nomor_surat);
    }

    public function test_api_request_is_refused_without_the_required_variable(): void
    {
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas');
        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->postJson('/api/surat-keluar/nomor', ['jumlah' => 1, 'jenis_surat' => $this->jenis->nama])
            ->assertUnprocessable()->assertJsonPath('message', 'Kelas wajib diisi untuk jenis surat ini.');
        NomorFormatSettings::save($this->jenis, '{N}/{V}/{M}/{Y}', 'romawi');
        $response = $this->postJson('/api/surat-keluar/nomor', ['jumlah' => 2, 'jenis_surat' => $this->jenis->nama, 'variabel' => 'ix-a', 'tanggal_surat' => now()->setMonth(3)->toDateString()])
            ->assertCreated()
            ->assertJsonPath('data.0.variabel', 'ix-a');
        foreach ($response->json('data') as $letter) {
            $this->assertSame("{$letter['nomor_urut']}/IX-A/III/" . now()->year, $letter['nomor_surat']);
        }
    }

    public function test_request_form_explains_missing_required_fields(): void
    {
        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->callAction('reserve', ['count' => null, 'tanggal_surat' => null])
            ->assertHasActionErrors(['count' => 'required', 'tanggal_surat' => 'required'])
            ->assertSee('Isi jumlah nomor yang dibutuhkan.')
            ->assertSee('Pilih tanggal surat; bulan dan tahunnya masuk ke nomor.');

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->callAction('reserve', ['count' => 51, 'tanggal_surat' => now()->toDateString()])
            ->assertHasActionErrors(['count' => 'max'])
            ->assertSee('Paling banyak 50 nomor sekali minta.');
    }

    public function test_given_number_is_shown_with_a_copy_button(): void
    {
        NomorFormatSettings::save($this->jenis, '{N}/{V}/{Y}');
        NomorFormatSettings::saveVariable($this->jenis, 'Kelas');

        Livewire::test(\App\Filament\Resources\SuratKeluarResource\Pages\ListSuratKeluar::class)
            ->callAction('reserve', ['count' => 1, 'tanggal_surat' => now()->toDateString(), 'jenis_surat' => $this->jenis->nama, 'variabel' => 'ix-a'])
            ->assertHasNoActionErrors();
        $letter = \App\Models\SuratKeluar::latest('id')->firstOrFail();

        $notification = collect(session('filament.notifications'))->last();
        $this->assertSame($letter->nomor_surat, $notification['body']);
        $copy = collect($notification['actions'])->firstWhere('name', 'copy');
        $this->assertSame('Salin nomor', $copy['label']);
        $this->assertSame($letter->nomor_surat, $copy['extraAttributes']['data-copy-number']);
    }

    public function test_variable_definition_is_stored_on_the_letter_type(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('persuratan_masters', 'variabel_nomor'));
        $this->assertSame('jsonb', \Illuminate\Support\Facades\Schema::getColumnType('persuratan_masters', 'variabel_nomor'));
        $this->assertNull($this->jenis->variabel_nomor);
        // A row without a label is no variable at all.
        $this->jenis->update(['variabel_nomor' => ['keterangan' => 'tanpa nama']]);
        $this->assertNull(NomorFormatSettings::variable($this->jenis->fresh()));

        NomorFormatSettings::saveVariable($this->jenis, '  Nama kegiatan ', null, false);
        $this->assertSame(['label' => 'Nama kegiatan', 'keterangan' => null, 'wajib' => false], NomorFormatSettings::variable($this->jenis->fresh()));

        NomorFormatSettings::saveVariable($this->jenis, '');
        $this->assertNull(NomorFormatSettings::variable($this->jenis->fresh()));

        $this->expectException(\InvalidArgumentException::class);
        NomorFormatSettings::saveVariable(PersuratanMaster::where('type', 'tembusan')->firstOrFail(), 'Kelas');
    }
}
