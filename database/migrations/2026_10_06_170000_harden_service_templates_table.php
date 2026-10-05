<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Template berkas layanan: one name per service (enforced by the database
 * too), ordered per service, and the file's type and size stored with it
 * so the download lists need not read the disk. Existing rows are
 * backfilled; duplicate names get a number before the unique index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->string('mime_type', 150)->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
        });

        $seen = [];
        foreach (DB::table('service_templates')->orderBy('id')->get() as $row) {
            $name = $row->nama;
            for ($i = 2; isset($seen[$row->service_id][mb_strtolower($name)]); $i++) {
                $name = "{$row->nama} ({$i})";
            }
            $seen[$row->service_id][mb_strtolower($name)] = true;

            $disk = Storage::disk('local');
            $exists = $row->file_path && $disk->exists($row->file_path);
            DB::table('service_templates')->where('id', $row->id)->update([
                'nama' => $name,
                'mime_type' => $exists ? $disk->mimeType($row->file_path) : null,
                'file_size' => $exists ? $disk->size($row->file_path) : null,
            ]);
        }

        Schema::table('service_templates', function (Blueprint $table) {
            $table->unique(['service_id', 'nama']);
            $table->index(['service_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::table('service_templates', function (Blueprint $table) {
            $table->dropUnique(['service_id', 'nama']);
            $table->dropIndex(['service_id', 'sort']);
            $table->dropColumn(['mime_type', 'file_size']);
        });
    }
};
