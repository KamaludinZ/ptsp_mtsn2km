<?php

use Filament\Support\Enums\MaxWidth;

$maxContentWidth = $this-> getMaxContentWidth() ?? MaxWidth::SevenExtraLarge;

?>

<x-filament::page
    :widget-data="$widgetData"
    :max-content-width="$maxContentWidth"
>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-8">
        <div class="lg:col-span-2">
            {{ $this->getHeaderWidgets()[0] }}
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 mt-6 lg:gap-8">
        {{ $this->getWidgets()[0] }}
        {{ $this->getWidgets()[1] }}
        {{ $this->getWidgets()[2] }}
    </div>

    <x-filament-actions::modals />
</x-filament::page>