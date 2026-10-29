<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** The register as evidence: a given number never changes, and an SK leaves it only through "Hapus". */
class RegisterSkRegisterGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    private function sk(int $nomorUrut): SuratKeputusan
    {
        return SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', $nomorUrut)->firstOrFail();
    }

    private function refused(\Closure $change, string $message): void
    {
        try {
            DB::transaction($change);
            $this->fail('The database accepted: ' . $message);
        } catch (QueryException $e) {
            $this->assertStringContainsString($message, $e->getMessage());
        }
    }

    public function test_a_given_number_never_changes(): void
    {
        $sk = $this->sk(2);
        $year = now()->year;

        $this->refused(fn () => DB::table('surat_keputusan')->where('id', $sk->id)->update(['nomor_urut' => 20, 'nomor_sk' => "20 TAHUN {$year}"]), 'Nomor SK yang sudah terbit tidak dapat diubah');
        $this->refused(fn () => DB::table('surat_keputusan')->where('id', $sk->id)->update(['tahun' => $year + 1, 'nomor_sk' => '2 TAHUN ' . ($year + 1), 'tanggal_surat' => ($year + 1) . '-01-02']), 'Nomor SK yang sudah terbit tidak dapat diubah');

        // The rest of the SK can still be corrected.
        DB::table('surat_keputusan')->where('id', $sk->id)->update(['perihal' => 'Pembagian tugas mengajar semester genap']);
        $this->assertSame("2 TAHUN {$year}", $sk->fresh()->nomor_sk);
    }

    public function test_an_sk_cannot_be_removed_behind_the_register_s_back(): void
    {
        $sk = $this->sk(3);

        $this->refused(fn () => $sk->delete(), 'SK hanya dapat dihapus lewat "Hapus" di register');
        $this->refused(fn () => DB::table('surat_keputusan')->delete(), 'SK hanya dapat dihapus lewat "Hapus" di register');
        $this->refused(fn () => DB::statement('TRUNCATE surat_keputusan CASCADE'), 'SK hanya dapat dihapus lewat "Hapus" di register');

        $this->assertSame(6, SuratKeputusan::count());
    }

    public function test_hapus_deletes_with_its_reason_and_closes_again(): void
    {
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($admin);

        app(SuratKeputusanService::class)->delete($this->sk(4), $admin, 'Salah catat, SK dibatalkan');

        $this->assertNull(SuratKeputusan::where('tahun', now()->year)->where('nomor_urut', 4)->first());
        $this->assertSame('Salah catat, SK dibatalkan', Activity::inLog('surat_keputusan')->where('event', 'deleted')->sole()->properties['alasan']);
        // The way stays shut after "Hapus".
        $this->refused(fn () => $this->sk(5)->delete(), 'SK hanya dapat dihapus lewat "Hapus" di register');
    }

    public function test_retention_may_delete_inside_with_deletes_allowed(): void
    {
        $removed = SuratKeputusan::withDeletesAllowed(fn () => SuratKeputusan::where('nomor_urut', 6)->delete());

        $this->assertSame(1, $removed);
        $this->refused(fn () => $this->sk(5)->delete(), 'SK hanya dapat dihapus lewat "Hapus" di register');
    }

    public function test_the_sk_trail_has_its_indexes(): void
    {
        $indexes = DB::table('pg_indexes')->where('tablename', 'activity_log')->pluck('indexdef', 'indexname');

        $this->assertStringContainsString('(log_name, event, id DESC)', $indexes['activity_log_log_name_event_id_idx']);
        $this->assertStringContainsString("WHERE (((log_name)::text = 'surat_keputusan'::text) AND ((event)::text = 'deleted'::text))", $indexes['activity_log_sk_dihapus_tahun_idx']);
    }
}
