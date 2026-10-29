<?php

namespace App\Services;

use App\Exports\SkRegisterRecapExport;
use App\Exports\SuratKeputusanExport;
use App\Models\SuratKeputusan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Unduh Laporan SK: the register as a file, for the panel's "Cetak / Unduh"
 * and API clients alike. Every download is logged in Log & Audit (log
 * surat_keputusan, event exported) with what it held.
 */
class SkReportService
{
    /** Formats offered, as the log and the messages name them. */
    public const FORMATS = ['excel' => 'Excel', 'pdf' => 'PDF'];

    /**
     * SK one PDF holds at most: DomPDF needs about 220 MB for 500 rows and the
     * server allows PHP 256 MB (docker/php.ini). Bigger reports go to Excel.
     */
    public const PDF_MAX_ROWS = 400;

    /** Downloads one account may make a minute (register and recap, panel and API): PDF rendering is heavy. */
    public const DOWNLOADS_PER_MINUTE = 6;

    /** Seconds to wait before this account may download again, or null when it may now (and the download is counted). */
    public static function downloadWait(User $user): ?int
    {
        $key = 'penomoran-sk:unduh:' . $user->getKey();
        if (RateLimiter::tooManyAttempts($key, self::DOWNLOADS_PER_MINUTE)) {
            return max(1, RateLimiter::availableIn($key));
        }
        RateLimiter::hit($key, 60);

        return null;
    }

    public static function downloadWaitMessage(int $seconds): string
    {
        return 'Batas ' . self::DOWNLOADS_PER_MINUTE . ' unduhan laporan per menit tercapai. Coba lagi dalam ' . $seconds . ' detik.';
    }

    /** "Register tahun 2026", or "Register semua tahun" without a year (or with "semua"). */
    public static function period(int|string|null $year): string
    {
        return self::hasYear($year) ? 'Register tahun ' . $year : 'Register semua tahun';
    }

    /** register-penomoran-sk-2026-20261027-093000, or …-semua-tahun-…, without extension. */
    public static function filename(int|string|null $year): string
    {
        return 'register-penomoran-sk-' . (self::hasYear($year) ? $year : 'semua-tahun') . '-' . now()->format('Ymd-His');
    }

    /** Why there is nothing to download. */
    public static function emptyMessage(int|string|null $year): string
    {
        return self::period($year) . ' kosong dengan pencarian dan saringan saat ini.';
    }

    /** Why these $count SK cannot be printed as one PDF, or null when they can. */
    public static function pdfRefusal(int $count): ?string
    {
        return $count > self::PDF_MAX_ROWS
            ? 'Laporan PDF memuat paling banyak ' . self::PDF_MAX_ROWS . ' SK, saringan ini berisi ' . $count . ' SK. Persempit saringan (mis. per bulan atau per jenis SK), atau unduh Excel.'
            : null;
    }

    /**
     * The register leaves the panel: who took which part of it.
     *
     * @param  array<string, mixed>  $criteria  saringan, pencarian, sumber (panel/api)
     */
    public static function log(User $user, string $format, int $count, int|string|null $year, array $criteria = []): void
    {
        activity('surat_keputusan')->causedBy($user)->event('exported')
            ->withProperties(['format' => $format, 'jumlah' => $count, ...$criteria])
            ->log('Mengunduh laporan ' . Str::replaceFirst('Register', 'register SK', self::period($year)) . ' (' . $format . ', ' . $count . ' SK)');
    }

    /** The register in the order given, one row per SK (SuratKeputusanExport). */
    public static function excel(Builder $query, string $filename): BinaryFileResponse
    {
        return Excel::download(new SuratKeputusanExport($query), $filename . '.xlsx');
    }

    /**
     * The register for print: A4 landscape with the madrasah letterhead, in
     * number order (a heading per year when it spans several), the criteria
     * it was taken with, and page numbers. Callers check pdfRefusal() first.
     *
     * @param  array<string, string>  $criteria  label => value (criteria())
     */
    public static function pdf(Builder $query, string $filename, array $criteria = []): StreamedResponse
    {
        $sk = $query->with('pembuat:id,name')->reorder('tahun')->orderBy('nomor_urut')->get();
        $pdf = Pdf::loadView('pdf.surat-keputusan-register', [
            'sk' => $sk,
            'years' => $sk->pluck('tahun')->unique()->sort()->join(', '),
            'criteria' => $criteria,
        ])->setPaper('a4', 'landscape');

        $pdf->render();
        $printed = now()->format('d/m/Y H:i');
        $pdf->getDomPDF()->getCanvas()->page_script(function ($page, $pages, $canvas, $fontMetrics) use ($printed) {
            $font = $fontMetrics->getFont('DejaVu Sans');
            $canvas->text(42, 572, "Register Penomoran SK · dicetak {$printed}", $font, 7, [0.4, 0.4, 0.4]);
            $canvas->text(740, 572, "Halaman {$page} dari {$pages}", $font, 7, [0.4, 0.4, 0.4]);
        });
        $output = $pdf->output();

        return response()->streamDownload(fn () => print $output, $filename . '.pdf', ['Content-Type' => 'application/pdf']);
    }

    /**
     * The criteria of a report in words, for the printed register: the
     * list's filters (tahun aside, it is in the title), API style.
     *
     * @param  array<string, mixed>  $filters  q, jenis_sk, status, petugas, dari, sampai, nomor_urut
     * @return array<string, string>
     */
    public static function criteria(array $filters): array
    {
        $date = fn (?string $d) => $d ? Carbon::parse($d)->translatedFormat('j M Y') : null;

        return array_filter([
            'Jenis SK' => implode(', ', (array) ($filters['jenis_sk'] ?? [])),
            'Status' => SuratKeputusan::STATUS[$filters['status'] ?? ''] ?? '',
            'Petugas' => filled($filters['petugas'] ?? null) ? (string) User::withTrashed()->find($filters['petugas'])?->name : '',
            'Tanggal surat' => match (true) {
                filled($filters['dari'] ?? null) && filled($filters['sampai'] ?? null) => $date($filters['dari']) . ' – ' . $date($filters['sampai']),
                filled($filters['dari'] ?? null) => 'sejak ' . $date($filters['dari']),
                filled($filters['sampai'] ?? null) => 'sampai ' . $date($filters['sampai']),
                default => '',
            },
            'Nomor urut' => (string) ($filters['nomor_urut'] ?? ''),
            'Pencarian' => filled($filters['q'] ?? null) ? '"' . $filters['q'] . '"' : '',
        ], 'filled');
    }

    /** Rekap Register SK leaves the panel: logged like the register's downloads. */
    public static function logRecap(User $user, string $format, array $recap, string $source = 'panel'): void
    {
        activity('surat_keputusan')->causedBy($user)->event('exported')
            ->withProperties(['format' => $format, 'tahun' => $recap['tahun'], 'jumlah' => $recap['terbit'], 'laporan' => 'rekap', 'sumber' => $source])
            ->log('Mengunduh rekap register SK tahun ' . $recap['tahun'] . ' (' . $format . ')');
    }

    /** Rekap Register SK as a workbook (four sheets) or a printable A4 page. */
    public static function recapFile(array $recap, string $format): BinaryFileResponse|StreamedResponse
    {
        if ($format === 'pdf') {
            $output = Pdf::loadView('pdf.sk-register-recap', ['recap' => $recap])->setPaper('a4')->output();

            return response()->streamDownload(fn () => print $output, SkRegisterRecapExport::filename($recap, 'pdf'), ['Content-Type' => 'application/pdf']);
        }

        return Excel::download(new SkRegisterRecapExport($recap), SkRegisterRecapExport::filename($recap, 'xlsx'));
    }

    private static function hasYear(int|string|null $year): bool
    {
        return filled($year) && $year !== 'semua';
    }
}
