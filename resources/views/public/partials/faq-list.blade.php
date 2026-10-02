{{-- FAQ accordion. $faqs: the questions to show; $searchable: show the filter box. --}}
@if ($faqs->count())
    <div data-faq-list>
        @if ($searchable ?? false)
            <div class="mb-4">
                <label for="faq-search" class="visually-hidden">Cari pertanyaan</label>
                <input id="faq-search" type="search" class="form-control form-control-lg" data-faq-search
                       placeholder="Cari pertanyaan, mis. legalisir, mutasi, biaya…" autocomplete="off">
            </div>
        @endif
        <div class="accordion" id="faqAccordion">
            @foreach ($faqs as $faq)
                <div class="accordion-item" data-faq-text="{{ \Illuminate\Support\Str::lower($faq->question . ' ' . strip_tags((string) $faq->answer)) }}">
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
        @if ($searchable ?? false)
            <p class="text-center text-muted mt-4" data-faq-empty hidden>
                Tidak ada pertanyaan yang cocok. Silakan <a href="{{ route('public.contact') }}">hubungi kami</a>.
            </p>
            <script>
                (() => {
                    const list = document.currentScript.closest('[data-faq-list]');
                    const items = [...list.querySelectorAll('[data-faq-text]')];
                    const empty = list.querySelector('[data-faq-empty]');
                    list.querySelector('[data-faq-search]').addEventListener('input', (event) => {
                        const q = event.target.value.trim().toLowerCase();
                        let shown = 0;
                        items.forEach((item) => {
                            item.hidden = q !== '' && ! item.dataset.faqText.includes(q);
                            shown += item.hidden ? 0 : 1;
                        });
                        empty.hidden = shown > 0;
                    });
                })();
            </script>
        @endif
    </div>
@else
    <div class="text-center">
        <p>Saat ini belum ada FAQ yang tersedia.</p>
    </div>
@endif
