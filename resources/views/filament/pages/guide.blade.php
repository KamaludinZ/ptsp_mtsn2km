<x-filament-panels::page>
    @if ($sections->isEmpty())
        <x-filament::section>
            Belum ada menu yang dapat dibuka dengan peran ini.
        </x-filament::section>
    @else
        <nav aria-label="Daftar isi panduan" class="flex flex-wrap gap-2">
            @foreach ($sections as $section)
                @foreach ($section['items'] as $item)
                    <a href="#panduan-{{ $item['key'] }}" class="rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            @endforeach
        </nav>

        @foreach ($sections as $section)
            <section class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ $section['group'] }}</h2>

                @foreach ($section['items'] as $item)
                    <x-filament::section :icon="$item['icon']" id="panduan-{{ $item['key'] }}" class="scroll-mt-24">
                        <x-slot name="heading">{{ $item['label'] }}</x-slot>
                        @if ($item['url'])
                            <x-slot name="headerEnd">
                                <x-filament::link :href="$item['url']" icon="heroicon-m-arrow-top-right-on-square" icon-position="after">
                                    Buka menu
                                </x-filament::link>
                            </x-slot>
                        @endif

                        <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                            <p>{{ $item['ringkas'] }}</p>

                            @if ($item['langkah'])
                                <ol class="list-decimal space-y-1 ps-5">
                                    @foreach ($item['langkah'] as $langkah)
                                        <li>{{ $langkah }}</li>
                                    @endforeach
                                </ol>
                            @endif

                            @if ($item['tips'])
                                <p class="rounded-lg bg-primary-50 px-3 py-2 text-primary-800 dark:bg-primary-500/10 dark:text-primary-300">
                                    <strong>Tips:</strong> {{ $item['tips'] }}
                                </p>
                            @endif
                        </div>
                    </x-filament::section>
                @endforeach
            </section>
        @endforeach
    @endif
</x-filament-panels::page>
