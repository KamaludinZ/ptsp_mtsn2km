<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Riwayat layanan is audit evidence: ticket_logs rows can be added but never
 * changed or removed. The only updates allowed are the database's own
 * ON DELETE SET NULL on performed_by / ticket_file_id. A maintenance session
 * may lift the lock with: SET LOCAL app.allow_history_changes = 'on'.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION ticket_logs_immutable() RETURNS trigger AS $$
            BEGIN
                IF current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN COALESCE(NEW, OLD);
                END IF;

                IF TG_OP = 'UPDATE'
                    AND (to_jsonb(NEW) - 'performed_by' - 'ticket_file_id') = (to_jsonb(OLD) - 'performed_by' - 'ticket_file_id')
                    AND (NEW.performed_by IS NOT DISTINCT FROM OLD.performed_by OR NEW.performed_by IS NULL)
                    AND (NEW.ticket_file_id IS NOT DISTINCT FROM OLD.ticket_file_id OR NEW.ticket_file_id IS NULL)
                THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'Riwayat layanan tidak dapat diubah atau dihapus (ticket_logs %).', OLD.id
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS ticket_logs_immutable ON ticket_logs;
            CREATE TRIGGER ticket_logs_immutable
                BEFORE UPDATE OR DELETE ON ticket_logs
                FOR EACH ROW EXECUTE FUNCTION ticket_logs_immutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS ticket_logs_immutable ON ticket_logs; DROP FUNCTION IF EXISTS ticket_logs_immutable();');
    }
};
