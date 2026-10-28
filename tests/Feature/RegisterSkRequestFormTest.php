<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\PersuratanMaster;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Minta Nomor SK: the form for the SK data (tanggal, jenis, perihal, peminta, keterangan). */
class RegisterSkRequestFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
    }

    private function data(array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Pembagian Tugas dan Beban Kerja',
            'perihal' => 'Pembagian tugas guru semester genap',
            'nama_peminta' => 'Waka Kurikulum',
            'keterangan' => null,
            ...$override,
        ];
    }

    public function test_form_opens_with_todays_date_and_every_sk_field(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSet('mountedActionsData.0.tanggal_surat', fn (?string $date) => str_starts_with((string) $date, now()->toDateString()))
            ->assertSeeInOrder(['Data SK', 'Tanggal surat', 'Jenis SK', 'Perihal', 'Tentang', 'Nama peminta nomor', 'Keterangan'])
            ->assertSee('Tahunnya menentukan urutan nomor SK.')
            ->assertSee('Ditulis manual oleh petugas');
    }

    public function test_jenis_offers_only_active_master_jenis(): void
    {
        PersuratanMaster::where('type', 'jenis_sk')->where('nama', 'Keuangan dan Operasional')->update(['is_active' => false]);

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['jenis_sk' => 'Keuangan dan Operasional']))
            ->assertHasActionErrors(['jenis_sk' => 'in']);

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data())
            ->assertHasNoActionErrors();
    }

    public function test_required_fields_and_lengths_are_checked(): void
    {
        $before = SuratKeputusan::count();

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['tanggal_surat' => null, 'jenis_sk' => null, 'perihal' => null, 'nama_peminta' => null]))
            ->assertHasActionErrors(['tanggal_surat' => 'required', 'jenis_sk' => 'required', 'perihal' => 'required', 'nama_peminta' => 'required']);

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['perihal' => Str::repeat('a', 256), 'nama_peminta' => Str::repeat('b', 256), 'keterangan' => Str::repeat('c', 1001)]))
            ->assertHasActionErrors(['perihal' => 'max', 'nama_peminta' => 'max', 'keterangan' => 'max']);

        $this->assertSame($before, SuratKeputusan::count());
    }

    public function test_text_is_tidied_and_a_typed_tentang_is_dropped(): void
    {
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['perihal' => '  Tentang   Tim  Adiwiyata ', 'nama_peminta' => ' Pembina   Adiwiyata ', 'keterangan' => 'Berlaku satu tahun.']))
            ->assertHasNoActionErrors();

        $sk = SuratKeputusan::latest('id')->firstOrFail();
        $this->assertSame('Tim Adiwiyata', $sk->perihal);
        $this->assertSame('Pembina Adiwiyata', $sk->nama_peminta);
        $this->assertSame('Berlaku satu tahun.', $sk->keterangan);
        $this->assertSame('Pembagian Tugas dan Beban Kerja', $sk->jenis_sk);
        $this->assertSame(now()->toDateString(), $sk->tanggal_surat->toDateString());
    }

    public function test_api_drops_a_typed_tentang_too(): void
    {
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail(), ['*']);

        $this->postJson('/api/penomoran-sk', $this->data(['perihal' => 'TENTANG Tim Kurikulum']))->assertCreated()
            ->assertJsonPath('perihal', 'Tim Kurikulum');
        // "Tentangan" is not the word "tentang".
        $this->postJson('/api/penomoran-sk', $this->data(['perihal' => 'Tentangan warga']))->assertCreated()
            ->assertJsonPath('perihal', 'Tentangan warga');
    }

    public function test_ubah_uses_the_same_fields(): void
    {
        $sk = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $sk)
            ->assertSeeInOrder(['Nomor SK', 'Tanggal surat', 'Jenis SK', 'Perihal', 'Nama peminta nomor', 'Keterangan', 'Petugas pencatat'])
            ->setTableActionData(['perihal' => 'tentang Wali kelas'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertSame('Wali kelas', $sk->fresh()->perihal);
    }
}
