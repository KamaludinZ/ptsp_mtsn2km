<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make the brand name editable in the admin panel even on installs
        // that never ran AppSettingsSeeder. An existing value is kept.
        if (! DB::table('app_settings')->where('key', 'app_name')->exists()) {
            DB::table('app_settings')->insert([
                'key' => 'app_name',
                'value' => 'PTSP MTsN 2 KOTA MALANG',
                'type' => 'text',
                'category' => 'branding',
                'display_name' => 'Nama Aplikasi',
                'description' => 'Nama yang tampil di header, footer dan judul halaman publik (satu baris).',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // One row per unique visitor per day. visitor_hash is a salted,
        // day-scoped hash, so no IP address or user agent is stored and the
        // same person cannot be followed from one day to the next.
        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->date('visited_on');
            $table->string('visitor_hash', 64);
            $table->timestamp('created_at')->nullable();

            $table->unique(['visited_on', 'visitor_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_visits');
    }
};
