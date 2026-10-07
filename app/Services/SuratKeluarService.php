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

/**
 * Penomoran surat keluar: sequential numbers 1-9999 that restart every year,
 * reserved one or several at a time. The yearly counter row is locked while
 * numbers are handed out, so two officers never receive the same number.
 */
class SuratKeluarService
{
    public const MAX_PER_REQUEST = 50;

    /**
     * Reserve $count consecutive numbers for the year of $tanggal.
     *
     * @param  array<string, mixed>  $data  letter fields shared by every reserved number
     * @return Collection<int, SuratKeluar>
     */
    public function reserve(int $count, CarbonInterface $tanggal, User $pembuat, array $data = []): Collection
    {
        if ($count < 1 || $count > self::MAX_PER_REQUEST) {
            throw new TicketActionException('Jumlah nomor harus antara 1 dan ' . self::MAX_PER_REQUEST . '.');
        }

        // The variable of the chosen jenis surat ({v}/{V}) must be filled when it is required.
        if ($problem = self::variableProblem($data['jenis_surat'] ?? null, $data['variabel'] ?? null)) {
            throw new TicketActionException($problem);
        }

        $year = (int) $tanggal->format('Y');

        return DB::transaction(function () use ($count, $tanggal, $pembuat, $data, $year) {
            DB::table('surat_sequences')->insertOrIgnore(['tahun' => $year, 'last_number' => 0, 'created_at' => now(), 'updated_at' => now()]);
            $last = (int) DB::table('surat_sequences')->where('tahun', $year)->lockForUpdate()->value('last_number');

            if ($last + $count > SuratKeluarNumber::MAX) {
                throw new TicketActionException('Nomor surat tahun ' . $year . ' tinggal ' . (SuratKeluarNumber::MAX - $last) . '.');
            }

            $batch = SuratKeluarBatch::create([
                'tahun' => $year,
                'jumlah_diminta' => $count,
                'nomor_awal' => $last + 1,
                'nomor_akhir' => $last + $count,
                'created_by' => $pembuat->id,
            ]);

            // Uploaded files belong to one letter only, never to a whole batch.
            $fields = ['tujuan_surat', 'perihal', 'jenis_surat', 'klasifikasi', 'variabel', 'lampiran', 'tembusan', 'keterangan', ...($count === 1 ? ['berkas_lampiran', 'berkas_lampiran_nama'] : [])];

            $letters = collect(range($last + 1, $last + $count))->map(fn (int $urut) => SuratKeluar::create([
                ...array_intersect_key($data, array_flip($fields)),
                'tahun' => $year,
                'nomor_urut' => $urut,
                'nomor_surat' => self::composeNumber($urut, $tanggal, $data['klasifikasi'] ?? null, $data['jenis_surat'] ?? null, $data['variabel'] ?? null),
                'tanggal_surat' => $tanggal,
                'pembuat_id' => $pembuat->id,
                'batch_id' => $batch->id,
            ]));

            DB::table('surat_sequences')->where('tahun', $year)->update(['last_number' => $last + $count, 'updated_at' => now()]);
            $this->rememberManual($data);

            activity('surat_keluar')->causedBy($pembuat)->performedOn($batch)->event('reserved')
                ->withProperties(['tahun' => $year, 'jumlah' => $count, 'nomor_awal' => $last + 1, 'nomor_akhir' => $last + $count])
                ->log($count === 1
                    ? 'Meminta nomor surat keluar ' . $letters->first()->nomor_surat
                    : "Meminta {$count} nomor surat keluar (urut " . ($last + 1) . '–' . ($last + $count) . " tahun {$year})");

            return $letters;
        });
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
        $jenis = self::jenisSurat($jenisSurat);
        $settings = $jenis ? NomorFormatSettings::for($jenis) : ['format' => null, 'mode_bulan' => 'arab'];

        return NomorFormat::render($settings['format'] ?? NomorFormat::DEFAULT, [
            'KS' => $jenis?->kode ?: NomorFormat::TOKENS['KS']['example'],
            'N' => $urut,
            'S' => NomorFormatSettings::effectiveSingkatan($jenis),
            // {k}/{K}: the letter's classification, else the jenis surat's archive classification.
            'k' => filled($klasifikasi) ? trim($klasifikasi) : ($jenis?->klasifikasi_arsip ?: ''),
            'v' => filled($variabel) ? trim($variabel) : '',
            'tanggal' => $tanggal,
            'mode_bulan' => $settings['mode_bulan'],
        ]);
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
        return (int) DB::table('surat_sequences')->where('tahun', $year)->value('last_number') + 1;
    }
}
