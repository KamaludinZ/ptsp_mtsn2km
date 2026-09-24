<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
@props(['statePath'])
<x-filament-tiptap-editor::tools.paragraph :state-path="$statePath" >

{{ $slot ?? "" }}
</x-filament-tiptap-editor::tools.paragraph>