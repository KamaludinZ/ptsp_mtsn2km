<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Nomor laporan: PEM (pengaduan), SRN (saran), WSB (whistleblowing) per
 * month, e.g. PEM-202610-0007. One atomic statement hands out the next
 * number, so reports sent at the same moment never get the same one. The
 * counter starts from the highest number already issued that month.
 */
class ComplaintNumber
{
    public const PREFIXES = [
        'complaint' => 'PEM',
        'pengaduan' => 'PEM',
        'suggestion' => 'SRN',
        'saran' => 'SRN',
        'whistleblowing' => 'WSB',
    ];

    public static function prefix(?string $type): string
    {
        return self::PREFIXES[$type] ?? 'PEM';
    }

    public static function next(?string $type): string
    {
        $prefix = self::prefix($type);
        $period = now()->format('Ym');

        $number = DB::selectOne(<<<'SQL'
            INSERT INTO complaint_sequences (prefix, period, last_number)
            VALUES (?, ?, (
                SELECT COALESCE(MAX(CAST(RIGHT(complaint_number, 4) AS INTEGER)), 0) + 1
                FROM complaints WHERE complaint_number LIKE ?
            ))
            ON CONFLICT (prefix, period) DO UPDATE SET last_number = complaint_sequences.last_number + 1
            RETURNING last_number
        SQL, [$prefix, $period, "{$prefix}-{$period}-%"])->last_number;

        return sprintf('%s-%s-%04d', $prefix, $period, $number);
    }
}
