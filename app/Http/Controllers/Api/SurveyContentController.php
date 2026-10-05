<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\SurveyQuestionResource;
use App\Filament\Resources\SurveyUnsurResource;
use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use App\Models\SurveyUnsur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Admin: unsur penilaian and survey questions. */
class SurveyContentController extends Controller
{
    /** GET /api/survei/unsur */
    public function unsurIndex(): JsonResponse
    {
        abort_unless(SurveyUnsurResource::canViewAny(), 403);

        return response()->json(['data' => SurveyUnsur::withCount('questions')->orderBy('survey_type')->orderBy('order')->get()]);
    }

    /** POST /api/survei/unsur */
    public function unsurStore(Request $request): JsonResponse
    {
        abort_unless(SurveyUnsurResource::canCreate(), 403);

        $data = $this->unsurData($request);
        $data['order'] ??= (int) SurveyUnsur::where('survey_type', $data['survey_type'])->max('order') + 1;

        return response()->json(SurveyUnsur::create($data), 201);
    }

    /** PUT /api/survei/unsur/{unsur} */
    public function unsurUpdate(Request $request, SurveyUnsur $unsur): JsonResponse
    {
        abort_unless(SurveyUnsurResource::canEdit($unsur), 403);

        $unsur->update($this->unsurData($request, $unsur));

        return response()->json($unsur);
    }

    /** DELETE /api/survei/unsur/{unsur}: only an unsur without questions. */
    public function unsurDestroy(SurveyUnsur $unsur): JsonResponse
    {
        abort_unless(SurveyUnsurResource::canDelete($unsur), 403);

        if ($unsur->questions()->exists()) {
            return response()->json(['message' => 'Pindahkan atau hapus dulu pertanyaan pada unsur ini.'], 422);
        }

        $unsur->delete();

        return response()->json(null, 204);
    }

    /** GET /api/survei/pertanyaan?bagian=identity|skm|spak */
    public function questionIndex(Request $request): JsonResponse
    {
        abort_unless(SurveyQuestionResource::canViewAny(), 403);

        $type = $request->validate(['bagian' => ['nullable', Rule::in(array_keys(SurveyQuestionResource::TYPES))]])['bagian'] ?? null;

        return response()->json([
            'data' => SurveyQuestion::with('unsur:id,code,name')
                ->when($type, fn ($q) => $q->where('type', $type))
                ->orderBy('type')->orderBy('order')->orderBy('id')
                ->get(),
        ]);
    }

    /** POST /api/survei/pertanyaan */
    public function questionStore(Request $request): JsonResponse
    {
        abort_unless(SurveyQuestionResource::canCreate(), 403);

        $data = $this->questionData($request);
        // Without an order, the question goes last in its part.
        $data['order'] ??= (int) SurveyQuestion::where('type', $data['type'])->max('order') + 1;

        return response()->json(SurveyQuestion::create(SurveyQuestionResource::syncSurveyType($data)), 201);
    }

    /** PUT /api/survei/pertanyaan/{question} */
    public function questionUpdate(Request $request, SurveyQuestion $question): JsonResponse
    {
        abort_unless(SurveyQuestionResource::canEdit($question), 403);

        // A partial update keeps the question's part (syncSurveyType needs it).
        $question->update(SurveyQuestionResource::syncSurveyType($this->questionData($request, $question) + ['type' => $question->type]));

        return response()->json($question);
    }

    /** DELETE /api/survei/pertanyaan/{question}: answered questions are deactivated instead. */
    public function questionDestroy(SurveyQuestion $question): JsonResponse
    {
        abort_unless(SurveyQuestionResource::canDelete($question), 403);

        if ($question->answers()->exists()) {
            $question->update(['is_active' => false]);

            return response()->json(['message' => 'Pertanyaan sudah dijawab responden, jadi dinonaktifkan agar hasil survei lama tetap utuh.']);
        }

        $question->delete();

        return response()->json(null, 204);
    }

    private function unsurData(Request $request, ?SurveyUnsur $unsur = null): array
    {
        $required = $unsur ? 'sometimes' : 'required';

        return $request->validate([
            'survey_type' => [$required, Rule::in(array_keys(SurveyUnsurResource::TYPES))],
            'code' => [$required, 'string', 'max:50', Rule::unique('survey_unsur', 'code')->ignore($unsur?->id)],
            'name' => [$required, 'string', 'max:255'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function questionData(Request $request, ?SurveyQuestion $question = null): array
    {
        $required = $question ? 'sometimes' : 'required';
        $type = $request->input('type', $question?->type);
        $fieldType = $request->input('field_type', $question?->field_type);
        $rated = in_array($type, ['skm', 'spak'], true);

        return $request->validate([
            'type' => [$required, Rule::in(array_keys(SurveyQuestionResource::TYPES))],
            'unsur_id' => [Rule::requiredIf($rated && ! $question?->unsur_id), 'nullable', Rule::exists('survey_unsur', 'id')->where('survey_type', $type)],
            'question' => [$required, 'string', 'max:1000'],
            'field_type' => [$required, Rule::in(array_keys(SurveyQuestionResource::FIELD_TYPES))],
            'order' => ['sometimes', 'integer', 'min:0'],
            // Rating questions: worst (1) to best (4).
            'options' => [Rule::requiredIf(in_array($fieldType, ['radio', 'select'], true) && ! $question?->options), 'nullable', 'array', $rated ? 'size:4' : 'min:1'],
            'options.*' => ['string', 'max:255', 'distinct'],
            'is_required' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
