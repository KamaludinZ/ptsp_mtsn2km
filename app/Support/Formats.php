<?php

namespace App\Support;

use App\Models\AppSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/** Format & penomoran set in Pengaturan: ticket number prefix and the date format of printed documents. */
class Formats
{
    public const TICKET_PREFIX = 'ticket_prefix';

    public const DATE_FORMAT = 'document_date_format';

    /** Carbon translatedFormat patterns offered in Pengaturan. */
    public const DATE_FORMATS = [
        'j F Y' => '5 Oktober 2026',
        'd/m/Y' => '05/10/2026',
        'd-m-Y' => '05-10-2026',
        'l, j F Y' => 'Senin, 5 Oktober 2026',
    ];

    public static function ticketPrefix(): string
    {
        $prefix = strtoupper((string) AppSetting::get(self::TICKET_PREFIX));

        return preg_match('/^[A-Z]{2,8}$/', $prefix) ? $prefix : 'PTSP';
    }

    /** "PTSP-202610-0007" */
    public static function ticketNumber(int $sequence, ?CarbonInterface $at = null, ?string $prefix = null): string
    {
        return ($prefix ?? self::ticketPrefix()) . '-' . ($at ?? now())->format('Ym') . '-' . sprintf('%04d', $sequence);
    }

    public static function dateFormat(): string
    {
        $format = (string) AppSetting::get(self::DATE_FORMAT);

        return array_key_exists($format, self::DATE_FORMATS) ? $format : 'j F Y';
    }

    /** A date for printed documents, in the configured format. */
    public static function date(CarbonInterface|string|null $date): string
    {
        return $date ? Carbon::parse($date)->translatedFormat(self::dateFormat()) : '';
    }
}
