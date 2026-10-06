<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Images inserted or pasted into a TinyMCE field: staff only, images only,
 * stored on the public disk because the content they sit in is public.
 */
class EditorUploadController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless((bool) $request->user()?->isStaff(), 403);

        $request->validate(
            ['file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:' . config('tinymce.uploads.max_kb', 2048)]],
            [
                'file.image' => 'Berkas harus berupa gambar.',
                'file.mimes' => 'Gambar harus berformat JPG, PNG, GIF, atau WebP.',
                'file.max' => 'Ukuran gambar maksimal ' . (int) (config('tinymce.uploads.max_kb', 2048) / 1024) . ' MB.',
            ],
        );

        $disk = config('tinymce.uploads.disk', 'public');
        $path = $request->file('file')->store(config('tinymce.uploads.directory', 'editor-uploads') . '/' . now()->format('Y/m'), $disk);

        // Root-relative: the URL is stored inside the content, so it must keep
        // working on any domain or port the site is opened from.
        return response()->json(['location' => '/storage/' . ltrim($path, '/')]);
    }
}
