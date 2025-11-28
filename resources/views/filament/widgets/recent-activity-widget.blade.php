<div class="p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Aktivitas Terbaru</h3>
    
    <div class="space-y-4">
        @foreach ($this->getRecentActivities() as $activity)
            <div class="flex items-start space-x-3 p-3 bg-white rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                <div class="flex-shrink-0">
                    @if($activity['type'] === 'ticket')
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <x-heroicon-s-ticket class="w-5 h-5 text-blue-600" />
                        </div>
                    @elseif($activity['type'] === 'complaint')
                        <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                            <x-heroicon-s-exclamation-triangle class="w-5 h-5 text-yellow-600" />
                        </div>
                    @else
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                            <x-heroicon-s-user class="w-5 h-5 text-green-600" />
                        </div>
                    @endif
                </div>
                
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">
                        <a href="{{ $activity['url'] }}" class="hover:text-blue-600">
                            {{ $activity['title'] }}
                        </a>
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ $activity['description'] }} • 
                        <span class="text-gray-700">{{ $activity['user'] }}</span> • 
                        {{ $activity['service'] }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ $activity['date'] }}</p>
                </div>
                
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $activity['color'] }}-100 text-{{ $activity['color'] }}-800">
                        @if($activity['type'] === 'ticket')
                            Tiket
                        @elseif($activity['type'] === 'complaint')
                            Pengaduan
                        @else
                            Pengguna
                        @endif
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>