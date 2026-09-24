@extends('backoffice.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Laporan Kinerja</h1>
                        <form method="GET" class="flex gap-2">
                            <select name="period" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Minggu Ini</option>
                                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Kuartal Ini</option>
                                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Tahun Ini</option>
                            </select>
                        </form>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Total Tiket</h2>
                            <p class="text-3xl font-bold text-blue-600">{{ $reportData['total_tickets'] }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Selesai</h2>
                            <p class="text-3xl font-bold text-green-600">{{ $reportData['completed_tickets'] }}</p>
                        </div>
                        <div class="bg-purple-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Rata-rata Waktu Selesai</h2>
                            <p class="text-3xl font-bold text-purple-600">{{ $reportData['average_completion_time'] }} <span class="text-base font-normal">menit</span></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-lg font-semibold mb-3">Tiket per Layanan</h2>
                            @if($reportData['tickets_by_service']->isEmpty())
                                <p class="text-gray-500 text-sm">Tidak ada data.</p>
                            @else
                                <ul class="divide-y divide-gray-100 border rounded-lg">
                                    @foreach($reportData['tickets_by_service'] as $row)
                                        <li class="px-4 py-2 flex justify-between">
                                            <span>{{ $row->service->name ?? 'Tidak diketahui' }}</span>
                                            <span class="font-semibold">{{ $row->count }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold mb-3">Tiket per Status</h2>
                            @if($reportData['tickets_by_status']->isEmpty())
                                <p class="text-gray-500 text-sm">Tidak ada data.</p>
                            @else
                                <ul class="divide-y divide-gray-100 border rounded-lg">
                                    @foreach($reportData['tickets_by_status'] as $row)
                                        <li class="px-4 py-2 flex justify-between">
                                            <span>{{ ucfirst(str_replace('_', ' ', $row->status)) }}</span>
                                            <span class="font-semibold">{{ $row->count }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
