<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat status permohonan: one row per status a request enters (from its
 * first status on), with who changed it and how long the previous status
 * lasted. Written by a trigger on tickets, so every change is recorded,
 * including ones made outside the application's service layer. Rows are
 * immutable like ticket_logs (maintenance: SET LOCAL app.allow_history_changes = 'on').
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->useCurrent();
            $table->unsignedBigInteger('previous_duration_seconds')->nullable(); // time spent in from_status
            $table->index(['ticket_id', 'changed_at']);
            $table->index(['to_status', 'changed_at']);
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION tickets_record_status() RETURNS trigger AS $$
            DECLARE
                since timestamp;
                stamp timestamp;
            BEGIN
                IF TG_OP = 'INSERT' THEN
                    INSERT INTO ticket_status_histories (ticket_id, from_status, to_status, changed_by, changed_at)
                    VALUES (NEW.id, NULL, NEW.status, NEW.created_by, COALESCE(NEW.created_at, now()));
                    RETURN NEW;
                END IF;

                IF NEW.status IS DISTINCT FROM OLD.status THEN
                    -- The application's clock (updated_at) when it stamped the row, else the database's.
                    stamp := CASE WHEN NEW.updated_at IS DISTINCT FROM OLD.updated_at THEN NEW.updated_at ELSE now() END;
                    SELECT max(changed_at) INTO since FROM ticket_status_histories WHERE ticket_id = NEW.id;
                    since := COALESCE(since, OLD.created_at, stamp);

                    INSERT INTO ticket_status_histories (ticket_id, from_status, to_status, changed_by, changed_at, previous_duration_seconds)
                    VALUES (NEW.id, OLD.status, NEW.status, NEW.updated_by, stamp,
                            GREATEST(0, floor(extract(epoch FROM (stamp - since))))::bigint);
                END IF;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS tickets_record_status ON tickets;
            CREATE TRIGGER tickets_record_status
                AFTER INSERT OR UPDATE OF status ON tickets
                FOR EACH ROW EXECUTE FUNCTION tickets_record_status();

            CREATE OR REPLACE FUNCTION ticket_status_histories_immutable() RETURNS trigger AS $$
            BEGIN
                IF current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN COALESCE(NEW, OLD);
                END IF;

                -- The database's own ON DELETE SET NULL on changed_by is allowed.
                IF TG_OP = 'UPDATE'
                    AND (to_jsonb(NEW) - 'changed_by') = (to_jsonb(OLD) - 'changed_by')
                    AND NEW.changed_by IS NULL
                THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'Riwayat status tidak dapat diubah atau dihapus (ticket_status_histories %).', OLD.id
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            DROP TRIGGER IF EXISTS ticket_status_histories_immutable ON ticket_status_histories;
            CREATE TRIGGER ticket_status_histories_immutable
                BEFORE UPDATE OR DELETE ON ticket_status_histories
                FOR EACH ROW EXECUTE FUNCTION ticket_status_histories_immutable();
        SQL);

        // Backfill: each ticket's first status, then the status changes already in the service history.
        DB::unprepared(<<<'SQL'
            INSERT INTO ticket_status_histories (ticket_id, from_status, to_status, changed_by, changed_at, previous_duration_seconds)
            SELECT ticket_id, from_status, to_status, changed_by, changed_at,
                   CASE WHEN from_status IS NULL THEN NULL
                        ELSE GREATEST(0, floor(extract(epoch FROM (changed_at - lag(changed_at) OVER w))))::bigint END
            FROM (
                SELECT t.id AS ticket_id, NULL::varchar AS from_status,
                       COALESCE((SELECT l.from_status FROM ticket_logs l
                                 WHERE l.ticket_id = t.id AND l.to_status IS NOT NULL AND l.from_status IS NOT NULL
                                 ORDER BY l.created_at, l.id LIMIT 1), t.status) AS to_status,
                       t.created_by AS changed_by, COALESCE(t.created_at, now()) AS changed_at, 0 AS seq
                FROM tickets t
                UNION ALL
                SELECT l.ticket_id, l.from_status, l.to_status, l.performed_by, l.created_at, l.id
                FROM ticket_logs l
                WHERE l.to_status IS NOT NULL AND l.from_status IS NOT NULL AND l.from_status <> l.to_status
                  AND EXISTS (SELECT 1 FROM tickets t WHERE t.id = l.ticket_id)
            ) h
            WINDOW w AS (PARTITION BY ticket_id ORDER BY changed_at, seq)
            ORDER BY ticket_id, changed_at, seq;
        SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            DROP TRIGGER IF EXISTS tickets_record_status ON tickets;
            DROP FUNCTION IF EXISTS tickets_record_status();
            DROP TRIGGER IF EXISTS ticket_status_histories_immutable ON ticket_status_histories;
            DROP FUNCTION IF EXISTS ticket_status_histories_immutable();
        SQL);
        Schema::dropIfExists('ticket_status_histories');
    }
};
