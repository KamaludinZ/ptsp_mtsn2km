<?php

namespace Tests\Feature;

use App\Filament\Pages\Services\IncomingServices;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\IncomingCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

/** Layanan masuk berkategori: disposed requests grouped per category. */
class IncomingServicesTest extends TestCase
{
    use RefreshDatabase;

    private User $headmaster;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();

        $this->headmaster = User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail();
    }

    private function disposed(Ticket $ticket, ?string $instruction): Ticket
    {
        $ticket->service->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        $ticket->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        app(TicketService::class)->dispose($ticket, $this->headmaster, 'acknowledged_by', ['tata_usaha'], $instruction);

        return $ticket->fresh();
    }

    public function test_instruction_decides_the_category(): void
    {
        [$a, $b, $c, $d] = Ticket::query()->limit(4)->get()->all();

        $this->assertSame('tembusan', IncomingCategory::of($this->disposed($a, 'Untuk diketahui')));
        $this->assertSame('koordinasi', IncomingCategory::of($this->disposed($b, 'Untuk dikoordinasikan')));
        $this->assertSame('arahan', IncomingCategory::of($this->disposed($c, 'Mohon saran/pertimbangan')));
        $this->assertSame('disposisi', IncomingCategory::of($this->disposed($d, 'Untuk diproses')));
    }

    public function test_back_office_browses_incoming_requests_by_tab(): void
    {
        [$a, $b, $c] = Ticket::query()->limit(3)->get()->all();
        $tembusan = $this->disposed($a, 'Untuk diketahui');
        $koordinasi = $this->disposed($b, 'Untuk dikoordinasikan');
        $disposisi = $this->disposed($c, null);

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->get(IncomingServices::getUrl())->assertOk()->assertSee('Koordinasi')->assertSee('Arahan');

        Livewire::test(IncomingServices::class)
            ->assertCanSeeTableRecords([$tembusan, $koordinasi, $disposisi])
            ->set('activeTab', 'tembusan')
            ->assertCanSeeTableRecords([$tembusan])
            ->assertCanNotSeeTableRecords([$koordinasi, $disposisi])
            ->set('activeTab', 'disposisi')
            ->assertCanSeeTableRecords([$disposisi])
            ->assertCanNotSeeTableRecords([$tembusan, $koordinasi]);
    }

    public function test_requests_still_waiting_for_disposition_are_not_listed(): void
    {
        $ticket = Ticket::firstOrFail();
        $ticket->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(IncomingServices::class)->assertCanNotSeeTableRecords([$ticket]);
    }

    public function test_front_desk_cannot_open_incoming_requests(): void
    {
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail())
            ->get(IncomingServices::getUrl())->assertForbidden();
    }

    public function test_processor_role_holders_start_with_their_own_units(): void
    {
        [$a, $b] = Ticket::query()->whereNotNull('service_id')->get()->unique('service_id')->take(2)->values()->all();
        $mine = $this->disposed($a, null);
        $other = $this->disposed($b, null);
        $mine->service->update(['disposition_roles' => ['waka_kesiswaan']]);
        $other->service->update(['disposition_roles' => ['waka_sarpras']]);

        $waka = User::where('email', 'waka.kesiswaan@mtsn2malang.sch.id')->firstOrFail();
        $waka->assignRole(\Spatie\Permission\Models\Role::findOrCreate('waka_kesiswaan', 'web'));
        $this->actingAs($waka);

        $forMyUnit = \App\Support\ProcessorRoles::scopeTicketsFor(IncomingServices::incomingQuery(), ['waka_kesiswaan'])->count();
        $this->assertGreaterThanOrEqual(1, $forMyUnit);
        $this->assertSame((string) $forMyUnit, IncomingServices::getNavigationBadge());

        Livewire::test(IncomingServices::class)
            ->assertSee('Unit Anda: Waka Kesiswaan.')
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$other])
            ->removeTableFilter('mine')
            ->assertCanSeeTableRecords([$mine, $other]);
    }

    public function test_other_back_office_staff_see_every_unit(): void
    {
        [$a, $b] = Ticket::query()->whereNotNull('service_id')->get()->unique('service_id')->take(2)->values()->all();
        $mine = $this->disposed($a, null);
        $other = $this->disposed($b, null);
        $mine->service->update(['disposition_roles' => ['waka_kesiswaan']]);

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(IncomingServices::class)
            ->assertDontSee('Unit Anda')
            ->assertCanSeeTableRecords([$mine, $other]);
    }

    public function test_request_list_filters_by_incoming_category_unit_and_overdue(): void
    {
        [$a, $b, $c] = Ticket::query()->limit(3)->get()->all();
        $tembusan = $this->disposed($a, 'Untuk diketahui');
        $koordinasi = $this->disposed($b, 'Untuk dikoordinasikan');
        $c->update(['status' => 'in_process', 'estimated_completion_date' => today()->subDays(2)]);

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());
        $this->get(\App\Filament\Resources\TicketResource::getUrl())->assertOk()->assertSee('Kategori masuk');

        Livewire::test(\App\Filament\Resources\TicketResource\Pages\ListTickets::class)
            ->set('activeTab', 'semua')
            ->filterTable('kategori_masuk', 'tembusan')
            ->assertCanSeeTableRecords([$tembusan])
            ->assertCanNotSeeTableRecords([$koordinasi, $c])
            ->resetTableFilters()
            ->filterTable('unit', 'tata_usaha')
            ->assertCanSeeTableRecords([$tembusan, $koordinasi])
            ->assertCanNotSeeTableRecords([$c])
            ->resetTableFilters()
            ->filterTable('terlambat', true)
            ->assertCanSeeTableRecords([$c])
            ->assertCanNotSeeTableRecords([$tembusan, $koordinasi]);
    }

    public function test_request_list_explains_why_it_is_empty(): void
    {
        Ticket::query()->update(['estimated_completion_date' => null]);
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(\App\Filament\Resources\TicketResource\Pages\ListTickets::class)
            ->set('activeTab', 'terlambat')
            ->assertSee('Tidak ada permohonan terlambat')
            ->assertSee('dalam target waktu')
            ->set('activeTab', 'semua')
            ->set('tableSearch', 'tidak-ada-yang-cocok-xyz')
            ->assertSee('Tidak ada permohonan yang cocok')
            ->assertSee('Hapus pencarian & saringan')
            ->callTableAction('resetFilters')
            ->assertSet('tableSearch', '')
            ->assertDontSee('Tidak ada permohonan yang cocok');

        $empty = \App\Filament\Resources\TicketResource::emptyState((object) ['activeTab' => 'semua', 'tableSearch' => null, 'tableFilters' => ['status' => ['value' => null]]]);
        $this->assertSame('Belum ada permohonan', $empty['heading']);
    }

    public function test_incoming_list_shows_category_and_destination_unit_on_every_tab(): void
    {
        [$a, $b] = Ticket::query()->limit(2)->get()->all();
        $koordinasi = $this->disposed($a, 'Untuk dikoordinasikan');
        $tembusan = $this->disposed($b, 'Untuk diketahui');

        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(IncomingServices::class)
            ->assertTableColumnVisible('category')
            ->assertTableColumnStateSet('category', 'koordinasi', $koordinasi)
            ->assertTableColumnStateSet('category', 'tembusan', $tembusan)
            ->assertTableColumnStateSet('disposition_recipients', \App\Support\ServiceDisposition::recipients(['tata_usaha']), $koordinasi)
            ->assertSee('Perlu dikoordinasikan antarunit sebelum diproses.')
            ->set('activeTab', 'koordinasi')
            ->assertTableColumnVisible('category')
            ->assertCanSeeTableRecords([$koordinasi])
            ->assertCanNotSeeTableRecords([$tembusan]);
    }

    public function test_category_counts_follow_the_active_filters(): void
    {
        [$a, $b] = Ticket::query()->whereNotNull('service_id')->get()->unique('service_id')->take(2)->values()->all();
        $mine = $this->disposed($a, 'Untuk dikoordinasikan');
        $other = $this->disposed($b, 'Untuk dikoordinasikan');
        $mine->service->update(['disposition_roles' => ['waka_kesiswaan']]);
        $other->service->update(['disposition_roles' => ['waka_sarpras']]);

        $waka = User::where('email', 'waka.kesiswaan@mtsn2malang.sch.id')->firstOrFail();
        $waka->assignRole(\Spatie\Permission\Models\Role::findOrCreate('waka_kesiswaan', 'web'));
        $this->actingAs($waka);

        $count = fn ($page, string $tab) => $page->instance()->getTabs()[$tab]['count'];
        $page = Livewire::test(IncomingServices::class);
        $all = IncomingServices::incomingQuery()->count();
        $this->assertSame(1, $count($page, 'koordinasi'));
        $this->assertLessThan($all, $count($page, ''));

        $page->removeTableFilter('mine');
        $this->assertSame(IncomingCategory::scope(IncomingServices::incomingQuery(), 'koordinasi')->count(), $count($page, 'koordinasi'));
        $this->assertSame($all, $count($page, ''));

        $page->filterTable('service_id', $other->service_id);
        $this->assertSame(IncomingCategory::scope(IncomingServices::incomingQuery()->where('service_id', $other->service_id), 'koordinasi')->count(), $count($page, 'koordinasi'));
        $this->assertSame(IncomingCategory::scope(IncomingServices::incomingQuery()->where('service_id', $other->service_id), 'arahan')->count(), $count($page, 'arahan'));
        $page->assertSee('Saring kategori layanan masuk');
    }

    public function test_staff_choose_the_category_on_the_ticket_detail(): void
    {
        $ticket = $this->disposed(Ticket::query()->first(), 'Untuk diproses');
        $this->assertSame('disposisi', IncomingCategory::of($ticket));
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(\App\Filament\Resources\TicketResource\Pages\ViewTicket::class, ['record' => $ticket->id])
            ->assertActionVisible('category')
            ->callAction('category', ['category' => 'tembusan', 'reason' => 'Cukup untuk arsip TU.'])
            ->assertHasNoActionErrors()
            ->assertNotified('Kategori menjadi Tembusan.')
            ->assertSee('Dipilih petugas');

        $ticket->refresh();
        $this->assertSame('tembusan', IncomingCategory::of($ticket));
        $log = $ticket->logs()->latest('id')->first();
        $this->assertSame('category_changed', $log->action);
        $this->assertEquals(['from' => 'disposisi', 'to' => 'tembusan', 'manual' => true, 'reason' => 'Cukup untuk arsip TU.'], $log->metadata);

        Livewire::test(IncomingServices::class)
            ->set('activeTab', 'tembusan')->assertCanSeeTableRecords([$ticket])
            ->set('activeTab', 'disposisi')->assertCanNotSeeTableRecords([$ticket]);

        Livewire::test(\App\Filament\Resources\TicketResource\Pages\ViewTicket::class, ['record' => $ticket->id])
            ->callAction('category', ['category' => 'auto', 'reason' => 'Kembali ke instruksi.'])
            ->assertHasNoActionErrors();
        $this->assertNull($ticket->fresh()->incoming_category);
        $this->assertSame('disposisi', IncomingCategory::of($ticket->fresh()));
    }

    public function test_category_cannot_be_chosen_before_the_disposition(): void
    {
        $ticket = Ticket::query()->first();
        $ticket->service->update(['approval_required' => true]);
        $ticket->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        $this->actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        Livewire::test(\App\Filament\Resources\TicketResource\Pages\ViewTicket::class, ['record' => $ticket->id])
            ->assertActionHidden('category');
    }

    public function test_category_history_shows_the_initial_category_and_each_change(): void
    {
        $ticket = $this->disposed(Ticket::query()->first(), 'Untuk diproses');
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        app(TicketService::class)->setIncomingCategory($ticket, 'koordinasi', $officer, 'Perlu rapat dengan Waka.');
        app(TicketService::class)->setIncomingCategory($ticket->fresh(), null, $officer, 'Sudah dikoordinasikan.');

        $history = IncomingCategory::history($ticket->fresh());
        $this->assertSame(['disposisi', 'koordinasi', 'disposisi'], array_column($history, 'to'));
        $this->assertSame([null, 'disposisi', 'koordinasi'], array_column($history, 'from'));
        $this->assertSame([false, true, false], array_column($history, 'manual'));
        $this->assertSame('Perlu rapat dengan Waka.', $history[1]['reason']);
        $this->assertSame($officer->name, $history[1]['actor']);
        $this->assertSame($this->headmaster->name, $history[0]['actor']);

        $this->actingAs($officer);
        Livewire::test(\App\Filament\Resources\TicketResource\Pages\ViewTicket::class, ['record' => $ticket->id])
            ->assertSee('Riwayat kategori layanan masuk')
            ->assertSeeInOrder(['Kategori awal', 'Perlu rapat dengan Waka.', 'Kembali mengikuti instruksi', 'Saat ini']);
    }

    public function test_disposition_stores_its_category_in_a_column(): void
    {
        $ticket = $this->disposed(Ticket::query()->first(), 'Untuk dikoordinasikan');

        $this->assertSame('koordinasi', $ticket->disposition_category);
        $this->assertNull($ticket->incoming_category);
        $this->assertSame('disposisi', IncomingCategory::forInstruction(null));
        $this->assertSame('tembusan', IncomingCategory::forInstruction(' untuk diarsipkan '));

        // The approval note's wording no longer matters: the column decides.
        \Illuminate\Support\Facades\DB::table('tickets')->where('id', $ticket->id)->update(['approval_notes' => 'Catatan lain.']);
        $this->assertContains($ticket->id, IncomingCategory::scope(Ticket::query(), 'koordinasi')->pluck('id')->all());

        $this->assertContains('tickets_effective_category_index', collect(\Illuminate\Support\Facades\Schema::getIndexes('tickets'))->pluck('name'));
        $this->expectException(\Illuminate\Database\QueryException::class);
        \Illuminate\Support\Facades\DB::table('tickets')->where('id', $ticket->id)->update(['disposition_category' => 'lainnya']);
    }

    public function test_existing_dispositions_are_backfilled_from_the_instruction_text(): void
    {
        $migration = require database_path('migrations/2026_10_06_140000_add_disposition_category_to_tickets.php');
        [$a, $b, $c] = Ticket::query()->limit(3)->get()->all();

        $migration->down();
        \Illuminate\Support\Facades\DB::table('tickets')->where('id', $a->id)->update(['approval_status' => 'approved', 'approval_notes' => 'Diteruskan kepada: Tata Usaha. Instruksi: Mohon saran/pertimbangan.']);
        \Illuminate\Support\Facades\DB::table('tickets')->where('id', $b->id)->update(['approval_status' => 'approved', 'approval_notes' => 'Instruksi: Untuk diproses.']);
        \Illuminate\Support\Facades\DB::table('tickets')->where('id', $c->id)->update(['approval_status' => 'pending', 'approval_notes' => null]);
        $migration->up();

        $this->assertSame(['arahan', 'disposisi', null], [$a->fresh()->disposition_category, $b->fresh()->disposition_category, $c->fresh()->disposition_category]);
    }

    public function test_incoming_api_filters_by_category_and_counts_each_category(): void
    {
        [$a, $b, $c] = Ticket::query()->whereNotNull('service_id')->get()->unique('service_id')->take(3)->values()->all();
        $koordinasi = $this->disposed($a, 'Untuk dikoordinasikan');
        $arahan = $this->disposed($b, 'Mohon saran/pertimbangan');
        $tembusan = $this->disposed($c, 'Untuk diketahui');
        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail());

        $expected = fn (string $category) => IncomingCategory::scope(IncomingServices::incomingQuery(), $category)->count();
        $response = $this->getJson('/api/layanan-masuk')->assertOk()->assertJsonPath('unit_saya', null);
        $counts = collect($response->json('kategori'))->pluck('jumlah', 'kunci');
        foreach (array_keys(IncomingCategory::CATEGORIES) as $category) {
            $this->assertSame($expected($category), $counts[$category], $category);
        }
        $this->assertSame(IncomingServices::incomingQuery()->count(), $response->json('jumlah_semua'));

        $this->getJson('/api/layanan-masuk?kategori=arahan')->assertOk()
            ->assertJsonPath('meta.total', $expected('arahan'))
            ->assertJsonFragment(['nomor_tiket' => $arahan->ticket_number, 'kategori_masuk' => 'arahan'])
            ->assertJsonMissing(['nomor_tiket' => $koordinasi->ticket_number]);

        // A filter narrows the counts too.
        $narrow = collect($this->getJson('/api/layanan-masuk?layanan=' . $tembusan->service_id)->json('kategori'))->pluck('jumlah', 'kunci');
        $this->assertSame(IncomingCategory::scope(IncomingServices::incomingQuery()->where('service_id', $tembusan->service_id), 'tembusan')->count(), $narrow['tembusan']);

        $this->getJson('/api/layanan-masuk?kategori=lainnya')->assertStatus(422);
    }

    public function test_incoming_api_starts_with_the_callers_units_and_is_back_office_only(): void
    {
        [$a, $b] = Ticket::query()->whereNotNull('service_id')->get()->unique('service_id')->take(2)->values()->all();
        $mine = $this->disposed($a, null);
        $other = $this->disposed($b, null);
        $mine->service->update(['disposition_roles' => ['waka_kesiswaan']]);
        $other->service->update(['disposition_roles' => ['waka_sarpras']]);
        $waka = User::where('email', 'waka.kesiswaan@mtsn2malang.sch.id')->firstOrFail();
        $waka->assignRole(\Spatie\Permission\Models\Role::findOrCreate('waka_kesiswaan', 'web'));
        \Laravel\Sanctum\Sanctum::actingAs($waka);

        $this->getJson('/api/layanan-masuk?per_halaman=100')->assertOk()
            ->assertJsonPath('unit_saya', ['waka_kesiswaan'])
            ->assertJsonPath('meta.total', \App\Support\ProcessorRoles::scopeTicketsFor(IncomingServices::incomingQuery(), ['waka_kesiswaan'])->count())
            ->assertJsonFragment(['nomor_tiket' => $mine->ticket_number])
            ->assertJsonMissing(['nomor_tiket' => $other->ticket_number]);
        $this->getJson('/api/layanan-masuk?unit_saya=0')->assertOk()->assertJsonPath('unit_saya', null)
            ->assertJsonFragment(['nomor_tiket' => $other->ticket_number]);

        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->getJson('/api/layanan-masuk')->assertForbidden();
    }

    public function test_category_api_requires_a_category_and_a_reason(): void
    {
        $ticket = $this->disposed(Ticket::query()->first(), 'Untuk diproses');
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        \Laravel\Sanctum\Sanctum::actingAs($officer);
        $url = '/api/tiket/' . $ticket->ticket_number . '/kategori';

        $this->putJson($url, [])->assertStatus(422)
            ->assertJsonValidationErrors(['kategori' => 'Pilih kategori layanan masuk.', 'alasan' => 'Alasan perubahan kategori wajib diisi.']);
        $this->putJson($url, ['kategori' => 'lainnya', 'alasan' => 'abc'])->assertStatus(422)->assertJsonValidationErrors(['kategori', 'alasan']);

        $this->putJson($url, ['kategori' => 'tembusan', 'alasan' => 'Cukup untuk arsip TU.'])->assertOk()
            ->assertJsonPath('message', 'Kategori menjadi Tembusan.')
            ->assertJsonPath('kategori_masuk', 'tembusan')
            ->assertJsonPath('dipilih_petugas', true)
            ->assertJsonPath('kategori_dari_disposisi', 'disposisi');
        $this->putJson($url, ['kategori' => 'tembusan', 'alasan' => 'Sekali lagi.'])->assertStatus(422)->assertJsonPath('message', 'Kategori tidak berubah.');

        $this->putJson($url, ['kategori' => 'otomatis', 'alasan' => 'Ikuti instruksi lagi.'])->assertOk()
            ->assertJsonPath('kategori_masuk', 'disposisi')
            ->assertJsonPath('dipilih_petugas', false);
        $this->assertSame(2, $ticket->logs()->where('action', 'category_changed')->count());
    }

    public function test_category_cannot_change_before_disposition_or_after_closing(): void
    {
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        [$waiting, $closed] = Ticket::query()->limit(2)->get()->all();
        $waiting->service->update(['approval_required' => true]);
        $waiting->update(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending']);
        $closed->update(['status' => 'completed', 'approval_required' => false]);
        \Laravel\Sanctum\Sanctum::actingAs($officer);

        foreach ([$waiting, $closed] as $ticket) {
            $this->putJson('/api/tiket/' . $ticket->ticket_number . '/kategori', ['kategori' => 'arahan', 'alasan' => 'Perlu arahan.'])
                ->assertStatus(422)
                ->assertJsonPath('message', 'Kategori hanya dapat dipilih untuk permohonan yang masih berjalan dan sudah melewati disposisi pimpinan.');
        }

        \Laravel\Sanctum\Sanctum::actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail());
        $this->putJson('/api/tiket/' . $closed->ticket_number . '/kategori', ['kategori' => 'arahan', 'alasan' => 'Perlu arahan.'])->assertForbidden();
    }

    public function test_category_history_is_recorded_immutably_and_readable_through_the_api(): void
    {
        $ticket = $this->disposed(Ticket::query()->first(), 'Untuk dikoordinasikan');
        $officer = User::where('email', 'staff1@mtsn2malang.sch.id')->firstOrFail();
        \Laravel\Sanctum\Sanctum::actingAs($officer);
        $this->putJson('/api/tiket/' . $ticket->ticket_number . '/kategori', ['kategori' => 'arahan', 'alasan' => 'Perlu arahan Kamad.'])->assertOk();

        $this->getJson('/api/tiket/' . $ticket->ticket_number . '/riwayat-kategori')->assertOk()
            ->assertJsonPath('kategori_masuk', 'arahan')
            ->assertJsonPath('kategori_dari_disposisi', 'koordinasi')
            ->assertJsonPath('riwayat.0.ke', 'koordinasi')
            ->assertJsonPath('riwayat.0.oleh', $this->headmaster->name)
            ->assertJsonPath('riwayat.1.dari_label', 'Koordinasi')
            ->assertJsonPath('riwayat.1.ke_label', 'Arahan')
            ->assertJsonPath('riwayat.1.oleh', $officer->name)
            ->assertJsonPath('riwayat.1.alasan', 'Perlu arahan Kamad.')
            ->assertJsonPath('riwayat.1.dipilih_petugas', true);

        // The change is part of the immutable service history.
        $log = $ticket->logs()->where('action', 'category_changed')->firstOrFail();
        $this->expectException(\LogicException::class);
        $log->update(['notes' => 'diubah']);
    }

    public function test_category_history_is_for_staff(): void
    {
        $ticket = Ticket::query()->first();
        \Laravel\Sanctum\Sanctum::actingAs($ticket->user()->first());

        $this->getJson('/api/tiket/' . $ticket->ticket_number . '/riwayat-kategori')->assertForbidden();
    }
}
