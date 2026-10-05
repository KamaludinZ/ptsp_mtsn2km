<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat status pengaduan/WBS: every stage a report goes through, by whom
 * and with what reply. Insert only (audit evidence). Existing reports get
 * their submission and current stage as a starting point.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('response')->nullable(); // what the reporter is told at this stage
            $table->text('internal_note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['complaint_id', 'created_at']);
        });

        DB::table('complaints')->orderBy('id')->chunk(500, function ($complaints) {
            $rows = [];
            foreach ($complaints as $complaint) {
                $rows[] = ['complaint_id' => $complaint->id, 'from_status' => null, 'to_status' => 'submitted', 'actor_id' => null, 'response' => null, 'internal_note' => null, 'created_at' => $complaint->created_at];
                if ($complaint->status !== 'submitted') {
                    $rows[] = ['complaint_id' => $complaint->id, 'from_status' => 'submitted', 'to_status' => $complaint->status, 'actor_id' => null, 'response' => $complaint->response, 'internal_note' => null, 'created_at' => $complaint->updated_at];
                }
            }
            DB::table('complaint_status_logs')->insert($rows);
        });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION complaint_status_logs_immutable() RETURNS trigger AS $$
            BEGIN
                IF current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN COALESCE(NEW, OLD);
                END IF;
                IF TG_OP = 'UPDATE'
                    AND (to_jsonb(NEW) - 'actor_id') = (to_jsonb(OLD) - 'actor_id')
                    AND (NEW.actor_id IS NOT DISTINCT FROM OLD.actor_id OR NEW.actor_id IS NULL)
                THEN
                    RETURN NEW;
                END IF;
                -- Deleting a report (admin only) takes its history along.
                IF TG_OP = 'DELETE' AND NOT EXISTS (SELECT 1 FROM complaints WHERE id = OLD.complaint_id) THEN
                    RETURN OLD;
                END IF;
                RAISE EXCEPTION 'Riwayat pengaduan tidak dapat diubah atau dihapus (complaint_status_logs %).', OLD.id
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER complaint_status_logs_immutable
                BEFORE UPDATE OR DELETE ON complaint_status_logs
                FOR EACH ROW EXECUTE FUNCTION complaint_status_logs_immutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS complaint_status_logs_immutable ON complaint_status_logs; DROP FUNCTION IF EXISTS complaint_status_logs_immutable();');
        Schema::dropIfExists('complaint_status_logs');
    }
};
