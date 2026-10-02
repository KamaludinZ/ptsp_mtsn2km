<p class="text-muted mb-0">MTsN 2 Kota Malang</p>
@isset($edition)
    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis mt-2">
        <i class="fas fa-calendar-alt me-1" aria-hidden="true"></i>{{ $edition->name }}@if ($edition->dateRange()) · {{ $edition->dateRange() }}@endif
    </span>
@endisset
