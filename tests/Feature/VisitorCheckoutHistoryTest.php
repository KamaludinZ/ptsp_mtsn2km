<?php

namespace Tests\Feature;

use App\Exceptions\TicketActionException;
use App\Filament\Resources\VisitorResource\Pages\ListVisitors;
use App\Filament\Resources\VisitorResource\Pages\ViewVisitor;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorCheckout;
use App\Services\FrontDeskService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

/** Riwayat checkout tamu: how each guest left, whoever checked them out. */
class VisitorCheckoutHistoryTest extends TestCase
{
    use RefreshDatabase;

    private User $counter;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->counter = User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail();
    }

    private function onSite(string $name): Visitor
    {
        return Visitor::where('name', $name)->whereNull('check_out_time')->firstOrFail();
    }

    public function test_every_checkout_is_the_same_on_the_visit_and_told_apart_in_the_history(): void
    {
        $desk = app(FrontDeskService::class);
        $byOfficer = $this->onSite('Rina Kartika');
        $byGuest = $this->onSite('Budi Hartono');

        $desk->checkOut($byOfficer, $this->counter);
        $desk->checkOut($byGuest, null, '203.0.113.7');

        foreach ([$byOfficer, $byGuest] as $visitor) {
            $this->assertSame('checked_out', $visitor->fresh()->status);
            $this->assertNotNull($visitor->fresh()->check_out_time);
        }
        $this->assertSame(['petugas', $this->counter->id, null], [$byOfficer->checkouts->first()->method, $byOfficer->checkouts->first()->checked_out_by, $byOfficer->checkouts->first()->ip_address]);
        $this->assertSame(['mandiri', null, '203.0.113.7'], [$byGuest->checkouts->first()->method, $byGuest->checkouts->first()->checked_out_by, $byGuest->checkouts->first()->ip_address]);
        $this->assertTrue($byGuest->checkouts->first()->checked_out_at->eq($byGuest->fresh()->check_out_time));

        $this->expectException(TicketActionException::class);
        $desk->checkOut($byGuest->fresh(), $this->counter);
    }

    public function test_the_counter_s_table_and_api_record_the_officer(): void
    {
        $this->actingAs($this->counter);
        $table = $this->onSite('Hendra Wijaya');
        Livewire::test(ListVisitors::class)->callTableAction('checkOut', $table)->assertNotified();
        $this->assertSame($this->counter->id, $table->checkouts()->value('checked_out_by'));

        Sanctum::actingAs($this->counter);
        $api = $this->onSite('Rina Kartika');
        $this->postJson("/api/buku-tamu/{$api->id}/keluar")->assertOk();
        $this->getJson("/api/buku-tamu/{$api->id}")->assertOk()
            ->assertJsonPath('checkout.cara', 'petugas')
            ->assertJsonPath('checkout.oleh', $this->counter->name);
    }

    public function test_the_history_cannot_be_changed_but_survives_an_officer_s_removal(): void
    {
        $visitor = $this->onSite('Rina Kartika');
        app(FrontDeskService::class)->checkOut($visitor, $this->counter);
        $entry = $visitor->checkouts()->firstOrFail();

        foreach ([fn () => $entry->update(['method' => 'mandiri']), fn () => $entry->delete()] as $change) {
            try {
                DB::transaction($change);
                $this->fail('The history was changed.');
            } catch (QueryException $e) {
                $this->assertStringContainsString('Riwayat checkout tamu tidak dapat diubah', $e->getMessage());
            }
        }

        // A guest's own checkout names no officer.
        $this->expectException(QueryException::class);
        DB::transaction(fn () => VisitorCheckout::create(['visitor_id' => $visitor->id, 'method' => 'mandiri', 'checked_out_by' => $this->counter->id, 'checked_out_at' => now()]));
    }

    public function test_an_officer_closes_an_earlier_visit_at_the_time_the_guest_left(): void
    {
        $desk = app(FrontDeskService::class);
        $joko = Visitor::where('name', 'Joko Prasetyo')->firstOrFail(); // came yesterday at 10:00
        $leftAt = $joko->check_in_time->copy()->addHours(2)->addSeconds(30);

        foreach ([$joko->check_in_time->copy()->subMinute(), now()->addHour()] as $impossible) {
            try {
                $desk->checkOut($joko, $this->counter, null, $impossible);
                $this->fail('Accepted ' . $impossible);
            } catch (TicketActionException $e) {
                $this->assertStringStartsWith('Waktu pulang harus di antara waktu datang', $e->getMessage());
            }
        }
        $this->assertNull($joko->fresh()->check_out_time);

        $desk->checkOut($joko, $this->counter, null, $leftAt);
        $this->assertSame($leftAt->copy()->startOfMinute()->toDateTimeString(), $joko->fresh()->check_out_time->toDateTimeString());
        $this->assertSame('checked_out', $joko->fresh()->status);
        $this->assertTrue($joko->checkouts->first()->checked_out_at->eq($joko->fresh()->check_out_time));

        // A guest's own checkout is always now: they cannot say when.
        $rina = $this->onSite('Rina Kartika');
        $desk->checkOut($rina, null, '203.0.113.7', now()->subHour());
        $this->assertTrue($rina->fresh()->check_out_time->diffInSeconds(now()) < 5);
    }

    public function test_the_counter_s_action_asks_when_only_for_an_earlier_day(): void
    {
        $this->actingAs($this->counter);
        $today = $this->onSite('Hendra Wijaya');
        $joko = Visitor::where('name', 'Joko Prasetyo')->firstOrFail();

        Livewire::test(ListVisitors::class)->callTableAction('checkOut', $today)->assertHasNoTableActionErrors();
        $this->assertNotNull($today->fresh()->check_out_time);

        Livewire::test(ListVisitors::class)
            ->set('activeTab', 'semua')
            ->callTableAction('checkOut', $joko, data: ['left_at' => null])
            ->assertHasTableActionErrors(['left_at' => 'required']);
        Livewire::test(ListVisitors::class)
            ->set('activeTab', 'semua')
            ->callTableAction('checkOut', $joko, data: ['left_at' => $joko->check_in_time->copy()->addHour()->format('Y-m-d H:i')])
            ->assertHasNoTableActionErrors();
        $this->assertSame($joko->check_in_time->copy()->addHour()->format('Y-m-d H:i'), $joko->fresh()->check_out_time->format('Y-m-d H:i'));

        Sanctum::actingAs($this->counter);
        $rina = $this->onSite('Rina Kartika');
        $this->travel(30)->minutes();
        $this->postJson("/api/buku-tamu/{$rina->id}/keluar", ['waktu' => now()->addDay()->toIso8601String()])->assertStatus(422);
        $this->postJson("/api/buku-tamu/{$rina->id}/keluar", ['waktu' => now()->subMinutes(5)->toIso8601String()])->assertOk();
    }

    public function test_the_visit_page_says_how_the_guest_left(): void
    {
        $visitor = $this->onSite('Rina Kartika');
        app(FrontDeskService::class)->checkOut($visitor, null, '203.0.113.7');

        $this->actingAs(User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail());
        Livewire::test(ViewVisitor::class, ['record' => $visitor->getRouteKey()])
            ->assertSee('Cara check-out')
            ->assertSee('Mandiri dari buku tamu');
    }

    public function test_the_migration_records_earlier_checkouts_as_the_counter_s(): void
    {
        $gone = Visitor::whereNotNull('check_out_time')->count();

        $migration = require database_path('migrations/2026_10_28_220000_create_visitor_checkouts_table.php');
        $migration->down();
        $migration->up();

        $this->assertSame($gone, VisitorCheckout::where('method', 'petugas')->whereNull('checked_out_by')->count());
    }
}
