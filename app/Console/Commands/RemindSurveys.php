<?php

namespace App\Console\Commands;

use App\Exceptions\TicketActionException;
use App\Models\Ticket;
use App\Support\SurveyReminders;
use Illuminate\Console\Command;

/**
 * Pengingat survei otomatis (setiap pagi): pemohon yang permohonannya
 * selesai tetapi belum mengisi SKM/SPAK diingatkan pada hari ke-1 dan ke-7
 * setelah selesai, lewat kanal notifikasi yang aktif. Satu pengingat per
 * permohonan per hari (SurveyReminders::COOLDOWN_HOURS).
 */
class RemindSurveys extends Command
{
    /** Days after completion on which the applicant is reminded. */
    public const REMIND_ON_DAYS = [1, 7];

    protected $signature = 'surveys:remind';

    protected $description = 'Ingatkan pemohon yang belum mengisi survei SKM/SPAK (hari ke-1 dan ke-7 setelah selesai)';

    public function handle(): int
    {
        $sent = 0;
        $oldest = today()->subDays(max(self::REMIND_ON_DAYS));

        $tickets = SurveyReminders::unrated()
            ->where(fn ($q) => $q->whereDate('actual_completion_date', '>=', $oldest)
                ->orWhere(fn ($q) => $q->whereNull('actual_completion_date')->whereDate('updated_at', '>=', $oldest)))
            ->with(['service', 'user'])
            ->get();

        foreach ($tickets as $ticket) {
            $completed = ($ticket->actual_completion_date ?? $ticket->updated_at)->copy()->startOfDay();
            if (! in_array((int) $completed->diffInDays(today()), self::REMIND_ON_DAYS, true)) {
                continue;
            }

            try {
                $sent += SurveyReminders::remind($ticket, null) ? 1 : 0;
            } catch (TicketActionException) {
                // Already reminded within the cooldown, or rated meanwhile.
            }
        }

        $this->info("{$sent} pengingat survei dikirim.");

        return self::SUCCESS;
    }
}
