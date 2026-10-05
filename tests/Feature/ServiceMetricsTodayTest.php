<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Support\ServiceMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Ringkasan layanan hari ini: requests received and closed today. */
class ServiceMetricsTodayTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_todays_intake_and_output(): void
    {
        $this->freezeTime();

        Ticket::factory()->create(['mode' => 'online', 'status' => 'submitted']);
        Ticket::factory()->create(['mode' => 'offline', 'status' => 'in_process']);
        Ticket::factory()->create(['mode' => 'online', 'status' => 'completed', 'actual_completion_date' => today()]);
        Ticket::factory()->create(['mode' => 'offline', 'status' => 'rejected']);

        // Yesterday's request, finished yesterday: not part of today.
        $old = Ticket::factory()->create(['status' => 'completed', 'actual_completion_date' => today()->subDay()]);
        $old->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();

        // Older request finished today: counts as output, not intake.
        $finished = Ticket::factory()->create(['status' => 'completed', 'actual_completion_date' => today()]);
        $finished->forceFill(['created_at' => now()->subDays(3)])->save();

        $today = ServiceMetrics::today();

        $this->assertSame(4, $today['in']);
        $this->assertSame(2, $today['in_online']);
        $this->assertSame(2, $today['in_offline']);
        $this->assertSame(2, $today['completed']);
        $this->assertSame(1, $today['rejected']);
        $this->assertSame(2, $today['open']);
    }

    public function test_staff_read_the_summary_as_json(): void
    {
        $this->seed();
        Ticket::factory()->create(['mode' => 'online', 'status' => 'submitted']);
        $staff = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();

        Sanctum::actingAs($staff);
        $this->getJson('/api/layanan/ringkasan-hari-ini')
            ->assertOk()
            ->assertJsonPath('tanggal', today()->toDateString())
            ->assertJsonPath('masuk.total', ServiceMetrics::today()['in'])
            ->assertJsonStructure(['masuk' => ['total', 'online', 'loket'], 'selesai', 'ditolak', 'masih_diproses', 'melewati_target']);
    }

    public function test_applicants_and_guests_cannot_read_the_summary(): void
    {
        $this->seed();
        $this->getJson('/api/layanan/ringkasan-hari-ini')->assertUnauthorized();

        Sanctum::actingAs(User::where('email', 'budi.santoso@email.com')->firstOrFail());
        $this->getJson('/api/layanan/ringkasan-hari-ini')->assertForbidden();
    }

    public function test_trend_buckets_requests_per_day_and_month(): void
    {
        $this->freezeTime();
        Ticket::factory()->create(['status' => 'submitted']);
        $done = Ticket::factory()->create(['status' => 'completed', 'actual_completion_date' => today()]);
        $done->forceFill(['created_at' => now()->subDays(2)])->save();

        $week = ServiceMetrics::trend('7d');
        $this->assertCount(7, $week);
        $this->assertSame(['periode' => today()->format('Y-m-d'), 'label' => today()->translatedFormat('j M'), 'masuk' => 1, 'selesai' => 1], end($week));
        $this->assertSame(1, $week[4]['masuk']);

        $year = ServiceMetrics::trend('12m');
        $this->assertCount(12, $year);
        $this->assertSame(2, array_sum(array_column($year, 'masuk')));
        $this->assertSame(1, array_sum(array_column($year, 'selesai')));
    }

    public function test_staff_read_the_trend_as_json(): void
    {
        $this->seed();
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/layanan/tren?rentang=7d')->assertOk()->assertJsonPath('rentang', '7d')->assertJsonCount(7, 'data');
        $this->getJson('/api/layanan/tren')->assertOk()->assertJsonCount(30, 'data');
        $this->getJson('/api/layanan/tren?rentang=5y')->assertUnprocessable();
    }

    public function test_staff_read_a_monthly_snapshot_as_json(): void
    {
        $this->seed();
        $lastMonth = now()->subMonthNoOverflow();
        $ticket = Ticket::factory()->create(['status' => 'completed', 'actual_completion_date' => $lastMonth->copy()->startOfMonth()->addDays(2)]);
        $ticket->forceFill(['created_at' => $lastMonth->copy()->startOfMonth()->addDay()])->save();
        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $expected = ServiceMetrics::tickets($lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth());
        $this->getJson('/api/layanan/laporan-bulanan?bulan=' . $lastMonth->format('Y-m'))
            ->assertOk()
            ->assertJsonPath('bulan', $lastMonth->format('Y-m'))
            ->assertJsonPath('permohonan', $expected['total'])
            ->assertJsonPath('selesai', $expected['completed']);

        $this->getJson('/api/layanan/laporan-bulanan')->assertOk()->assertJsonPath('bulan', now()->format('Y-m'));
        $this->getJson('/api/layanan/laporan-bulanan?bulan=' . now()->addMonths(2)->format('Y-m'))->assertUnprocessable();
    }

    public function test_staff_read_the_service_condition_as_json(): void
    {
        $this->seed();
        Ticket::query()->delete();
        Ticket::factory()->create(['status' => 'submitted']);
        Ticket::factory()->create(['status' => 'verified']);
        Ticket::factory()->create(['status' => 'in_process']);
        Ticket::factory()->create(['status' => 'completed', 'mode' => 'offline', 'ready_for_pickup' => true]);
        Sanctum::actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/layanan/kondisi')
            ->assertOk()
            ->assertJsonPath('total', 4)
            ->assertJsonPath('menunggu_verifikasi', 1)
            ->assertJsonPath('sedang_diproses', 2)
            ->assertJsonPath('siap_diambil', 1)
            ->assertJsonPath('selesai', 1);
    }

    public function test_condition_can_be_limited_to_the_callers_own_work(): void
    {
        $this->seed();
        Ticket::query()->delete();
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        Ticket::factory()->create(['status' => 'in_process', 'assigned_to_id' => $officer->id]);
        Ticket::factory()->create(['status' => 'in_process']);
        Ticket::factory()->create(['status' => 'submitted']);

        Sanctum::actingAs($officer);
        $this->getJson('/api/layanan/kondisi')->assertJsonPath('lingkup', 'semua')->assertJsonPath('total', 3);
        $this->getJson('/api/layanan/kondisi?lingkup=saya')->assertJsonPath('lingkup', 'saya')->assertJsonPath('total', 1)->assertJsonPath('sedang_diproses', 1);

        // Supervisors have no personal queue: they always see the whole office.
        Sanctum::actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/layanan/kondisi?lingkup=saya')->assertJsonPath('lingkup', 'semua')->assertJsonPath('total', 3);
    }
}
