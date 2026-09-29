<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A ticket action that the current state of the ticket does not allow
 * (e.g. finishing a ticket that still waits for a leader's approval).
 * The message is written for the officer and shown as-is.
 */
class TicketActionException extends RuntimeException
{
}
