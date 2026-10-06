<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeroSlider;
use Illuminate\Http\JsonResponse;

/** Hero slider beranda for public clients: the active slides, in order. */
class HeroSliderController extends Controller
{
    /** GET /api/publik/slider */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => HeroSlider::active()->get()->map(fn (HeroSlider $slide) => [
                'id' => $slide->id,
                'judul' => $slide->title,
                'subjudul' => $slide->subtitle,
                'deskripsi' => $slide->description,
                'gambar' => $slide->image ? url($slide->imageUrl()) : null,
                'tombol' => collect([[$slide->button1_text, $slide->button1_url], [$slide->button2_text, $slide->button2_url]])
                    ->filter(fn (array $button) => filled($button[0]) && filled($button[1]))
                    ->map(fn (array $button) => ['teks' => $button[0], 'tautan' => str_starts_with($button[1], '/') ? url($button[1]) : $button[1]])
                    ->values(),
                'warna_teks' => $slide->text_color,
                'warna_lapisan' => $slide->overlay_color,
            ])->values(),
        ])->setPublic()->setMaxAge(300);
    }
}
