<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * IP terblokir disimpan di basis data. Sebelumnya hanya di cache, sehingga
 * "Bersihkan cache" atau deploy menghapus semua blokir. Blokir yang masih
 * ada di cache dipindahkan ke tabel ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->unique();
            $table->string('reason', 255);
            $table->string('blocked_by', 255)->nullable();
            $table->timestamp('blocked_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('expires_at');
        });

        try {
            $cached = Cache::get('blocked_ips', []);
        } catch (Throwable) {
            $cached = [];
        }

        foreach (is_array($cached) ? $cached : [] as $ip => $data) {
            $expires = ! empty($data['expires_at']) ? Carbon::parse($data['expires_at']) : null;
            if ($expires?->isPast()) {
                continue;
            }

            DB::table('blocked_ips')->insertOrIgnore([
                'ip' => mb_substr((string) $ip, 0, 45),
                'reason' => mb_substr((string) ($data['reason'] ?? 'Diblokir'), 0, 255),
                'blocked_by' => $data['blocked_by'] ?? null,
                'blocked_at' => $data['blocked_at'] ?? now(),
                'expires_at' => $expires,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
