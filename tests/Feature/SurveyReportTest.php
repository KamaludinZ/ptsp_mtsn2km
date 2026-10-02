<?php

namespace Tests\Feature;

use App\Exports\SurveyResponsesExport;
use App\Filament\Pages\Reports\SurveyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/** Laporan SKM & SPAK: period and edition filters, print and Excel download. */
class SurveyReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
        Mail::fake();
        $this->seed();
        $this->actingAs(User::where('email', 'pengawas@mtsn2malang.sch.id')->firstOrFail());
    }

    public function test_report_follows_the_chosen_period(): void
    {
        $this->get(SurveyReport::getUrl())->assertOk()->assertSee('bulan ' . now()->translatedFormat('F Y'));

        Livewire::test(SurveyReport::class)
            ->set('period', 'year')
            ->assertSee(now()->startOfYear()->translatedFormat('j M Y'))
            ->set('period', 'bogus')
            ->assertSet('period', 'month');

        foreach (array_keys(SurveyReport::PERIODS) as $period) {
            Livewire::test(SurveyReport::class, ['period' => $period, 'type' => 'spak'])->assertOk();
        }
    }

    public function test_report_downloads_as_excel_for_the_period(): void
    {
        Excel::fake();

        Livewire::test(SurveyReport::class)->set('period', 'year')->callAction('excel');

        Excel::assertDownloaded(
            'laporan-survei-' . now()->startOfYear()->format('Ymd') . '-' . now()->endOfYear()->format('Ymd') . '.xlsx',
            fn (SurveyResponsesExport $export) => count($export->sheets()) === 4,
        );
    }
}
