<?php

use App\Models\Service;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tickets never stored a target date, so service-standard compliance
     * (on time vs late) could not be measured. Derive it from each service's
     * processing time, and use the last update as completion date for
     * completed tickets that lack one.
     */
    public function up(): void
    {
        $slaDays = Service::withTrashed()->get()->mapWithKeys(fn ($s) => [$s->id => $s->slaWorkingDays()]);

        DB::table('tickets')->whereNull('estimated_completion_date')->orderBy('id')
            ->each(function ($ticket) use ($slaDays) {
                $days = $slaDays[$ticket->service_id] ?? null;
                if ($days && $ticket->created_at) {
                    DB::table('tickets')->where('id', $ticket->id)->update([
                        'estimated_completion_date' => Carbon::parse($ticket->created_at)->addWeekdays($days)->toDateString(),
                    ]);
                }
            });

        DB::statement("UPDATE tickets SET actual_completion_date = updated_at::date
                       WHERE status = 'completed' AND actual_completion_date IS NULL");
    }

    public function down(): void
    {
        // Data backfill only.
    }
};
