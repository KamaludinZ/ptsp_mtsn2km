@extends('frontdesk.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Success! </strong>
                        <span class="block sm:inline">Visitor check-out was successful.</span>
                    </div>
                    
                    <h1 class="text-2xl font-bold mb-6">Check-out Confirmation</h1>
                    
                    <div class="border border-gray-200 rounded-lg p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">Name:</p>
                                <p>{{ $visitor->name }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Institution:</p>
                                <p>{{ $visitor->institution }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Card Number:</p>
                                <p>{{ $visitor->visitor_card_number }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Check-out Time:</p>
                                <p>{{ $visitor->check_out_time->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('frontdesk.visitor.checkout.form') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            New Visitor Check-out
                        </a>
                        <a href="{{ route('frontdesk.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection