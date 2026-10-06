<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rekam jejak aktivitas (activity_log) adalah bukti audit: perpindahan
 * peran, sesi ganti akun, dan aksi selama peran aktif hanya dapat ditambah,
 * tidak diubah atau dihapus. Sesi pemeliharaan (mis. retensi data) dapat
 * membuka kunci dengan: SET LOCAL app.allow_history_changes = 'on'.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION activity_log_immutable() RETURNS trigger AS $$
            BEGIN
                IF current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN COALESCE(NEW, OLD);
                END IF;

                RAISE EXCEPTION 'Rekam jejak aktivitas tidak dapat diubah atau dihapus (activity_log %).', OLD.id
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS activity_log_immutable ON activity_log;
            CREATE TRIGGER activity_log_immutable
                BEFORE UPDATE OR DELETE ON activity_log
                FOR EACH ROW EXECUTE FUNCTION activity_log_immutable();

            -- TRUNCATE skips row triggers; refuse it too.
            DROP TRIGGER IF EXISTS activity_log_no_truncate ON activity_log;
            CREATE TRIGGER activity_log_no_truncate
                BEFORE TRUNCATE ON activity_log
                FOR EACH STATEMENT EXECUTE FUNCTION activity_log_immutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS activity_log_no_truncate ON activity_log;
            DROP TRIGGER IF EXISTS activity_log_immutable ON activity_log;
            DROP FUNCTION IF EXISTS activity_log_immutable();
        SQL);
    }
};
