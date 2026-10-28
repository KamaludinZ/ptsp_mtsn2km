<?php

namespace Tests;

use App\Models\DispositionLog;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * A seeded ticket with a service that no leader has disposed yet, the same
     * one every run. The demo data disposes some tickets, and a leader who
     * already disposed one may not decide it again (TicketPolicy::approve).
     */
    protected function undisposedTicket(): Ticket
    {
        return Ticket::query()->whereHas('service')
            ->whereNotIn('id', DispositionLog::query()->select('ticket_id'))
            ->orderBy('id')
            ->firstOrFail();
    }
}
