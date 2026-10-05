<?php

namespace App\Enums;

/** What a leader decided on a ticket (disposition_logs.action). */
enum DispositionAction: string
{
    case Disposisi = 'disposisi';
    case Reject = 'reject';
    case Acknowledge = 'acknowledge';

    public function label(): string
    {
        return match ($this) {
            self::Disposisi => 'Didisposisi',
            self::Reject => 'Ditolak',
            self::Acknowledge => 'Diketahui',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Disposisi => 'success',
            self::Reject => 'danger',
            self::Acknowledge => 'info',
        };
    }
}
