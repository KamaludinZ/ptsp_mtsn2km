{{--
    Label status berwarna. Usage: <x-status-badge :status="$ticket->status" />,
    type="complaint|approval|update" for other records. Styles are inline
    (Filament's prebuilt CSS and the public pages share no utility classes)
    and follow both dark-mode switches (html.dark and data-bs-theme).
--}}
@props(['status' => null, 'type' => 'ticket', 'size' => 'md'])
@php($badge = \App\Support\StatusBadge::for($status, $type))
@once
<style>
    .status-pill{display:inline-flex;align-items:center;gap:.375rem;border-radius:9999px;font-weight:600;line-height:1.25;white-space:nowrap;border:1px solid transparent}
    .status-pill--md{padding:.25rem .7rem;font-size:.8125rem}
    .status-pill--sm{padding:.125rem .5rem;font-size:.75rem}
    .status-pill__dot{width:.45rem;height:.45rem;border-radius:9999px;background:currentColor;flex:none}
    .status-pill--gray{background:#f3f4f6;color:#374151;border-color:#e5e7eb}
    .status-pill--info{background:#e0f2fe;color:#075985;border-color:#bae6fd}
    .status-pill--warning{background:#fef3c7;color:#92400e;border-color:#fde68a}
    .status-pill--success{background:#d1fae5;color:#065f46;border-color:#a7f3d0}
    .status-pill--danger{background:#fee2e2;color:#991b1b;border-color:#fecaca}
    :is(.dark,[data-bs-theme=dark]) .status-pill--gray{background:rgba(156,163,175,.15);color:#e5e7eb;border-color:rgba(156,163,175,.3)}
    :is(.dark,[data-bs-theme=dark]) .status-pill--info{background:rgba(56,189,248,.15);color:#7dd3fc;border-color:rgba(56,189,248,.3)}
    :is(.dark,[data-bs-theme=dark]) .status-pill--warning{background:rgba(251,191,36,.15);color:#fcd34d;border-color:rgba(251,191,36,.3)}
    :is(.dark,[data-bs-theme=dark]) .status-pill--success{background:rgba(52,211,153,.15);color:#6ee7b7;border-color:rgba(52,211,153,.3)}
    :is(.dark,[data-bs-theme=dark]) .status-pill--danger{background:rgba(248,113,113,.15);color:#fca5a5;border-color:rgba(248,113,113,.3)}
</style>
@endonce
<span {{ $attributes->merge(['class' => "status-pill status-pill--{$size} status-pill--{$badge['color']}", 'data-status' => $status]) }}>
    <span class="status-pill__dot" aria-hidden="true"></span><span data-status-label>{{ $badge['label'] }}</span>
</span>
