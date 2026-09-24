@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold">Kinerja Pelayanan</h1>
                        <form method="GET" class="flex gap-2">
                            <select name="period" onchange="this.form.submit()" class="border rounded px-3 py-2 text-sm">
                                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>Minggu Ini</option>
                                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>Bulan Ini</option>
                                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>Kuartal Ini</option>
                                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>Tahun Ini</option>
                            </select>
                        </form>
                    </div>

                    <h2 class="text-lg font-semibold mb-3">Tiket Layanan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Tiket</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ $ticketStats['total'] }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Selesai</h3>
                            <p class="text-3xl font-bold text-green-600">{{ $ticketStats['completed'] }}</p>
                        </div>
                        <div class="bg-purple-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Rata-rata Waktu Selesai</h3>
                            <p class="text-3xl font-bold text-purple-600">{{ $ticketStats['average_time'] }} <span class="text-base font-normal">menit</span></p>
                        </div>
                    </div>

                    <h2 class="text-lg font-semibold mb-3">Pengaduan</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-yellow-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Pengaduan</h3>
                            <p class="text-3xl font-bold text-yellow-600">{{ $complaintStats['total'] }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Selesai</h3>
                            <p class="text-3xl font-bold text-green-600">{{ $complaintStats['completed'] }}</p>
                        </div>
                    </div>

                    @if($complaintStats['by_type']->isNotEmpty())
                        <div class="mb-8">
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">Pengaduan per Jenis</h3>
                            <ul class="divide-y divide-gray-100 border rounded-lg">
                                @foreach($complaintStats['by_type'] as $row)
                                    <li class="px-4 py-2 flex justify-between">
                                        <span>{{ ucfirst($row->complaint_type) }}</span>
                                        <span class="font-semibold">{{ $row->count }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h2 class="text-lg font-semibold mb-3">Survei</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Total Responden</h3>
                            <p class="text-3xl font-bold text-blue-600">{{ $surveyStats['total_responses'] }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h3 class="text-sm font-semibold text-gray-600 mb-1">Indeks Kepuasan (SKM)</h3>
                            <p class="text-3xl font-bold text-green-600">{{ $surveyStats['satisfaction_index'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
