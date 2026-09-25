@props(['status'])

@php
    $tone = [
        'submitted' => 'secondary',
        'verified' => 'info',
        'in_process' => 'warning',
        'approved' => 'primary',
        'completed' => 'success',
        'rejected' => 'danger',
        'cancelled' => 'dark',
    ][$status] ?? 'secondary';
@endphp

<span {{ $attributes->merge(['class' => "badge rounded-pill bg-{$tone} bg-opacity-10 text-{$tone}-emphasis border border-{$tone}-subtle"]) }}>
    {{ \App\Support\ServiceMetrics::STATUS_LABELS[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}
</span>
