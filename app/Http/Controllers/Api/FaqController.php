<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * FAQ for administrators (Manajemen Konten): every question, shown or not,
 * in display order; the public list is /api/publik/faq.
 */
class FaqController extends Controller
{
    /** GET /api/faq?kelompok=&aktif=&q= : the whole list in display order (no paging; FAQs are short). */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Faq::class);

        $filters = $request->validate([
            'kelompok' => ['nullable', 'string', 'max:50'],
            'aktif' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $faqs = Faq::query()
            ->when(isset($filters['kelompok']), fn ($q) => $q->inCategory($filters['kelompok'] === 'umum' ? null : Str::lower($filters['kelompok'])))
            ->when(isset($filters['aktif']), fn ($q) => $q->where('is_active', (bool) $filters['aktif']))
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $like = '%' . addcslashes($term, '%_\\') . '%';
                $query->where(fn ($q) => $q->where('question', 'ilike', $like)->orWhere('answer', 'ilike', $like));
            })
            ->orderByRaw('category is null')->orderBy('category')->orderBy('sort')->orderBy('id')
            ->get();

        return response()->json([
            'data' => $faqs->map(fn (Faq $faq) => $this->present($faq)),
            'total' => $faqs->count(),
            'kelompok' => Faq::categories(),
        ]);
    }

    /** GET /api/faq/{faq} */
    public function show(Faq $faq): JsonResponse
    {
        $this->authorize('view', $faq);

        return response()->json($this->present($faq));
    }

    /** POST /api/faq: goes to the end of the list unless an urutan is given. */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Faq::class);

        $data = $this->validated($request);
        $data['sort'] ??= Faq::nextSort();
        $data['is_active'] ??= true;

        return response()->json($this->present(Faq::create($data)), 201);
    }

    /** PATCH /api/faq/{faq}: only the fields sent change. */
    public function update(Request $request, Faq $faq): JsonResponse
    {
        $this->authorize('update', $faq);

        $faq->update($this->validated($request, $faq));

        return response()->json($this->present($faq));
    }

    /** DELETE /api/faq/{faq} */
    public function destroy(Faq $faq): JsonResponse
    {
        $this->authorize('delete', $faq);

        $faq->delete();

        return response()->json(null, 204);
    }

    /**
     * PUT /api/faq/urutan {"urutan": [id, id, …]}: the new order, e.g. after
     * dragging rows. The ids listed get positions 1..n in that order; any not
     * listed keep their relative order after them.
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('reorder', Faq::class);

        $ids = $request->validate([
            'urutan' => ['required', 'array', 'min:1', 'max:500'],
            'urutan.*' => ['integer', 'distinct', Rule::exists('faqs', 'id')],
        ], ['urutan.*.exists' => 'Pertanyaan #:input tidak ditemukan.', 'urutan.*.distinct' => 'Ada pertanyaan yang disebut dua kali.'])['urutan'];

        DB::transaction(function () use ($ids) {
            $rest = Faq::query()->whereNotIn('id', $ids)->orderBy('sort')->orderBy('id')->lockForUpdate()->pluck('id')->all();
            foreach ([...$ids, ...$rest] as $position => $id) {
                Faq::query()->whereKey($id)->update(['sort' => $position + 1]);
            }
        });

        return $this->index(new Request());
    }

    /** POST /api/faq/{faq}/naik or /turun: swap with the neighbour in the same group. */
    public function move(Faq $faq, string $direction): JsonResponse
    {
        $this->authorize('reorder', Faq::class);

        DB::transaction(function () use ($faq, $direction) {
            // Renumber in display order first, so ties from older data can't block the swap
            $ordered = Faq::query()->orderByRaw('category is null')->orderBy('category')->orderBy('sort')->orderBy('id')
                ->lockForUpdate()->get(['id', 'category']);
            foreach ($ordered->pluck('id') as $position => $id) {
                Faq::query()->whereKey($id)->update(['sort' => $position + 1]);
            }

            $group = $ordered->filter(fn (Faq $f) => $f->category === $faq->category)->pluck('id')->values();
            $index = $group->search($faq->id);
            $neighbour = $group->get($direction === 'naik' ? $index - 1 : $index + 1);

            abort_if($neighbour === null, 422,
                $direction === 'naik' ? 'Pertanyaan ini sudah paling atas.' : 'Pertanyaan ini sudah paling bawah.');

            $mine = Faq::query()->whereKey($faq->id)->value('sort');
            $theirs = Faq::query()->whereKey($neighbour)->value('sort');
            Faq::query()->whereKey($faq->id)->update(['sort' => $theirs]);
            Faq::query()->whereKey($neighbour)->update(['sort' => $mine]);
        });

        return response()->json($this->present($faq->fresh()));
    }

    private function validated(Request $request, ?Faq $faq = null): array
    {
        $required = $faq ? 'sometimes' : 'required';

        $data = $request->validate([
            'pertanyaan' => [$required, 'string', 'max:500', 'regex:/\S/'],
            'jawaban' => [$required, 'string', 'max:20000', 'regex:/\S/'],
            'kelompok' => ['sometimes', 'nullable', 'string', 'max:50'],
            'urutan' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'aktif' => ['sometimes', 'boolean'],
        ], [
            'pertanyaan.required' => 'Pertanyaan tidak boleh kosong.', 'pertanyaan.regex' => 'Pertanyaan tidak boleh kosong.',
            'jawaban.required' => 'Jawaban tidak boleh kosong.', 'jawaban.regex' => 'Jawaban tidak boleh kosong.',
        ]);

        $values = [];
        if (array_key_exists('pertanyaan', $data)) {
            $values['question'] = trim($data['pertanyaan']);
        }
        if (array_key_exists('jawaban', $data)) {
            $values['answer'] = (string) str($data['jawaban'])->sanitizeHtml();
            if (blank(trim(strip_tags($values['answer'])))) {
                throw \Illuminate\Validation\ValidationException::withMessages(['jawaban' => 'Jawaban tidak boleh kosong.']);
            }
        }
        if (array_key_exists('kelompok', $data)) {
            $values['category'] = filled($data['kelompok']) ? Str::lower(trim($data['kelompok'])) : null;
        }
        if (array_key_exists('urutan', $data)) {
            $values['sort'] = $data['urutan'];
        }
        if (array_key_exists('aktif', $data)) {
            $values['is_active'] = $data['aktif'];
        }

        return $values;
    }

    private function present(Faq $faq): array
    {
        return [
            'id' => $faq->id,
            'pertanyaan' => $faq->question,
            'jawaban' => $faq->answer,
            'kelompok' => $faq->category,
            'kelompok_label' => $faq->categoryLabel(),
            'urutan' => $faq->sort,
            'aktif' => $faq->is_active,
            'diperbarui' => $faq->updated_at?->toIso8601String(),
        ];
    }
}
