<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Log sesi ganti akun sementara (impersonation): administrator mana memakai
 * akun siapa, mengapa, dari mana, kapan mulai dan berakhir. Bukti audit:
 * baris tidak dapat dihapus, dan satu-satunya perubahan yang diterima adalah
 * menutup sesi yang masih terbuka (ended_at, end_reason) sekali saja. Nama
 * disimpan sebagai salinan agar log tetap terbaca bila akun dihapus.
 * Sesi pemeliharaan dapat membuka kunci dengan:
 * SET LOCAL app.allow_history_changes = 'on'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impersonation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('admin_name');
            $table->foreignId('target_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('target_name');
            $table->string('target_role', 125)->nullable();
            $table->text('reason');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 200)->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->string('end_reason', 20)->nullable(); // selesai | keluar | kedaluwarsa

            $table->index(['admin_id', 'started_at']);
            $table->index(['target_id', 'started_at']);
            $table->index('ended_at');
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION impersonation_sessions_immutable() RETURNS trigger AS $$
            BEGIN
                IF current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN COALESCE(NEW, OLD);
                END IF;

                -- Closing an open session once; ON DELETE SET NULL on the user columns.
                IF TG_OP = 'UPDATE'
                    AND (to_jsonb(NEW) - 'ended_at' - 'end_reason' - 'admin_id' - 'target_id')
                        = (to_jsonb(OLD) - 'ended_at' - 'end_reason' - 'admin_id' - 'target_id')
                    AND (NEW.admin_id IS NOT DISTINCT FROM OLD.admin_id OR NEW.admin_id IS NULL)
                    AND (NEW.target_id IS NOT DISTINCT FROM OLD.target_id OR NEW.target_id IS NULL)
                    AND (
                        (NEW.ended_at IS NOT DISTINCT FROM OLD.ended_at AND NEW.end_reason IS NOT DISTINCT FROM OLD.end_reason)
                        OR (OLD.ended_at IS NULL AND NEW.ended_at IS NOT NULL)
                    )
                THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'Log sesi ganti akun tidak dapat diubah atau dihapus (impersonation_sessions %).', OLD.id
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER impersonation_sessions_immutable
                BEFORE UPDATE OR DELETE ON impersonation_sessions
                FOR EACH ROW EXECUTE FUNCTION impersonation_sessions_immutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS impersonation_sessions_immutable ON impersonation_sessions; DROP FUNCTION IF EXISTS impersonation_sessions_immutable();');
        Schema::dropIfExists('impersonation_sessions');
    }
};
