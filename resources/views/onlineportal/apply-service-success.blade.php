@extends('onlineportal.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Success! </strong>
                        <span class="block sm:inline">Your service application has been submitted.</span>
                    </div>
                    
                    <h1 class="text-2xl font-bold mb-6">Application Confirmation</h1>
                    
                    <div class="border border-gray-200 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="font-semibold">Ticket Number:</p>
                                <p class="text-xl font-bold text-blue-600">{{ $ticket->ticket_number }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Service:</p>
                                <p>{{ $ticket->service->name }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Channel:</p>
                                <p>{{ ucfirst($ticket->channel) }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Submission Date:</p>
                                <p>{{ $ticket->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <p class="mb-4">Please keep your ticket number for tracking purposes.</p>
                        <p>You can track the status of your application using the <a href="{{ route('onlineportal.track.ticket.form') }}" class="text-blue-600 hover:underline">Ticket Tracking</a> feature.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-between gap-4">
                        <div class="flex gap-2">
                            <a href="{{ route('onlineportal.my.services') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                My Services
                            </a>
                            <a href="{{ route('onlineportal.track.ticket.form') }}" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                                Track Status
                            </a>
                        </div>
                        <a href="{{ route('onlineportal.service.catalog') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded self-start">
                            Apply Another Service
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection