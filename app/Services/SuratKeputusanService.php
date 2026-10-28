<?php

namespace App\Services;

use App\Exceptions\SkNumberLimitException;
use App\Exceptions\SkVersionConflictException;
use App\Exceptions\TicketActionException;
use App\Models\SuratKeputusan;
use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\Persuratan;
use App\Support\RoleAccess;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Penomoran SK: "{N} TAHUN {YYYY}", 1-9999 per year, its own sequence apart
 * from surat keluar. The year is locked while a number is handed out, so
 * two officers never receive the same one.
 */
class SuratKeputusanService
{
    public const MAX_NUMBER = 9999;

    /** SK numbers one account may take: numbers are never given back, so bulk taking would use up the year. */
    public const MAX_PER_MINUTE = 10;

    public const MAX_PER_DAY = 150;

    /** pg_trgm similarity from which two perihal read as the same SK. */
    public const SIMILAR_PERIHAL = 0.5;

    /**
     * Years that have SK, newest first; this year is always offered.
     *
     * @return array<int, int>
     */
    public static function years(): array
    {
        return SuratKeputusan::query()->distinct()->pluck('tahun')
            ->push(now()->year)
            ->map(fn ($year) => (int) $year)
            ->unique()->sortDesc()->values()->all();
    }

    /**
     * Jenis SK in Master Persuratan order, then any jenis only found on
     * recorded SK (renamed or switched off since), so old SK stay findable.
     *
     * @return array<int, string>
     */
    public static function jenisOptions(): array
    {
        $recorded = SuratKeputusan::query()->distinct()->orderBy('jenis_sk')->pluck('jenis_sk');

        return collect(Persuratan::options('jenis_sk'))->merge($recorded)->unique()->values()->all();
    }

    /**
     * The next number of $year without taking it (null once 9999 is used).
     * Numbers are never reused: the next one follows the highest ever
     * handed out (surat_keputusan_sequences, so a deleted last SK keeps its
     * number) or recorded, whichever is higher.
     */
    public static function nextNumber(int $year): ?int
    {
        $next = max(
            (int) DB::table('surat_keputusan_sequences')->where('tahun', $year)->value('last_number'),
            (int) SuratKeputusan::where('tahun', $year)->max('nomor_urut'),
        ) + 1;

        return $next <= self::MAX_NUMBER ? $next : null;
    }

    /**
     * Status penomoran of a year, for Laporan & Evaluasi: the last number
     * given, what is left of 1–9999, and every number missing from the
     * register (celah): deleted through "Hapus" (who, when, why) or, without
     * that trail, flagged for a look.
     *
     * @return array{tahun: int, nomor_terakhir: ?string, nomor_berikutnya: ?string, terbit: int, sisa: int, celah: list<array{nomor_urut: int, nomor_sk: string, dihapus: bool, pada: ?string, oleh: ?string, alasan: ?string}>}
     */
    public static function numberingStatus(int $year): array
    {
        $given = SuratKeputusan::where('tahun', $year)->pluck('nomor_urut')->map(fn ($n) => (int) $n)->all();
        $last = max((int) DB::table('surat_keputusan_sequences')->where('tahun', $year)->value('last_number'), $given ? max($given) : 0);
        $next = self::nextNumber($year);
        $deleted = Activity::query()->inLog('surat_keputusan')->where('event', 'deleted')
            ->where('properties->old->tahun', (string) $year)->with('causer')->get()
            ->keyBy(fn (Activity $activity) => (int) data_get($activity->properties, 'old.nomor_urut'));

        return [
            'tahun' => $year,
            'nomor_terakhir' => $last ? SuratKeputusan::composeNumber($last, $year) : null,
            'nomor_berikutnya' => $next ? SuratKeputusan::composeNumber($next, $year) : null,
            'terbit' => count($given),
            'sisa' => self::MAX_NUMBER - $last,
            'celah' => collect($last ? array_diff(range(1, $last), $given) : [])->values()->map(function (int $number) use ($deleted, $year) {
                $log = $deleted[$number] ?? null;

                return [
                    'nomor_urut' => $number,
                    'nomor_sk' => SuratKeputusan::composeNumber($number, $year),
                    'dihapus' => $log !== null,
                    'pada' => $log?->created_at?->toIso8601String(),
                    'oleh' => $log ? ($log->causer?->name ?? 'Akun sudah dihapus') : null,
                    'alasan' => $log ? data_get($log->properties, 'alasan') : null,
                ];
            })->all(),
        ];
    }

    /**
     * The register of one year at a glance.
     *
     * @return array{tahun: int, terbit: int, terakhir: ?string, berikutnya: ?string, menunggu_scan: int}
     */
    public static function yearSummary(int $year): array
    {
        $last = SuratKeputusan::where('tahun', $year)->orderByDesc('nomor_urut')->value('nomor_sk');
        $next = self::nextNumber($year);

        return [
            'tahun' => $year,
            'terbit' => SuratKeputusan::where('tahun', $year)->count(),
            'terakhir' => $last,
            'berikutnya' => $next ? SuratKeputusan::composeNumber($next, $year) : null,
            'menunggu_scan' => SuratKeputusan::where('tahun', $year)->withStatus('menunggu_scan')->count(),
        ];
    }

    /**
     * How a jenis SK has been used in $year, shown under the jenis picker.
     *
     * @return array{jumlah: int, terakhir: ?string}
     */
    public static function jenisUsage(string $jenis, int $year): array
    {
        $rows = SuratKeputusan::where('tahun', $year)->where('jenis_sk', $jenis);

        return [
            'jumlah' => (clone $rows)->count(),
            'terakhir' => (clone $rows)->orderByDesc('nomor_urut')->value('nomor_sk'),
        ];
    }

    /**
     * SK of $year whose perihal reads much like $perihal (pg_trgm similarity),
     * most alike first: a possible double request before a number is taken.
     *
     * @return Collection<int, SuratKeputusan>
     */
    public static function similarPerihal(?string $perihal, int $year, ?int $except = null, int $limit = 3): Collection
    {
        $perihal = preg_replace('/^tentang\s+/iu', '', Str::squish((string) $perihal));
        if (mb_strlen($perihal) < 8) {
            return new Collection;
        }

        return SuratKeputusan::query()
            ->where('tahun', $year)
            ->when($except, fn ($query) => $query->whereKeyNot($except))
            ->whereRaw('similarity(perihal, ?) >= ?', [$perihal, self::SIMILAR_PERIHAL])
            ->orderByRaw('similarity(perihal, ?) desc', [$perihal])
            ->limit($limit)
            ->get(['id', 'nomor_sk', 'perihal']);
    }

    /**
     * Take the next SK number for the year of tanggal_surat.
     *
     * @param  array<string, mixed>  $data  tanggal_surat, perihal, jenis_sk, nama_peminta, keterangan, ticket_id (checked by the caller), kunci_permintaan
     */
    public function reserve(CarbonInterface $tanggal, User $pembuat, array $data): SuratKeputusan
    {
        $year = (int) $tanggal->format('Y');
        $data['jenis_sk'] = JenisSkService::assert($data['jenis_sk'] ?? null);
        self::refuseBulkTaking($pembuat);

        $sk = DB::transaction(function () use ($tanggal, $pembuat, $data, $year) {
            // One officer at a time per year: held until the transaction ends.
            DB::select('select pg_advisory_xact_lock(hashtext(?))', ['surat_keputusan:' . $year]);

            return $this->issue($tanggal, $pembuat, $data, $year);
        });

        self::countTaken($pembuat, 1);

        return $sk;
    }

    /**
     * Registrasi kumpulan: several SK of one year at once (e.g. decided in one
     * meeting), numbered one after another with no other number between them,
     * all or none. At most MAX_PER_MINUTE; each counts against the limits.
     *
     * @param  list<array<string, mixed>>  $items  each: tanggal_surat, perihal, jenis_sk, nama_peminta, keterangan, ticket_id
     * @return list<SuratKeputusan>
     */
    public function reserveMany(User $pembuat, array $items): array
    {
        if ($items === [] || count($items) > self::MAX_PER_MINUTE) {
            throw new TicketActionException('Satu kumpulan berisi 1–' . self::MAX_PER_MINUTE . ' SK.');
        }
        $dates = array_map(fn (array $item) => Carbon::parse($item['tanggal_surat']), $items);
        $years = array_unique(array_map(fn (CarbonInterface $date) => (int) $date->format('Y'), $dates));
        if (count($years) > 1) {
            throw new TicketActionException('Semua SK dalam satu kumpulan harus bertanggal tahun yang sama: nomornya satu urutan tahunan.');
        }
        $year = reset($years);
        foreach ($items as $i => $item) {
            $items[$i]['jenis_sk'] = JenisSkService::assert($item['jenis_sk'] ?? null);
        }
        self::refuseBulkTaking($pembuat, count($items));

        $taken = DB::transaction(function () use ($pembuat, $items, $dates, $year) {
            DB::select('select pg_advisory_xact_lock(hashtext(?))', ['surat_keputusan:' . $year]);

            return array_map(fn (array $item, CarbonInterface $date) => $this->issue($date, $pembuat, $item, $year), $items, $dates);
        });

        self::countTaken($pembuat, count($taken));

        return $taken;
    }

    /** Take the next number of $year for one SK (the year lock is held by the caller). */
    private function issue(CarbonInterface $tanggal, User $pembuat, array $data, int $year): SuratKeputusan
    {
        $next = self::nextNumber($year);

        if ($next === null) {
            throw new TicketActionException('Nomor SK tahun ' . $year . ' sudah mencapai ' . self::MAX_NUMBER . '.');
        }

        $sk = SuratKeputusan::create([
            ...array_intersect_key($data, array_flip(['perihal', 'jenis_sk', 'nama_peminta', 'keterangan', 'berkas_scan', 'berkas_scan_nama', 'ticket_id', 'kunci_permintaan'])),
            'tahun' => $year,
            'nomor_urut' => $next,
            'nomor_sk' => SuratKeputusan::composeNumber($next, $year),
            'tanggal_surat' => $tanggal->toDateString(),
            'pembuat_id' => $pembuat->id,
            'peran_pembuat' => self::officer($pembuat)['peran'],
        ]);
        DB::table('surat_keputusan_sequences')->upsert([['tahun' => $year, 'last_number' => $next]], ['tahun'], ['last_number']);

        activity('surat_keputusan')->causedBy($pembuat)->performedOn($sk)->event('created')
            ->withProperties(['attributes' => self::snapshot($sk)])
            ->log('Meminta nomor SK ' . $sk->nomor_sk);

        return $sk;
    }

    /** The numbers just taken count against the account's limits. */
    private static function countTaken(User $user, int $count): void
    {
        foreach (self::limits($user) as [$key, , $seconds]) {
            RateLimiter::increment($key, $seconds, $count);
        }
    }

    /** [rate limiter key, most numbers, window in seconds, window in words] per window. */
    private static function limits(User $user): array
    {
        return [
            ['penomoran-sk:menit:' . $user->getKey(), self::MAX_PER_MINUTE, 60, 'satu menit'],
            ['penomoran-sk:hari:' . $user->getKey(), self::MAX_PER_DAY, 86400, 'satu hari'],
        ];
    }

    /** One account may not take numbers faster than a person asks for them ($count more now); the refusal is logged. */
    private static function refuseBulkTaking(User $user, int $count = 1): void
    {
        foreach (self::limits($user) as [$key, $max, , $window]) {
            if (RateLimiter::attempts($key) + $count > $max) {
                $retry = max(1, RateLimiter::availableIn($key));
                activity('surat_keputusan')->causedBy($user)->event('limited')
                    ->withProperties(['batas' => $max, 'jangka' => $window, 'coba_lagi_detik' => $retry])
                    ->log("Permintaan nomor SK ditahan: lebih dari {$max} nomor dalam {$window}");

                $wait = 'Coba lagi dalam ' . max(1, (int) ceil($retry / 60)) . ' menit; nomor SK tidak pernah dipakai ulang, jadi permintaan beruntun ditahan.';

                throw new SkNumberLimitException($count > 1
                    ? "Kumpulan {$count} SK melebihi batas {$max} nomor SK dalam {$window} (sudah diminta " . RateLimiter::attempts($key) . "). {$wait}"
                    : "Akun ini sudah meminta {$max} nomor SK dalam {$window}. {$wait}", $retry);
            }
        }
    }

    /**
     * Optimistic lock: a correction says which versi of the SK it was made on
     * (the form or API client got it with the SK); null skips the check.
     *
     * @throws SkVersionConflictException
     */
    public static function assertVersion(SuratKeputusan $sk, mixed $versi): void
    {
        if (filled($versi) && (int) $versi !== (int) $sk->versi) {
            throw new SkVersionConflictException($sk);
        }
    }

    /** Why a date outside this year deserves a second look: its number belongs to that year's register. Null for this year. */
    public static function otherYearNote(int $year): ?string
    {
        return match (true) {
            $year < now()->year => "Tahun {$year} sudah lewat: nomor ini menyambung register SK tahun {$year}. Pastikan tanggal surat memang tahun {$year}.",
            $year > now()->year => "Tahun {$year} belum berjalan: nomor ini membuka register SK tahun {$year}. Pastikan tanggal surat memang tahun {$year}.",
            default => null,
        };
    }

    /**
     * What a request for an SK number starts with (Registrasi Nomor SK, panel
     * and API): from a ticket the applicant, the ticket number and the
     * service's jenis SK; the number the date would get now (taken for real
     * only on submit); and the SK of that year whose perihal reads alike.
     *
     * @return array{isian: array{tanggal_surat: string, jenis_sk: ?string, nama_peminta: ?string, keterangan: ?string, tiket: ?int}, nomor_berikutnya: ?array{nomor_urut: int, nomor_sk: string, tahun: int}, catatan_tahun: ?string, perihal_mirip: list<array{id: int, nomor_sk: string, perihal: string}>}
     */
    public static function registrationDraft(?\App\Models\Ticket $ticket = null, ?string $tanggal = null, ?string $perihal = null): array
    {
        $date = rescue(fn () => Carbon::parse($tanggal ?: now()), now(), report: false);
        $year = (int) $date->format('Y');
        $next = self::nextNumber($year);

        return [
            'isian' => [
                'tanggal_surat' => $date->toDateString(),
                'jenis_sk' => $ticket ? JenisSkService::defaultFor($ticket->service) : null,
                'nama_peminta' => $ticket?->user?->name,
                'keterangan' => $ticket ? 'Untuk permohonan ' . $ticket->ticket_number . ($ticket->service ? ' (' . $ticket->service->name . ')' : '') . '.' : null,
                'tiket' => $ticket?->getKey(),
            ],
            'nomor_berikutnya' => $next === null ? null : ['nomor_urut' => $next, 'nomor_sk' => SuratKeputusan::composeNumber($next, $year), 'tahun' => $year],
            'catatan_tahun' => self::otherYearNote($year),
            'perihal_mirip' => self::similarPerihal($perihal, $year)->map(fn (SuratKeputusan $sk) => $sk->only(['id', 'nomor_sk', 'perihal']))->values()->all(),
        ];
    }

    /** What the archive check found per scan. */
    public const ARCHIVE_PROBLEMS = [
        'hilang' => 'Berkas scan tidak ada di arsip',
        'berubah' => 'Isi berkas berbeda dari saat diunggah',
        'tanpa_sidik' => 'Belum ada sidik berkas untuk dibandingkan',
    ];

    /**
     * Periksa arsip scan SK (Laporan & Evaluasi): every archived scan of a
     * year (or all) is still on the private disk and is the file that was
     * uploaded (SHA-256 taken then).
     *
     * @return array{diperiksa: int, utuh: int, masalah: list<array{id: int, nomor_sk: string, masalah: string, keterangan: string}>}
     */
    public static function verifyArchive(?int $year = null): array
    {
        $disk = Storage::disk('local');
        $checked = 0;
        $problems = [];

        SuratKeputusan::query()->whereNotNull('berkas_scan')->when($year, fn ($q) => $q->where('tahun', $year))
            ->orderBy('tahun')->orderBy('nomor_urut')
            ->select(['id', 'nomor_sk', 'berkas_scan', 'berkas_scan_sha256'])
            ->chunk(200, function ($scans) use ($disk, &$checked, &$problems) {
                foreach ($scans as $sk) {
                    $checked++;
                    $problem = match (true) {
                        ! $disk->exists($sk->berkas_scan) => 'hilang',
                        $sk->berkas_scan_sha256 === null => 'tanpa_sidik',
                        ! hash_equals($sk->berkas_scan_sha256, hash_file('sha256', $disk->path($sk->berkas_scan))) => 'berubah',
                        default => null,
                    };
                    if ($problem) {
                        $problems[] = ['id' => $sk->id, 'nomor_sk' => $sk->nomor_sk, 'masalah' => $problem, 'keterangan' => self::ARCHIVE_PROBLEMS[$problem]];
                    }
                }
            });

        return ['diperiksa' => $checked, 'utuh' => $checked - count($problems), 'masalah' => $problems];
    }

    /** The SK this officer was already given for this kunci permintaan (Idempotency-Key), if any. */
    public static function givenFor(User $pembuat, ?string $kunci): ?SuratKeputusan
    {
        return filled($kunci) ? SuratKeputusan::where('pembuat_id', $pembuat->getKey())->where('kunci_permintaan', $kunci)->first() : null;
    }

    /** Correct the data of a recorded SK; its number (and so its year) stays. $data['versi'], when given, must be current. */
    public function describe(SuratKeputusan $sk, array $data): SuratKeputusan
    {
        self::assertVersion($sk, $data['versi'] ?? null);
        if (isset($data['tanggal_surat']) && (int) Carbon::parse($data['tanggal_surat'])->format('Y') !== $sk->tahun) {
            throw new TicketActionException('Tanggal surat harus dalam tahun ' . $sk->tahun . ', tahun nomor SK ini.');
        }
        if (array_key_exists('jenis_sk', $data)) {
            $data['jenis_sk'] = JenisSkService::assert($data['jenis_sk'], $sk->jenis_sk);
        }

        $sk->update(array_intersect_key($data, array_flip(['tanggal_surat', 'perihal', 'jenis_sk', 'nama_peminta', 'keterangan', 'berkas_scan', 'berkas_scan_nama'])));

        // Reloaded: the database computes bulan and hari_sampai_terarsip.
        return $sk->refresh();
    }

    /**
     * Petugas pencatat: the signed-in officer and the active role they act
     * in. Filled in by the server when a number is taken, never from what
     * the form or API client sends.
     *
     * @return array{id: int, nama: string, peran: ?string, peran_label: ?string}
     */
    public static function officer(User $user): array
    {
        $role = ActiveRoles::actingRoleOf($user);

        return ['id' => $user->id, 'nama' => $user->name, 'peran' => $role, 'peran_label' => $role ? RoleAccess::roleLabel($role) : null];
    }

    /** "Nama · Peran" for the forms and the register; the role is left out when unknown. */
    public static function officerLabel(?string $name, ?string $role): string
    {
        return collect([$name ?? 'Akun sudah dihapus', $role ? RoleAccess::roleLabel($role) : null])->filter()->implode(' · ');
    }

    /**
     * The last correction of an SK in Log & Audit: who made it (none for a
     * change without a signed-in account), in which active role, and when.
     *
     * @return array{sistem: bool, nama: ?string, peran: ?string, pada: CarbonInterface, ganti_akun_oleh: ?string}|null
     */
    public static function lastChange(SuratKeputusan $sk): ?array
    {
        $activity = Activity::query()->inLog('surat_keputusan')->forSubject($sk)->where('event', 'updated')
            ->latest('id')->with('causer')->first();

        return $activity ? [
            'sistem' => ! $activity->causer_id,
            'nama' => $activity->causer?->name,
            'peran' => $activity->properties['peran_aktif'] ?? null,
            'pada' => $activity->created_at,
            'ganti_akun_oleh' => self::impersonator($activity),
        ] : null;
    }

    /**
     * Ganti akun sementara: the administrator who was using the officer's
     * account when the number was taken, as Log & Audit recorded it; null
     * when the officer took it themselves.
     */
    public static function takenByImpersonator(SuratKeputusan $sk): ?string
    {
        $activity = Activity::query()->inLog('surat_keputusan')->forSubject($sk)->where('event', 'created')->latest('id')->first();

        return $activity ? self::impersonator($activity) : null;
    }

    private static function impersonator(Activity $activity): ?string
    {
        $by = $activity->properties['impersonated_by'] ?? null;

        return is_array($by) ? ($by['admin'] ?? 'Administrator') : null;
    }

    /** Riwayat SK: what each entry of its trail is called. */
    public const HISTORY_EVENTS = ['created' => 'Nomor diambil', 'updated' => 'Data diubah', 'deleted' => 'SK dihapus'];

    /**
     * Riwayat perubahan of one SK, oldest first, from Log & Audit: when, by
     * whom (in which active role, through ganti akun sementara or not), and
     * for a correction every field before → after.
     *
     * @return list<array{pada: CarbonInterface, peristiwa: string, label: string, oleh: string, peran: ?string, ganti_akun_oleh: ?string, perubahan: list<array{kolom: string, label: string, lama: mixed, baru: mixed}>, alasan: ?string}>
     */
    public static function history(SuratKeputusan $sk): array
    {
        return Activity::query()->inLog('surat_keputusan')->forSubject($sk)
            ->whereIn('event', array_keys(self::HISTORY_EVENTS))
            ->orderBy('id')->with('causer')->get()
            ->map(function (Activity $activity) {
                $p = $activity->properties;
                $changes = $activity->event === 'updated'
                    ? collect($p['attributes'] ?? [])->only(array_keys(SuratKeputusan::FIELD_LABELS))
                        ->map(fn ($new, string $field) => [
                            'kolom' => $field,
                            'label' => SuratKeputusan::FIELD_LABELS[$field],
                            'lama' => self::historyValue($field, data_get($p, "old.{$field}")),
                            'baru' => self::historyValue($field, $new),
                        ])->values()->all()
                    : [];

                return [
                    'pada' => $activity->created_at,
                    'peristiwa' => $activity->event,
                    'label' => self::HISTORY_EVENTS[$activity->event],
                    'oleh' => $activity->causer?->name ?? ($activity->causer_id ? 'Akun sudah dihapus' : 'Sistem'),
                    'peran' => ($role = $p['peran_aktif'] ?? null) ? RoleAccess::roleLabel($role) : null,
                    'ganti_akun_oleh' => self::impersonator($activity),
                    'perubahan' => $changes,
                    'alasan' => $p['alasan'] ?? null,
                ];
            })->all();
    }

    /** A logged value as the history shows it: tanggal surat as a date. */
    private static function historyValue(string $field, mixed $value): mixed
    {
        return $field === 'tanggal_surat' && filled($value)
            ? rescue(fn () => Carbon::parse($value)->toDateString(), $value, report: false)
            : $value;
    }

    /** "Nama · Peran" of the last correction ("Sistem" without an account); null when it was never corrected. */
    public static function lastEditor(SuratKeputusan $sk): ?string
    {
        $change = self::lastChange($sk);

        return match (true) {
            $change === null => null,
            $change['sistem'] => 'Sistem',
            default => self::officerLabel($change['nama'], $change['peran']),
        };
    }

    /**
     * Unggah Scan SK: keep the file on the private disk under a random name
     * (its own name only as berkas_scan_nama).
     *
     * @return array{berkas_scan: string, berkas_scan_nama: ?string}
     */
    public static function storeScan(UploadedFile $file): array
    {
        return [
            'berkas_scan' => $file->store(SuratKeputusan::SCAN_DIRECTORY, 'local'),
            'berkas_scan_nama' => SuratKeputusan::cleanScanName($file->getClientOriginalName()),
        ];
    }

    /**
     * Unduh berkas arsip: the scan served as the type it was checked as, never
     * sniffed or cached, under its own name. Shown in the browser only when
     * that type is known; otherwise (and with $download) always downloaded.
     */
    public static function scanResponse(SuratKeputusan $sk, bool $download = false): StreamedResponse
    {
        abort_unless($sk->hasScan(), 404, 'SK ini belum memiliki scan.');
        $disk = Storage::disk('local');
        abort_unless($disk->exists($sk->berkas_scan), 404, 'Berkas scan SK tidak ditemukan di arsip; unggah ulang lewat "Ubah".');

        $type = $sk->berkas_scan_mime;
        $headers = [
            'Content-Type' => $type ?? 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        return $download || ! $type
            ? $disk->download($sk->berkas_scan, $sk->scanName(), $headers)
            : $disk->response($sk->berkas_scan, $sk->scanName(), $headers);
    }

    /** Attach the signed scan to an SK, replacing an earlier one (removed from the disk by the model). */
    public function attachScan(SuratKeputusan $sk, UploadedFile $file): SuratKeputusan
    {
        $scan = self::storeScan($file);

        try {
            $sk->update($scan);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($scan['berkas_scan']);

            throw $e;
        }

        // Reloaded: the database computes hari_sampai_terarsip.
        return $sk->refresh();
    }

    /** Take the scan off an SK; it waits for one again ("Menunggu scan"). */
    public function removeScan(SuratKeputusan $sk): SuratKeputusan
    {
        $sk->update(['berkas_scan' => null]);

        return $sk->refresh();
    }

    /** Administrators only (SuratKeputusanPolicy); the reason goes to Log & Audit. */
    public function delete(SuratKeputusan $sk, User $by, string $alasan): void
    {
        DB::transaction(function () use ($sk, $by, $alasan) {
            SuratKeputusan::withDeletesAllowed(fn () => $sk->delete());

            activity('surat_keputusan')->causedBy($by)->performedOn($sk)->event('deleted')
                ->withProperties(['alasan' => $alasan, 'old' => self::snapshot($sk)])
                ->log('Menghapus SK ' . $sk->nomor_sk . ' — alasan: ' . $alasan);
        });
    }

    /** What Log & Audit keeps of an SK when it is created or deleted. */
    private static function snapshot(SuratKeputusan $sk): array
    {
        return [
            ...$sk->only(['tahun', 'nomor_urut', 'nomor_sk', 'perihal', 'jenis_sk', 'nama_peminta', 'keterangan', 'berkas_scan_nama', 'pembuat_id']),
            'tanggal_surat' => $sk->tanggal_surat?->toDateString(),
        ];
    }
}
