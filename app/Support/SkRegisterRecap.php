<?php

namespace App\Support;

use App\Models\PersuratanMaster;
use App\Models\SuratKeluar;
use App\Models\SuratKeputusan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Rekap Register SK for one year (Laporan & Evaluasi Kinerja): how many
 * numbers were given, how complete the scan archive is and how long a
 * signed SK took to reach it, the SK deleted, letters of the buku surat
 * keluar that are really SK, and the year per month, per jenis SK and per
 * petugas pencatat. Months follow tanggal surat.
 */
class SkRegisterRecap
{
    /** Days after the number was taken from which a missing scan is overdue. */
    public const SCAN_DUE_DAYS = 14;

    /** SK listed under "Menunggu scan paling lama". */
    public const OLDEST_WAITING = 5;

    /**
     * @return array{tahun: int, terbit: int, terarsip: int, menunggu_scan: int, persen_arsip: ?int, nomor_terakhir: ?string, dihapus: int, rata_hari_arsip: ?float, surat_keluar_sk: int,
     *     menunggu_terlama: list<array{id: int, nomor_sk: string, perihal: string, hari: int}>, scan_terlambat: int,
     *     per_bulan: list<array{bulan: int, nama: string, terbit: int, terarsip: int}>,
     *     per_jenis: list<array{jenis: string, terbit: int, terarsip: int, aktif: bool}>,
     *     per_petugas: list<array{petugas: string, peran: ?string, terbit: int, terarsip: int}>}
     */
    public static function build(int $year): array
    {
        $register = SuratKeputusan::query()->where('tahun', $year);
        $archived = fn ($query) => $query->whereNotNull('berkas_scan');

        $terbit = (clone $register)->count();
        $terarsip = $archived(clone $register)->count();

        // Days from the number to the signed scan (hari_sampai_terarsip), for the SK archived so far.
        $days = $archived(clone $register)->toBase()->avg('hari_sampai_terarsip');

        $count = fn (string $group) => (clone $register)
            ->select($group, DB::raw('count(*) as terbit'), DB::raw('count(berkas_scan) as terarsip'))
            ->groupBy($group)
            ->get();

        $months = (clone $register)
            ->select('bulan', DB::raw('count(*) as terbit'), DB::raw('count(berkas_scan) as terarsip'))
            ->groupBy('bulan')
            ->get()->keyBy('bulan');

        $active = Persuratan::options('jenis_sk');
        // By Master Jenis SK entry under its current name (a renamed jenis keeps its older SK); without one, by the SK's own name.
        $masters = PersuratanMaster::query()->where('type', 'jenis_sk')->pluck('nama', 'id');
        $used = (clone $register)
            ->select('jenis_sk_id', 'jenis_sk', DB::raw('count(*) as terbit'), DB::raw('count(berkas_scan) as terarsip'))
            ->groupBy('jenis_sk_id', 'jenis_sk')->get()
            ->groupBy(fn ($row) => $masters[$row->jenis_sk_id] ?? $row->jenis_sk)
            ->map(fn ($rows) => (object) ['terbit' => $rows->sum('terbit'), 'terarsip' => $rows->sum('terarsip')]);
        $officers = $count('pembuat_id');
        $names = User::withTrashed()->whereIn('id', $officers->pluck('pembuat_id')->filter())->pluck('name', 'id');
        $roles = (clone $register)->whereNotNull('pembuat_id')->select('pembuat_id', 'peran_pembuat')->distinct()->get()->groupBy('pembuat_id');

        return [
            'tahun' => $year,
            'terbit' => $terbit,
            'terarsip' => $terarsip,
            'menunggu_scan' => $terbit - $terarsip,
            'persen_arsip' => $terbit ? (int) round($terarsip / $terbit * 100) : null,
            'nomor_terakhir' => (clone $register)->orderByDesc('nomor_urut')->value('nomor_sk'),
            'dihapus' => Activity::query()->inLog('surat_keputusan')->where('event', 'deleted')->where('properties->old->tahun', (string) $year)->count(),
            'rata_hari_arsip' => $days !== null ? round((float) $days, 1) : null,
            // Arsip scan SK still to complete: the SK waiting longest since their number was taken.
            'menunggu_terlama' => (clone $register)->whereNull('berkas_scan')->orderBy('created_at')->orderBy('nomor_urut')
                ->limit(self::OLDEST_WAITING)->get(['id', 'nomor_sk', 'perihal', 'created_at'])
                ->map(fn (SuratKeputusan $sk) => [
                    'id' => $sk->id,
                    'nomor_sk' => $sk->nomor_sk,
                    'perihal' => $sk->perihal,
                    'hari' => (int) floor($sk->created_at->diffInDays(now())),
                ])->all(),
            'scan_terlambat' => (clone $register)->whereNull('berkas_scan')->where('created_at', '<=', now()->subDays(self::SCAN_DUE_DAYS))->count(),
            // SK that took an agenda number in the buku surat keluar instead.
            'surat_keluar_sk' => SuratKeluar::query()->where('tahun', $year)->numberedAsSk()->count(),
            'per_bulan' => collect(range(1, 12))->map(fn (int $month) => [
                'bulan' => $month,
                'nama' => MonthlyReport::MONTH_NAMES[$month],
                'terbit' => (int) ($months[$month]->terbit ?? 0),
                'terarsip' => (int) ($months[$month]->terarsip ?? 0),
            ])->all(),
            // For whoever keeps Master Jenis SK: every active jenis, the unused ones
            // included (Master Persuratan order), then switched-off jenis this year still used.
            'per_jenis' => collect($active)->merge($used->keys()->diff($active)->sort())
                ->map(fn (string $jenis) => [
                    'jenis' => $jenis,
                    'terbit' => (int) ($used[$jenis]->terbit ?? 0),
                    'terarsip' => (int) ($used[$jenis]->terarsip ?? 0),
                    'aktif' => in_array($jenis, $active, true),
                ])
                ->values()->all(),
            'per_petugas' => $officers
                ->map(fn ($row) => [
                    'petugas' => $row->pembuat_id ? ($names[$row->pembuat_id] ?? 'Akun sudah dihapus') : 'Tanpa petugas',
                    'peran' => ($role = $roles[$row->pembuat_id] ?? null) && $role->count() === 1 && $role->first()->peran_pembuat
                        ? RoleAccess::roleLabel($role->first()->peran_pembuat)
                        : null,
                    'terbit' => (int) $row->terbit,
                    'terarsip' => (int) $row->terarsip,
                ])
                ->sortByDesc('terbit')->values()->all(),
        ];
    }
}
