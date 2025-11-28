@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold">{{ $survey->name }}</h1>
                        <a href="{{ route('supervision.surveys.dashboard') }}" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to Surveys
                        </a>
                    </div>
                    
                    <p class="mb-6">{{ $survey->description }}</p>
                    
                    <form method="POST" action="{{ route('supervision.survey.submit', $survey->id) }}">
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
                                    <div class="flex space-x-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="flex items-center">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $i }}"
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                       @if($question->is_required) required @endif>
                                                <span class="ml-2">{{ $i }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                @elseif($question->question_type === 'yes_no')
                                    <div class="flex space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="Yes"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                   @if($question->is_required) required @endif>
                                            <span class="ml-2">Yes</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="No"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                   @if($question->is_required) required @endif>
                                            <span class="ml-2">No</span>
                                        </label>
                                    </div>
                                @elseif($question->question_type === 'multiple_choice')
                                    <div class="space-y-2">
                                        @php
                                            $options = explode("\n", $question->question_text);
                                            $questionText = $options[0];
                                            $options = array_slice($options, 1);
                                        @endphp
                                        
                                        @if(count($options) > 0)
                                            @foreach($options as $option)
                                                <label class="flex items-center">
                                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ trim($option) }}"
                                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                           @if($question->is_required) required @endif>
                                                    <span class="ml-2">{{ trim($option) }}</span>
                                                </label>
                                            @endforeach
                                        @else
                                            <textarea name="answers[{{ $question->id }}]" 
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                      rows="3" @if($question->is_required) required @endif></textarea>
                                        @endif
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