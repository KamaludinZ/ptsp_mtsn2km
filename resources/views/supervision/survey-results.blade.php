@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <a href="{{ route('supervision.management') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Manajemen Survey</a>

                    <h1 class="text-2xl font-bold mt-2 mb-1">{{ $survey->name }}</h1>
                    <p class="text-gray-500 mb-6">{{ ucfirst($survey->type) }} &middot; {{ $survey->responses->count() }} responden</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Rata-rata Skor (1-4)</h2>
                            <p class="text-3xl font-bold text-blue-600">{{ $overallScore }}</p>
                        </div>
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h2 class="text-sm font-semibold text-gray-600 mb-1">Indeks Kepuasan (0-100)</h2>
                            <p class="text-3xl font-bold text-green-600">{{ $satisfactionIndex }}</p>
                        </div>
                    </div>

                    <h2 class="text-lg font-semibold mb-4">Hasil per Pertanyaan</h2>

                    @if(empty($results))
                        <p class="text-gray-500">Belum ada jawaban untuk survey ini.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($results as $result)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <p class="font-medium">{{ $result['question']->question }}</p>
                                        <span class="text-sm text-gray-500 whitespace-nowrap ml-4">{{ $result['total_responses'] }} jawaban</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">Rata-rata: <span class="font-semibold">{{ $result['average_score'] }}</span></p>
                                    <div class="flex gap-3 text-xs text-gray-500">
                                        @foreach($result['distribution'] as $score => $count)
                                            <span>Skor {{ $score }}: {{ $count }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
