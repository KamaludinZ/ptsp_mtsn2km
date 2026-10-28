<?php

namespace App\Exceptions;

/**
 * Too many SK numbers asked for by one account in a short time
 * (SuratKeputusanService::MAX_PER_MINUTE / MAX_PER_DAY). Numbers are never
 * given back, so a runaway script or stolen token could otherwise use up
 * the year's register. The message is shown as-is; $retryAfter in seconds.
 */
class SkNumberLimitException extends TicketActionException
{
    public function __construct(string $message, public readonly int $retryAfter)
    {
        parent::__construct($message);
    }
}
