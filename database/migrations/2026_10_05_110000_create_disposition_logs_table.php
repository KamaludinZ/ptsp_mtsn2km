<?php

use App\Support\ServiceDisposition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat disposisi (PRD: disposition_logs): one row per leadership decision
 * with its signature model, signed sheet, recipients and instruction. Rows
 * are audit evidence: insert only, like ticket_logs. Earlier decisions are
 * copied from ticket_logs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposition_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_log_id')->nullable()->constrained('ticket_logs')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role', 50)->nullable();
            $table->string('action', 20); // disposisi | reject | acknowledge
            $table->string('signature_model', 20)->nullable(); // ttd_upload | tte_upload | acknowledged_by
            $table->foreignId('signature_file_id')->nullable()->constrained('ticket_files')->nullOnDelete();
            $table->jsonb('recipients')->nullable();
            $table->text('instruction')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ticket_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
        });

        // Copy decisions recorded before this table existed.
        $models = array_flip(ServiceDisposition::SIGNATURE_TYPES); // ttd => ttd_upload, ...
        DB::table('ticket_logs')
            ->join('tickets', 'tickets.id', '=', 'ticket_logs.ticket_id')
            ->whereIn('ticket_logs.action', ['approved', 'rejected'])
            ->orderBy('ticket_logs.id')
            ->select('ticket_logs.*', 'tickets.signature_type')
            ->chunk(500, function ($logs) use ($models) {
                DB::table('disposition_logs')->insert($logs->map(function ($log) use ($models) {
                    $meta = json_decode((string) $log->metadata, true) ?: [];
                    $approved = $log->action === 'approved';

                    return [
                        'ticket_id' => $log->ticket_id,
                        'ticket_log_id' => $log->id,
                        'actor_id' => $log->performed_by,
                        'role' => null,
                        'action' => $approved ? 'disposisi' : 'reject',
                        'signature_model' => $approved ? ($meta['signature_model'] ?? ($models[$log->signature_type] ?? null)) : null,
                        'signature_file_id' => null,
                        'recipients' => isset($meta['recipients']) ? json_encode($meta['recipients']) : null,
                        'instruction' => $meta['instruction'] ?? null,
                        'note' => $meta ? ($meta['note'] ?? ($approved ? null : $log->notes)) : $log->notes,
                        'created_at' => $log->created_at,
                    ];
                })->all());
            });

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION disposition_logs_immutable() RETURNS trigger AS $$
            BEGIN
                IF current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN COALESCE(NEW, OLD);
                END IF;

                -- Allowed: ON DELETE SET NULL of the referenced log, actor or file.
                IF TG_OP = 'UPDATE'
                    AND (to_jsonb(NEW) - 'ticket_log_id' - 'actor_id' - 'signature_file_id') = (to_jsonb(OLD) - 'ticket_log_id' - 'actor_id' - 'signature_file_id')
                    AND (NEW.ticket_log_id IS NOT DISTINCT FROM OLD.ticket_log_id OR NEW.ticket_log_id IS NULL)
                    AND (NEW.actor_id IS NOT DISTINCT FROM OLD.actor_id OR NEW.actor_id IS NULL)
                    AND (NEW.signature_file_id IS NOT DISTINCT FROM OLD.signature_file_id OR NEW.signature_file_id IS NULL)
                THEN
                    RETURN NEW;
                END IF;

                RAISE EXCEPTION 'Riwayat disposisi tidak dapat diubah atau dihapus (disposition_logs %).', OLD.id
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER disposition_logs_immutable
                BEFORE UPDATE OR DELETE ON disposition_logs
                FOR EACH ROW EXECUTE FUNCTION disposition_logs_immutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS disposition_logs_immutable ON disposition_logs; DROP FUNCTION IF EXISTS disposition_logs_immutable();');
        Schema::dropIfExists('disposition_logs');
    }
};
