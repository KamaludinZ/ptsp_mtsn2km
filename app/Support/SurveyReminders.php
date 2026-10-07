<?php

namespace App\Support;

use App\Exceptions\TicketActionException;
use App\Models\Ticket;
use App\Models\User;
use App\Services\NotificationDispatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Pengingat survei: completed requests whose applicant has not rated the
 * service yet (SKM/SPAK), and the reminder staff can send them. One
 * reminder per request per day, so an applicant is never flooded.
 */
class SurveyReminders
{
    public const COOLDOWN_HOURS = 24;

    /** Completed requests of a registered applicant without a survey response. */
    public static function unrated(): Builder
    {
        return Ticket::query()
            ->where('status', 'completed')
            ->whereNotNull('user_id')
            ->whereDoesntHave('surveyResponses');
    }

    public static function lastSentAt(Ticket $ticket): ?string
    {
        return Cache::get(self::key($ticket));
    }

    /**
     * Send the reminder on the active channels ($actor: the staff member, or null for the scheduler).
     *
     * @return array<int, string> the channels it went out on
     */
    public static function remind(Ticket $ticket, ?User $actor): array
    {
        if ($ticket->status !== 'completed' || $ticket->hasSurveyCompleted()) {
            throw new TicketActionException("Permohonan {$ticket->ticket_number} tidak perlu diingatkan.");
        }

        if (self::lastSentAt($ticket)) {
            throw new TicketActionException("Pengingat untuk {$ticket->ticket_number} sudah dikirim dalam " . self::COOLDOWN_HOURS . ' jam terakhir.');
        }

        $channels = app(NotificationDispatcher::class)->send('survey_reminder', $ticket->loadMissing(['service', 'user']), null, [
            'tautan' => route('survey.form', ['tiket' => $ticket->ticket_number]),
        ]);

        if ($channels) {
            Cache::put(self::key($ticket), now()->toIso8601String(), now()->addHours(self::COOLDOWN_HOURS));
            // $actor null: the daily automatic reminder (surveys:remind).
            activity('audit')->causedBy($actor)->performedOn($ticket)
                ->withProperties(['kanal' => $channels, 'otomatis' => $actor === null])
                ->log($actor ? 'Mengirim pengingat survei' : 'Mengirim pengingat survei otomatis');
        }

        return $channels;
    }

    private static function key(Ticket $ticket): string
    {
        return 'survey-reminder:' . $ticket->id;
    }
}
