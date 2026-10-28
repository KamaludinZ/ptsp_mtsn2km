<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\ActiveRoles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Formulir Minta Nomor SK: only its own fields and its own scan are saved, by an officer whose active role may ask. */
class RegisterSkRequestFormSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function data(array $override = []): array
    {
        return [
            'tanggal_surat' => now()->toDateString(),
            'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan',
            'perihal' => 'Panitia class meeting',
            'nama_peminta' => 'Waka Kesiswaan',
            ...$override,
        ];
    }

    public function test_the_form_says_the_scan_stays_private(): void
    {
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->assertSee('Berkas disimpan privat dan hanya dapat dibuka petugas yang berwenang atas register SK.');
    }

    public function test_smuggled_fields_do_not_reach_the_register(): void
    {
        $this->actingAs($officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $year = now()->year;

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data([
                'pembuat_id' => $admin->id,
                'peran_pembuat' => 'admin',
                'nomor_urut' => 999,
                'nomor_sk' => '999 TAHUN 2000',
                'tahun' => 2000,
            ]))
            ->assertHasNoActionErrors();

        $sk = SuratKeputusan::where('perihal', 'Panitia class meeting')->sole();
        $this->assertSame([7, "7 TAHUN {$year}", $year], [$sk->nomor_urut, $sk->nomor_sk, $sk->tahun]);
        $this->assertSame([$officer->id, 'back_office'], [$sk->pembuat_id, $sk->peran_pembuat]);
        $this->assertNull($sk->berkas_scan);
    }

    public function test_a_role_switched_away_mid_form_cannot_take_a_number(): void
    {
        $user = User::factory()->create(['user_type' => 'pegawai']);
        $user->assignRole(['back_office', 'front_desk']);
        $user->forceFill(['active_role_id' => Role::findByName('back_office', 'web')->id])->save();
        $this->actingAs($user = $user->fresh());
        session([ActiveRoles::SESSION_KEY => 'back_office']);
        $before = SuratKeputusan::count();

        $page = Livewire::test(ListSuratKeputusan::class)
            ->mountAction('reserve')
            ->setActionData($this->data());

        // Another tab switched the active role to Front Desk while the form was open.
        $user->forceFill(['active_role_id' => Role::findByName('front_desk', 'web')->id])->save();
        $user->unsetRelation('activeRole');
        session([ActiveRoles::SESSION_KEY => 'front_desk']);

        // The register refuses the submit: Front Desk has no access to it.
        $page->call('callMountedAction')->assertForbidden();
        $this->assertSame($before, SuratKeputusan::count());
    }

    public function test_a_tampered_form_cannot_claim_another_sks_scan(): void
    {
        Storage::fake('local');
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $archived = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 1)->firstOrFail();
        app(SuratKeputusanService::class)->attachScan($archived, UploadedFile::fake()->createWithContent('SK wali kelas.pdf', '%PDF-1.4 asli'));
        $path = $archived->fresh()->berkas_scan;
        $before = SuratKeputusan::count();

        // The stored path of SK 1 put back into the form state instead of an upload.
        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(['berkas_scan' => ['x' => $path]]))
            ->assertNotified('Nomor SK tidak dapat diberikan');
        $this->assertSame($before, SuratKeputusan::count());

        $other = SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 2)->firstOrFail();
        try {
            app(SuratKeputusanService::class)->describe($other, ['berkas_scan' => $path, 'berkas_scan_nama' => 'curian.pdf']);
            $this->fail('Another SK\'s scan was accepted.');
        } catch (TicketActionException $e) {
            $this->assertSame('Berkas scan tidak dikenal; unggah ulang scan SK.', $e->getMessage());
        }

        $this->assertNull($other->fresh()->berkas_scan);
        $this->assertSame($path, $archived->fresh()->berkas_scan);
        Storage::disk('local')->assertExists($path);
        // Its own scan still saves unchanged.
        app(SuratKeputusanService::class)->describe($archived->fresh(), ['berkas_scan' => $path, 'nama_peminta' => 'Kepala TU']);
        $this->assertSame('Kepala TU', $archived->fresh()->nama_peminta);
    }
}
