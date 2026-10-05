<?php

namespace App\Http\Controllers\Api;

use App\Filament\Pages\Services\IncomingServices;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Support\IncomingCategory;
use App\Support\ProcessorRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Layanan masuk berkategori for the back office: open requests that reached
 * the units, filtered by category, with the number per category.
 */
class IncomingServiceController extends Controller
{
    /** GET /api/layanan-masuk?kategori=&unit_saya=&layanan=&q=&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->can('backoffice.access'), 403);

        $filters = $request->validate([
            'kategori' => ['nullable', Rule::in(array_keys(IncomingCategory::CATEGORIES))],
            'unit_saya' => ['nullable', 'boolean'],
            'layanan' => ['nullable', 'integer'],
            'q' => ['nullable', 'string', 'max:100'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        // Like the page: holders of a unit role start with their own units.
        $units = ProcessorRoles::unitsOf($user);
        $mine = $units && ($filters['unit_saya'] ?? true);

        $base = fn () => IncomingServices::incomingQuery()
            ->when($mine, fn (Builder $q) => ProcessorRoles::scopeTicketsFor($q, $units))
            ->when($filters['layanan'] ?? null, fn (Builder $q, int $service) => $q->where('service_id', $service))
            ->search($filters['q'] ?? null);

        $counts = $base()->toBase()
            ->selectRaw(IncomingCategory::EFFECTIVE_SQL . ' as category, count(*) as total')
            ->groupByRaw(IncomingCategory::EFFECTIVE_SQL)
            ->pluck('total', 'category');

        $page = $base()
            ->with(['service:id,name', 'user:id,name,whatsapp_number', 'assignedTo:id,name', 'approver:id,name', 'latestStatusHistory'])
            ->when($filters['kategori'] ?? null, fn (Builder $q, string $category) => $q->incomingCategory($category))
            ->orderByDesc('approved_at')->latest('id')
            ->paginate($filters['per_halaman'] ?? 20)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (Ticket $ticket) => StaffTicketController::item($ticket) + [
                'instruksi_pimpinan' => $ticket->approval_notes,
                'didisposisi_oleh' => $ticket->approver?->name,
                'masuk' => ($ticket->approved_at ?? $ticket->created_at)?->toIso8601String(),
            ]),
            'kategori' => collect(IncomingCategory::CATEGORIES)->map(fn (string $label, string $key) => [
                'kunci' => $key,
                'label' => $label,
                'keterangan' => IncomingCategory::DESCRIPTIONS[$key],
                'jumlah' => (int) ($counts[$key] ?? 0),
            ])->values(),
            'jumlah_semua' => (int) $counts->sum(),
            'unit_saya' => $mine ? $units : null,
            'meta' => [
                'halaman' => $page->currentPage(),
                'per_halaman' => $page->perPage(),
                'total' => $page->total(),
                'halaman_terakhir' => $page->lastPage(),
            ],
        ]);
    }
}
