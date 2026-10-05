<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    /** GET /api/layanan/laporan-bulanan?bulan=YYYY-MM (default: this month, up to today) */
    public function month(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isStaff(), 403);

        $month = $request->validate(['bulan' => ['nullable', 'date_format:Y-m', 'before_or_equal:' . now()->format('Y-m')]])['bulan'] ?? null;
        $from = $month ? Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay() : now()->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $tickets = ServiceMetrics::tickets($from, $to);
        $survey = ServiceMetrics::survey($from, $to);

        return response()->json([
            'bulan' => $from->format('Y-m'),
            'label' => $from->translatedFormat('F Y'),
            'permohonan' => $tickets['total'],
            'selesai' => $tickets['completed'],
            'persen_selesai' => $tickets['completion_rate'],
            'persen_tepat_waktu' => $tickets['on_time_rate'],
            'rata_rata_hari' => $tickets['avg_days'],
            'per_status' => $tickets['by_status'],
            'ikm' => $survey['ikm'],
            'mutu_ikm' => $survey['ikm_grade'],
            'responden_survei' => $survey['respondents'],
        ]);
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
