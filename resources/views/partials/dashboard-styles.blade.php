{{-- Shared dashboard cards (x-stat-card, .dash-card), used by layouts.app and layouts.admin. --}}
<style>
    .stat-card { border: 0; border-radius: .75rem; box-shadow: 0 1px 3px rgba(15, 23, 42, .08); }
    .stat-card .stat-icon { width: 2.5rem; height: 2.5rem; border-radius: .6rem; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1.1; font-variant-numeric: tabular-nums; }
    .stat-card .stat-label { font-size: .85rem; color: var(--bs-secondary-color, #6c757d); }
    .stat-card a.stretched-link:focus-visible { outline: 3px solid var(--bs-primary); outline-offset: 2px; }
    .min-w-0 { min-width: 0; }
    .dash-section-title { font-size: 1rem; font-weight: 700; margin-bottom: .75rem; }
    .dash-card { border: 0; border-radius: .75rem; box-shadow: 0 1px 3px rgba(15, 23, 42, .08); }
    .dash-card .table { margin-bottom: 0; }
    .dash-card .table th { font-size: .75rem; text-transform: uppercase; letter-spacing: .03em; color: var(--bs-secondary-color, #6c757d); font-weight: 600; }
</style>
