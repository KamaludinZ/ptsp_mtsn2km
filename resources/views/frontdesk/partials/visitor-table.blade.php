{{-- Guest list with check-out and pass printing; expects $visitors. --}}
<div class="table-responsive">
    <table class="table table-sm align-middle">
        <thead>
            <tr>
                <th scope="col">Nama</th>
                <th scope="col">Instansi</th>
                <th scope="col">Keperluan</th>
                <th scope="col">Bertemu</th>
                <th scope="col">Masuk</th>
                <th scope="col">Keluar</th>
                <th scope="col"><span class="visually-hidden">Aksi</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($visitors as $visitor)
                <tr>
                    <td class="fw-semibold">{{ $visitor->name }}</td>
                    <td>{{ $visitor->institution ?: '–' }}</td>
                    <td class="small">{{ \Illuminate\Support\Str::limit($visitor->purpose, 60) }}</td>
                    <td>{{ $visitor->person_to_meet ?: '–' }}</td>
                    <td class="text-nowrap">{{ $visitor->check_in_time?->format('H:i') }}</td>
                    <td class="text-nowrap">
                        @if ($visitor->check_out_time)
                            {{ $visitor->check_out_time->format('H:i') }}
                        @else
                            <span class="badge bg-success bg-opacity-10 text-success-emphasis">Di lokasi</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('frontdesk.visitors.print', $visitor) }}" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                            <i class="fas fa-print" aria-hidden="true"></i><span class="visually-hidden">Cetak kartu tamu {{ $visitor->name }}</span>
                        </a>
                        @unless ($visitor->check_out_time)
                            <form method="POST" action="{{ route('frontdesk.visitors.checkout', $visitor) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">Check out</button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Tidak ada tamu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
