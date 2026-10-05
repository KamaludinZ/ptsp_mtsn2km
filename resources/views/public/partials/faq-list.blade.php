{{-- FAQ accordion, grouped by kelompok. $faqs: the questions to show; $searchable: filter box, group links and FAQPage data (the FAQ page). --}}
@if ($faqs->count())
    @php
        // Headings only when the questions span more than one group.
        $groups = $faqs->groupBy(fn ($faq) => $faq->categoryLabel());
        $showGroups = $groups->count() > 1;
        $slug = fn (string $label) => 'faq-' . \Illuminate\Support\Str::slug($label);
    @endphp
    <div data-faq-list>
        @if ($searchable ?? false)
            <div class="mb-3">
                <label for="faq-search" class="visually-hidden">Cari pertanyaan</label>
                <input id="faq-search" type="search" class="form-control form-control-lg" data-faq-search
                       placeholder="Cari pertanyaan, mis. legalisir, mutasi, biaya…" autocomplete="off">
            </div>
            @if ($showGroups)
                <nav aria-label="Kelompok pertanyaan" class="d-flex flex-wrap gap-2 mb-4">
                    @foreach ($groups as $label => $items)
                        <a href="#{{ $slug($label) }}" class="badge rounded-pill text-bg-light border text-decoration-none" style="font-size: .85rem; padding: .45rem .8rem;">
                            {{ $label }} <span class="text-muted">({{ $items->count() }})</span>
                        </a>
                    @endforeach
                </nav>
            @endif
        @endif

        <div id="faqAccordion">
            @foreach ($groups as $label => $items)
                <section data-faq-group aria-labelledby="{{ $slug($label) }}" class="{{ $loop->first ? '' : 'mt-4' }}">
                    @if ($showGroups)
                        <h2 class="h6 text-uppercase text-muted fw-bold mb-2" id="{{ $slug($label) }}" style="scroll-margin-top: 6rem;">{{ $label }}</h2>
                    @endif
                    <div class="accordion">
                        @foreach ($items as $faq)
                            <div class="accordion-item" id="faq-{{ $faq->id }}" style="scroll-margin-top: 6rem;"
                                 data-faq-text="{{ \Illuminate\Support\Str::lower($faq->question . ' ' . strip_tags((string) $faq->answer)) }}">
                                <h3 class="accordion-header" id="heading{{ $faq->id }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $faq->id }}" aria-expanded="false" aria-controls="collapse{{ $faq->id }}">
                                        {{ $faq->question }}
                                    </button>
                                </h3>
                                <div id="collapse{{ $faq->id }}" class="accordion-collapse collapse" aria-labelledby="heading{{ $faq->id }}" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        {!! $faq->safeAnswer() !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        @if ($searchable ?? false)
            <p class="text-center text-muted mt-4" data-faq-empty hidden>
                Tidak ada pertanyaan yang cocok. Silakan <a href="{{ route('public.contact') }}">hubungi kami</a>.
            </p>
            <script type="application/ld+json">
                {!! json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqs->map(fn ($faq) => [
                        '@type' => 'Question',
                        'name' => $faq->question,
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($faq->safeAnswer())],
                    ])->values(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
            </script>
            <script>
                (() => {
                    const list = document.currentScript.closest('[data-faq-list]');
                    const items = [...list.querySelectorAll('[data-faq-text]')];
                    const groups = [...list.querySelectorAll('[data-faq-group]')];
                    const empty = list.querySelector('[data-faq-empty]');
                    list.querySelector('[data-faq-search]').addEventListener('input', (event) => {
                        const q = event.target.value.trim().toLowerCase();
                        let shown = 0;
                        items.forEach((item) => {
                            item.hidden = q !== '' && ! item.dataset.faqText.includes(q);
                            shown += item.hidden ? 0 : 1;
                        });
                        // A group without matches hides its heading too.
                        groups.forEach((group) => {
                            group.hidden = ! [...group.querySelectorAll('[data-faq-text]')].some((item) => ! item.hidden);
                        });
                        empty.hidden = shown > 0;
                    });

                    // /faq#faq-12 opens that question.
                    const target = location.hash && list.querySelector(location.hash + '[data-faq-text] .accordion-collapse');
                    if (target && window.bootstrap) {
                        new bootstrap.Collapse(target, { toggle: true });
                    } else if (target) {
                        target.classList.add('show');
                    }
                })();
            </script>
        @endif
    </div>
@else
    <div class="text-center">
        <p>Saat ini belum ada FAQ yang tersedia.</p>
    </div>
@endif
