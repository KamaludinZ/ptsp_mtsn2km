<?php

namespace App\Services;

use App\Exceptions\TicketActionException;
use App\Models\PersuratanMaster;
use App\Models\SuratKeluar;
use App\Models\SuratKeluarBatch;
use App\Models\User;
use App\Support\Persuratan;
use App\Support\NomorFormat;
use App\Support\NomorFormatSettings;
use App\Support\SuratKeluarNumber;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Penomoran surat keluar: numbers 1-9999 that restart every year, reserved
 * one or several at a time. A request gets the lowest free numbers of the
 * year, so a number freed by deleting its letter is handed out again first.
 * The yearly counter row is locked while numbers are handed out, so two
 * officers never receive the same number.
 */
class SuratKeluarService
{
    public const MAX_PER_REQUEST = 5000;

    /** Rows per insert statement when reserving many numbers at once. */
    private const INSERT_CHUNK = 500;

    /**
     * Reserve $count numbers for the year of $tanggal: the lowest free ones.
     *
     * @param  array<string, mixed>  $data  letter fields shared by every reserved number
     * @return Collection<int, SuratKeluar>
     */
    public function reserve(int $count, CarbonInterface $tanggal, User $pembuat, array $data = []): Collection
    {
        if ($count < 1 || $count > self::MAX_PER_REQUEST) {
            throw new TicketActionException('Jumlah nomor harus antara 1 dan ' . number_format(self::MAX_PER_REQUEST, 0, ',', '.') . '.');
        }

        // The variable of the chosen jenis surat ({v}/{V}) must be filled when it is required.
        if ($problem = self::variableProblem($data['jenis_surat'] ?? null, $data['variabel'] ?? null)) {
            throw new TicketActionException($problem);
        }

        $year = (int) $tanggal->format('Y');

        return DB::transaction(function () use ($count, $tanggal, $pembuat, $data, $year) {
            DB::table('surat_sequences')->insertOrIgnore(['tahun' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $last = (int) DB::table('surat_sequences')->where('tahun', $year)->lockForUpdate()->value('last_number');

            $numbers = $this->freeNumbers($year, $count);
            if (count($numbers) < $count) {
                throw new TicketActionException('Nomor surat tahun ' . $year . ' tinggal ' . count($numbers) . '.');
            }

            $batch = SuratKeluarBatch::create([
                'tahun' => $year,
                'jumlah_diminta' => $count,
                'nomor_awal' => $numbers[0],
                'nomor_akhir' => end($numbers),
                'created_by' => $pembuat->id,
            ]);

            // Uploaded files belong to one letter only, never to a whole batch.
            $fields = ['tujuan_surat', 'perihal', 'jenis_surat', 'klasifikasi', 'variabel', 'lampiran', 'tembusan', 'keterangan', ...($count === 1 ? ['berkas_lampiran', 'berkas_lampiran_nama'] : [])];
            $compose = self::numberComposer($tanggal, $data['klasifikasi'] ?? null, $data['jenis_surat'] ?? null, $data['variabel'] ?? null);
            $now = now();

            // Many numbers at once: built through the model (casts) and inserted in chunks.
            collect($numbers)->chunk(self::INSERT_CHUNK)->each(fn (Collection $chunk) => SuratKeluar::insert($chunk->map(fn (int $urut) => [
                ...(new SuratKeluar([
                    ...array_intersect_key($data, array_flip($fields)),
                    'tahun' => $year,
                    'nomor_urut' => $urut,
                    'nomor_surat' => $compose($urut),
                    'tanggal_surat' => $tanggal,
                    'pembuat_id' => $pembuat->id,
                    'batch_id' => $batch->id,
                ]))->getAttributes(),
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all()));

            $letters = SuratKeluar::where('batch_id', $batch->id)->orderBy('nomor_urut')->get();

            DB::table('surat_sequences')->where('tahun', $year)->update(['last_number' => max($last, end($numbers)), 'updated_at' => now()]);
            $this->rememberManual($data);

            activity('surat_keluar')->causedBy($pembuat)->performedOn($batch)->event('reserved')
                ->withProperties(['tahun' => $year, 'jumlah' => $count, 'nomor_awal' => $numbers[0], 'nomor_akhir' => end($numbers)])
                ->log($count === 1
                    ? 'Meminta nomor surat keluar ' . $letters->first()->nomor_surat
                    : "Meminta {$count} nomor surat keluar (urut " . SuratKeluarNumber::ranges($numbers) . " tahun {$year})");

            return $letters;
        });
    }

    /**
     * The $count lowest numbers of $year that no letter holds: numbers freed
     * by deleted letters first, then new ones, up to SuratKeluarNumber::MAX.
     *
     * @return list<int>
     */
    public function freeNumbers(int $year, int $count): array
    {
        $used = array_flip(SuratKeluar::where('tahun', $year)->pluck('nomor_urut')->all());
        $free = [];

        for ($n = 1; $n <= SuratKeluarNumber::MAX && count($free) < $count; $n++) {
            if (! isset($used[$n])) {
                $free[] = $n;
            }
        }

        return $free;
    }

    /**
     * Fill in (or correct) a reserved letter. The date must stay in the
     * number's year; month and classification are part of the number, so
     * it is composed again.
     *
     * @param  array<string, mixed>  $data
     */
    public function describe(SuratKeluar $letter, array $data): SuratKeluar
    {
        $date = Carbon::parse($data['tanggal_surat'] ?? $letter->tanggal_surat);

        if ((int) $date->format('Y') !== $letter->tahun) {
            throw new TicketActionException("Tanggal surat harus di tahun {$letter->tahun}, sesuai nomornya.");
        }

        $letter->fill([
            ...array_intersect_key($data, array_flip(['tujuan_surat', 'perihal', 'jenis_surat', 'klasifikasi', 'variabel', 'lampiran', 'tembusan', 'keterangan', 'berkas_lampiran', 'berkas_lampiran_nama'])),
            'tanggal_surat' => $date,
        ]);
        // Same rule as when the number was requested: the (possibly new) jenis surat's required variable.
        if ($letter->isDirty(['jenis_surat', 'variabel']) && ($problem = self::variableProblem($letter->jenis_surat, $letter->variabel))) {
            throw new TicketActionException($problem);
        }
        $letter->nomor_surat = self::composeNumber($letter->nomor_urut, $date, $letter->klasifikasi, $letter->jenis_surat, $letter->variabel);
        $letter->save();
        $this->rememberManual($data);

        return $letter;
    }

    /**
     * Delete one letter (administrators only, see SuratKeluarPolicy). Its
     * number becomes free and is handed out again by the next request; its
     * request in Riwayat permintaan nomor shrinks to the letters left, or
     * disappears with the last one. The audit log keeps the letter's data,
     * who deleted it, and why.
     */
    public function delete(SuratKeluar $letter, User $by, string $alasan): void
    {
        DB::transaction(function () use ($letter, $by, $alasan) {
            $letter->delete();

            if ($batch = $letter->batch) {
                $left = $batch->letters()->pluck('nomor_urut');
                $left->isEmpty()
                    ? $batch->delete()
                    : $batch->update(['jumlah_diminta' => $left->count(), 'nomor_awal' => $left->min(), 'nomor_akhir' => $left->max()]);
            }

            activity('surat_keluar')->causedBy($by)->performedOn($letter)->event('deleted')
                ->withProperties([
                    'alasan' => $alasan,
                    'old' => [
                        ...$letter->only(['tahun', 'nomor_urut', 'nomor_surat', 'tujuan_surat', 'perihal', 'jenis_surat', 'klasifikasi', 'pembuat_id', 'batch_id']),
                        'tanggal_surat' => $letter->tanggal_surat?->toDateString(),
                        'berkas_lampiran_nama' => array_values($letter->berkas_lampiran_nama ?? []),
                    ],
                ])
                ->log('Menghapus surat keluar ' . $letter->nomor_surat . ' — alasan: ' . $alasan);
        });

        Storage::disk('local')->delete($letter->berkas_lampiran ?? []);
    }

    /** Hand-typed letter values become inactive Master Persuratan choices for review. */
    private function rememberManual(array $data): void
    {
        Persuratan::rememberManual([
            'tujuan_naskah' => $data['tujuan_surat'] ?? null,
            'jenis_surat' => $data['jenis_surat'] ?? null,
            'tembusan' => $data['tembusan'] ?? null,
        ]);
    }

    /**
     * Perakit nomor terpusat: nomor surat dari format jenis surat (Master
     * Persuratan, tab Penomoran Otomatis) atau format bawaan, mode bulannya,
     * kode surat jenis itu ({KS}, bawaan B), singkatan unit kerja ({S}),
     * klasifikasi ({k}/{K}), dan variabel tambahan ({v}/{V}).
     */
    public static function composeNumber(int $urut, CarbonInterface $tanggal, ?string $klasifikasi = null, ?string $jenisSurat = null, ?string $variabel = null): string
    {
        return self::numberComposer($tanggal, $klasifikasi, $jenisSurat, $variabel)($urut);
    }

    /**
     * composeNumber() for many numbers sharing date, classification, jenis
     * surat and variable: the jenis surat and its settings are looked up once.
     *
     * @return \Closure(int): string
     */
    public static function numberComposer(CarbonInterface $tanggal, ?string $klasifikasi = null, ?string $jenisSurat = null, ?string $variabel = null): \Closure
    {
        $jenis = self::jenisSurat($jenisSurat);
        $settings = $jenis ? NomorFormatSettings::for($jenis) : ['format' => null, 'mode_bulan' => 'arab'];
        $tokens = [
            'KS' => $jenis?->kode ?: NomorFormat::TOKENS['KS']['example'],
            'S' => NomorFormatSettings::effectiveSingkatan($jenis),
            // {k}/{K}: the letter's classification, else the jenis surat's archive classification.
            'k' => filled($klasifikasi) ? trim($klasifikasi) : ($jenis?->klasifikasi_arsip ?: ''),
            'v' => filled($variabel) ? trim($variabel) : '',
            'tanggal' => $tanggal,
            'mode_bulan' => $settings['mode_bulan'],
        ];

        return fn (int $urut) => NomorFormat::render($settings['format'] ?? NomorFormat::DEFAULT, [...$tokens, 'N' => $urut]);
    }

    /** Why the variable value cannot be used for this jenis surat, or null. */
    public static function variableProblem(?string $jenisSurat, ?string $value): ?string
    {
        $variable = NomorFormatSettings::variable(self::jenisSurat($jenisSurat));
        $value = trim((string) $value);

        return match (true) {
            $variable && $variable['wajib'] && $value === '' => $variable['label'] . ' wajib diisi untuk jenis surat ini.',
            str_contains($value, '/') => 'Variabel tambahan tidak boleh berisi garis miring (/).',
            mb_strlen($value) > 50 => 'Variabel tambahan paling panjang 50 karakter.',
            default => null,
        };
    }

    /** The jenis surat row for a letter's (typed or chosen) jenis surat, if it is in the master list. */
    public static function jenisSurat(?string $nama): ?PersuratanMaster
    {
        return filled($nama)
            ? PersuratanMaster::where('type', 'jenis_surat')->whereRaw('lower(nama) = ?', [mb_strtolower(trim($nama))])->first()
            : null;
    }

    /** The number the next request would receive for $year. */
    public function nextNumber(int $year): int
    {
        return $this->freeNumbers($year, 1)[0] ?? SuratKeluarNumber::MAX + 1;
    }
}
