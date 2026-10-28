<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat checkout tamu: how each guest left. The visit itself records a
 * checkout the same way whoever does it (time and status); this history
 * says whether an officer did it (and who) or the guest from the public
 * visitor book (and from where). Insert only. Checkouts so far were all
 * done by the counter, by an officer no longer known.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->string('method', 10);
            $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('checked_out_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['visitor_id', 'checked_out_at']);
        });

        DB::statement("ALTER TABLE visitor_checkouts ADD CONSTRAINT visitor_checkouts_method_check CHECK (method IN ('petugas', 'mandiri'))");
        DB::statement("ALTER TABLE visitor_checkouts ADD CONSTRAINT visitor_checkouts_by_check CHECK (method = 'petugas' OR checked_out_by IS NULL)");

        DB::statement("INSERT INTO visitor_checkouts (visitor_id, method, checked_out_by, ip_address, checked_out_at, created_at)
            SELECT id, 'petugas', NULL, NULL, check_out_time, check_out_time FROM visitors WHERE check_out_time IS NOT NULL");

        DB::unprepared(<<<'SQL'
            CREATE OR REPLACE FUNCTION visitor_checkouts_immutable() RETURNS trigger AS $$
            BEGIN
                IF current_setting('app.allow_history_changes', true) = 'on' THEN
                    RETURN COALESCE(NEW, OLD);
                END IF;
                -- An officer's account removed: the entry stays, without them.
                IF TG_OP = 'UPDATE'
                    AND (to_jsonb(NEW) - 'checked_out_by') = (to_jsonb(OLD) - 'checked_out_by')
                    AND NEW.checked_out_by IS NULL
                THEN
                    RETURN NEW;
                END IF;
                IF TG_OP = 'DELETE' AND NOT EXISTS (SELECT 1 FROM visitors WHERE id = OLD.visitor_id) THEN
                    RETURN OLD;
                END IF;
                RAISE EXCEPTION 'Riwayat checkout tamu tidak dapat diubah atau dihapus (visitor_checkouts %).', OLD.id
                    USING ERRCODE = 'insufficient_privilege';
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER visitor_checkouts_immutable
                BEFORE UPDATE OR DELETE ON visitor_checkouts
                FOR EACH ROW EXECUTE FUNCTION visitor_checkouts_immutable();
        SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS visitor_checkouts_immutable ON visitor_checkouts; DROP FUNCTION IF EXISTS visitor_checkouts_immutable();');
        Schema::dropIfExists('visitor_checkouts');
    }
};
