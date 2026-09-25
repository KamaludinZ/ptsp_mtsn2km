{{-- Reporting period selector; expects $period and $periods (ServiceMetrics::PERIODS). --}}
<form method="GET" class="d-flex align-items-center gap-2">
    <label for="periode" class="small text-muted text-nowrap">Periode</label>
    <select id="periode" name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
        @foreach ($periods as $key => $label)
            <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
        @endforeach
    </select>
    <noscript><button class="btn btn-sm btn-primary">Terapkan</button></noscript>
</form>
