<?php

namespace App\Http\Controllers\Api;

use App\Exports\SurveyResponsesExport;
use App\Filament\Pages\Reports\SurveyReport;
use App\Filament\Resources\SurveyEditionResource;
use App\Http\Controllers\Controller;
use App\Models\SurveyEdition;
use App\Models\SurveyQuestion;
use App\Services\SurveySubmission;
use App\Support\SurveyReportData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

/** Survei SKM & SPAK for kiosks and apps: the same three steps as the web form. */
class SurveyController extends Controller
{
    /** GET /api/publik/survei: the open edition and its steps (identitas, skm, spak). */
    public function structure(): JsonResponse
    {
        if (! $edition = SurveyEdition::current()) {
            return response()->json(['message' => 'Belum ada survei yang dibuka.'], 404);
        }

        $step = fn (string $type) => SurveyQuestion::active()->byType($type)->with('unsur:id,name')->orderBy('order')->orderBy('id')->get()
            ->map(fn (SurveyQuestion $q) => [
                'id' => $q->id,
                'pertanyaan' => $q->question,
                'jenis_isian' => $q->field_type,
                'wajib' => (bool) $q->is_required,
                'pilihan' => $q->options ?: null,
                'unsur' => $q->unsur?->name,
            ])->values();

        return response()->json([
            'edisi' => [
                'id' => $edition->id,
                'nama' => $edition->name,
                'periode' => $edition->period,
                'tahun' => $edition->year,
                'mulai' => $edition->start_date?->toDateString(),
                'selesai' => $edition->end_date?->toDateString(),
            ],
            'skala' => ['min' => 1, 'max' => 4, 'keterangan' => 'Tidak baik (1) sampai sangat baik (4)'],
            'langkah' => [
                ['kunci' => 'identitas', 'judul' => 'Identitas Responden', 'pertanyaan' => $step('identity')],
                ['kunci' => 'skm', 'judul' => 'Survei Kepuasan Masyarakat', 'pertanyaan' => $step('skm')],
                ['kunci' => 'spak', 'judul' => 'Survei Persepsi Anti Korupsi', 'pertanyaan' => $step('spak')],
            ],
        ])->setPublic()->setMaxAge(120);
    }

    /**
     * POST /api/publik/survei: a completed survey in one go.
     * identitas{question id: answer}, skm{...}, spak{...}, saran
     */
    public function submit(Request $request, SurveySubmission $surveys): JsonResponse
    {
        if (! $edition = SurveyEdition::current()) {
            return response()->json(['message' => 'Belum ada survei yang dibuka.'], 404);
        }

        [$identityRules, $attributes] = SurveySubmission::identityRules('identitas');
        $data = $request->validate([
            'identitas' => ['required', 'array'],
            'skm' => ['required', 'array'],
            'spak' => ['required', 'array'],
            'saran' => ['nullable', 'string', 'max:1000'],
        ] + $identityRules + SurveySubmission::ratingRules('skm', 'skm') + SurveySubmission::ratingRules('spak', 'spak'), SurveySubmission::MESSAGES, $attributes);

        $surveys->save(
            $edition,
            SurveySubmission::only('identity', $data['identitas']),
            SurveySubmission::only('skm', $data['skm']),
            SurveySubmission::only('spak', $data['spak']),
            $data['saran'] ?? null,
            $request->user()?->id,
            $request->ip(),
        );

        return response()->json(['pesan' => 'Terima kasih, survei Anda tersimpan.'], 201);
    }

    /** GET /api/survei/edisi (admin) */
    public function editions(): JsonResponse
    {
        abort_unless(SurveyEditionResource::canViewAny(), 403);

        return response()->json([
            'data' => SurveyEdition::withCount('responses')->orderByDesc('year')->orderByDesc('period')->get()
                ->map(fn (SurveyEdition $edition) => $this->presentEdition($edition)),
        ]);
    }

    /** POST /api/survei/edisi (admin): triwulan, tahun, keterangan, aktif */
    public function storeEdition(Request $request): JsonResponse
    {
        abort_unless(SurveyEditionResource::canCreate(), 403);

        $edition = SurveyEdition::create(SurveyEditionResource::prepare($this->editionData($request)));

        return response()->json($this->presentEdition($edition->loadCount('responses')), 201);
    }

    /** PUT /api/survei/edisi/{edition} (admin), also to switch it on or off */
    public function updateEdition(Request $request, SurveyEdition $edition): JsonResponse
    {
        abort_unless(SurveyEditionResource::canEdit($edition), 403);

        $edition->update(SurveyEditionResource::prepare($this->editionData($request, $edition), $edition));

        return response()->json($this->presentEdition($edition->loadCount('responses')));
    }

    /** DELETE /api/survei/edisi/{edition} (admin): only an edition nobody has answered yet. */
    public function destroyEdition(SurveyEdition $edition): JsonResponse
    {
        abort_unless(SurveyEditionResource::canDelete($edition), 403);

        if ($edition->responses()->exists()) {
            return response()->json(['message' => 'Edisi yang sudah memiliki jawaban tidak dapat dihapus; nonaktifkan saja.'], 422);
        }

        $edition->delete();

        return response()->json(null, 204);
    }

    private function editionData(Request $request, ?SurveyEdition $edition = null): array
    {
        $data = $request->validate([
            'triwulan' => [$edition ? 'sometimes' : 'required', Rule::in(array_keys(SurveyEditionResource::QUARTERS))],
            'tahun' => [$edition ? 'sometimes' : 'required', 'integer', 'min:2000', 'max:2100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'aktif' => ['sometimes', 'boolean'],
        ]);

        $period = $data['triwulan'] ?? $edition?->period;
        $year = (int) ($data['tahun'] ?? $edition?->year);

        if (SurveyEdition::where('period', $period)->where('year', $year)->when($edition, fn ($q) => $q->whereKeyNot($edition->id))->exists()) {
            throw ValidationException::withMessages(['triwulan' => 'Edisi untuk triwulan dan tahun ini sudah ada.']);
        }

        return array_filter([
            'period' => $period,
            'year' => $year,
            'description' => $data['keterangan'] ?? $edition?->description,
            'is_active' => array_key_exists('aktif', $data) ? (bool) $data['aktif'] : ($edition?->is_active ?? false),
        ], fn ($value) => $value !== null);
    }

    private function presentEdition(SurveyEdition $edition): array
    {
        return [
            'id' => $edition->id,
            'nama' => $edition->name,
            'triwulan' => $edition->period,
            'tahun' => $edition->year,
            'mulai' => $edition->start_date?->toDateString(),
            'selesai' => $edition->end_date?->toDateString(),
            'aktif' => (bool) $edition->is_active,
            'keterangan' => $edition->description,
            'jumlah_responden' => $edition->responses_count ?? null,
        ];
    }

    /** GET /api/survei/laporan?jenis=skm|spak&periode=month|last_month|quarter|year|all&edisi=&format=json|pdf|xlsx */
    public function report(Request $request)
    {
        abort_unless(SurveyReport::canAccess(), 403);

        $filters = $request->validate([
            'jenis' => ['nullable', Rule::in(['skm', 'spak'])],
            'periode' => ['nullable', Rule::in(array_keys(SurveyReport::PERIODS))],
            'edisi' => ['nullable', 'integer', 'exists:survey_editions,id'],
            'format' => ['nullable', Rule::in(['json', 'pdf', 'xlsx'])],
        ]);
        $data = SurveyReportData::build($filters['jenis'] ?? 'skm', $filters['periode'] ?? 'month', $filters['edisi'] ?? null);

        if (($filters['format'] ?? 'json') === 'xlsx') {
            return Excel::download(
                new SurveyResponsesExport($data['from']->toDateString(), $data['to']->toDateString(), $filters['edisi'] ?? null),
                'laporan-survei-' . $data['from']->format('Ymd') . '-' . $data['to']->format('Ymd') . '.xlsx',
            );
        }

        if (($filters['format'] ?? 'json') === 'pdf') {
            return Pdf::loadView('pdf.survey-report', ['data' => $data])
                ->download('laporan-' . $data['type'] . '-' . $data['from']->format('Ymd') . '-' . $data['to']->format('Ymd') . '.pdf');
        }

        return response()->json([
            'jenis' => $data['type'],
            'indeks' => $data['label'],
            'periode' => ['dari' => $data['from']->toDateString(), 'sampai' => $data['to']->toDateString()],
            'nilai_indeks' => $data['index'],
            'mutu' => $data['grade'],
            'rata_rata_unsur' => $data['average'] ? round($data['average'], 3) : null,
            'responden' => $data['respondents'],
            'jumlah_jawaban' => $data['answers'],
            'per_pertanyaan' => $data['scores'],
            'demografi' => $data['demographics'],
        ]);
    }
}
