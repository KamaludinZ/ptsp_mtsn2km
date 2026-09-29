<?php

namespace App\Filament\Widgets\Performance;

use App\Support\ServiceMetrics;
use Carbon\CarbonInterface;

/** The reporting period chosen on the performance page (?periode=). */
trait ReadsPeriod
{
    protected function period(): string
    {
        return ServiceMetrics::period($this->filters['periode'] ?? null);
    }

    protected function periodStart(): ?CarbonInterface
    {
        return ServiceMetrics::periodStart($this->period());
    }
}
