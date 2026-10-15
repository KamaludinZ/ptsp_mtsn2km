<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Foto wajah tamu tidak lagi di disk publik (/storage/visitor-photos/…):
 * dipindah ke disk privat dan hanya ditampilkan ke petugas lewat
 * route visitors.photo. Jalur di database tetap sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        DB::table('visitors')->whereNotNull('photo_path')->orderBy('id')->select(['id', 'photo_path'])
            ->each(function ($visitor) use ($public, $private) {
                $path = $visitor->photo_path;
                if ($private->exists($path) || ! $public->exists($path)) {
                    return;
                }

                $private->writeStream($path, $public->readStream($path));
                $public->delete($path);
            });
    }

    public function down(): void
    {
        // Photos stay private.
    }
};
