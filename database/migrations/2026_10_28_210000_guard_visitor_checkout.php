<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Checkout tamu: by the counter, the API, an admin's correction and now the
 * guest from the public visitor book, a checkout is recorded one way only,
 * the time with the status. A status that disagrees with the time (an edit
 * that set the time and left the status) follows the time; a checkout
 * before the check-in becomes the check-in time. Then the database holds
 * them together.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE visitors SET status = CASE WHEN check_out_time IS NULL THEN 'active' ELSE 'checked_out' END
            WHERE (status = 'checked_out') <> (check_out_time IS NOT NULL)");
        DB::statement('UPDATE visitors SET check_out_time = check_in_time WHERE check_out_time < check_in_time');

        DB::statement("ALTER TABLE visitors ADD CONSTRAINT visitors_checkout_status_check CHECK ((status = 'checked_out') = (check_out_time IS NOT NULL))");
        DB::statement('ALTER TABLE visitors ADD CONSTRAINT visitors_checkout_time_check CHECK (check_out_time IS NULL OR check_out_time >= check_in_time)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE visitors DROP CONSTRAINT IF EXISTS visitors_checkout_time_check');
        DB::statement('ALTER TABLE visitors DROP CONSTRAINT IF EXISTS visitors_checkout_status_check');
    }
};
