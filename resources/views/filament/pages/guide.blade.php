@php
    $totalMenu = $sections->sum(fn ($section) => count($section['items']));
    // Teks yang dicocokkan kotak "Cari menu": nama menu, ringkasan, dan langkahnya.
    $cari = fn (array $item) => mb_strtolower(implode(' ', [$item['label'], $item['ringkas'], ...$item['langkah']]));
@endphp

<x-filament-panels::page>
    {{-- Gaya khusus halaman Panduan. Ditulis di sini (bukan kelas Tailwind)
         karena panel memakai CSS bawaan Filament tanpa tema terkompilasi. --}}
    <style>
        .pg { --pg-primary: rgb(var(--primary-600)); --pg-primary-soft: rgba(var(--primary-500), .1); --pg-border: rgba(var(--gray-950), .08); --pg-muted: rgb(var(--gray-500)); --pg-text: rgb(var(--gray-700)); --pg-strong: rgb(var(--gray-950)); --pg-card: #fff; }
        .dark .pg { --pg-primary: rgb(var(--primary-400)); --pg-primary-soft: rgba(var(--primary-400), .12); --pg-border: rgba(255, 255, 255, .1); --pg-muted: rgb(var(--gray-400)); --pg-text: rgb(var(--gray-300)); --pg-strong: #fff; --pg-card: rgb(var(--gray-900)); }

        .pg { display: grid; gap: 1.5rem; }
        @media (min-width: 1024px) { .pg { grid-template-columns: 17rem minmax(0, 1fr); align-items: start; } }

        .pg-card { background: var(--pg-card); border: 1px solid var(--pg-border); border-radius: .75rem; box-shadow: 0 1px 2px rgba(0, 0, 0, .04); }

        /* Daftar isi */
        .pg-toc { padding: 1rem; }
        @media (min-width: 1024px) { .pg-toc { position: sticky; top: 5.5rem; max-height: calc(100vh - 7rem); overflow-y: auto; } }
        .pg-toc-toggle { display: flex; width: 100%; align-items: center; justify-content: space-between; padding: .4rem .25rem; font-size: .9rem; font-weight: 600; color: var(--pg-strong); }
        .pg-toc-toggle svg { width: 1.25rem; height: 1.25rem; color: var(--pg-muted); transition: transform .15s; }
        .pg-toc-list { display: none; margin-top: .75rem; }
        .pg-toc-list.is-open { display: block; }
        @media (min-width: 1024px) { .pg-toc-toggle { display: none; } .pg-toc-list { display: block; margin-top: 0; } }
        .pg-toc-title { font-size: .75rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--pg-muted); margin-bottom: .75rem; }
        .pg-search { position: relative; margin-bottom: 1rem; }
        .pg-search svg { position: absolute; left: .65rem; top: 50%; width: 1rem; height: 1rem; transform: translateY(-50%); color: var(--pg-muted); pointer-events: none; }
        .pg-search input { width: 100%; padding: .5rem .75rem .5rem 2.1rem; font-size: .875rem; border: 1px solid var(--pg-border); border-radius: .5rem; background: transparent; color: var(--pg-strong); }
        .pg-search input:focus { outline: 2px solid var(--pg-primary); outline-offset: -1px; }
        .pg-toc-group + .pg-toc-group { margin-top: 1rem; }
        .pg-toc-group-name { display: flex; justify-content: space-between; font-size: .8rem; font-weight: 600; color: var(--pg-strong); margin-bottom: .25rem; }
        .pg-toc-group-name span { color: var(--pg-muted); font-weight: 500; }
        .pg-toc a { display: flex; align-items: center; gap: .5rem; padding: .35rem .5rem; border-radius: .375rem; font-size: .85rem; color: var(--pg-text); line-height: 1.3; }
        .pg-toc a:hover { background: var(--pg-primary-soft); color: var(--pg-primary); }
        .pg-toc a svg { width: 1rem; height: 1rem; flex-shrink: 0; color: var(--pg-muted); }
        .pg-toc a:hover svg { color: var(--pg-primary); }

        /* Isi */
        .pg-main { display: grid; gap: 2rem; min-width: 0; }
        .pg-intro { padding: 1.25rem; display: grid; gap: 1rem; }
        .pg-intro h2 { font-size: 1rem; font-weight: 600; color: var(--pg-strong); }
        .pg-intro-meta { display: flex; flex-wrap: wrap; gap: .5rem; }
        .pg-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .2rem .65rem; border-radius: 999px; font-size: .78rem; font-weight: 500; background: var(--pg-primary-soft); color: var(--pg-primary); }
        .pg-badge.is-gray { background: rgba(var(--gray-500), .1); color: var(--pg-text); }
        .pg-howto { display: grid; gap: .75rem; }
        @media (min-width: 640px) { .pg-howto { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .pg-howto-item { display: flex; gap: .65rem; font-size: .85rem; color: var(--pg-text); line-height: 1.45; }
        .pg-howto-item svg { width: 1.25rem; height: 1.25rem; flex-shrink: 0; color: var(--pg-primary); margin-top: .1rem; }
        .pg-howto-item strong { display: block; color: var(--pg-strong); font-weight: 600; }

        .pg-group { display: grid; gap: 1rem; }
        .pg-group-head { display: flex; align-items: baseline; gap: .75rem; padding-bottom: .5rem; border-bottom: 1px solid var(--pg-border); }
        .pg-group-head h2 { font-size: 1.125rem; font-weight: 700; color: var(--pg-strong); }
        .pg-group-head span { font-size: .8rem; color: var(--pg-muted); }

        .pg-item { scroll-margin-top: 6rem; overflow: hidden; }
        .pg-item:target { outline: 2px solid var(--pg-primary); outline-offset: 2px; }
        .pg-item-head { display: flex; flex-wrap: wrap; align-items: flex-start; gap: .85rem; padding: 1.1rem 1.25rem; }
        .pg-icon { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; flex-shrink: 0; border-radius: .6rem; background: var(--pg-primary-soft); color: var(--pg-primary); }
        .pg-icon svg { width: 1.35rem; height: 1.35rem; }
        .pg-item-title { flex: 1 1 14rem; min-width: 0; }
        .pg-item-title h3 { font-size: 1rem; font-weight: 600; color: var(--pg-strong); }
        .pg-item-title p { margin-top: .25rem; font-size: .875rem; line-height: 1.55; color: var(--pg-text); }
        .pg-item-body { border-top: 1px solid var(--pg-border); padding: 1rem 1.25rem 1.25rem; display: grid; gap: 1rem; }
        .pg-label { font-size: .72rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: var(--pg-muted); }
        .pg-steps { display: grid; gap: .65rem; margin-top: .6rem; counter-reset: pg-step; }
        .pg-steps li { counter-increment: pg-step; display: grid; grid-template-columns: 1.6rem minmax(0, 1fr); gap: .65rem; font-size: .875rem; line-height: 1.55; color: var(--pg-text); }
        .pg-steps li::before { content: counter(pg-step); display: grid; place-items: center; width: 1.6rem; height: 1.6rem; border-radius: 999px; font-size: .75rem; font-weight: 700; background: var(--pg-primary); color: #fff; }
        .dark .pg-steps li::before { color: rgb(var(--gray-950)); }
        .pg-tip { display: flex; gap: .65rem; padding: .75rem .9rem; border-radius: .6rem; font-size: .85rem; line-height: 1.5; background: rgba(var(--warning-500), .1); color: rgb(var(--warning-800)); border: 1px solid rgba(var(--warning-500), .25); }
        .dark .pg-tip { color: rgb(var(--warning-300)); }
        .pg-tip svg { width: 1.15rem; height: 1.15rem; flex-shrink: 0; margin-top: .1rem; }

        .pg-empty { padding: 2rem; text-align: center; font-size: .9rem; color: var(--pg-muted); }
        .pg-top { display: inline-flex; align-items: center; gap: .35rem; font-size: .85rem; color: var(--pg-primary); justify-self: center; }
    </style>

    @if ($sections->isEmpty())
        <x-filament::section>
            Belum ada menu yang dapat dibuka dengan peran ini.
        </x-filament::section>
    @else
        <div
            class="pg"
            x-data="{
                q: '',
                buka: false,
                semua: @js($sections->flatMap(fn ($section) => array_map($cari, $section['items']))->values()),
                get adaHasil() { return this.semua.some((teks) => this.cocok(teks)) },
                cocok(teks) { return this.q.trim() === '' || teks.includes(this.q.trim().toLowerCase()) },
            }"
        >
            {{-- Daftar isi + pencarian --}}
            <aside class="pg-card pg-toc" aria-label="Daftar isi panduan">
                <div class="pg-search">
                    <x-filament::icon icon="heroicon-m-magnifying-glass" />
                    <input type="search" x-model="q" placeholder="Cari menu…" aria-label="Cari menu di panduan">
                </div>

                <button type="button" class="pg-toc-toggle" x-on:click="buka = ! buka" :aria-expanded="buka">
                    <span>Daftar isi ({{ $totalMenu }} menu)</span>
                    <x-filament::icon icon="heroicon-m-chevron-down" x-bind:style="buka && 'transform: rotate(180deg)'" />
                </button>

                <div class="pg-toc-list" :class="{ 'is-open': buka || q.trim() !== '' }">
                <p class="pg-toc-title">Daftar isi</p>

                @foreach ($sections as $section)
                    <div class="pg-toc-group" x-show="@js(array_map($cari, $section['items'])).some((teks) => cocok(teks))">
                        <p class="pg-toc-group-name">{{ $section['group'] }} <span>{{ count($section['items']) }}</span></p>
                        @foreach ($section['items'] as $item)
                            <a
                                href="#panduan-{{ $item['key'] }}"
                                x-show="cocok(@js($cari($item)))"
                            >
                                @if ($item['icon'])
                                    <x-filament::icon :icon="$item['icon']" />
                                @endif
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endforeach
                </div>
            </aside>

            <div class="pg-main">
                {{-- Pengantar --}}
                <div class="pg-card pg-intro" id="panduan-atas">
                    <div class="pg-intro-meta">
                        <span class="pg-badge">
                            <x-filament::icon icon="heroicon-m-squares-2x2" class="h-4 w-4" />
                            {{ $totalMenu }} menu
                        </span>
                        <span class="pg-badge is-gray">{{ $sections->count() }} kelompok menu</span>
                    </div>

                    <h2>Cara membaca panduan ini</h2>

                    <div class="pg-howto">
                        <div class="pg-howto-item">
                            <x-filament::icon icon="heroicon-o-list-bullet" />
                            <div><strong>Pilih menu</strong> Klik nama menu di daftar isi, atau ketik di kotak "Cari menu".</div>
                        </div>
                        <div class="pg-howto-item">
                            <x-filament::icon icon="heroicon-o-check-circle" />
                            <div><strong>Ikuti langkahnya</strong> Kerjakan langkah bernomor dari atas ke bawah.</div>
                        </div>
                        <div class="pg-howto-item">
                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" />
                            <div><strong>Langsung praktik</strong> Tekan tombol "Buka menu" untuk membuka menu tersebut.</div>
                        </div>
                    </div>
                </div>

                @foreach ($sections as $section)
                    <section
                        class="pg-group"
                        x-show="@js(array_map($cari, $section['items'])).some((teks) => cocok(teks))"
                    >
                        <div class="pg-group-head">
                            <h2>{{ $section['group'] }}</h2>
                            <span>{{ count($section['items']) }} menu</span>
                        </div>

                        @foreach ($section['items'] as $item)
                            <article
                                class="pg-card pg-item"
                                id="panduan-{{ $item['key'] }}"
                                x-show="cocok(@js($cari($item)))"
                            >
                                <div class="pg-item-head">
                                    <div class="pg-icon">
                                        <x-filament::icon :icon="$item['icon'] ?? 'heroicon-o-document-text'" />
                                    </div>

                                    <div class="pg-item-title">
                                        <h3>{{ $item['label'] }}</h3>
                                        <p>{{ $item['ringkas'] }}</p>
                                    </div>

                                    @if ($item['url'])
                                        <x-filament::button
                                            tag="a"
                                            :href="$item['url']"
                                            size="sm"
                                            icon="heroicon-m-arrow-top-right-on-square"
                                            icon-position="after"
                                        >
                                            Buka menu
                                        </x-filament::button>
                                    @endif
                                </div>

                                @if ($item['langkah'] || $item['tips'])
                                    <div class="pg-item-body">
                                        @if ($item['langkah'])
                                            <div>
                                                <p class="pg-label">Langkah-langkah</p>
                                                <ol class="pg-steps">
                                                    @foreach ($item['langkah'] as $langkah)
                                                        <li><span>{{ $langkah }}</span></li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        @endif

                                        @if ($item['tips'])
                                            <div class="pg-tip">
                                                <x-filament::icon icon="heroicon-o-light-bulb" />
                                                <div><strong>Tips:</strong> {{ $item['tips'] }}</div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </section>
                @endforeach

                <p class="pg-empty pg-card" x-show="! adaHasil" x-cloak>
                    Tidak ada menu yang cocok dengan "<span x-text="q"></span>".
                </p>

                <a href="#panduan-atas" class="pg-top">
                    <x-filament::icon icon="heroicon-m-arrow-up" class="h-4 w-4" />
                    Kembali ke atas
                </a>
            </div>
        </div>
    @endif
</x-filament-panels::page>
