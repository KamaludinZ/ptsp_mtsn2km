<?php

namespace Tests\Feature;

use App\Exceptions\SkNumberLimitException;
use App\Filament\Resources\SuratKeputusanResource\Pages\ListSuratKeputusan;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** SK numbers are never given back: one account cannot take them faster than a person asks for them. */
class RegisterSkNumberLimitTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($this->officer);
    }

    private function data(int $n = 1): array
    {
        return ['tanggal_surat' => now()->toDateString(), 'perihal' => 'Panitia kegiatan ' . $n, 'jenis_sk' => 'Kepanitiaan dan Tim Pelaksana Kegiatan', 'nama_peminta' => 'Waka Kesiswaan'];
    }

    private function take(int $times, ?User $by = null): void
    {
        foreach (range(1, $times) as $n) {
            app(SuratKeputusanService::class)->reserve(Carbon::parse(now()->toDateString()), $by ?? $this->officer, $this->data($n));
        }
    }

    private function lastIssued(): ?int
    {
        return \Illuminate\Support\Facades\DB::table('surat_keputusan_sequences')->where('tahun', now()->year)->value('last_number');
    }

    public function test_more_than_ten_numbers_a_minute_are_held_and_logged(): void
    {
        $this->take(SuratKeputusanService::MAX_PER_MINUTE);
        $last = $this->lastIssued();

        try {
            $this->take(1);
            $this->fail('An eleventh number in a minute was given.');
        } catch (SkNumberLimitException $e) {
            $this->assertStringStartsWith('Akun ini sudah meminta 10 nomor SK dalam satu menit. Coba lagi dalam 1 menit', $e->getMessage());
            $this->assertGreaterThan(0, $e->retryAfter);
        }

        $this->assertSame($last, $this->lastIssued());
        $log = Activity::inLog('surat_keputusan')->where('event', 'limited')->sole();
        $this->assertTrue($log->causer->is($this->officer));
        $this->assertSame('Permintaan nomor SK ditahan: lebih dari 10 nomor dalam satu menit', $log->description);

        // Another officer is not held back, and a minute later this one may go on.
        $this->take(1, User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        $this->travel(61)->seconds();
        $this->take(1);
    }

    public function test_the_day_has_its_own_limit(): void
    {
        foreach (range(1, intdiv(SuratKeputusanService::MAX_PER_DAY, SuratKeputusanService::MAX_PER_MINUTE)) as $minute) {
            $this->take(SuratKeputusanService::MAX_PER_MINUTE);
            $this->travel(61)->seconds();
        }

        $this->expectException(SkNumberLimitException::class);
        $this->expectExceptionMessage('Akun ini sudah meminta 150 nomor SK dalam satu hari.');
        $this->take(1);
    }

    public function test_api_clients_get_429_with_retry_after(): void
    {
        $this->take(SuratKeputusanService::MAX_PER_MINUTE);
        Sanctum::actingAs($this->officer, ['*']);

        $response = $this->postJson('/api/penomoran-sk', $this->data(99))->assertStatus(429)
            ->assertJsonStructure(['message', 'coba_lagi_dalam_detik']);
        $this->assertSame((string) $response->json('coba_lagi_dalam_detik'), $response->headers->get('Retry-After'));
        $this->assertSame(0, SuratKeputusan::where('perihal', 'Panitia kegiatan 99')->count());
    }

    public function test_the_panel_tells_the_officer_and_keeps_the_form(): void
    {
        $this->take(SuratKeputusanService::MAX_PER_MINUTE);

        Livewire::test(ListSuratKeputusan::class)
            ->callAction('reserve', $this->data(99))
            ->assertNotified('Nomor SK tidak dapat diberikan')
            ->assertActionMounted('reserve');

        $this->assertSame(0, SuratKeputusan::where('perihal', 'Panitia kegiatan 99')->count());
    }
}
