<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** One SK number in the Register Penomoran SK ("{N} TAHUN {YYYY}"). */
class SuratKeputusan extends Model
{
    use LogsActivity;

    protected $table = 'surat_keputusan';

    /** Asking for a number and deleting (with its reason) are logged by SuratKeputusanService; changes per SK. */
    protected static $recordEvents = ['updated'];

    /** Fields whose changes go to Log & Audit (log "surat_keputusan"). */
    public const LOGGED = ['nomor_sk', 'tanggal_surat', 'perihal', 'jenis_sk', 'nama_peminta', 'keterangan', 'berkas_scan_nama'];

    /** The logged fields as Log & Audit and the SK's history name them. */
    public const FIELD_LABELS = [
        'nomor_sk' => 'Nomor SK', 'tanggal_surat' => 'Tanggal surat', 'perihal' => 'Perihal', 'jenis_sk' => 'Jenis SK',
        'nama_peminta' => 'Nama peminta', 'keterangan' => 'Keterangan', 'berkas_scan_nama' => 'Scan SK',
    ];

    protected static function booted(): void
    {
        // Peran aktif petugas saat meminta nomor ini (bukan semua peran yang ia pegang).
        static::creating(function (SuratKeputusan $sk) {
            if ($sk->peran_pembuat === null && $sk->pembuat_id) {
                $sk->peran_pembuat = \App\Support\ActiveRoles::actingRoleOf(User::find($sk->pembuat_id));
            }
        });
        // Versi: one up with every change (optimistic lock, SuratKeputusanService::assertVersion); never set from outside.
        static::updating(function (SuratKeputusan $sk) {
            $sk->versi = (int) ($sk->getOriginal('versi') ?? 1) + 1;
        });
        // "No scan" is stored as NULL, and the original file name goes with it.
        static::saving(function (SuratKeputusan $sk) {
            if (blank($sk->berkas_scan)) {
                $sk->berkas_scan = null;
                $sk->berkas_scan_nama = null;
            } elseif ($sk->isDirty('berkas_scan_nama')) {
                $sk->berkas_scan_nama = self::cleanScanName($sk->berkas_scan_nama);
            }

            if ($sk->isDirty('berkas_scan')) {
                $sk->refuseAnotherSksScan();
                $sk->fillScanDetails();
            }

            // The Master Jenis SK entry behind the name, for reports that outlive a rename.
            if ($sk->isDirty('jenis_sk') || ($sk->jenis_sk_id === null && ! $sk->exists)) {
                $sk->jenis_sk_id = PersuratanMaster::query()->where('type', 'jenis_sk')
                    ->whereRaw('lower(btrim(nama)) = ?', [mb_strtolower(trim((string) $sk->jenis_sk))])->value('id');
            }
        });
        // A replaced or removed scan, or the scan of a deleted SK, leaves the
        // private disk once the change is committed.
        static::updated(function (SuratKeputusan $sk) {
            if ($sk->wasChanged('berkas_scan') && filled($old = $sk->getOriginal('berkas_scan'))) {
                self::deleteScanFile($old);
            }
        });
        static::deleted(function (SuratKeputusan $sk) {
            if (filled($sk->berkas_scan)) {
                self::deleteScanFile($sk->berkas_scan);
            }
        });
    }

    /**
     * Rows may leave the register only inside $callback: "Hapus"
     * (SuratKeputusanService::delete) or retention. The database refuses any
     * other delete (trigger surat_keputusan_guard).
     */
    public static function withDeletesAllowed(\Closure $callback): mixed
    {
        return DB::transaction(function () use ($callback) {
            DB::select("select set_config('app.allow_sk_delete', 'on', true)");

            try {
                return $callback();
            } finally {
                DB::select("select set_config('app.allow_sk_delete', 'off', true)");
            }
        });
    }

    /**
     * A scan is one SK's own file. A tampered form could name another SK's
     * stored scan here; this SK would then delete it as its own when its scan
     * is replaced or the SK is deleted, without a trace on the other SK.
     */
    private function refuseAnotherSksScan(): void
    {
        $taken = filled($this->berkas_scan) && static::query()
            ->where('berkas_scan', $this->berkas_scan)
            ->when($this->exists, fn (Builder $query) => $query->whereKeyNot($this->getKey()))
            ->exists();

        if ($taken) {
            throw new \App\Exceptions\TicketActionException('Berkas scan tidak dikenal; unggah ulang scan SK.');
        }
    }

    /**
     * Arsip scan SK: the type the stored scan was checked as (its server-given
     * extension), its size, and who uploaded it, when; nothing without a scan.
     */
    private function fillScanDetails(): void
    {
        if ($this->berkas_scan === null) {
            $this->forceFill(['berkas_scan_mime' => null, 'berkas_scan_ukuran' => null, 'berkas_scan_sha256' => null, 'scan_diunggah_pada' => null, 'scan_diunggah_oleh' => null]);

            return;
        }

        $disk = Storage::disk('local');
        $stored = $disk->exists($this->berkas_scan);

        $this->forceFill([
            'berkas_scan_mime' => self::SCAN_MIME_BY_EXTENSION[strtolower(pathinfo($this->berkas_scan, PATHINFO_EXTENSION))] ?? null,
            'berkas_scan_ukuran' => ($stored ? $disk->size($this->berkas_scan) : null) ?: null,
            // Sidik berkas: the archive check (SuratKeputusanService::verifyArchive) compares the file with it later.
            'berkas_scan_sha256' => $stored ? hash('sha256', $disk->get($this->berkas_scan)) : null,
            'scan_diunggah_pada' => now(),
            'scan_diunggah_oleh' => auth()->id(),
        ]);
    }

    private static function deleteScanFile(string $path): void
    {
        DB::afterCommit(fn () => Storage::disk('local')->delete($path));
    }

    /** The uploaded file's own name, without any folder or control characters (shown and offered as download name). */
    public static function cleanScanName(?string $name): ?string
    {
        $name = Str::squish(preg_replace('/[\x00-\x1F\x7F]/u', '', (string) $name) ?? '');
        $name = trim(Str::afterLast(str_replace('\\', '/', $name), '/'));

        return $name === '' ? null : mb_substr($name, 0, 255);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('surat_keputusan')
            ->logOnly(self::LOGGED)
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $event) => match ($event) {
                'updated' => 'Mengubah data SK ' . $this->nomor_sk,
                default => $event,
            });
    }

    /** A new SK starts at versi 1, as the column default (also in memory, before it is reloaded). */
    protected $attributes = ['versi' => 1];

    protected $fillable = [
        'tahun',
        'nomor_urut',
        'nomor_sk',
        'tanggal_surat',
        'perihal',
        'jenis_sk',
        'nama_peminta',
        'ticket_id',
        'kunci_permintaan',
        'keterangan',
        'berkas_scan',
        'berkas_scan_nama',
        'pembuat_id',
        'peran_pembuat',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'berkas_scan_ukuran' => 'integer',
        'versi' => 'integer',
        'jenis_sk_id' => 'integer',
        // Computed by the database (Laporan & Evaluasi); never written.
        'bulan' => 'integer',
        'hari_sampai_terarsip' => 'float',
        'scan_diunggah_pada' => 'datetime',
        'tahun' => 'integer',
        'nomor_urut' => 'integer',
    ];

    /** "{N} TAHUN {YYYY}": the sequence number, the literal word TAHUN, and the year. */
    public static function composeNumber(int $nomorUrut, int $tahun): string
    {
        return $nomorUrut . ' TAHUN ' . $tahun;
    }

    /** Numbers one after another, e.g. "7–16 TAHUN 2026"; one number alone as composeNumber. */
    public static function composeRange(int $first, int $last, int $tahun): string
    {
        return $first === $last ? self::composeNumber($first, $tahun) : $first . '–' . self::composeNumber($last, $tahun);
    }

    /** Status of an SK in the register: its number is taken; the archive is complete once the scan is uploaded. */
    public const STATUS = [
        'menunggu_scan' => 'Menunggu scan',
        'terarsip' => 'Terarsip',
    ];

    /** Scans of the signed SK: PDF or a photo/scan image, kept on the private disk. */
    public const SCAN_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    public const SCAN_DIRECTORY = 'surat-keputusan';

    /** The scan types by the name officers know them by. */
    public const SCAN_TYPES = ['PDF' => 'application/pdf', 'JPG' => 'image/jpeg', 'PNG' => 'image/png'];

    /** Stored scans are named with the extension of the type they were checked as. */
    public const SCAN_MIME_BY_EXTENSION = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];

    /** Kilobytes. */
    public const SCAN_MAX_SIZE = 10240;

    /** Original name of the uploaded scan, falling back to its stored name. */
    public function scanName(): ?string
    {
        return $this->hasScan() ? ($this->berkas_scan_nama ?: basename($this->berkas_scan)) : null;
    }

    /** PDF, JPG or PNG: the type the stored scan was checked as. */
    public function scanType(): ?string
    {
        return $this->hasScan() ? (array_search($this->berkas_scan_mime, self::SCAN_TYPES, true) ?: null) : null;
    }

    public function hasScan(): bool
    {
        return filled($this->berkas_scan);
    }

    public function status(): string
    {
        return $this->hasScan() ? 'terarsip' : 'menunggu_scan';
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'terarsip' => $query->whereNotNull('berkas_scan')->where('berkas_scan', '!=', ''),
            'menunggu_scan' => $query->where(fn (Builder $q) => $q->whereNull('berkas_scan')->orWhere('berkas_scan', '')),
            default => $query,
        };
    }

    /**
     * The register narrowed by the filters shared by the API and the
     * exports: tahun (default this year, "semua" for every year), q (nomor,
     * perihal, jenis, peminta, keterangan, and the number of the ticket it was
     * asked for from), nomor_urut, jenis_sk (one or more), status, petugas
     * (pembuat_id), tiket (ticket id or number), dari/sampai (tanggal surat).
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeRegister(Builder $query, array $filters): Builder
    {
        return $query
            ->when(($filters['tahun'] ?? null) !== 'semua', fn (Builder $q) => $q->where('tahun', $filters['tahun'] ?? now()->year))
            ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                // Perihal is stored without the "tentang" the SK title already has.
                $like = '%' . addcslashes(preg_replace('/^tentang\s+/iu', '', trim($term)), '%_\\') . '%';
                $query->where(fn (Builder $q) => $q->where('nomor_sk', 'ilike', $like)->orWhere('perihal', 'ilike', $like)
                    ->orWhere('jenis_sk', 'ilike', $like)->orWhere('nama_peminta', 'ilike', $like)->orWhere('keterangan', 'ilike', $like)
                    // The number of the ticket it was asked for from (Permohonan terkait).
                    ->orWhereHas('ticket', fn (Builder $t) => $t->where('ticket_number', 'ilike', $like))
                    // A plain number is also the sequence number.
                    ->when(ctype_digit(trim($term)), fn (Builder $w) => $w->orWhere('nomor_urut', (int) trim($term))));
            })
            ->when($filters['nomor_urut'] ?? null, fn (Builder $q, $v) => $q->where('nomor_urut', $v))
            ->when($filters['jenis_sk'] ?? null, fn (Builder $q, $v) => $q->whereIn('jenis_sk', (array) $v))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->withStatus($status))
            ->when($filters['petugas'] ?? null, fn (Builder $q, $id) => $q->where('pembuat_id', $id))
            // tiket: the ticket's id or its number.
            ->when($filters['tiket'] ?? null, fn (Builder $q, $ticket) => ctype_digit((string) $ticket)
                ? $q->where('ticket_id', (int) $ticket)
                : $q->whereHas('ticket', fn (Builder $t) => $t->where('ticket_number', $ticket)))
            ->when($filters['dari'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal_surat', '>=', $d))
            ->when($filters['sampai'] ?? null, fn (Builder $q, $d) => $q->whereDate('tanggal_surat', '<=', $d));
    }

    /** The Master Jenis SK entry the SK's jenis was (kept when the jenis is renamed later). */
    public function jenisSkMaster(): BelongsTo
    {
        return $this->belongsTo(PersuratanMaster::class, 'jenis_sk_id');
    }

    /** Permohonan terkait: the PTSP ticket this SK was asked for, if any; still found once the ticket is deleted (soft). */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class)->withTrashed();
    }

    /** Who uploaded the scan now in the archive. */
    public function pengunggahScan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scan_diunggah_oleh');
    }

    /** The officer who asked for the number (petugas pencatat). */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
}
