@extends('layouts.admin')

@section('title', 'Security Logs')

@section('content')
<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div>
            <h4 class="text-2xl font-bold text-base-content">Security Logs</h4>
            <div class="text-sm breadcrumbs">
                <ul>
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('admin.security.dashboard') }}">Security</a></li>
                    <li>Logs</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h4 class="card-title mb-4">Recent Security Events</h4>
            
            <!-- Filter Form -->
            <form method="GET" action="{{ route('admin.security.logs') }}" class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="label">
                            <span class="label-text">Cari</span>
                        </label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="input input-bordered w-full" placeholder="Cari log..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">Cari</button>
                        </div>
                    </div>
                    <div>
                        <label for="type" class="label">
                            <span class="label-text">Tipe Log</span>
                        </label>
                        <select name="type" id="type" class="select select-bordered w-full">
                            <option value="">Semua Tipe</option>
                            <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="error" {{ request('type') == 'error' ? 'selected' : '' }}>Error</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button class="btn btn-primary w-full" type="submit">Terapkan Filter</button>
                    </div>
                </div>
            </form>

            <!-- Security Logs Table -->
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Level</th>
                            <th>Message</th>
                            <th>Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log['timestamp'] }}</td>
                                <td>
                                    <span class="badge 
                                        @if($log['level'] == 'info') badge-info
                                        @elseif($log['level'] == 'warning') badge-warning
                                        @elseif($log['level'] == 'error') badge-error
                                        @else badge-neutral @endif">
                                        {{ ucfirst($log['level']) }}
                                    </span>
                                </td>
                                <td>{{ $log['message'] }}</td>
                                <td>
                                    @if(isset($log['context']))
                                        <pre class="whitespace-pre-wrap text-xs bg-base-200 p-2 rounded">{{ json_encode($log['context'], JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-16">
                                    <i class="fas fa-file-alt fa-5x text-base-content/20 mb-3"></i>
                                    <h5 class="text-lg font-bold text-base-content/70">Tidak ada log keamanan ditemukan.</h5>
                                    <p class="text-base-content/50">Log akan ditampilkan di sini ketika tersedia.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination (if applicable) -->
            {{-- Assuming $logs is a paginator instance --}}
            {{-- <div class="flex justify-center mt-4">
                {{ $logs->links('vendor.pagination.daisyui') }}
            </div> --}}
        </div>
    </div>
</div>
@endsection