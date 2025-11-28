@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Surveys Management</h1>
                    
                    @if($surveys->isEmpty())
                        <p class="text-gray-500">No surveys available.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($surveys as $survey)
                                <div class="border rounded-lg p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <h2 class="text-lg font-semibold">{{ $survey->name }}</h2>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $survey->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $survey->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-600 mb-4">{{ $survey->description }}</p>
                                    
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-500">Type: {{ $survey->type === 'skm' ? 'Survei Kepuasan Masyarakat' : ($survey->type === 'spak' ? 'Survei Persepsi Anti Korupsi' : ucfirst($survey->type)) }}</p>
                                        <p class="text-sm text-gray-500">Period: {{ $survey->start_date->format('d M Y') }} - {{ $survey->end_date ? $survey->end_date->format('d M Y') : 'Ongoing' }}</p>
                                    </div>
                                    
                                    @if($survey->is_active)
                                        @if($survey->type === 'skm')
                                            <a href="{{ route('onlineportal.skm.form') }}" 
                                               class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                                Take Survey
                                            </a>
                                        @elseif($survey->type === 'spak')
                                            <a href="{{ route('supervision.spak.form') }}" 
                                               class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                                Take Survey
                                            </a>
                                        @else
                                            <a href="{{ route('supervision.survey.form', $survey->id) }}" 
                                               class="inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                                Take Survey
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Survey not available</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection