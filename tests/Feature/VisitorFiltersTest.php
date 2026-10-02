<?php

namespace Tests\Feature;

use App\Filament\Resources\VisitorResource;
use App\Filament\Resources\VisitorResource\Pages\ListVisitors;
use App\Filament\Resources\VisitorResource\Widgets\VisitHistory;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Buku tamu in the staff panel: search and filters. */
class VisitorFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_can_be_searched_and_filtered(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $here = Visitor::create(['name' => 'Tamu Masih Di Sini', 'phone' => '081234500001', 'purpose' => 'Rapat', 'institution_category' => 'Dinas', 'check_in_time' => now()]);
        $gone = Visitor::create(['name' => 'Tamu Sudah Pulang', 'phone' => '081234500002', 'purpose' => 'Antar berkas', 'institution_category' => 'Orang tua', 'check_in_time' => now()->subHour(), 'check_out_time' => now()]);

        Livewire::test(ListVisitors::class)->searchTable('081234500001')
            ->assertCanSeeTableRecords([$here])->assertCanNotSeeTableRecords([$gone]);
        Livewire::test(ListVisitors::class)->filterTable('on_site', true)
            ->assertCanSeeTableRecords([$here])->assertCanNotSeeTableRecords([$gone]);
        Livewire::test(ListVisitors::class)->filterTable('institution_category', 'Orang tua')
            ->assertCanSeeTableRecords([$gone])->assertCanNotSeeTableRecords([$here]);
    }

    public function test_front_desk_checks_out_and_notes_a_visit(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $visitor = Visitor::create(['name' => 'Tamu Catatan', 'phone' => '081234500003', 'purpose' => 'Konsultasi', 'check_in_time' => now()]);

        Livewire::test(ListVisitors::class)
            ->callTableAction('note', $visitor, ['notes' => 'Menunggu Waka Kesiswaan.'])
            ->callTableAction('checkOut', $visitor);

        $visitor->refresh();
        $this->assertSame('Menunggu Waka Kesiswaan.', $visitor->notes);
        $this->assertNotNull($visitor->check_out_time);
    }

    public function test_visitor_page_lists_earlier_visits_by_phone(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $earlier = Visitor::create(['name' => 'Bu Sari', 'phone' => '081299900001', 'purpose' => 'Ambil rapor', 'check_in_time' => now()->subMonth()]);
        $other = Visitor::create(['name' => 'Pak Lain', 'phone' => '081299900002', 'purpose' => 'Rapat', 'check_in_time' => now()->subWeek()]);
        $today = Visitor::create(['name' => 'Bu Sari', 'phone' => '081299900001', 'purpose' => 'Konsultasi', 'check_in_time' => now()]);

        Livewire::test(VisitHistory::class, ['record' => $today])
            ->assertCanSeeTableRecords([$earlier])
            ->assertCanNotSeeTableRecords([$today, $other]);

        $this->get(VisitorResource::getUrl('view', ['record' => $today]))->assertOk();
    }
}
