@foreach (['total', 'online', 'offline', 'completed', 'rejected', 'open'] as $key)
    <td style="padding: 0.5rem 0.75rem; text-align: right; font-variant-numeric: tabular-nums;">{{ number_format($row[$key], 0, ',', '.') }}</td>
@endforeach
<td style="padding: 0.5rem 0.75rem; text-align: right; font-variant-numeric: tabular-nums;">{{ $row['on_time_rate'] !== null ? $row['on_time_rate'] . '%' : '–' }}</td>
<td style="padding: 0.5rem 0.75rem; text-align: right; white-space: nowrap;">{{ \App\Support\ServiceMetrics::days($row['avg_days']) }}</td>
