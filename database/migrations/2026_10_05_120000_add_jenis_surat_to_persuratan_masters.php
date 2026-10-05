<?php

use App\Support\Persuratan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Jenis surat becomes a Master Persuratan list like tujuan and tembusan. */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('persuratan_masters')->insertOrIgnore(collect(Persuratan::JENIS_SURAT)->values()->map(fn (string $name, int $i) => [
            'type' => 'jenis_surat',
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
        DB::table('persuratan_masters')->where('type', 'jenis_surat')->delete();
    }
};
