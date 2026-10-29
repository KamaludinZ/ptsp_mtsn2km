<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Master Jenis SK rules kept by the database too. Within the jenis_sk list
 * a name is stored once ignoring case (the form and API compare
 * lower(trim(nama))), and it is never padded or shorter than 3 characters.
 * The other Master Persuratan lists keep their plain (type, nama) unique.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS persuratan_masters_jenis_sk_nama_unique ON persuratan_masters (lower(btrim(nama))) WHERE type = 'jenis_sk'");
        DB::statement("ALTER TABLE persuratan_masters ADD CONSTRAINT persuratan_masters_jenis_sk_nama_check CHECK (type <> 'jenis_sk' OR (nama = btrim(nama) AND char_length(nama) >= 3))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE persuratan_masters DROP CONSTRAINT IF EXISTS persuratan_masters_jenis_sk_nama_check');
        DB::statement('DROP INDEX IF EXISTS persuratan_masters_jenis_sk_nama_unique');
    }
};
