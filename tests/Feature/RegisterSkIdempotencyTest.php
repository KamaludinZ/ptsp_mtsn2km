<?php

namespace Tests\Feature;

use App\Models\SuratKeputusan;
use App\Models\User;
use App\Services\SuratKeputusanService;
use App\Support\Persuratan;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Idempotency-Key on POST /api/penomoran-sk: a request sent again gets the SK it was given, not a second number. */
class RegisterSkIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'tiket-PTSP-0142:sk-1';

    private User $officer;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function data(): array
    {
        return ['tanggal_surat' => now()->toDateString(), 'perihal' => 'Penugasan guru pendamping', 'jenis_sk' => Persuratan::JENIS_SK[1], 'nama_peminta' => 'Waka Kurikulum'];
    }

    private function lastIssued(): ?int
    {
        return DB::table('surat_keputusan_sequences')->where('tahun', now()->year)->value('last_number');
    }

    public function test_the_same_request_sent_again_gets_the_same_number(): void
    {
        Sanctum::actingAs($this->officer, ['*']);

        $first = $this->withHeader('Idempotency-Key', self::KEY)->postJson('/api/penomoran-sk', $this->data())->assertCreated();
        $last = $this->lastIssued();

        $again = $this->withHeader('Idempotency-Key', self::KEY)->postJson('/api/penomoran-sk', $this->data())->assertOk()
            ->assertHeader('Idempotent-Replayed', 'true');

        $this->assertSame($first->json('id'), $again->json('id'));
        $this->assertSame($first->json('nomor_sk'), $again->json('nomor_sk'));
        $this->assertSame($last, $this->lastIssued());
        $this->assertSame(1, SuratKeputusan::where('perihal', 'Penugasan guru pendamping')->count());
        $this->assertArrayNotHasKey('kunci_permintaan', $again->json());
    }

    public function test_keys_belong_to_one_officer_and_a_new_key_is_a_new_number(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $first = $this->withHeader('Idempotency-Key', self::KEY)->postJson('/api/penomoran-sk', $this->data())->assertCreated()->json('nomor_urut');
        $second = $this->withHeader('Idempotency-Key', self::KEY . '-b')->postJson('/api/penomoran-sk', $this->data())->assertCreated()->json('nomor_urut');
        $this->assertSame($first + 1, $second);

        Sanctum::actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail(), ['*']);
        $this->withHeader('Idempotency-Key', self::KEY)->postJson('/api/penomoran-sk', $this->data())->assertCreated()->assertJsonPath('nomor_urut', $second + 1);
    }

    public function test_a_malformed_key_is_refused_before_a_number_is_taken(): void
    {
        Sanctum::actingAs($this->officer, ['*']);
        $last = $this->lastIssued();

        foreach (['pendek', str_repeat('k', 101), 'ada spasi di sini', 'kunci/miring'] as $key) {
            $this->withHeader('Idempotency-Key', $key)->postJson('/api/penomoran-sk', $this->data())->assertStatus(422);
        }
        $this->assertSame($last, $this->lastIssued());
    }

    public function test_two_requests_racing_with_one_key_take_one_number(): void
    {
        $service = app(SuratKeputusanService::class);
        $this->actingAs($this->officer);
        $sk = $service->reserve(Carbon::parse(now()->toDateString()), $this->officer, [...$this->data(), 'kunci_permintaan' => self::KEY]);
        $last = $this->lastIssued();

        // The second, which did not see the first yet: refused by the database, its number rolled back.
        try {
            $service->reserve(Carbon::parse(now()->toDateString()), $this->officer, [...$this->data(), 'kunci_permintaan' => self::KEY]);
            $this->fail('A second number was given for one key.');
        } catch (UniqueConstraintViolationException) {
        }
        $this->assertSame($last, $this->lastIssued());

        // Through the API, that late request gets the first one's SK.
        Sanctum::actingAs($this->officer, ['*']);
        $this->withHeader('Idempotency-Key', self::KEY)->postJson('/api/penomoran-sk', $this->data())->assertOk()->assertJsonPath('id', $sk->id);
    }
}
