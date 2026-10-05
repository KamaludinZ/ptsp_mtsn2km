<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\DisplayPreferences;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Preferensi tampilan of the signed-in user (staff or applicant): theme mode
 * and text size, the same values the profile page saves and both panels apply.
 */
class DisplayPreferenceController extends Controller
{
    /** GET /api/preferensi-tampilan */
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->present($request));
    }

    /** PATCH /api/preferensi-tampilan {"mode": "dark", "ukuran": "large"}: either or both. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode' => ['sometimes', 'required', Rule::in(array_keys(DisplayPreferences::MODES))],
            'ukuran' => ['sometimes', 'required', Rule::in(array_keys(DisplayPreferences::SIZES))],
        ], [
            'mode.in' => 'Mode tampilan: ' . implode(', ', array_keys(DisplayPreferences::MODES)) . '.',
            'ukuran.in' => 'Ukuran teks: ' . implode(', ', array_keys(DisplayPreferences::SIZES)) . '.',
        ]);
        abort_if($data === [], 422, 'Kirim mode dan/atau ukuran.');

        $user = $request->user();
        $current = DisplayPreferences::for($user);
        DisplayPreferences::save($user, $data['mode'] ?? $current['mode'], $data['ukuran'] ?? $current['size']);

        return response()->json(['message' => 'Preferensi tampilan disimpan.'] + $this->present($request));
    }

    private function present(Request $request): array
    {
        $p = DisplayPreferences::for($request->user()->fresh());

        return [
            'mode' => $p['mode'],
            'mode_label' => DisplayPreferences::MODES[$p['mode']],
            'ukuran' => $p['size'],
            'ukuran_label' => DisplayPreferences::SIZES[$p['size']],
            'ukuran_persen' => DisplayPreferences::SIZE_PERCENT[$p['size']],
            'diperbarui' => $p['updated_at'] ? now()->setTimestamp($p['updated_at'])->toIso8601String() : null,
            'pilihan' => [
                'mode' => collect(DisplayPreferences::MODES)->map(fn ($label, $value) => ['nilai' => $value, 'label' => $label])->values(),
                'ukuran' => collect(DisplayPreferences::SIZES)->map(fn ($label, $value) => ['nilai' => $value, 'label' => $label, 'persen' => DisplayPreferences::SIZE_PERCENT[$value]])->values(),
            ],
        ];
    }
}
