<?php

namespace App\Support;

use App\Models\Service;
use Illuminate\Support\HtmlString;

/** A service's standard (requirements, time, fee, product) for request forms. */
class ServiceSummary
{
    public static function html(?Service $service): HtmlString
    {
        if (! $service) {
            return new HtmlString('');
        }

        $rows = array_filter([
            'Persyaratan' => $service->getAttribute('requirements'),
            'Standar waktu' => $service->processing_time,
            'Biaya' => (float) $service->fee > 0 ? 'Rp ' . number_format((float) $service->fee, 0, ',', '.') : 'Gratis',
            'Produk layanan' => $service->product,
        ], fn ($value) => filled($value));

        return new HtmlString(collect($rows)->map(fn ($value, $label) => sprintf(
            '<p style="margin:0 0 .5rem"><strong>%s</strong><br>%s</p>',
            e($label),
            nl2br(e(preg_replace('/\s+(?=\d+\.\s)/', "\n", trim($value)))),
        ))->implode(''));
    }
}
