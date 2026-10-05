<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyReportExport;
use App\Filament\Pages\Reports\MonthlyReport as MonthlyReportPage;
use App\Support\MonthlyReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laporan bulanan as a file: PDF (with the madrasah letterhead, signature
 * and page numbers) or Excel. Served to the panel (session) and the API
 * (Sanctum) alike.
 */
class MonthlyReportExportController extends Controller
{
    public function __invoke(Request $request, string $format): Response
    {
        abort_unless($request->user() && MonthlyReportPage::canAccess(), 403);

        $month = $request->validate(['bulan' => ['nullable', 'date_format:Y-m', 'before_or_equal:' . now()->format('Y-m')]])['bulan'] ?? null;
        $report = MonthlyReport::build(MonthlyReport::month($month));

        activity('audit')->causedBy($request->user())->log('Mengunduh laporan bulanan ' . $report['label'] . ' (' . strtoupper($format) . ')');

        if ($format === 'xlsx') {
            return Excel::download(new MonthlyReportExport($report), MonthlyReportExport::filename($report, 'xlsx'));
        }

        $pdf = Pdf::loadView('print.monthly-report', ['report' => $report, 'pdf' => true])->setPaper('a4');
        $pdf->render();
        $printed = now()->format('d/m/Y H:i');
        $pdf->getDomPDF()->getCanvas()->page_script(function ($page, $pages, $canvas, $fontMetrics) use ($printed) {
            $font = $fontMetrics->getFont('DejaVu Sans');
            $canvas->text(42, 818, "Laporan Bulanan PTSP · dicetak {$printed}", $font, 7, [0.4, 0.4, 0.4]);
            $canvas->text(500, 818, "Halaman {$page} dari {$pages}", $font, 7, [0.4, 0.4, 0.4]);
        });

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . MonthlyReportExport::filename($report, 'pdf') . '"',
        ]);
    }
}
