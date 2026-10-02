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

        $this->assertSame('1', IncomingServices::getNavigationBadge());

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
}
