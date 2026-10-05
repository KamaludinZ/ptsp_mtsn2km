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
use Laravel\Sanctum\Sanctum;
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

        // The list opens on "Hari ini": keep both visits on the same day whatever the clock says.
        $this->travelTo(today()->setTime(12, 0));

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

    public function test_counter_registers_and_checks_out_guests_through_the_api(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $host = User::where('is_active', true)->whereIn('user_type', ['guru', 'pegawai'])->firstOrFail();
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $visit = $this->postJson('/api/buku-tamu', ['name' => 'Tamu Dinas', 'purpose' => 'Monitoring', 'person_to_meet_id' => $host->id])
            ->assertCreated()->assertJsonPath('bertemu', $host->name)->json();
        $this->postJson('/api/buku-tamu/' . $visit['id'] . '/keluar')->assertOk()->assertJsonPath('id', $visit['id']);
        $this->assertNotNull(Visitor::find($visit['id'])->check_out_time);

        $this->postJson('/api/buku-tamu', ['name' => 'X', 'purpose' => 'Y', 'person_to_meet_id' => 999999])->assertJsonValidationErrors('person_to_meet_id');

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->postJson('/api/buku-tamu', ['name' => 'X', 'purpose' => 'Y', 'person_to_meet_id' => $host->id])->assertForbidden();
    }

    public function test_counter_lists_visitors_with_filters_through_the_api(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $this->travelTo(today()->setTime(12, 0));
        Visitor::create(['name' => 'Tamu API Satu', 'phone' => '0899001', 'purpose' => 'Rapat', 'institution_category' => 'Dinas', 'check_in_time' => now()]);
        Visitor::create(['name' => 'Tamu API Dua', 'phone' => '0899002', 'purpose' => 'Antar', 'check_in_time' => now()->subHour(), 'check_out_time' => now()]);
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $names = fn (string $query) => collect($this->getJson('/api/buku-tamu?' . $query)->assertOk()->json('data'))->pluck('nama');

        $this->assertSame(['Tamu API Satu'], $names('q=0899001')->all());
        $this->assertTrue($names('tanggal=' . today()->toDateString() . '&status=di_lokasi')->contains('Tamu API Satu'));
        $this->assertFalse($names('tanggal=' . today()->toDateString() . '&status=di_lokasi')->contains('Tamu API Dua'));
        $this->assertSame(['Tamu API Satu'], $names('kategori=Dinas&q=Tamu API')->all());
    }

    public function test_counter_notes_a_visit_through_the_api(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $visitor = Visitor::create(['name' => 'Tamu Catat', 'phone' => '0811', 'purpose' => 'Rapat', 'check_in_time' => now()]);

        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->patchJson('/api/buku-tamu/' . $visitor->id . '/catatan', ['notes' => 'Menunggu di ruang tamu'])->assertOk()->assertJsonPath('catatan', 'Menunggu di ruang tamu');

        Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->patchJson('/api/buku-tamu/' . $visitor->id . '/catatan', ['notes' => 'x'])->assertForbidden();
    }

    public function test_visit_detail_includes_card_and_earlier_visits(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $earlier = Visitor::create(['name' => 'Bu Rina', 'phone' => '0877001', 'purpose' => 'Ambil rapor', 'check_in_time' => now()->subMonth()]);
        $today = Visitor::create(['name' => 'Bu Rina', 'phone' => '0877001', 'purpose' => 'Konsultasi', 'check_in_time' => now()]);
        Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());

        $this->getJson('/api/buku-tamu/' . $today->id)
            ->assertOk()
            ->assertJsonPath('kartu_tamu', route('visitors.print', $today))
            ->assertJsonCount(1, 'riwayat_kunjungan')
            ->assertJsonPath('riwayat_kunjungan.0.id', $earlier->id);
    }

    public function test_guest_book_entries_are_never_deleted(): void
    {
        Http::fake();
        Mail::fake();
        $this->seed();
        $visitor = Visitor::create(['name' => 'Tamu Tetap', 'phone' => '0811', 'purpose' => 'Rapat', 'check_in_time' => now()]);
        $admin = User::where('email', 'ptsp@mtsn2malang.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $this->assertTrue($admin->can('update', $visitor));
        $this->assertFalse($admin->can('delete', $visitor));
        $this->assertFalse($admin->can('deleteAny', Visitor::class));

        Livewire::test(ListVisitors::class, ['activeTab' => 'semua'])
            ->assertTableActionHidden('delete', $visitor)
            ->assertTableBulkActionHidden('delete');
    }
}
