<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ActiveRoles;
use App\Support\ActivityTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;

/** Rekam jejak aktivitas untuk administrator (peran aktif): perpindahan peran, ganti akun, aksi tiket. */
class ActivityTrailController extends Controller
{
    /** GET /api/rekam-jejak?pengguna=&peran=&jenis=role_switch|impersonation|ticket&aksi=&dari=&sampai=&halaman= */
    public function index(Request $request): JsonResponse
    {
        abort_unless(ActiveRoles::hasRole($request->user(), 'admin'), 403, 'Rekam jejak hanya untuk peran aktif Administrator.');

        $filters = $request->validate([
            'pengguna' => ['nullable', 'integer'],
            'peran' => ['nullable', 'string', 'max:125'],
            'jenis' => ['nullable', Rule::in(array_values(ActivityTrail::LOGS))],
            'aksi' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $page = ActivityTrail::query($filters)->paginate(25, ['*'], 'halaman');

        return response()->json([
            'data' => collect($page->items())->map(function (Activity $activity) {
                $row = ActivityTrail::present($activity);

                return [
                    'id' => $row['id'],
                    'waktu' => $row['at']?->toIso8601String(),
                    'jenis' => $row['type'],
                    'kegiatan' => $row['event'],
                    'pengguna' => ['id' => $row['user_id'], 'nama' => $row['user']],
                    'peran_aktif' => $row['acting_role_key'],
                    'peran_aktif_label' => $row['acting_role'],
                    'keterangan' => $row['description'],
                    'peran_asal' => $row['from_role'],
                    'peran_tujuan' => $row['to_role'],
                    'tiket' => $row['ticket'],
                    'layanan' => $row['service'],
                    'lewat_ganti_akun_oleh' => $row['impersonated_by'],
                    'ip' => $row['ip'],
                    'detail' => $row['details'],
                ];
            }),
            'halaman' => $page->currentPage(),
            'total' => $page->total(),
            'halaman_terakhir' => $page->lastPage(),
        ]);
    }
}
