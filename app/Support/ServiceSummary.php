<?php

namespace App\Support;

use App\Models\Service;
use App\Models\ServiceTemplate;
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
            'Disposisi' => match ($mode = $service->disposition_mode) {
                'none' => 'Langsung diproses petugas tanpa disposisi pimpinan.',
                'custom' => 'Memerlukan disposisi pimpinan.',
                default => 'Memerlukan disposisi ' . ServiceDisposition::mode($mode) . '.',
            },
        ], fn ($value) => filled($value));

        // Persyaratan and produk are rich text (TinyMCE); the rest is plain.
        return new HtmlString(collect($rows)->map(fn ($value, $label) => sprintf(
            '<div style="margin:0 0 .5rem"><strong>%s</strong><div class="rich-text">%s</div></div>',
            e($label),
            in_array($label, ['Persyaratan', 'Produk layanan'], true) ? RichText::render($value) : e($value),
        ))->implode(''));
    }

    /** Download links for the service's template berkas, empty when it has none. */
    public static function templates(?Service $service): HtmlString
    {
        $templates = $service?->activeTemplates ?? collect();

        return new HtmlString($templates->map(fn (ServiceTemplate $template) => $template->isAvailable()
            ? sprintf(
                '<p style="margin:0 0 .5rem"><a href="%s" style="font-weight:600;text-decoration:underline">%s</a>%s%s</p>',
                e($template->downloadUrl()),
                e($template->nama),
                $template->is_required ? ' <span style="font-size:.75rem;opacity:.8">(wajib dilengkapi)</span>' : '',
                $template->petunjuk ? '<br><span style="font-size:.8rem;opacity:.85">' . e($template->petunjuk) . '</span>' : '',
            )
            : sprintf('<p style="margin:0 0 .5rem;opacity:.7">%s <span style="font-size:.75rem">(sedang tidak tersedia, hubungi petugas)</span></p>', e($template->nama))
        )->implode(''));
    }
}
