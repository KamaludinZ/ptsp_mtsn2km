<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\SuratKeluar;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Visitor;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Pengaturan reaches the documents: kop surat, date format and ticket numbers. */
class SettingsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->travelTo(Carbon::parse('2026-10-05 09:30'));

        foreach ([
            'letterhead_line_1' => 'Kementerian Agama RI',
            'letterhead_line_2' => 'Kankemenag Kota Malang',
            'app_name_full' => 'MTsN 2 Kota Malang',
            'contact_phone' => '0341-551752',
            'document_date_format' => 'd/m/Y',
            'ticket_prefix' => 'MTS',
        ] as $key => $value) {
            AppSetting::set($key, $value);
        }
    }

    public function test_printed_documents_use_the_letterhead_and_date_format(): void
    {
        // A ticket waiting for the headmaster's disposition (as in DispositionActionTest)
        $ticket = Ticket::whereNotIn('id', \App\Models\DispositionLog::select('ticket_id'))->orderBy('id')->firstOrFail();
        $ticket->service->update(['approval_required' => true, 'approval_roles' => null, 'approval_users' => null]);
        $ticket->forceFill(['status' => 'verified', 'approval_required' => true, 'approval_status' => 'pending', 'created_at' => '2026-09-01 08:00'])->save();
        $headmaster = User::where('email', 'kepsek@mtsn2malang.sch.id')->firstOrFail();

        foreach ([route('tickets.disposition-sheet', $ticket), route('tickets.receipt', $ticket)] as $url) {
            $this->actingAs($headmaster)->get($url)->assertOk()
                ->assertSeeInOrder(['Kementerian Agama RI', 'Kankemenag Kota Malang', 'MTsN 2 Kota Malang', 'Telp. 0341-551752'])
                ->assertSee('01/09/2026');
        }

        $visitor = Visitor::create(['name' => 'Bu Rina', 'phone' => '0877001', 'purpose' => 'Konsultasi', 'check_in_time' => now()]);
        $this->actingAs(User::where('email', 'loket1@mtsn2malang.sch.id')->firstOrFail())
            ->get(route('visitors.print', $visitor))->assertOk()->assertSee('05/10/2026 09:30');
    }

    public function test_pdf_registers_and_reports_carry_the_letterhead(): void
    {
        $html = view('pdf.surat-keluar-register', ['letters' => SuratKeluar::query()->limit(0)->get(), 'years' => '2026'])->render();
        $this->assertStringContainsString('Kankemenag Kota Malang', $html);
        $this->assertStringContainsString('Dicetak 05/10/2026 09:30', $html);

        $report = view('print.monthly-report', ['report' => \App\Support\MonthlyReport::build(\App\Support\MonthlyReport::month('2026-09')), 'pdf' => true])->render();
        $this->assertStringContainsString('Kementerian Agama RI', $report);
        $this->assertStringContainsString('05/10/2026', $report);
    }

    public function test_new_ticket_numbers_use_the_prefix(): void
    {
        $this->assertMatchesRegularExpression('/^MTS-202610-\d{4}$/', TicketService::nextTicketNumber());

        AppSetting::set('ticket_prefix', null);
        $this->assertMatchesRegularExpression('/^PTSP-202610-\d{4}$/', TicketService::nextTicketNumber());
    }
}
