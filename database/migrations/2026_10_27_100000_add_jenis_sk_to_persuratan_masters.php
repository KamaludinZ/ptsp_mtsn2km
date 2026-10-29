<?php

use App\Support\Persuratan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Jenis SK becomes a Master Persuratan list (tab "Jenis SK"), the
 * classifications of the SK register. The rows share persuratan_masters:
 * type, nama, is_active and sort, unique per (type, nama).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('persuratan_masters')->insertOrIgnore(collect(Persuratan::JENIS_SK)->values()->map(fn (string $name, int $i) => [
            'type' => 'jenis_sk',
            'kode' => null,
            'nama' => $name,
            'is_active' => true,
            'sort' => $i,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }

    public function down(): void
    {
        DB::table('persuratan_masters')->where('type', 'jenis_sk')->delete();
    }
};
