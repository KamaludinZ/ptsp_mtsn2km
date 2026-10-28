<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/** POST /api/penomoran-sk/kumpulan: several SK of one year, numbered one after another, all or none. */
class RegisterSkBatchTest extends TestCase
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
        Sanctum::actingAs($this->officer, ['penomoran-sk:tulis']);
    }

    private function item(string $perihal, array $override = []): array
    {
        return ['tanggal_surat' => now()->toDateString(), 'perihal' => $perihal, 'jenis_sk' => Persuratan::JENIS_SK[2], 'nama_peminta' => 'Waka Kesiswaan', ...$override];
    }

    private function lastIssued(): ?int
    {
        return DB::table('surat_keputusan_sequences')->where('tahun', now()->year)->value('last_number');
    }

    public function test_one_meeting_s_sk_get_numbers_one_after_another(): void
    {
        $year = now()->year;

        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => [
            $this->item('Tentang Panitia lomba'),
            $this->item('Panitia pentas seni', ['jenis_sk' => mb_strtoupper(Persuratan::JENIS_SK[2])]),
            $this->item('Panitia karya wisata', ['keterangan' => 'Dua hari.']),
        ]])->assertCreated()
            ->assertJsonPath('jumlah', 3)
            ->assertJsonPath('nomor', "7–9 TAHUN {$year}")
            ->assertJsonPath('data.*.nomor_urut', [7, 8, 9])
            ->assertJsonPath('data.0.perihal', 'Panitia lomba')
            ->assertJsonPath('data.1.jenis_sk', Persuratan::JENIS_SK[2]);

        $this->assertSame(9, $this->lastIssued());
        $this->assertSame(3, Activity::inLog('surat_keputusan')->where('event', 'created')->count());
    }

    public function test_one_bad_sk_takes_no_number_for_any(): void
    {
        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => [$this->item('Panitia lomba'), $this->item('Panitia salah', ['jenis_sk' => 'SK Lainnya'])]])
            ->assertJsonValidationErrors('sk.1.jenis_sk');

        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => [$this->item('Panitia lomba'), $this->item('Panitia tahun lalu', ['tanggal_surat' => (now()->year - 1) . '-12-30'])]])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Semua SK dalam satu kumpulan harus bertanggal tahun yang sama: nomornya satu urutan tahunan.');

        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => array_map(fn (int $n) => $this->item("Panitia {$n}"), range(1, 11))])
            ->assertJsonValidationErrors(['sk' => 'Satu kumpulan berisi paling banyak 10 SK.']);

        $this->assertSame(6, SuratKeputusan::where('tahun', now()->year)->count());
    }

    public function test_the_numbers_count_against_the_limits(): void
    {
        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => array_map(fn (int $n) => $this->item("Panitia {$n}"), range(1, 8))])->assertCreated();

        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => [$this->item('Panitia 9'), $this->item('Panitia 10'), $this->item('Panitia 11')]])->assertStatus(429)
            ->assertJsonPath('message', fn (string $message) => str_starts_with($message, 'Kumpulan 3 SK melebihi batas 10 nomor SK dalam satu menit (sudah diminta 8).'));
        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => [$this->item('Panitia 9'), $this->item('Panitia 10')]])->assertCreated();
        $this->postJson('/api/penomoran-sk', $this->item('Panitia 11'))->assertStatus(429);
    }

    public function test_a_batch_may_belong_to_a_ticket_and_needs_a_writing_token(): void
    {
        $ticket = Ticket::whereHas('service')->firstOrFail();

        $ids = $this->postJson('/api/penomoran-sk/kumpulan', ['tiket' => $ticket->id, 'sk' => [$this->item('Panitia lomba'), $this->item('Panitia pentas')]])
            ->assertCreated()->json('data.*.id');
        $this->assertSame([$ticket->id, $ticket->id], SuratKeputusan::whereIn('id', $ids)->pluck('ticket_id')->all());

        Sanctum::actingAs($this->officer, ['penomoran-sk:baca']);
        $this->postJson('/api/penomoran-sk/kumpulan', ['sk' => [$this->item('Panitia lomba')]])->assertForbidden();
    }

    public function test_the_service_refuses_an_empty_or_oversized_batch(): void
    {
        $this->actingAs($this->officer);
        $this->expectExceptionMessage('Satu kumpulan berisi 1–10 SK.');
        app(SuratKeputusanService::class)->reserveMany($this->officer, []);
    }
}
