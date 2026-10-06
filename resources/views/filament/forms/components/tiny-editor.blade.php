@php
    $statePath = $getStatePath();
    $config = $getEditorConfig();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @if (! $config['script'])
        <p class="text-sm text-danger-600 dark:text-danger-400">
            Editor teks belum dapat dimuat: TINYMCE_API_KEY belum diisi di .env.
        </p>
    @endif

    <div
        wire:ignore
        x-data="ptspTinyEditor({
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')", isOptimisticallyLive: false) }},
            config: @js($config),
        })"
        class="ptsp-tiny-editor"
        {{ $attributes->merge($getExtraAttributes(), escape: false) }}
    >
        <textarea
            x-ref="textarea"
            id="{{ $getId() }}"
            class="block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5"
            rows="8"
            aria-label="{{ $getLabel() }}"
        ></textarea>
    </div>
</x-dynamic-component>
