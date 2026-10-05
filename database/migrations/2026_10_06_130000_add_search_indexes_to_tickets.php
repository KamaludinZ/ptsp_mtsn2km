<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Trigram indexes for pencarian permohonan (TicketSearch): the expressions
 * match the search's lower(...) LIKE '%word%' and the digits-only WhatsApp
 * comparison, so PostgreSQL can use them.
 */
return new class extends Migration
{
    private const INDEXES = [
        'idx_tickets_ticket_number_trgm' => "tickets USING GIN (lower(ticket_number) gin_trgm_ops)",
        'idx_tickets_notes_trgm' => "tickets USING GIN (lower(coalesce(notes, '')) gin_trgm_ops)",
        'idx_users_name_trgm' => 'users USING GIN (lower(name) gin_trgm_ops)',
        'idx_users_email_trgm' => 'users USING GIN (lower(email) gin_trgm_ops)',
        'idx_users_whatsapp_digits_trgm' => "users USING GIN (regexp_replace(coalesce(whatsapp_number, ''), '[^0-9]', '', 'g') gin_trgm_ops)",
        'idx_services_name_trgm' => 'services USING GIN (lower(name) gin_trgm_ops)',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS "pg_trgm"');

        foreach (self::INDEXES as $name => $definition) {
            DB::statement("CREATE INDEX IF NOT EXISTS {$name} ON {$definition}");
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::INDEXES) as $name) {
            DB::statement("DROP INDEX IF EXISTS {$name}");
        }
    }
};
