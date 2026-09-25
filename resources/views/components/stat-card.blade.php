@props(['label', 'value', 'icon' => 'fa-chart-simple', 'tone' => 'primary', 'hint' => null, 'href' => null])

<div {{ $attributes->merge(['class' => 'card stat-card h-100']) }}>
    <div class="card-body d-flex align-items-start gap-3">
        <span class="stat-icon bg-{{ $tone }} bg-opacity-10 text-{{ $tone }}"><i class="fas {{ $icon }}" aria-hidden="true"></i></span>
        <div class="min-w-0">
            <div class="stat-label">{{ $label }}</div>
            <div class="stat-value">{{ $value ?? '–' }}</div>
            @if ($hint)
                <div class="small text-muted mt-1">{{ $hint }}</div>
            @endif
            @if ($href)
                <a href="{{ $href }}" class="stretched-link"><span class="visually-hidden">Lihat {{ $label }}</span></a>
            @endif
        </div>
    </div>
</div>
