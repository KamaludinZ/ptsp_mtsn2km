<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Register Penomoran SK as evidence, like activity_log: a number once given
 * keeps its tahun, nomor urut and nomor SK, and an SK leaves the register
 * only through "Hapus" (SuratKeputusanService::delete, which logs the reason)
 * or SuratKeputusan::withDeletesAllowed() for retention. Plus the indexes
 * the SK trail is read with: riwayat nomor SK and SK dihapus per tahun.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION surat_keputusan_guard() RETURNS trigger AS $$
            BEGIN
                IF TG_OP = 'UPDATE' THEN
                    IF NEW.tahun IS DISTINCT FROM OLD.tahun
                        OR NEW.nomor_urut IS DISTINCT FROM OLD.nomor_urut
                        OR NEW.nomor_sk IS DISTINCT FROM OLD.nomor_sk THEN
                        RAISE EXCEPTION 'Nomor SK yang sudah terbit tidak dapat diubah (%).', OLD.nomor_sk
                            USING ERRCODE = 'insufficient_privilege';
                    END IF;

                    RETURN NEW;
                END IF;

                IF current_setting('app.allow_sk_delete', true) = 'on'
                    OR current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN OLD;
                END IF;

                RAISE EXCEPTION 'SK hanya dapat dihapus lewat "Hapus" di register; alasannya dicatat di Log & Audit.'
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS surat_keputusan_guard ON surat_keputusan;
            CREATE TRIGGER surat_keputusan_guard
                BEFORE UPDATE OR DELETE ON surat_keputusan
                FOR EACH ROW EXECUTE FUNCTION surat_keputusan_guard();

            -- TRUNCATE skips row triggers; refuse it the same way.
            DROP TRIGGER IF EXISTS surat_keputusan_no_truncate ON surat_keputusan;
            CREATE TRIGGER surat_keputusan_no_truncate
                BEFORE TRUNCATE ON surat_keputusan
                FOR EACH STATEMENT EXECUTE FUNCTION surat_keputusan_guard();
        SQL);

        // Riwayat nomor SK (SkNumberHistory), lastChange(): one log, some events, newest first.
        DB::statement('CREATE INDEX IF NOT EXISTS activity_log_log_name_event_id_idx ON activity_log (log_name, event, id DESC)');
        // Rekap Register SK: SK deleted in a year.
        DB::statement("CREATE INDEX IF NOT EXISTS activity_log_sk_dihapus_tahun_idx ON activity_log ((properties -> 'old' ->> 'tahun')) WHERE log_name = 'surat_keputusan' AND event = 'deleted'");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS activity_log_sk_dihapus_tahun_idx');
        DB::statement('DROP INDEX IF EXISTS activity_log_log_name_event_id_idx');
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS surat_keputusan_no_truncate ON surat_keputusan;
            DROP TRIGGER IF EXISTS surat_keputusan_guard ON surat_keputusan;
            DROP FUNCTION IF EXISTS surat_keputusan_guard();
        SQL);
    }
};
