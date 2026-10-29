<?php

namespace App\Support;

use App\Models\Ticket;
use App\Models\TicketLog;
use Illuminate\Support\Facades\URL;

/**
 * What public ticket tracking may show. Ticket numbers are sequential and
 * easy to guess, so without the applicant's e-mail only progress is given
 * (status, stages, dates); the request text and the milestones' notes are
 * shown only when the e-mail matches the applicant. Internal entries (notes,
 * officers' uploads) are never shown publicly.
 */
class TicketTracking
{
    /** How long the link to the product file, given once the applicant is verified, works. */
    public const LINK_MINUTES = 10;

    public static function find(string $number): ?Ticket
    {
        return Ticket::with(['service', 'user', 'logs', 'workflowSteps' => fn ($q) => $q->with('workflowStep')->orderBy('id'), 'output'])
            ->where('ticket_number', trim($number))
            ->first();
    }

    public static function isOwner(Ticket $ticket, ?string $email): bool
    {
        return filled($email) && $ticket->user && strcasecmp(trim($email), (string) $ticket->user->email) === 0;
    }

    /**
     * How a finished request's product file is reached: berkas, whether there
     * is one to download (finished, and on the disk); akun, whether to sign in
     * (User::signsIn), told only to the verified owner since anyone may try a
     * number (null otherwise: both ways are offered); masuk_url, the request in
     * the portal, past the login.
     *
     * @return array{berkas: bool, akun: ?bool, masuk_url: string}
     */
    public static function output(Ticket $ticket, bool $owner): array
    {
        return [
            'berkas' => self::hasOutputFile($ticket),
            'akun' => $owner ? $ticket->user?->signsIn() : null,
            'masuk_url' => \App\Filament\Portal\Resources\TicketResource::getUrl('view', ['record' => $ticket], panel: 'portal'),
        ];
    }

    /** A finished request whose product is a file still on the disk. */
    public static function hasOutputFile(Ticket $ticket): bool
    {
        return $ticket->status === 'completed' && TicketDocuments::exists($ticket->output?->file_path);
    }

    /**
     * The short-lived link to the product file for the verified applicant.
     * Signed on the path alone (signed:relative), so it holds behind a proxy
     * that changes the scheme or the host.
     */
    public static function outputLink(Ticket $ticket): string
    {
        return url(URL::temporarySignedRoute('onlineportal.track.ticket.output', now()->addMinutes(self::LINK_MINUTES), ['output' => $ticket->output], absolute: false));
    }

    public static function summary(Ticket $ticket, bool $owner): array
    {
        return [
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status,
            'status_label' => TicketLabels::status($ticket->status),
            'status_color' => StatusBadge::for($ticket->status)['color'],
            'submitted_at' => $ticket->created_at?->toIso8601String(),
            'estimated_completion' => $ticket->estimated_completion_date?->toDateString(),
            'verified_owner' => $owner,
            'description' => $owner ? $ticket->notes : null,
            'service' => $ticket->service ? [
                'name' => $ticket->service->name,
                'mode' => $ticket->mode,
                'processing_time' => $ticket->service->processing_time,
            ] : null,
            'has_output_file' => (bool) $ticket->output?->file_path,
            'ready_for_pickup' => (bool) $ticket->ready_for_pickup,
            'hasil' => self::output($ticket, $owner),
            // Like the portal: only milestones, never officers' internal entries.
            'logs' => $ticket->logs->whereIn('action', TicketLog::APPLICANT_VISIBLE)->sortBy('created_at')->values()->map(fn (TicketLog $log) => [
                'action' => $log->action,
                'action_label' => TicketLabels::logAction($log->action),
                'notes' => $owner ? $log->notes : null,
                'created_at' => $log->created_at?->toIso8601String(),
            ]),
            'workflow_steps' => $ticket->workflowSteps->map(fn ($step) => [
                'name' => $step->workflowStep?->name,
                'pivot' => [
                    'completed_at' => $step->completed_at?->toIso8601String(),
                    'notes' => $owner ? $step->notes : null,
                ],
            ]),
        ];
    }
}
