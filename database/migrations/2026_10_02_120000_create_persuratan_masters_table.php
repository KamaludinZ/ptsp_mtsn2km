<?php

use App\Support\Persuratan;
use App\Support\ServiceDisposition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Master persuratan: the routine choices offered in letter and disposition
 * forms (tujuan naskah, tembusan, klasifikasi, instruksi disposisi). Seeded
 * with the defaults the forms used before this table existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persuratan_masters', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('kode', 50)->nullable();
            $table->string('nama');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['type', 'nama']);
            $table->index(['type', 'is_active', 'sort']);
        });

        $now = now();
        $rows = [];
        $add = function (string $type, array $items) use (&$rows, $now) {
            foreach (array_values($items) as $i => [$kode, $nama]) {
                $rows[] = ['type' => $type, 'kode' => $kode, 'nama' => $nama, 'is_active' => true, 'sort' => $i, 'created_at' => $now, 'updated_at' => $now];
            }
        };

        $add('tujuan_naskah', array_map(fn ($nama) => [null, $nama], Persuratan::TUJUAN_NASKAH));
        $add('tembusan', array_map(fn ($nama) => [null, $nama], Persuratan::TEMBUSAN));
        $add('klasifikasi', array_map(fn ($kode, $label) => [$kode, trim(explode('—', $label)[1] ?? $label)], array_keys(Persuratan::KLASIFIKASI), Persuratan::KLASIFIKASI));
        $add('instruksi_disposisi', array_map(fn ($nama) => [null, $nama], ServiceDisposition::INSTRUCTIONS));

        DB::table('persuratan_masters')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('persuratan_masters');
    }
};
