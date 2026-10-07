<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\PersuratanMasterResource;
use App\Http\Controllers\Controller;
use App\Models\PersuratanMaster;
use App\Support\NomorFormat;
use App\Support\NomorFormatSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Penomoran Otomatis: format nomor surat keluar per jenis surat. Back office
 * membaca; pengelola Master Persuratan mengubah.
 */
class NumberingFormatController extends Controller
{
    /** GET /api/persuratan/penomoran: semua jenis surat dengan format dan contoh nomornya. */
    public function index(): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canViewAny(), 403);

        return response()->json([
            'format_bawaan' => NomorFormat::DEFAULT,
            'pilihan_cepat' => NomorFormat::QUICK_FORMATS,
            'token' => collect(NomorFormat::TOKENS)->map(fn (array $t, string $token) => ['token' => '{' . $token . '}', 'arti' => $t['label'], 'keterangan' => $t['description'], 'contoh' => $t['example']])->values(),
            'data' => PersuratanMaster::where('type', 'jenis_surat')->orderBy('sort')->orderBy('nama')->get()->map(fn (PersuratanMaster $jenis) => $this->present($jenis)),
        ]);
    }

    /**
     * GET /api/persuratan/penomoran/pratinjau?format=&mode_bulan=&singkatan=&jenis_surat_id=
     * Contoh nomor sebelum disimpan. Singkatan: isian, lalu penimpaan jenis surat, lalu Pengaturan Aplikasi.
     */
    public function preview(Request $request): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canViewAny(), 403);

        $data = $request->validate([
            'format' => ['nullable', 'string', 'max:120', function (string $attribute, $value, \Closure $fail) {
                if (filled($value) && ($problem = NomorFormat::problem($value))) {
                    $fail($problem);
                }
            }],
            'mode_bulan' => ['nullable', Rule::in(array_keys(PersuratanMaster::MODE_BULAN))],
            'singkatan' => ['nullable', 'string', function (string $attribute, $value, \Closure $fail) {
                if (filled($value) && ($problem = NomorFormatSettings::singkatanProblem(trim($value)))) {
                    $fail($problem);
                }
            }],
            'jenis_surat_id' => ['nullable', 'integer', Rule::exists('persuratan_masters', 'id')->where('type', 'jenis_surat')],
        ]);

        $jenis = filled($data['jenis_surat_id'] ?? null) ? PersuratanMaster::find($data['jenis_surat_id']) : null;
        $format = filled($data['format'] ?? null) ? trim($data['format']) : ($jenis ? NomorFormatSettings::effectiveFormat($jenis) : NomorFormat::DEFAULT);
        $mode = $data['mode_bulan'] ?? ($jenis ? NomorFormatSettings::for($jenis)['mode_bulan'] : 'arab');
        [$singkatan, $sumber] = match (true) {
            filled($data['singkatan'] ?? null) => [trim($data['singkatan']), 'isian'],
            $jenis && NomorFormatSettings::for($jenis)['singkatan'] !== null => [NomorFormatSettings::for($jenis)['singkatan'], 'jenis_surat'],
            default => [\App\Support\SuratKeluarNumber::kodeSatker(), 'pengaturan_aplikasi'],
        };

        return response()->json([
            'format' => $format,
            'mode_bulan' => $mode,
            'singkatan_berlaku' => $singkatan,
            'sumber_singkatan' => $sumber,
            'contoh' => NomorFormat::preview($format, $mode, $singkatan),
        ]);
    }

    /** GET /api/persuratan/penomoran/{master} */
    public function show(PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canViewAny(), 403);
        abort_unless($master->type === 'jenis_surat', 404);

        return response()->json($this->present($master));
    }

    /** PUT /api/persuratan/penomoran/{master} {format (null = bawaan), mode_bulan} */
    public function update(Request $request, PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canEdit($master), 403);
        abort_unless($master->type === 'jenis_surat', 404);

        $data = $request->validate([
            'format' => ['present', 'nullable', 'string', function (string $attribute, $value, \Closure $fail) {
                if ($value !== null && ($problem = NomorFormat::problem($value))) {
                    $fail($problem);
                }
            }],
            'mode_bulan' => ['sometimes', 'required', Rule::in(array_keys(PersuratanMaster::MODE_BULAN))],
            // {S} override for this letter type; null = follow Pengaturan Aplikasi.
            'singkatan' => ['sometimes', 'nullable', 'string', function (string $attribute, $value, \Closure $fail) {
                if ($value !== null && ($problem = NomorFormatSettings::singkatanProblem(trim($value)))) {
                    $fail($problem);
                }
            }],
            // {k}/{K} fallback when the officer picks no classification; null = none.
            'klasifikasi_arsip' => ['sometimes', 'nullable', 'string', 'max:50', 'regex:/^[A-Za-z0-9]+([.\-][A-Za-z0-9]+)*$/'],
        ], ['klasifikasi_arsip.regex' => 'Gunakan kode klasifikasi seperti PP.00 (tanpa garis miring).']);

        NomorFormatSettings::save($master, $data['format'], $data['mode_bulan'] ?? $master->mode_bulan ?? 'arab', array_key_exists('singkatan', $data) ? $data['singkatan'] : false);
        if (array_key_exists('klasifikasi_arsip', $data)) {
            $master->update(['klasifikasi_arsip' => filled($data['klasifikasi_arsip']) ? mb_strtoupper(trim($data['klasifikasi_arsip'])) : null]);
        }
        activity('audit')->causedBy($request->user())->performedOn($master)
            ->withProperties(['format' => $master->format_nomor, 'mode_bulan' => $master->mode_bulan, 'singkatan' => $master->singkatan_unit_kerja, 'klasifikasi_arsip' => $master->klasifikasi_arsip])
            ->log('Mengubah format penomoran ' . $master->nama);

        return response()->json(['message' => 'Format penomoran ' . $master->nama . ' disimpan.'] + $this->present($master->fresh()));
    }

    /** PUT /api/persuratan/penomoran/{master}/singkatan {singkatan (null = ikut Pengaturan Aplikasi)}: format tidak berubah. */
    public function updateAbbreviation(Request $request, PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canEdit($master), 403);
        abort_unless($master->type === 'jenis_surat', 404);

        $data = $request->validate([
            'singkatan' => ['present', 'nullable', 'string', function (string $attribute, $value, \Closure $fail) {
                if ($value !== null && ($problem = NomorFormatSettings::singkatanProblem(trim($value)))) {
                    $fail($problem);
                }
            }],
        ]);

        $settings = NomorFormatSettings::for($master);
        NomorFormatSettings::save($master, $settings['format'], $settings['mode_bulan'], $data['singkatan']);
        activity('audit')->causedBy($request->user())->performedOn($master)
            ->withProperties(['singkatan' => $master->singkatan_unit_kerja])
            ->log('Mengubah singkatan unit kerja ' . $master->nama);

        return response()->json(['message' => 'Singkatan ' . $master->nama . ' disimpan.'] + $this->present($master->fresh()));
    }

    /**
     * GET /api/persuratan/penomoran/variabel?jenis_surat=Nama: variabel tersimpan untuk jenis surat
     * yang dipilih di form Minta Nomor (nama tanpa peka huruf besar; jenis di luar master = tanpa variabel).
     */
    public function variableFor(Request $request): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canViewAny(), 403);
        $request->validate(['jenis_surat' => ['required', 'string', 'max:100']]);

        $jenis = \App\Services\SuratKeluarService::jenisSurat($request->string('jenis_surat')->toString());

        return response()->json($jenis
            ? $this->presentVariable($jenis)
            : ['id' => null, 'jenis_surat' => trim($request->string('jenis_surat')->toString()), 'variabel' => null, 'dipakai_di_format' => false]);
    }

    /** GET /api/persuratan/penomoran/{master}/variabel: definisi variabel {v}/{V} (null bila tidak ada). */
    public function showVariable(PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canViewAny(), 403);
        abort_unless($master->type === 'jenis_surat', 404);

        return response()->json($this->presentVariable($master));
    }

    /** PUT /api/persuratan/penomoran/{master}/variabel {label, keterangan?, wajib?}: tambah atau ubah. */
    public function saveVariable(Request $request, PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canEdit($master), 403);
        abort_unless($master->type === 'jenis_surat', 404);

        $data = $request->validate([
            'label' => ['required', 'string', 'max:60'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'wajib' => ['sometimes', 'boolean'],
        ], ['label.required' => 'Nama variabel wajib diisi.']);

        $created = NomorFormatSettings::variable($master) === null;
        NomorFormatSettings::saveVariable($master, $data['label'], $data['keterangan'] ?? null, (bool) ($data['wajib'] ?? true));
        activity('audit')->causedBy($request->user())->performedOn($master)
            ->withProperties(['variabel' => $master->variabel_nomor])
            ->log(($created ? 'Menambah' : 'Mengubah') . ' variabel tambahan ' . $master->nama);

        return response()->json(['message' => 'Variabel ' . $master->nama . ' disimpan.'] + $this->presentVariable($master->fresh()), $created ? 201 : 200);
    }

    /** DELETE /api/persuratan/penomoran/{master}/variabel */
    public function deleteVariable(Request $request, PersuratanMaster $master): JsonResponse
    {
        abort_unless(PersuratanMasterResource::canEdit($master), 403);
        abort_unless($master->type === 'jenis_surat', 404);
        abort_unless(NomorFormatSettings::variable($master) !== null, 404, 'Jenis surat ini tidak punya variabel tambahan.');

        NomorFormatSettings::saveVariable($master, null);
        activity('audit')->causedBy($request->user())->performedOn($master)->log('Menghapus variabel tambahan ' . $master->nama);

        return response()->json(['message' => 'Variabel ' . $master->nama . ' dihapus.'] + $this->presentVariable($master->fresh()));
    }

    private function presentVariable(PersuratanMaster $jenis): array
    {
        return [
            'id' => $jenis->id,
            'jenis_surat' => $jenis->nama,
            'variabel' => NomorFormatSettings::variable($jenis),
            'dipakai_di_format' => NomorFormatSettings::usesVariable($jenis),
        ];
    }

    private function present(PersuratanMaster $jenis): array
    {
        $settings = NomorFormatSettings::for($jenis);

        return [
            'id' => $jenis->id,
            'jenis_surat' => $jenis->nama,
            'aktif' => $jenis->is_active,
            'format' => $settings['format'],
            'memakai_bawaan' => $settings['format'] === null,
            'format_berlaku' => NomorFormatSettings::effectiveFormat($jenis),
            'mode_bulan' => $settings['mode_bulan'],
            'singkatan' => $settings['singkatan'],
            'singkatan_berlaku' => NomorFormatSettings::effectiveSingkatan($jenis),
            'singkatan_dari_pengaturan' => $settings['singkatan'] === null,
            'contoh' => NomorFormatSettings::preview($jenis),
            'klasifikasi_arsip' => $jenis->klasifikasi_arsip,
            'variabel' => NomorFormatSettings::variable($jenis),
        ];
    }
}
