<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Register Penomoran SK buttons: Minta Nomor SK, Ubah, Hapus. */
class RegisterSkActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_back_office_sees_minta_nomor_and_ubah_but_not_hapus(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $sk = SuratKeputusan::firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->assertActionVisible('reserve')
            ->assertActionHasLabel('reserve', 'Minta Nomor SK')
            ->assertTableActionVisible('edit', $sk)
            ->assertTableActionHasLabel('edit', 'Ubah', $sk)
            ->assertTableActionHidden('delete', $sk);
    }

    public function test_administrator_also_sees_hapus(): void
    {
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));
        $sk = SuratKeputusan::firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->assertActionVisible('reserve')
            ->assertTableActionVisible('edit', $sk)
            ->assertTableActionVisible('delete', $sk);
    }

    public function test_minta_nomor_gives_the_next_number_of_the_year(): void
    {
        $this->actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'));
        $year = now()->year;
        $next = SuratKeputusan::where('tahun', $year)->max('nomor_urut') + 1;

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', [
                'tanggal_surat' => now()->toDateString(),
                'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
                'perihal' => 'Panitia ujian akhir madrasah',
                'nama_peminta' => 'Waka Kurikulum',
            ])
            ->assertHasNoActionErrors()
            ->assertNotified('Nomor SK diberikan');

        $this->assertDatabaseHas('surat_keputusan', [
            'tahun' => $year,
            'nomor_urut' => $next,
            'nomor_sk' => "{$next} TAHUN {$year}",
            'perihal' => 'Panitia ujian akhir madrasah',
            'pembuat_id' => $officer->id,
        ]);
    }

    public function test_minta_nomor_requires_the_sk_data(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $before = SuratKeputusan::count();

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', ['tanggal_surat' => now()->toDateString(), 'jenis_sk' => null, 'perihal' => '', 'nama_peminta' => ''])
            ->assertHasActionErrors(['jenis_sk' => 'required', 'perihal' => 'required', 'nama_peminta' => 'required']);

        // The letter date stays within the years the register (and its API) can show.
        $sk = ['jenis_sk' => 'Keuangan dan Operasional', 'perihal' => 'Bendahara', 'nama_peminta' => 'Kepala Tata Usaha'];
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', ['tanggal_surat' => '1999-12-31', ...$sk])
            ->assertHasActionErrors(['tanggal_surat' => 'after_or_equal'])
            ->callAction('reserve', ['tanggal_surat' => '2101-01-01', ...$sk])
            ->assertHasActionErrors(['tanggal_surat' => 'before_or_equal']);

        $this->assertSame($before, SuratKeputusan::count());
    }

    public function test_ubah_corrects_the_data_but_keeps_the_number(): void
    {
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));
        $sk = SuratKeputusan::where('nomor_urut', 1)->firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->mountTableAction('edit', $sk)
            ->assertSee('Data SK ' . $sk->nomor_sk)
            ->setTableActionData(['perihal' => 'Pengangkatan wali kelas (revisi)', 'nama_peminta' => 'Kepala Madrasah'])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $sk->refresh();
        $this->assertSame('Pengangkatan wali kelas (revisi)', $sk->perihal);
        $this->assertSame('Kepala Madrasah', $sk->nama_peminta);
        $this->assertSame('1 TAHUN ' . now()->year, $sk->nomor_sk);
    }

    public function test_hapus_asks_for_a_reason_and_logs_it(): void
    {
        $this->actingAs($admin = $this->user('ptsp@mtsn2malang.sch.id'));
        $sk = SuratKeputusan::where('nomor_urut', 2)->firstOrFail();

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('delete', $sk, ['alasan' => ''])
            ->assertHasTableActionErrors(['alasan' => 'required']);
        $this->assertModelExists($sk);

        Livewire::test(ListSuratKeputusan::class)
            ->callTableAction('delete', $sk, ['alasan' => 'Salah input, SK dibatalkan.'])
            ->assertHasNoTableActionErrors()
            ->assertNotified('SK dihapus');

        $this->assertModelMissing($sk);
        $log = \Spatie\Activitylog\Models\Activity::inLog('surat_keputusan')->latest('id')->firstOrFail();
        $this->assertSame($admin->id, $log->causer_id);
        $this->assertSame('Salah input, SK dibatalkan.', $log->properties['alasan']);
        $this->assertSame($sk->nomor_sk, $log->properties['old']['nomor_sk']);
    }

    public function test_front_desk_gets_no_buttons(): void
    {
        $this->actingAs($this->user('loket1@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeputusan::class)->assertForbidden();
    }
}
