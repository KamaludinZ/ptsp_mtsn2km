{{-- Hasil import akun: ringkasan jumlah dan daftar baris yang gagal. Inline styles: the panel uses Filament's prebuilt CSS. --}}
@php($failed = $report['failed'] ?? [])

<div data-import-report class="space-y-4">
    @if ($report['demo'] ?? false)
        <p class="text-sm text-warning-600 dark:text-warning-400">Contoh tampilan: pemrosesan berkas belum aktif, angka di bawah bukan hasil berkas Anda.</p>
    @endif

    <dl style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem;">
        @foreach ([
            'Baris dibaca' => ($report['created'] ?? 0) + count($failed),
            'Akun dibuat' => $report['created'] ?? 0,
            'Baris gagal' => count($failed),
        ] as $label => $value)
            <div class="rounded-lg bg-gray-50 dark:bg-white/5" style="padding: 0.75rem;">
                <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                <dd class="text-gray-950 dark:text-white" style="font-size: 1.5rem; font-weight: 600;">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    @if ($failed)
        <div style="overflow: auto; max-height: 50vh;">
            <table class="text-sm" style="width: 100%; border-collapse: collapse;">
                <caption class="sr-only">Baris yang gagal diimport</caption>
                <thead>
                    <tr class="text-gray-950 dark:text-white" style="text-align: left;">
                        <th scope="col" style="padding: 0.375rem 0.5rem;">Baris</th>
                        <th scope="col" style="padding: 0.375rem 0.5rem;">Email</th>
                        <th scope="col" style="padding: 0.375rem 0.5rem;">Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($failed as $row)
                        <tr class="border-t border-gray-100 dark:border-white/5 text-gray-700 dark:text-gray-200">
                            <td style="padding: 0.375rem 0.5rem; font-variant-numeric: tabular-nums;">{{ $row['row'] }}</td>
                            <td style="padding: 0.375rem 0.5rem;">{{ $row['email'] ?: '—' }}</td>
                            <td style="padding: 0.375rem 0.5rem;">{{ $row['reason'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Perbaiki baris di atas pada berkas, lalu unggah ulang; akun yang sudah dibuat dilewati sebagai email duplikat.</p>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">Semua baris berhasil dibuat menjadi akun.</p>
    @endif
</div>
