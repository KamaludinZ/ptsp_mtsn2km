@extends('layouts.admin')

@section('title', 'Laporan Kinerja')

@section('content')
<div class="container-fluid p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-base-content">📊 Laporan Kinerja</h1>
        <div class="dropdown dropdown-end">
            <label tabindex="0" class="btn btn-outline btn-primary">
                <i class="fas fa-download mr-1"></i>Export
            </label>
            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-32">
                <li><a>PDF</a></li>
                <li><a>Excel</a></li>
                <li><a>CSV</a></li>
            </ul>
        </div>
    </div>

    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Ringkasan Kinerja</h2>
            <p>Halaman ini akan menampilkan ringkasan kinerja pelayanan dan kinerja unit.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-primary">
                            <i class="fas fa-ticket-alt fa-2x"></i>
                        </div>
                        <div class="stat-title">Tiket Selesai</div>
                        <div class="stat-value text-primary">90%</div>
                        <div class="stat-desc">Dari total tiket</div>
                    </div>
                </div>
                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-secondary">
                            <i class="fas fa-hourglass-half fa-2x"></i>
                        </div>
                        <div class="stat-title">Waktu Respon Rata-rata</div>
                        <div class="stat-value text-secondary">24 Jam</div>
                        <div class="stat-desc">Untuk semua pengaduan</div>
                    </div>
                </div>
                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-info">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div class="stat-title">Kepuasan Pengguna</div>
                        <div class="stat-value text-info">4.5/5</div>
                        <div class="stat-desc">Berdasarkan survei</div>
                    </div>
                </div>
            </div>

            <h2 class="card-title mt-8">Grafik Kinerja</h2>
            <p>Grafik di bawah ini akan menampilkan tren kinerja dari waktu ke waktu.</p>
            <div class="bg-base-200 p-4 rounded-lg mt-4">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'],
                datasets: [{
                    label: 'Tiket Selesai',
                    data: [65, 59, 80, 81, 56, 55, 40],
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }, {
                    label: 'Waktu Respon (Jam)',
                    data: [28, 48, 40, 19, 86, 27, 90],
                    fill: false,
                    borderColor: 'rgb(255, 99, 132)',
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
    });
</script>
@endpush