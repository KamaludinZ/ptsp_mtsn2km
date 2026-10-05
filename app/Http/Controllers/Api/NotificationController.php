<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Support\TicketNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Notifikasi in-app through the API: the signed-in user's own
 * notifications (the same as the bell and the Notifikasi page).
 */
class NotificationController extends Controller
{
    /** GET /api/notifikasi?status=semua|belum-dibaca&tiket=&q=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $filters = $request->validate([
            'status' => ['nullable', 'in:semua,belum-dibaca,dibaca'],
            'tiket' => ['nullable', 'string', 'max:50'],
            'q' => ['nullable', 'string', 'max:100'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $page = $user->notifications()
            ->with('ticket:id,ticket_number,status')
            ->when(($filters['status'] ?? null) === 'belum-dibaca', fn (Builder $q) => $q->whereNull('read_at'))
            ->when(($filters['status'] ?? null) === 'dibaca', fn (Builder $q) => $q->whereNotNull('read_at'))
            ->when($filters['tiket'] ?? null, fn (Builder $q, string $number) => $q->whereHas('ticket', fn ($t) => $t->where('ticket_number', $number)))
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->whereRaw('lower(data::text) like ?', ['%' . mb_strtolower($term) . '%']))
            ->latest('created_at')
            ->paginate($filters['per_halaman'] ?? 20)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Notification $n) => self::item($n, $user)),
            'belum_dibaca' => $user->unreadNotifications()->count(),
            'meta' => ['halaman' => $page->currentPage(), 'per_halaman' => $page->perPage(), 'total' => $page->total(), 'halaman_terakhir' => $page->lastPage()],
        ]);
    }

    /** GET /api/notifikasi/jumlah: the bell badge. */
    public function count(Request $request): JsonResponse
    {
        return response()->json(['belum_dibaca' => $request->user()->unreadNotifications()->count()]);
    }

    /** PATCH /api/notifikasi/{id}/dibaca {dibaca?: bool} — read (default) or back to unread. */
    public function markOne(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail(); // only one's own
        $request->validate(['dibaca' => ['sometimes', 'boolean']]);
        $read = $request->has('dibaca') ? $request->boolean('dibaca') : true;

        $read ? $notification->markAsRead() : $notification->markAsUnread();

        return response()->json([
            'data' => self::item($notification->fresh('ticket'), $request->user()),
            'belum_dibaca' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * POST /api/notifikasi/dibaca {id?: string[], tiket?: string}: mark several,
     * all of one request, or (without either) every unread notification read.
     */
    public function markMany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['sometimes', 'array', 'max:200'],
            'id.*' => ['uuid'],
            'tiket' => ['sometimes', 'string', 'max:50'],
        ]);

        $updated = $request->user()->unreadNotifications()
            ->when($data['id'] ?? null, fn (Builder $q, array $ids) => $q->whereKey($ids))
            ->when($data['tiket'] ?? null, fn (Builder $q, string $number) => $q->whereHas('ticket', fn ($t) => $t->where('ticket_number', $number)))
            ->update(['read_at' => now()]);

        return response()->json([
            'ditandai' => $updated,
            'belum_dibaca' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public static function item(Notification $notification, $reader): array
    {
        return [
            'id' => $notification->id,
            'judul' => $notification->title(),
            'isi' => $notification->body(),
            'ikon' => $notification->data['icon'] ?? null,
            'warna' => $notification->data['iconColor'] ?? null,
            'dibaca' => $notification->read_at !== null,
            'dibaca_pada' => $notification->read_at?->toIso8601String(),
            'waktu' => $notification->created_at?->toIso8601String(),
            'tiket' => $notification->ticket ? ['nomor' => $notification->ticket->ticket_number, 'status' => $notification->ticket->status] : null,
            'tautan' => TicketNotification::urlOf($notification, $reader),
        ];
    }
}
