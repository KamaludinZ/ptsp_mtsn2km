@props(['ticket'])

@if ($ticket->estimated_completion_date)
    @if ($ticket->isOverdue())
        <span class="text-danger fw-semibold"><i class="fas fa-triangle-exclamation me-1" aria-hidden="true"></i>{{ $ticket->estimated_completion_date->translatedFormat('d M Y') }}</span>
        <span class="visually-hidden">(melewati target)</span>
    @else
        {{ $ticket->estimated_completion_date->translatedFormat('d M Y') }}
    @endif
@else
    <span class="text-muted">–</span>
@endif
