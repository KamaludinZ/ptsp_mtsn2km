<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\PersuratanMaster;
use App\Models\Service;
use App\Models\SuratKeputusan;
use App\Support\Persuratan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Validasi jenis SK: the jenis an SK may be saved with are the active ones
 * in Master Persuratan (tab Jenis SK), plus the jenis an existing SK
 * already has, so an SK keeps a jenis that was switched off or renamed
 * later. The panel picker, the API and SuratKeputusanService all check
 * this one list.
 */
class JenisSkService
{
    public const MESSAGE = 'Pilih jenis SK dari daftar Master Persuratan.';

    /**
     * @param  ?string  $kept  The jenis the SK being corrected already has.
     * @return array<int, string> In Master Persuratan order, the kept jenis last.
     */
    public static function allowed(?string $kept = null): array
    {
        return collect(Persuratan::options('jenis_sk'))->push($kept)->filter()->unique()->values()->all();
    }

    /**
     * The jenis as Master Persuratan spells it, whatever its case or spacing
     * (names are unique that way); null when it may not be used.
     */
    public static function resolve(?string $jenis, ?string $kept = null): ?string
    {
        $key = fn (?string $name) => mb_strtolower(Str::squish((string) $name));

        return $key($jenis) === '' ? null : collect(self::allowed($kept))->first(fn (string $name) => $key($name) === $key($jenis));
    }

    /**
     * Pemakaian: how many SK of the register carry each jenis, in every year
     * and in $year (default this year); one query for the whole list.
     *
     * @param  iterable<string>  $names
     * @return array<string, array{semua: int, tahun: int}>
     */
    public static function usage(iterable $names, ?int $year = null): array
    {
        $names = collect($names)->filter()->unique()->values();
        if ($names->isEmpty()) {
            return [];
        }

        // An SK counts for the master entry it was saved under, whatever that entry is called now;
        // SK without one (a jenis no longer in the master) count by the name they carry.
        $ids = PersuratanMaster::query()->where('type', 'jenis_sk')->whereIn('nama', $names)->pluck('id', 'nama');
        $rows = SuratKeputusan::query()
            ->where(fn ($q) => $q->whereIn('jenis_sk_id', $ids->values())->orWhere(fn ($n) => $n->whereNull('jenis_sk_id')->whereIn('jenis_sk', $names)))
            ->groupBy('jenis_sk_id', 'jenis_sk')
            ->selectRaw('jenis_sk_id, jenis_sk, count(*) as semua, count(*) filter (where tahun = ?) as tahun', [$year ?? now()->year])
            ->get();

        return $names->mapWithKeys(function (string $name) use ($ids, $rows) {
            $mine = $rows->filter(fn ($row) => isset($ids[$name]) ? (int) $row->jenis_sk_id === (int) $ids[$name] : ($row->jenis_sk_id === null && $row->jenis_sk === $name));

            return [$name => ['semua' => (int) $mine->sum('semua'), 'tahun' => (int) $mine->sum('tahun')]];
        })->all();
    }

    /**
     * Statistik of one jenis SK, per year (newest first): SK issued and
     * archived under this master entry (whatever it is called now; SK
     * without one count by the name), and the latest of them.
     *
     * @return array{per_tahun: list<array{tahun: int, terbit: int, terarsip: int}>, terbit: int, terarsip: int, sk_terakhir: ?string}
     */
    public static function statistics(PersuratanMaster $jenis): array
    {
        $register = SuratKeputusan::query()->where(fn ($q) => $q->where('jenis_sk_id', $jenis->getKey())
            ->orWhere(fn ($n) => $n->whereNull('jenis_sk_id')->where('jenis_sk', $jenis->nama)));

        $years = (clone $register)->groupBy('tahun')->orderByDesc('tahun')
            ->selectRaw('tahun, count(*) as terbit, count(berkas_scan) as terarsip')->get()
            ->map(fn ($row) => ['tahun' => (int) $row->tahun, 'terbit' => (int) $row->terbit, 'terarsip' => (int) $row->terarsip]);

        return [
            'per_tahun' => $years->all(),
            'terbit' => (int) $years->sum('terbit'),
            'terarsip' => (int) $years->sum('terarsip'),
            'sk_terakhir' => (clone $register)->orderByDesc('tahun')->orderByDesc('nomor_urut')->value('nomor_sk'),
        ];
    }

    /**
     * Layanan PTSP of a jenis SK: these services (ids) take it as their jenis
     * SK bawaan, the others it had lose it. Logged on the jenis (log "jenis_sk").
     *
     * @param  array<int, int|string>  $serviceIds
     */
    public static function assignServices(PersuratanMaster $jenis, array $serviceIds): void
    {
        $ids = collect($serviceIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        $before = $jenis->layanan()->orderBy('name')->pluck('name');

        DB::transaction(function () use ($jenis, $ids) {
            Service::query()->where('jenis_sk_id', $jenis->getKey())->whereNotIn('id', $ids)->update(['jenis_sk_id' => null]);
            Service::query()->whereIn('id', $ids)->update(['jenis_sk_id' => $jenis->getKey()]);
        });

        $after = $jenis->layanan()->orderBy('name')->pluck('name');
        if ($before->all() !== $after->all()) {
            activity('jenis_sk')->causedBy(auth()->user())->performedOn($jenis)->event('updated')
                ->withProperties(['layanan' => $after->all(), 'layanan_sebelumnya' => $before->all()])
                ->log('Mengatur layanan PTSP jenis SK ' . $jenis->nama);
        }
    }

    /** The jenis an SK of this service starts with: its jenis SK bawaan, while it may be chosen. */
    public static function defaultFor(?Service $service): ?string
    {
        $jenis = $service?->jenisSk;

        return $jenis && $jenis->type === 'jenis_sk' && $jenis->is_active ? self::resolve($jenis->nama) : null;
    }

    /** The jenis to save, or TicketActionException when it is not in the list. */
    public static function assert(?string $jenis, ?string $kept = null): string
    {
        return self::resolve($jenis, $kept) ?? throw new TicketActionException(self::MESSAGE);
    }
}
