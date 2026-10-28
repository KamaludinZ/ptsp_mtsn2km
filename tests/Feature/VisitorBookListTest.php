<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorResource\Pages\ListVisitors;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** The visitor book lists (public page, counter API, Buku Tamu table) by date and kind of guest. */
class VisitorBookListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
    }

    public function test_the_public_list_shows_one_kind_and_counts_the_whole_day(): void
    {
        $today = Visitor::whereDate('check_in_time', today());
        $applicants = (clone $today)->where('guest_type', 'pemohon')->count();

        $response = $this->get(route('public.visitor.book', ['jenis' => 'pemohon']))->assertOk()
            ->assertSee('aria-current="page"', false)
            ->assertSee('Pemohon Layanan Offline')
            ->assertViewHas('totalCount', (clone $today)->count())
            ->assertViewHas('typeCounts', ['kunjungan' => (clone $today)->count() - $applicants, 'pemohon' => $applicants]);

        $this->assertSame($applicants, $response->viewData('visitors')->total());
        $this->assertTrue(collect($response->viewData('visitors')->items())->every(fn (Visitor $v) => $v->guest_type === 'pemohon'));
    }

    public function test_a_date_or_kind_it_does_not_understand_lists_today_instead_of_failing(): void
    {
        foreach (['abc', '2026-13-45', '28-10-2026'] as $date) {
            $this->get(route('public.visitor.book', ['date' => $date]))->assertOk()->assertViewHas('date', today()->toDateString());
        }
        $this->get(route('public.visitor.book', ['jenis' => 'vip']))->assertOk()->assertViewHas('type', null);
    }

    public function test_the_pages_of_an_earlier_day_stay_on_that_day(): void
    {
        $day = today()->subDays(3);
        Visitor::factory()->count(25)->create(['check_in_time' => $day->copy()->setTime(9, 0)]);

        $page = $this->get(route('public.visitor.book', ['date' => $day->toDateString()]))->assertOk()->viewData('visitors');
        $this->assertStringContainsString('date=' . $day->toDateString(), $page->nextPageUrl());
    }

    public function test_the_counter_api_and_the_table_filter_by_kind(): void
    {
        $counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();

        // The panel first: Sanctum::actingAs() below switches the default guard.
        $this->actingAs($counter);
        $applicant = Visitor::where('guest_type', 'pemohon')->whereDate('check_in_time', today())->firstOrFail();
        $visit = Visitor::where('guest_type', 'kunjungan')->whereDate('check_in_time', today())->firstOrFail();
        Livewire::test(ListVisitors::class)
            ->filterTable('guest_type', 'pemohon')
            ->assertCanSeeTableRecords([$applicant])
            ->assertCanNotSeeTableRecords([$visit])
            ->assertTableColumnFormattedStateSet('guest_type', 'Pemohon', $applicant);

        Sanctum::actingAs($counter);
        $kinds = collect($this->getJson('/api/buku-tamu?jenis=pemohon&per_halaman=100')->assertOk()->json('data'))->pluck('jenis')->unique()->values()->all();
        $this->assertSame(['pemohon'], $kinds);
        $this->getJson('/api/buku-tamu?jenis=vip')->assertJsonValidationErrors('jenis');
    }
}
