<?php

namespace Tests\Feature;

use App\Filament\Widgets\MonthlyReportSnapshot;
use App\Filament\Widgets\QuickActions;
use App\Filament\Widgets\ServiceConditionOverview;
use App\Filament\Widgets\TicketTrendChart;
use App\Filament\Widgets\TodaySummary;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The dashboard widgets every staff role shares: today's summary, service
 * condition, quick access, this month's report snapshot and the request trend chart.
 */
class StaffDashboardWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public static function staff(): array
    {
        return [
            'admin' => ['ptsp@mtsn2malang.sch.id'],
            'kepala_sekolah' => ['kepsek@mtsn2malang.sch.id'],
            'back_office' => ['staff1@mtsn2malang.sch.id'],
            'front_desk' => ['loket1@mtsn2malang.sch.id'],
            'supervisor' => ['pengawas@mtsn2malang.sch.id'],
        ];
    }

    /** @dataProvider staff */
    public function test_shared_widgets_render_for_every_staff_role(string $email): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();

        $this->actingAs(User::where('email', $email)->firstOrFail());

        Livewire::test(TodaySummary::class)->assertOk()->assertSee('Layanan Hari Ini');
        Livewire::test(ServiceConditionOverview::class)->assertOk()->assertSee('Kondisi Layanan');
        Livewire::test(MonthlyReportSnapshot::class)->assertOk()->assertSee('Laporan Bulan');
        Livewire::test(QuickActions::class)->assertOk();

        foreach (['7d', '30d', '12m'] as $range) {
            Livewire::test(TicketTrendChart::class)->set('filter', $range)->assertOk();
        }
    }

    public function test_quick_actions_only_list_pages_the_role_may_open(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();

        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(QuickActions::class)
            ->assertSee('Registrasi Layanan')
            ->assertDontSee('Pengguna')
            ->assertDontSee('Keamanan Sistem');
    }

    public function test_service_condition_explains_an_empty_office(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        Ticket::query()->delete();

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(ServiceConditionOverview::class)
            ->assertSee('Belum ada permohonan')
            ->assertDontSee('Menunggu verifikasi');
    }
}
