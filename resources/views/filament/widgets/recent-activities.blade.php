<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Recent Tickets -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Permohonan Baru</h3>
        <ul class="space-y-2">
            @forelse($recentTickets as $ticket)
                <li class="text-sm text-gray-600 dark:text-gray-300">
                    <div class="font-medium">{{ $ticket->user?->name ?? 'Unknown' }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $ticket->service?->name ?? 'Layanan' }} - {{ $ticket->created_at->format('d/m/Y H:i') }}
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">Tidak ada permohonan baru</li>
            @endforelse
        </ul>
    </div>

    <!-- Recent Complaints -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Pengaduan Masuk</h3>
        <ul class="space-y-2">
            @forelse($recentComplaints as $complaint)
                <li class="text-sm text-gray-600 dark:text-gray-300">
                    <div class="font-medium">{{ $complaint->applicant_name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ Str::limit($complaint->subject, 30) }} - {{ $complaint->created_at->format('d/m/Y H:i') }}
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">Tidak ada pengaduan baru</li>
            @endforelse
        </ul>
    </div>

    <!-- Recent Whistleblowing -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Whistleblowing Masuk</h3>
        <ul class="space-y-2">
            @forelse($recentWhistleblowing as $wbs)
                <li class="text-sm text-gray-600 dark:text-gray-300">
                    <div class="font-medium">{{ $wbs->reporterLabel() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ Str::limit($wbs->subject, 30) }} - {{ $wbs->created_at->format('d/m/Y H:i') }}
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">Tidak ada whistleblowing baru</li>
            @endforelse
        </ul>
    </div>

    <!-- Survey Completions -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Survey Selesai</h3>
        <ul class="space-y-2">
            @forelse($recentSurveyCompletions as $survey)
                <li class="text-sm text-gray-600 dark:text-gray-300">
                    <div class="font-medium">{{ $survey->user?->name ?? 'Pengguna' }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $survey->created_at->format('d/m/Y H:i') }}
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">Tidak ada survey selesai</li>
            @endforelse
        </ul>
    </div>

    <!-- Recent Verified Registrations -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Registrasi Terverifikasi</h3>
        <ul class="space-y-2">
            @forelse($recentVerifiedRegistrations as $user)
                <li class="text-sm text-gray-600 dark:text-gray-300">
                    <div class="font-medium">{{ $user->name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $user->email }} - {{ $user->email_verified_at->format('d/m/Y H:i') }}
                    </div>
                </li>
            @empty
                <li class="text-sm text-gray-500 dark:text-gray-400">Tidak ada registrasi baru</li>
            @endforelse
        </ul>
    </div>

    <!-- Login Activity -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">Aktivitas Login Terbaru</h3>
        <ul class="space-y-2">
            <li class="text-sm text-gray-600 dark:text-gray-300">
                <div class="font-medium">Sistem</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Dashboard aktif - {{ now()->format('d/m/Y H:i') }}
                </div>
            </li>
            <li class="text-sm text-gray-600 dark:text-gray-300">
                <div class="font-medium">Administrator</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Login terakhir - {{ now()->subHours(2)->format('d/m/Y H:i') }}
                </div>
            </li>
        </ul>
    </div>
</div>