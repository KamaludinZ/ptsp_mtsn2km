@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Maintenance Mode</h1>
                <p class="text-gray-600 mt-2">Kelola mode pemeliharaan aplikasi</p>
            </div>
            <a href="{{ route('admin.security.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg">
                Back to Security Dashboard
            </a>
        </div>
    </div>

    <!-- Current Status Card -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Current Status</h2>
                <p class="text-gray-600 mt-2">Aplikasi saat ini dalam mode {{ $isDown ? 'PEMELIHARAAN' : 'AKTIF' }}</p>
            </div>
            <div>
                @if($isDown)
                    <span class="inline-flex items-center px-6 py-3 rounded-full text-lg font-bold bg-red-100 text-red-800">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        MAINTENANCE MODE
                    </span>
                @else
                    <span class="inline-flex items-center px-6 py-3 rounded-full text-lg font-bold bg-green-100 text-green-800">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        ONLINE
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Enable Maintenance Mode -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">
                <svg class="w-6 h-6 inline mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Enable Maintenance Mode
            </h3>
            <form id="enableMaintenanceForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pesan Pemeliharaan</label>
                    <textarea name="message" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent" placeholder="Aplikasi sedang dalam pemeliharaan...">Aplikasi sedang dalam pemeliharaan. Kami akan segera kembali.</textarea>
                    <p class="text-sm text-gray-500 mt-1">Pesan yang akan ditampilkan kepada pengguna</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Retry After (detik)</label>
                    <input type="number" name="retry" value="3600" min="60" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">Waktu tunggu sebelum mencoba lagi (default: 3600 detik = 1 jam)</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Secret Key (Opsional)</label>
                    <input type="text" name="secret" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent" placeholder="your-secret-key">
                    <p class="text-sm text-gray-500 mt-1">Kunci rahasia untuk tetap mengakses aplikasi (tambahkan ?secret=key di URL)</p>
                </div>

                <button type="submit" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200" @if($isDown) disabled @endif>
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Enable Maintenance Mode
                </button>
            </form>
        </div>

        <!-- Disable Maintenance Mode -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">
                <svg class="w-6 h-6 inline mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
                Disable Maintenance Mode
            </h3>
            <div class="mb-4">
                <p class="text-gray-600">Nonaktifkan mode pemeliharaan untuk mengizinkan semua pengguna mengakses aplikasi kembali.</p>
            </div>

            @if($isDown)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Aplikasi saat ini dalam mode pemeliharaan. Klik tombol di bawah untuk mengaktifkan kembali.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form id="disableMaintenanceForm">
                @csrf
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200" @if(!$isDown) disabled @endif>
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                    Disable Maintenance Mode
                </button>
            </form>

            <!-- Cache Management -->
            <div class="mt-8 pt-8 border-t border-gray-200">
                <h4 class="text-lg font-bold text-gray-900 mb-4">Cache Management</h4>
                <div class="space-y-2">
                    <button onclick="clearCache('all')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        Clear All Cache
                    </button>
                    <button onclick="clearCache('config')" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        Clear Config Cache
                    </button>
                    <button onclick="clearCache('route')" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        Clear Route Cache
                    </button>
                    <button onclick="clearCache('view')" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm">
                        Clear View Cache
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Panel -->
    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-blue-900">Informasi Penting</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Mode pemeliharaan akan memblokir semua pengguna kecuali yang memiliki secret key</li>
                        <li>Pastikan Anda menyimpan secret key jika mengaturnya</li>
                        <li>Gunakan format: <code class="bg-blue-100 px-2 py-1 rounded">{{ url('/') }}?secret=your-secret-key</code></li>
                        <li>Clearing cache dapat membantu mengatasi masalah konfigurasi dan performa</li>
                        <li>Selalu informasikan pengguna sebelum mengaktifkan mode pemeliharaan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('enableMaintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!confirm('Enable maintenance mode? This will prevent users from accessing the application.')) {
        return;
    }

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    fetch('{{ route('admin.security.maintenance.enable') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        window.location.reload();
    })
    .catch(error => {
        alert('Error enabling maintenance mode');
        console.error(error);
    });
});

document.getElementById('disableMaintenanceForm').addEventListener('submit', function(e) {
    e.preventDefault();

    if (!confirm('Disable maintenance mode? Users will be able to access the application again.')) {
        return;
    }

    fetch('{{ route('admin.security.maintenance.disable') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        window.location.reload();
    })
    .catch(error => {
        alert('Error disabling maintenance mode');
        console.error(error);
    });
});

function clearCache(type) {
    if (!confirm(`Clear ${type} cache?`)) {
        return;
    }

    fetch('{{ route('admin.security.cache.clear') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ cache_type: type })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => {
        alert('Error clearing cache');
        console.error(error);
    });
}
</script>
@endsection
