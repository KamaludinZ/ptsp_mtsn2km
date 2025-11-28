@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold">{{ $survey->name }}</h1>
                        <a href="{{ route('onlineportal.my.services') }}" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to My Services
                        </a>
                    </div>
                    
                    <p class="mb-6">{{ $survey->description }}</p>
                    
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <p class="font-medium">Related Service:</p>
                        <p>{{ $ticket->service->name }}</p>
                        <p class="text-sm text-gray-600">Ticket: {{ $ticket->ticket_number }}</p>
                    </div>
                    
                    <form method="POST" action="{{ route('supervision.skm.submit', [$ticket->id, $survey->id]) }}">
                        @csrf
                        
                        @foreach($survey->questions as $question)
                            <div class="mb-6 p-4 border rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ $loop->iteration }}. {{ $question->question_text }}
                                    @if($question->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                
                                @if($question->question_type === 'rating')
                                    <div class="flex flex-wrap gap-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="flex items-center bg-gray-100 px-3 py-2 rounded cursor-pointer hover:bg-gray-200">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                       @if($question->is_required) required @endif>
                                                <span class="ml-2">{{ $i }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>1 (Very Poor)</span>
                                        <span>5 (Excellent)</span>
                                    </div>
                                @else
                                    <textarea name="answers[{{ $question->id }}]" 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                              rows="3" @if($question->is_required) required @endif></textarea>
                                @endif
                            </div>
                        @endforeach
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Submit Survey
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection