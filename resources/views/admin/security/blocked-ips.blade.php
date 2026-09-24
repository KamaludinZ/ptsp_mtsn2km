@extends('layouts.admin')

@section('title', 'Blocked IPs')

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Blocked IP Addresses</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.security.dashboard') }}">Security</a></li>
                    <li>Blocked IPs</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h4 class="card-title mb-4">Manage Blocked IP Addresses</h4>
            
            <!-- Block IP Form -->
            <form action="{{ route('admin.security.blocked-ips.block') }}" method="POST" class="mb-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="ip_address" class="label">
                            <span class="label-text">IP Address</span>
                        </label>
                        <input type="text" name="ip_address" id="ip_address" class="input input-bordered w-full" placeholder="e.g., 192.168.1.1" required>
                    </div>
                    <div>
                        <label for="reason" class="label">
                            <span class="label-text">Reason</span>
                        </label>
                        <input type="text" name="reason" id="reason" class="input input-bordered w-full" placeholder="e.g., Suspicious activity" required>
                    </div>
                    <div>
                        <label for="duration" class="label">
                            <span class="label-text">Duration (hours, optional)</span>
                        </label>
                        <input type="number" name="duration" id="duration" class="input input-bordered w-full" placeholder="e.g., 24">
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button type="submit" class="btn btn-error">Block IP</button>
                    </div>
                </div>
            </form>

            <!-- Blocked IPs Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Reason</th>
                            <th>Blocked At</th>
                            <th>Expires At</th>
                            <th>Blocked By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blockedIPs as $ip => $data)
                            <tr>
                                <td>{{ $ip }}</td>
                                <td>{{ $data['reason'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($data['blocked_at'])->format('d M Y H:i') }}</td>
                                <td>
                                    @if($data['expires_at'])
                                        {{ \Carbon\Carbon::parse($data['expires_at'])->format('d M Y H:i') }}
                                    @else
                                        Permanent
                                    @endif
                                </td>
                                <td>{{ $data['blocked_by'] }}</td>
                                <td>
                                    <form action="{{ route('admin.security.blocked-ips.unblock') }}" method="POST" onsubmit="return confirm('Are you sure you want to unblock this IP address?')">
                                        @csrf
                                        <input type="hidden" name="ip_address" value="{{ $ip }}">
                                        <button type="submit" class="btn btn-success btn-sm">Unblock</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-16">
                                    <i class="fas fa-check-circle fa-5x text-base-content/20 mb-3"></i>
                                    <h5 class="text-lg font-bold text-base-content/70">No IP addresses are currently blocked.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection