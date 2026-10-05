<?php

namespace App\Http\Controllers\Api;

use App\Filament\Resources\NotificationTemplateResource;
use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Models\NotificationTemplate;
use App\Models\NotificationTemplateRevision;
use App\Support\NotificationTemplates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Template notifikasi (admin): wording per event and channel. */
class NotificationTemplateController extends Controller
{
    /** GET /api/template-notifikasi?kanal=&notifikasi=&aktif= */
    public function index(Request $request): JsonResponse
    {
        abort_unless(NotificationTemplateResource::canViewAny(), 403);

        $filters = $request->validate([
            'kanal' => ['nullable', Rule::in(array_keys(NotificationSetting::CHANNELS))],
            'notifikasi' => ['nullable', Rule::in(array_keys(NotificationTemplates::EVENTS))],
            'aktif' => ['nullable', 'boolean'],
        ]);

        return response()->json([
            'placeholder' => NotificationTemplates::PLACEHOLDERS,
            'data' => NotificationTemplate::query()
                ->when($filters['kanal'] ?? null, fn ($q, $channel) => $q->where('channel', $channel))
                ->when($filters['notifikasi'] ?? null, fn ($q, $key) => $q->where('key', $key))
                ->when(isset($filters['aktif']), fn ($q) => $q->where('is_active', (bool) $filters['aktif']))
                ->orderBy('key')->orderBy('channel')
                ->get()
                ->map(fn (NotificationTemplate $template) => $this->present($template)),
        ]);
    }

    /** GET /api/template-notifikasi/{template} */
    public function show(NotificationTemplate $template): JsonResponse
    {
        abort_unless(NotificationTemplateResource::canView($template), 403);

        return response()->json($this->present($template) + [
            'pratinjau' => [
                'subjek' => $template->subject ? NotificationTemplates::render($template->subject, NotificationTemplateResource::SAMPLE) : null,
                'isi' => NotificationTemplates::render($template->body, NotificationTemplateResource::SAMPLE),
            ],
            'riwayat' => $template->revisions()->with('author:id,name')->limit(20)->get()->map(fn (NotificationTemplateRevision $revision) => [
                'id' => $revision->id,
                'waktu' => $revision->created_at?->toIso8601String(),
                'oleh' => $revision->author?->name,
                'subjek' => $revision->subject,
                'isi' => $revision->body,
                'aktif' => $revision->is_active,
            ]),
        ]);
    }

    /** PUT /api/template-notifikasi/{template}: subjek (email), isi */
    public function update(Request $request, NotificationTemplate $template): JsonResponse
    {
        abort_unless(NotificationTemplateResource::canEdit($template), 403);

        $data = $request->validate([
            'subjek' => [Rule::requiredIf($template->channel === 'email'), 'nullable', 'string', 'max:255', NotificationTemplates::placeholderRule()],
            'isi' => ['required', 'string', 'max:4000', NotificationTemplates::placeholderRule()],
        ]);

        $template->update([
            'subject' => $template->channel === 'email' ? $data['subjek'] : null,
            'body' => $data['isi'],
            'updated_by' => $request->user()->id,
        ]);

        return $this->show($template->fresh());
    }

    /** PATCH /api/template-notifikasi/{template}/aktif {aktif: bool}: send this notification or not. */
    public function toggle(Request $request, NotificationTemplate $template): JsonResponse
    {
        abort_unless(NotificationTemplateResource::canEdit($template), 403);

        $template->update([
            'is_active' => (bool) $request->validate(['aktif' => ['required', 'boolean']])['aktif'],
            'updated_by' => $request->user()->id,
        ]);

        return response()->json($this->present($template));
    }

    private function present(NotificationTemplate $template): array
    {
        return [
            'id' => $template->id,
            'notifikasi' => $template->key,
            'notifikasi_label' => NotificationTemplates::EVENTS[$template->key] ?? $template->key,
            'kanal' => $template->channel,
            'subjek' => $template->subject,
            'isi' => $template->body,
            'aktif' => $template->is_active,
            'kanal_aktif' => NotificationSetting::for($template->channel)->is_enabled,
            'diperbarui' => $template->updated_at?->toIso8601String(),
        ];
    }
}
