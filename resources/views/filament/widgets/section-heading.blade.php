<x-filament-widgets::widget>
    <div class="fi-section-header-ctn">
        <div class="flex items-center gap-x-3 py-4 border-b border-gray-200 dark:border-gray-700">
            @if($icon)
            <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-primary-500/10">
                <x-filament::icon
                    :icon="$icon"
                    class="w-6 h-6 text-primary-500"
                />
            </div>
            @endif

            <div class="flex-1">
                <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $heading }}
                </h2>
                @if($description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $description }}
                </p>
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
