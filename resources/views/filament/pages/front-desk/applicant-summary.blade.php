{{-- Ringkasan akun pemohon terpilih di Registrasi Layanan Loket (hanya baca). --}}
<div class="rounded-lg border border-gray-200 dark:border-white/10" style="padding: 0.75rem 1rem;">
    <dl style="display: grid; grid-template-columns: minmax(8rem, auto) 1fr; gap: 0.375rem 1rem; margin: 0;">
        @foreach ($rows as $label => $value)
            <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</dt>
            <dd class="text-sm font-medium text-gray-950 dark:text-white" style="margin: 0; overflow-wrap: anywhere;">{{ $value }}</dd>
        @endforeach
    </dl>

    <p class="text-sm text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-white/10" style="margin: 0.75rem 0 0; padding-top: 0.625rem;">
        Data ini diambil dari akun pemohon dan tidak bisa diubah di loket.
        @if ($fixUrl)
            <x-filament::link :href="$fixUrl" target="_blank" icon="heroicon-m-pencil-square" size="sm">Perbaiki di Pengguna › Pemohon</x-filament::link>
        @else
            Bila ada yang keliru, minta administrator memperbaikinya di menu Pengguna, tab Pemohon.
        @endif
    </p>
</div>
