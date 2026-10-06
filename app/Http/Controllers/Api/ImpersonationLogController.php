<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ImpersonationSession;
use App\Support\ActiveRoles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Log ganti akun sementara: hanya untuk administrator (peran aktif). */
class ImpersonationLogController extends Controller
{
    /** GET /api/ganti-akun/log?admin=&akun=&cari=&status=berjalan|selesai&cara=&dari=&sampai=&halaman= */
    public function index(Request $request): JsonResponse
    {
        abort_unless(ActiveRoles::hasRole($request->user(), 'admin'), 403, 'Log ganti akun hanya untuk peran aktif Administrator.');

        $filters = $request->validate([
            'admin' => ['nullable', 'integer'],
            'akun' => ['nullable', 'integer'],
            'cari' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['berjalan', 'selesai'])],
            'cara' => ['nullable', Rule::in(array_keys(ImpersonationSession::END_REASONS))],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $page = ImpersonationSession::query()->filtered($filters)
            ->latest('started_at')->latest('id')
            ->paginate(20, ['*'], 'halaman');

        return response()->json([
            'data' => collect($page->items())->map(fn (ImpersonationSession $s) => [
                'id' => $s->id,
                'admin' => ['id' => $s->admin_id, 'nama' => $s->admin_name],
                'akun_dipakai' => ['id' => $s->target_id, 'nama' => $s->target_name, 'peran' => $s->target_role],
                'alasan' => $s->reason,
                'mulai' => $s->started_at?->toIso8601String(),
                'selesai' => $s->ended_at?->toIso8601String(),
                'berjalan' => $s->isOpen(),
                'cara_berakhir' => $s->end_reason,
                'cara_berakhir_label' => $s->end_reason ? (ImpersonationSession::END_REASONS[$s->end_reason] ?? $s->end_reason) : null,
                'durasi_menit' => $s->ended_at ? (int) $s->started_at->diffInMinutes($s->ended_at) : null,
                'ip' => $s->ip_address,
            ]),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }
}
