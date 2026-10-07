<?php

namespace App\Support;

use App\Models\Service;
use App\Models\ServiceTemplate;
use Illuminate\Support\HtmlString;

/** A service's standard and template berkas for the request forms. */
class ServiceSummary
{
    /**
     * Standar pelayanan for the request forms (portal "Ajukan" and the counter):
     * facts, requirements, steps and the other components, the same as the
     * public service page (resources/views/filament/components/service-standard).
     */
    public static function html(?Service $service): HtmlString
    {
        return new HtmlString($service ? view('filament.components.service-standard', ['service' => $service])->render() : '');
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
