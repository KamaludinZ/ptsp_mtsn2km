@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Success! </strong>
                        <span class="block sm:inline">Your complaint/suggestion has been submitted.</span>
                    </div>
                    
                    <h1 class="text-2xl font-bold mb-6">Submission Confirmation</h1>
                    
                    <div class="border border-gray-200 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">Complaint ID:</p>
                                <p class="text-xl font-bold text-blue-600">{{ $complaint->id }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Type:</p>
                                <p>{{ ucfirst($complaint->complaint_type) }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Title:</p>
                                <p>{{ $complaint->title }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Status:</p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    bg-gray-100 text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </div>
                            <div class="md:col-span-2">
                                <p class="font-semibold">Submitted:</p>
                                <p>{{ $complaint->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <p class="mb-4">Thank you for your feedback. Our team will review your submission and take appropriate action.</p>
                        @if($complaint->anonymous)
                            <p class="text-sm text-gray-600">Note: This was submitted anonymously, so we won't be able to contact you regarding the outcome.</p>
                        @else
                            <p class="text-sm text-gray-600">We may contact you at {{ $complaint->complainant_email }} for updates on your complaint.</p>
                        @endif
                    </div>
                    
                    <div class="flex justify-between">
                        <a href="{{ route('supervision.complaint.create.form') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Submit Another
                        </a>
                        <a href="{{ route('supervision.complaints.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection