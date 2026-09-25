<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seeders passed json_encode()d strings to the "array" cast, so
     * user_types_allowed was stored as a JSON string ("[\"siswa\"]") instead
     * of a JSON array. whereJsonContains() never matches those rows, which
     * emptied the public service catalog and the front-desk service list.
     */
    public function up(): void
    {
        DB::statement(<<<'SQL'
            UPDATE services
            SET user_types_allowed = (user_types_allowed #>> '{}')::jsonb
            WHERE jsonb_typeof(user_types_allowed::jsonb) = 'string'
        SQL);
    }

    public function down(): void
    {
        // Data repair only; nothing to reverse.
    }
};
