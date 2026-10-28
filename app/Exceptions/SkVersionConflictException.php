<?php

namespace App\Exceptions;

use App\Models\SuratKeputusan;

/**
 * A correction made on an older versi of the SK than the register holds:
 * another officer changed it meanwhile. The message is shown as-is.
 */
class SkVersionConflictException extends TicketActionException
{
    public function __construct(public readonly SuratKeputusan $current)
    {
        parent::__construct('SK ' . $current->nomor_sk . ' sudah diubah petugas lain sejak Anda membukanya. Muat ulang datanya, lalu ulangi koreksi Anda.');
    }
}
