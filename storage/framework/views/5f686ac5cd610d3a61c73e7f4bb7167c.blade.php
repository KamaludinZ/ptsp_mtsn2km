<?php extract((new \Illuminate\Support\Collection($attributes->getAttributes()))->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
@props(['statePath','editor'])
<x-filament-tiptap-editor::tools.align-left :state-path="$statePath" :editor="$editor" >

{{ $slot ?? "" }}
</x-filament-tiptap-editor::tools.align-left>