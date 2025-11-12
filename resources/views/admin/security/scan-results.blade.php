@extends('layouts.admin')

@section('title', 'Security Scan Results')

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Security Scan Results</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('suadmin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('suadmin.security.dashboard') }}">Security</a></li>
                    <li>Scan Results</li>
                </ul>
            </div>
        </div>
    </div>

    @if($results['timestamp'] !== 'Never')
        <div class="card bg-base-100 shadow-xl mb-8">
            <div class="card-body">
                <h2 class="card-title">Last Scan on {{ \Carbon\Carbon::parse($results['timestamp'])->format('d M Y H:i') }}</h2>
                <div class="flex items-center gap-4">
                    <div class="text-5xl font-bold text-primary">{{ $results['overall_score'] }}%</div>
                    <div>
                        <p class="text-base-content/70">Overall Security Score</p>
                        @if($results['overall_score'] >= 90)
                            <span class="badge badge-success">Excellent</span>
                        @elseif($results['overall_score'] >= 70)
                            <span class="badge badge-warning">Good</span>
                        @else
                            <span class="badge badge-error">Needs Attention</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($results['checks'] as $checkName => $check)
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-xl capitalize">{{ str_replace('_', ' ', $checkName) }}</h3>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="badge 
                                @if($check['status'] === 'pass') badge-success
                                @elseif($check['status'] === 'warning') badge-warning
                                @elseif($check['status'] === 'fail') badge-error
                                @else badge-info @endif">
                                {{ ucfirst($check['status']) }}
                            </span>
                            <p class="text-base-content/70">{{ $check['message'] }}</p>
                        </div>
                        @if(isset($check['issues']) && count($check['issues']) > 0)
                            <ul class="list-disc list-inside mt-4 text-error">
                                @foreach($check['issues'] as $issue)
                                    <li>{{ $issue }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>No security scan has been run yet. Please run a scan from the Security Dashboard.</span>
        </div>
    @endif
</div>
@endsection