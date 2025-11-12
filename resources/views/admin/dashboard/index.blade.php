@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <h1 class="text-2xl font-bold mb-4">Dashboard Super Admin</h1>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </div>
                <div class="stat-title">Total Users</div>
                <div class="stat-value text-primary">{{ $totalUsers }}</div>
                <div class="stat-desc">21% more than last month</div>
            </div>
        </div>
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="stat-title">Total Services</div>
                <div class="stat-value text-secondary">{{ $totalServices }}</div>
                <div class="stat-desc">21% more than last month</div>
            </div>
        </div>
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-info">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="stat-title">Total Tickets</div>
                <div class="stat-value text-info">{{ $totalTickets }}</div>
                <div class="stat-desc">21% more than last month</div>
            </div>
        </div>
        <div class="stats shadow">
            <div class="stat">
                <div class="stat-figure text-success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div class="stat-title">Visitors This Month</div>
                <div class="stat-value text-success">{{ $visitorsThisMonth }}</div>
                <div class="stat-desc">21% more than last month</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Service Performance</h2>
                <canvas id="servicePerformanceChart"></canvas>
            </div>
        </div>
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Complaint Performance</h2>
                <canvas id="complaintPerformanceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Recent Activities</h2>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTickets as $ticket)
                        <tr>
                            <td>Ticket</td>
                            <td>New ticket #{{ $ticket->ticket_number }} from {{ $ticket->user->name }} for {{ $ticket->service->name }}</td>
                            <td>{{ $ticket->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                        @foreach($recentComplaints as $complaint)
                        <tr>
                            <td>Complaint</td>
                            <td>New complaint #{{ $complaint->id }} ({{ $complaint->complaint_type }})</td>
                            <td>{{ $complaint->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                        @foreach($recentVisitors as $visitor)
                        <tr>
                            <td>Visitor</td>
                            <td>New visitor {{ $visitor->name }} ({{ $visitor->purpose }})</td>
                            <td>{{ $visitor->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Service Performance Chart
        const servicePerformanceCtx = document.getElementById('servicePerformanceChart').getContext('2d');
        new Chart(servicePerformanceCtx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Tickets',
                    data: @json($ticketData),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Complaint Performance Chart
        const complaintPerformanceCtx = document.getElementById('complaintPerformanceChart').getContext('2d');
        new Chart(complaintPerformanceCtx, {
            type: 'bar',
            data: {
                labels: @json($complaintChartLabels),
                datasets: [{
                    label: 'Avg. Resolution Time (hours)',
                    data: @json($complaintResolutionData),
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
@endpush
