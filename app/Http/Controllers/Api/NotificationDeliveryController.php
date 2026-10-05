<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationDelivery;
use App\Services\NotificationDispatcher;
use App\Support\NotificationTemplates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Riwayat pengiriman notifikasi as JSON (admin), with resending of failed messages. */
class NotificationDeliveryController extends Controller
{
    /** GET /api/riwayat-notifikasi?kanal=&status=&pemicu=&q=&dari=&sampai=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $filters = $request->validate([
            'kanal' => ['nullable', Rule::in(array_keys(NotificationDelivery::CHANNELS))],
            'status' => ['nullable', Rule::in(array_keys(NotificationDelivery::STATUSES))],
            'pemicu' => ['nullable', Rule::in(array_keys(NotificationTemplates::EVENTS))],
            'q' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = NotificationDelivery::query()
            ->with('ticket:id,ticket_number')
            ->when($filters['kanal'] ?? null, fn (Builder $q, $v) => $q->where('channel', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v))
            ->when($filters['pemicu'] ?? null, fn (Builder $q, $v) => $q->where('event', $v))
            ->when($filters['dari'] ?? null, fn (Builder $q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['sampai'] ?? null, fn (Builder $q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['q'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $w) => $w
                ->where('recipient', 'ilike', "%{$v}%")
                ->orWhereHas('ticket', fn (Builder $t) => $t->where('ticket_number', 'ilike', "%{$v}%"))))
            ->latest('id')
            ->paginate($filters['per_halaman'] ?? 25)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (NotificationDelivery $delivery) => $this->present($delivery, false))->values(),
            'meta' => ['halaman' => $page->currentPage(), 'per_halaman' => $page->perPage(), 'total' => $page->total(), 'halaman_terakhir' => $page->lastPage()],
        ]);
    }

    /** GET /api/riwayat-notifikasi/{delivery}: one attempt, with the message body. */
    public function show(Request $request, NotificationDelivery $delivery): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        return response()->json($this->present($delivery->load('ticket:id,ticket_number'), true));
    }

    /** POST /api/riwayat-notifikasi/{delivery}/kirim-ulang: resend a failed email/WhatsApp message. */
    public function resend(Request $request, NotificationDelivery $delivery): JsonResponse
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        if ($delivery->status !== 'failed' || ! in_array($delivery->channel, ['email', 'whatsapp'], true)) {
            return response()->json(['message' => 'Hanya pesan Email/WhatsApp yang gagal yang dapat dikirim ulang.'], 422);
        }

        $sent = app(NotificationDispatcher::class)->deliver($delivery);
        activity('audit')->causedBy($request->user())->performedOn($delivery)->withProperties(['terkirim' => $sent])->log('Mengirim ulang notifikasi');

        return response()->json($this->present($delivery->fresh('ticket:id,ticket_number'), true), $sent ? 200 : 422);
    }

    private function present(NotificationDelivery $delivery, bool $withBody): array
    {
        return array_filter([
            'id' => $delivery->id,
            'waktu' => $delivery->created_at?->toIso8601String(),
            'pemicu' => $delivery->event,
            'pemicu_label' => NotificationTemplates::EVENTS[$delivery->event] ?? $delivery->event,
            'kanal' => $delivery->channel,
            'penerima' => $delivery->recipient,
            'tiket' => $delivery->ticket?->ticket_number,
            'status' => $delivery->status,
            'status_label' => NotificationDelivery::STATUSES[$delivery->status] ?? $delivery->status,
            'percobaan' => $delivery->attempts,
            'galat' => $delivery->error,
            'subjek' => $withBody ? $delivery->subject : null,
            'isi' => $withBody ? $delivery->body : null,
        ], fn ($value, $key) => $value !== null || in_array($key, ['tiket', 'galat'], true), ARRAY_FILTER_USE_BOTH);
    }
}
