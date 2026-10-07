@php
    /** @var \App\Models\Service $service */
    use App\Support\RichText;
    use App\Support\ServiceStandard;

    $items = collect(ServiceStandard::items($service))->keyBy('key');
    $facts = ServiceStandard::facts($service);
    $rows = ServiceStandard::requirementRows($service);
    $essential = $items->only(ServiceStandard::ESSENTIAL);
    $others = $items->except(ServiceStandard::ESSENTIAL)->except(['jangka_waktu', 'biaya']);
    $filledOthers = $others->filter(fn ($item) => filled($item['content']) || $item['list']);
    // The public page only opens services the viewer may apply for (e.g. siswa-only ones 404 for staff).
    $detailVisible = \App\Models\Service::whereKey($service->getKey())->availableFor(auth()->user()?->user_type ?? 'umum')->exists();
@endphp

{{-- Standar pelayanan on the request forms (portal "Ajukan" and the counter):
     the same 14 components as the public service page (App\Support\ServiceStandard).
     Scoped inline styles: the panel uses Filament's prebuilt CSS. --}}
<div class="ptsp-std">
    <style>
        .ptsp-std { --std-primary: rgb(var(--primary-600)); --std-primary-soft: rgba(var(--primary-500), .1); --std-border: rgba(var(--gray-950), .08);
                    --std-card: #fff; --std-muted: rgb(var(--gray-500)); --std-text: rgb(var(--gray-950)); display: grid; gap: 1rem; color: var(--std-text); }
        .dark .ptsp-std { --std-primary: rgb(var(--primary-400)); --std-primary-soft: rgba(var(--primary-400), .12); --std-border: rgba(255, 255, 255, .1);
                          --std-card: rgba(255, 255, 255, .03); --std-muted: rgb(var(--gray-400)); --std-text: #fff; }
        .ptsp-std__desc { font-size: .925rem; line-height: 1.6; color: var(--std-muted); }
        .ptsp-std__facts { display: grid; gap: .75rem; grid-template-columns: repeat(auto-fit, minmax(10.5rem, 1fr)); }
        .ptsp-std__fact { display: flex; gap: .75rem; align-items: flex-start; padding: .85rem 1rem; border-radius: .75rem;
                          background: var(--std-primary-soft); border: 1px solid var(--std-border); }
        .ptsp-std__fact-icon { flex: none; width: 2.25rem; height: 2.25rem; border-radius: .6rem; display: grid; place-items: center;
                               background: var(--std-primary); color: #fff; }
        .ptsp-std__fact-label { display: block; font-size: .7rem; line-height: 1.3; color: var(--std-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .15rem; }
        .ptsp-std__fact-value { font-weight: 600; font-size: .925rem; line-height: 1.35; }
        .ptsp-std__grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(19rem, 1fr)); }
        .ptsp-std__card { border: 1px solid var(--std-border); border-radius: .85rem; padding: 1rem 1.15rem; background: var(--std-card); }
        .ptsp-std__card-head { display: flex; align-items: center; gap: .6rem; margin-bottom: .75rem; font-weight: 700; font-size: .95rem; }
        .ptsp-std__no { flex: none; min-width: 1.6rem; height: 1.6rem; padding: 0 .35rem; border-radius: 999px; display: grid; place-items: center;
                        font-size: .75rem; font-weight: 700; background: var(--std-primary); color: #fff; }
        .ptsp-std__muted { color: var(--std-muted); font-style: italic; font-size: .875rem; }
        .ptsp-std__check { list-style: none; margin: 0; padding: 0; display: grid; gap: .55rem; }
        .ptsp-std__check li { display: flex; gap: .55rem; align-items: flex-start; font-size: .9rem; line-height: 1.45; }
        .ptsp-std__check svg { flex: none; width: 1.15rem; height: 1.15rem; margin-top: .1rem; color: rgb(var(--success-600)); }
        .ptsp-std__check small { display: block; color: var(--std-muted); }
        .ptsp-std__tag { font-size: .7rem; font-weight: 600; padding: .05rem .45rem; border-radius: 999px; margin-left: .35rem; white-space: nowrap;
                         background: rgba(var(--warning-500), .15); color: rgb(var(--warning-700)); }
        .dark .ptsp-std__tag { color: rgb(var(--warning-400)); }
        .ptsp-std__tag--opt { background: rgba(var(--gray-500), .12); color: var(--std-muted); }
        .ptsp-std .rich-text { font-size: .9rem; line-height: 1.55; }
        .ptsp-std .rich-text p { margin: 0 0 .5rem; }
        .ptsp-std .rich-text > :last-child { margin-bottom: 0; }
        .ptsp-std .rich-text ul { list-style: disc; padding-left: 1.25rem; margin: 0 0 .5rem; }
        .ptsp-std .rich-text a { color: var(--std-primary); text-decoration: underline; }
        .ptsp-std .rich-text strong { font-weight: 700; }
        .ptsp-std .rich-text em { font-style: italic; }
        /* Numbered steps (Alur / mekanisme) */
        .ptsp-std .rich-text ol { list-style: none; counter-reset: step; padding: 0; margin: 0 0 .5rem; display: grid; gap: .6rem; }
        .ptsp-std .rich-text ol > li { counter-increment: step; position: relative; padding-left: 2.25rem; min-height: 1.6rem; }
        .ptsp-std .rich-text ol > li::before { content: counter(step); position: absolute; left: 0; top: 0; width: 1.6rem; height: 1.6rem;
                                               border-radius: 999px; display: grid; place-items: center; font-size: .75rem; font-weight: 700;
                                               background: var(--std-primary-soft); color: var(--std-primary); border: 1px solid var(--std-primary); }
        /* Persyaratan: a checklist even when written as a numbered list */
        .ptsp-std__card--check .rich-text ol > li::before,
        .ptsp-std__card--check .rich-text ul > li::before { content: "✓"; position: absolute; left: 0; top: .05rem; width: 1.35rem; height: 1.35rem;
                                                            border-radius: 999px; display: grid; place-items: center; font-size: .75rem; font-weight: 700;
                                                            background: rgba(var(--success-500), .15); color: rgb(var(--success-600)); border: 0; }
        .ptsp-std__card--check .rich-text ul { list-style: none; padding: 0; display: grid; gap: .55rem; }
        .ptsp-std__card--check .rich-text ul > li,
        .ptsp-std__card--check .rich-text ol > li { position: relative; padding-left: 2rem; min-height: 1.35rem; }
        .ptsp-std__card--check .rich-text ol { gap: .55rem; }
        .ptsp-std__link { display: inline-flex; align-items: center; gap: .3rem; margin-top: .75rem; font-size: .85rem; font-weight: 600; color: var(--std-primary); }
        .ptsp-std__link svg { width: 1rem; height: 1rem; }
        .ptsp-std__more { border: 1px dashed var(--std-border); border-radius: .85rem; padding: .25rem 1rem; }
        .ptsp-std__more > summary { cursor: pointer; padding: .65rem 0; font-weight: 600; font-size: .9rem; color: var(--std-primary); }
        .ptsp-std__more[open] > summary { margin-bottom: .5rem; }
        .ptsp-std__more .ptsp-std__grid { padding-bottom: 1rem; }
        .ptsp-std__footer { display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: center; justify-content: space-between;
                            font-size: .8rem; color: var(--std-muted); }
    </style>

    @if (filled($service->description))
        <div class="ptsp-std__desc rich-text">{{ RichText::render($service->description) }}</div>
    @endif

    <div class="ptsp-std__facts">
        @foreach ($facts as $fact)
            <div class="ptsp-std__fact">
                <span class="ptsp-std__fact-icon"><x-filament::icon :icon="$fact['heroicon']" class="h-5 w-5" style="width:1.25rem;height:1.25rem" /></span>
                <span>
                    <span class="ptsp-std__fact-label">{{ $fact['label'] }}</span>
                    <span class="ptsp-std__fact-value" style="display:block">{{ $fact['value'] }}</span>
                </span>
            </div>
        @endforeach
    </div>

    <div class="ptsp-std__grid">
        @foreach ($essential as $item)
            <section @class(['ptsp-std__card', 'ptsp-std__card--check' => $item['key'] === 'persyaratan']) aria-label="{{ $item['title'] }}">
                <h3 class="ptsp-std__card-head">
                    <span class="ptsp-std__no">{{ $item['no'] }}</span>
                    <x-filament::icon :icon="$item['heroicon']" style="width:1.15rem;height:1.15rem;color:var(--std-primary)" />
                    {{ $item['title'] }}
                </h3>

                @if ($item['key'] === 'persyaratan' && $rows->isNotEmpty())
                    <ul class="ptsp-std__check">
                        @foreach ($rows as $row)
                            <li>
                                <x-filament::icon icon="heroicon-m-check-circle" />
                                <span>
                                    {{ $row->requirement_name }}
                                    <span @class(['ptsp-std__tag', 'ptsp-std__tag--opt' => ! $row->is_required])>{{ $row->is_required ? 'Wajib' : 'Opsional' }}</span>
                                    @if (filled($row->description))<small>{{ $row->description }}</small>@endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @elseif ($item['list'])
                    <ul class="ptsp-std__check">
                        @foreach ($item['list'] as $entry)
                            <li><x-filament::icon icon="heroicon-m-check-circle" /><span>{{ is_array($entry) ? implode(' ', $entry) : $entry }}</span></li>
                        @endforeach
                    </ul>
                @elseif (filled($item['content']))
                    <div class="rich-text">{{ RichText::render($item['content']) }}</div>
                @else
                    <p class="ptsp-std__muted">Belum diisi.</p>
                @endif

                @if ($item['link'])
                    <a href="{{ $item['link'][0] }}" target="_blank" rel="noopener" class="ptsp-std__link">
                        {{ $item['link'][1] }} <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" />
                    </a>
                @endif
            </section>
        @endforeach
    </div>

    @if ($filledOthers->isNotEmpty())
        <details class="ptsp-std__more">
            <summary>Komponen standar lainnya ({{ $filledOthers->count() }})</summary>
            <div class="ptsp-std__grid">
                @foreach ($filledOthers as $item)
                    <section class="ptsp-std__card" aria-label="{{ $item['title'] }}">
                        <h3 class="ptsp-std__card-head">
                            <span class="ptsp-std__no">{{ $item['no'] }}</span>
                            <x-filament::icon :icon="$item['heroicon']" style="width:1.15rem;height:1.15rem;color:var(--std-primary)" />
                            {{ $item['title'] }}
                        </h3>
                        <div class="rich-text">{{ RichText::render($item['content']) }}</div>
                        @if ($item['link'])
                            <a href="{{ $item['link'][0] }}" target="_blank" rel="noopener" class="ptsp-std__link">
                                {{ $item['link'][1] }} <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" />
                            </a>
                        @endif
                    </section>
                @endforeach
            </div>
        </details>
    @endif

    <div class="ptsp-std__footer">
        <span>14 komponen standar pelayanan sesuai Permen PANRB Nomor 15 Tahun 2014.</span>
        @if ($detailVisible)
            <a href="{{ route('onlineportal.service.detail', $service->slug) }}" target="_blank" rel="noopener" class="ptsp-std__link" style="margin-top:0">
                Lihat standar pelayanan lengkap <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" />
            </a>
        @endif
    </div>
</div>
