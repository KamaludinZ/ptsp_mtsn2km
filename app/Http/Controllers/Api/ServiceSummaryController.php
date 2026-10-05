<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\MonthlyReport;
use App\Support\ServiceMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Service figures as JSON for staff tools (e.g. a counter display),
 * computed exactly like the dashboard widgets.
 */
class ServiceSummaryController extends Controller
{
    /** GET /api/layanan/ringkasan-hari-ini */
    public function today(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $today = ServiceMetrics::today();

        return response()->json([
            'tanggal' => today()->toDateString(),
            'diperbarui' => now()->toIso8601String(),
            'masuk' => [
                'total' => $today['in'],
                'online' => $today['in_online'],
                'loket' => $today['in_offline'],
            ],
            'selesai' => $today['completed'],
            'ditolak' => $today['rejected'],
            'masih_diproses' => $today['open'],
            'melewati_target' => $today['overdue'],
        ]);
    }

    /** GET /api/layanan/tren?rentang=7d|30d|12m */
    public function trend(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $range = $request->validate(['rentang' => ['nullable', 'in:' . implode(',', array_keys(ServiceMetrics::TREND_RANGES))]])['rentang'] ?? '30d';

        return response()->json([
            'rentang' => $range,
            'keterangan' => ServiceMetrics::TREND_RANGES[$range],
            'data' => ServiceMetrics::trend($range),
        ]);
    }

    /**
     * GET /api/layanan/laporan-bulanan?bulan=YYYY-MM (default: this month, up
     * to today): the Laporan Bulanan summary, compared with the month before.
     */
    public function month(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $month = $request->validate(['bulan' => ['nullable', 'date_format:Y-m', 'before_or_equal:' . now()->format('Y-m')]])['bulan'] ?? null;
        $r = MonthlyReport::build(MonthlyReport::month($month));
        $t = $r['tickets'];
        $change = fn (?array $c) => $c ? ['nilai' => $c['value'], 'label' => $c['label'], 'arah' => $c['trend'], 'baik' => $c['good']] : null;

        return response()->json([
            'bulan' => $r['month'],
            'label' => $r['label'],
            'bulan_berjalan' => $r['is_current'],
            'periode' => ['dari' => $r['from']->toDateString(), 'sampai' => $r['to']->toDateString()],
            'permohonan' => $t['total'],
            'online' => $t['online'],
            'loket' => $t['offline'],
            'selesai' => $t['completed'],
            'persen_selesai' => $t['completion_rate'],
            'persen_tepat_waktu' => $t['on_time_rate'],
            'rata_rata_hari' => $t['avg_days'],
            'masih_terbuka' => $t['open'],
            'melewati_target' => $t['overdue'],
            'per_status' => $t['by_status'],
            'dibanding_bulan_lalu' => [
                'bulan' => $r['previous_label'],
                'permohonan' => $change($r['changes']['total']),
                'selesai' => $change($r['changes']['completed']),
                'persen_tepat_waktu' => $change($r['changes']['on_time_rate']),
                'rata_rata_hari' => $change($r['changes']['avg_days']),
            ],
            'harian' => collect($r['daily'])->map(fn (array $day) => ['tanggal' => $day['date'], 'jumlah' => $day['total']]),
            'ikm' => $r['survey']['ikm'],
            'mutu_ikm' => $r['survey']['ikm_grade'],
            'responden_survei' => $r['survey']['respondents'],
            'pengaduan' => [
                'masuk' => $r['complaints']['dumas']['total'],
                'selesai' => $r['complaints']['dumas']['resolved'],
                'whistleblowing' => $r['complaints']['whistleblowing']['total'],
            ],
            'kunjungan_buku_tamu' => $r['visitors'],
            'bulan_terakhir_berisi_data' => $r['nearest_with_data']?->format('Y-m'),
            'unduhan' => [
                'cetak' => route('reports.monthly.print', ['bulan' => $r['month']]),
                'pdf' => route('api.layanan.laporan-bulanan.ekspor', ['format' => 'pdf', 'bulan' => $r['month']]),
                'xlsx' => route('api.layanan.laporan-bulanan.ekspor', ['format' => 'xlsx', 'bulan' => $r['month']]),
            ],
        ]);
    }

    /** GET /api/layanan/laporan-bulanan/rekap-layanan?bulan=YYYY-MM: rekap per jenis layanan with a total row. */
    public function serviceRecap(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $month = $request->validate(['bulan' => ['nullable', 'date_format:Y-m', 'before_or_equal:' . now()->format('Y-m')]])['bulan'] ?? null;
        $from = MonthlyReport::month($month);
        $recap = MonthlyReport::services($from->copy()->startOfMonth(), $from->copy()->endOfMonth());

        return response()->json([
            'bulan' => $from->format('Y-m'),
            'label' => MonthlyReport::label($from),
            'data' => collect($recap['rows'])->map(fn (array $row) => self::recapRow($row)),
            'total' => self::recapRow($recap['total']),
        ]);
    }

    /** GET /api/layanan/rekap-tahunan?tahun=YYYY: the same recap per month of one year. */
    public function yearRecap(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $year = (int) ($request->validate(['tahun' => ['nullable', 'integer', 'min:2000', 'max:' . now()->year]])['tahun'] ?? now()->year);
        $recap = MonthlyReport::year($year);

        return response()->json([
            'tahun' => $year,
            'data' => collect($recap['months'])->map(fn (array $row) => ['bulan' => sprintf('%d-%02d', $year, $row['month']), 'belum_berjalan' => $row['future']] + self::recapRow($row)),
            'total' => self::recapRow($recap['total']),
        ]);
    }

    /** One recap row with Indonesian keys. */
    private static function recapRow(array $row): array
    {
        return [
            'nama' => $row['name'],
            'jumlah' => $row['total'],
            'online' => $row['online'],
            'loket' => $row['offline'],
            'selesai' => $row['completed'],
            'ditolak_batal' => $row['rejected'],
            'dalam_proses' => $row['open'],
            'persen_tepat_waktu' => $row['on_time_rate'],
            'rata_rata_hari' => $row['avg_days'],
        ];
    }

    /**
     * GET /api/layanan/kondisi?lingkup=semua|saya: where every request sits in
     * the workflow right now, for the whole office or the caller's own work.
     */
    public function condition(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $scope = $request->validate(['lingkup' => ['nullable', 'in:semua,saya']])['lingkup'] ?? 'semua';
        $filter = $scope === 'saya' ? ServiceMetrics::scopeFor($request->user()) : null;
        $tickets = ServiceMetrics::tickets(scope: $filter);

        return response()->json([
            'lingkup' => $filter ? 'saya' : 'semua',
            'diperbarui' => now()->toIso8601String(),
            'total' => $tickets['total'],
            'menunggu_verifikasi' => $tickets['by_status']['submitted'],
            'sedang_diproses' => $tickets['by_status']['verified'] + $tickets['by_status']['in_process'],
            'menunggu_disposisi' => $tickets['awaiting_approval'],
            'siap_diambil' => $tickets['ready_for_pickup'],
            'selesai' => $tickets['completed'],
            'melewati_target' => $tickets['overdue'],
            'persen_selesai' => $tickets['completion_rate'],
            'persen_tepat_waktu' => $tickets['on_time_rate'],
            'per_status' => $tickets['by_status'],
        ]);
    }
}
