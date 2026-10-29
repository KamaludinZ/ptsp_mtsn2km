<?php

namespace Tests\Feature;

use App\Filament\Resources\SuratKeputusanResource;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Filament\Resources\SuratKeputusanResource\Pages\ViewSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\RoleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** Rincian SK: the detail page of one SK in the register. */
class RegisterSkDetailPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        Storage::fake('local');
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function sk(int $nomorUrut): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->with('pembuat')->firstOrFail();
    }

    private function url(SuratKeputusan $sk): string
    {
        return SuratKeputusanResource::getUrl('view', ['record' => $sk]);
    }

    public function test_the_detail_page_shows_everything_recorded_about_the_sk(): void
    {
        $sk = $this->sk(2);
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $this->get($this->url($sk))->assertOk()
            ->assertSee('SK ' . $sk->nomor_sk)
            ->assertSeeInOrder(['No. urut', '2', 'Tahun register', (string) now()->year])
            ->assertSee($sk->tanggal_surat->translatedFormat('d M Y'))
            ->assertSee($sk->jenis_sk)
            ->assertSee('Tentang ' . $sk->perihal)
            ->assertSee($sk->nama_peminta)
            ->assertSee('Lampiran daftar pembagian tugas 4 halaman.')
            ->assertSeeInOrder(['Petugas pencatat', $sk->pembuat->name, 'Sebagai', RoleAccess::roleLabel('back_office')])
            ->assertSeeInOrder(['Dicatat', $sk->created_at->translatedFormat('d M Y H:i')])
            ->assertSee('Belum pernah diubah')
            ->assertSee('Menunggu scan')
            ->assertSee('Belum ada scan diunggah.');
    }

    public function test_an_archived_sk_shows_its_scan(): void
    {
        $sk = $this->sk(1);
        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->create('SK wali kelas.pdf', 100, 'application/pdf'));
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        $this->get($this->url($sk))->assertOk()
            ->assertSee('Terarsip')
            ->assertSee('SK wali kelas.pdf')
            ->assertSeeInOrder(['Jenis berkas', 'PDF'])
            ->assertDontSee('Belum ada scan diunggah.')
            // The stored path stays on the server.
            ->assertDontSee($sk->fresh()->berkas_scan);
    }

    public function test_the_register_opens_the_detail_page(): void
    {
        $sk = $this->sk(3);
        $this->actingAs($this->user('staff1@mtsn2malang.sch.id'));

        Livewire::test(ListSuratKeputusan::class)
            ->assertTableActionVisible('view', $sk)
            ->assertTableActionHasUrl('view', $this->url($sk), $sk);
    }

    public function test_corrections_from_the_detail_page_show_right_away(): void
    {
        $sk = $this->sk(4);
        $this->actingAs($officer = $this->user('staff1@mtsn2malang.sch.id'));
        $this->travel(5)->minutes();

        Livewire::test(ViewSuratKeputusan::class, ['record' => $sk->getRouteKey()])
            ->assertActionVisible('edit')
            ->assertActionHidden('delete')
            ->callAction('edit', ['keterangan' => 'Berlaku sampai akhir tahun pelajaran.'])
            ->assertHasNoActionErrors()
            ->assertSee('Berlaku sampai akhir tahun pelajaran.')
            ->assertDontSee('Belum pernah diubah')
            ->assertSee('oleh ' . $officer->name . ' · ' . RoleAccess::roleLabel('back_office'));

        $this->assertSame('Berlaku sampai akhir tahun pelajaran.', $sk->fresh()->keterangan);
    }

    public function test_changes_without_a_signed_in_account_are_shown_as_sistem(): void
    {
        $sk = $this->sk(3);
        $this->assertNull(SuratKeputusanService::lastEditor($sk));

        app(SuratKeputusanService::class)->attachScan($sk, UploadedFile::fake()->image('SK panitia.jpg'));

        $this->assertSame('Sistem', SuratKeputusanService::lastEditor($sk->fresh()));
        $this->assertSame('JPG', $sk->fresh()->scanType());
    }

    public function test_an_administrator_can_delete_from_the_detail_page(): void
    {
        $sk = $this->sk(6);
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        Livewire::test(ViewSuratKeputusan::class, ['record' => $sk->getRouteKey()])
            ->callAction('delete', ['alasan' => 'Salah input data'])
            ->assertHasNoActionErrors()
            ->assertNotified('SK dihapus')
            ->assertRedirect(SuratKeputusanResource::getUrl('index'));

        $this->assertModelMissing($sk);
    }

    public function test_an_sk_whose_officer_was_removed_still_opens(): void
    {
        $sk = $this->sk(5);
        $sk->pembuat->forceDelete();
        $this->actingAs($this->user('ptsp@mtsn2malang.sch.id'));

        $this->get($this->url($sk))->assertOk()
            ->assertSeeInOrder(['Petugas pencatat', 'Akun sudah dihapus', 'Sebagai', RoleAccess::roleLabel('back_office')]);
    }

    public function test_front_desk_cannot_open_the_detail_page(): void
    {
        $this->actingAs($this->user('loket1@mtsn2malang.sch.id'));

        $this->get($this->url($this->sk(1)))->assertForbidden();
    }
}
