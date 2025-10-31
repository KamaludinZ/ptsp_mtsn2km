@extends('onlineportal.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold">Ticket Tracking</h1>
                        <a href="{{ route('onlineportal.track.ticket.form') }}" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to Search
                        </a>
                    </div>
                    
                    <div class="border border-gray-200 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="font-semibold">Ticket Number:</p>
                                <p class="text-xl font-bold text-blue-600">{{ $ticket->ticket_number }}</p>
                            </div>
                            <div>
                                <p class="font-semibold">Applicant:</p>
                                <p>{{ $ticket->user->name }}</p>
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
                                <p class="font-semibold">Status:</p>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($ticket->status === 'completed') bg-green-100 text-green-800
                                    @elseif($ticket->status === 'in_process') bg-yellow-100 text-yellow-800
                                    @elseif($ticket->status === 'approved') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-semibold">Submission Date:</p>
                                <p>{{ $ticket->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress Timeline -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Processing Timeline</h2>
                        <div class="space-y-4">
                            @forelse($ticket->logs as $log)
                                <div class="flex">
                                    <div class="flex flex-col items-center mr-4">
                                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-full bg-gray-300 mt-2"></div>
                                        @endif
                                    </div>
                                    <div class="pb-4">
                                        <p class="font-medium">
                                            {{ $log->action }} 
                                            @if($log->performed_by)
                                                <span class="font-normal">by {{ $log->performer->name ?? 'Unknown' }}</span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-600">{{ $log->created_at->format('d M Y H:i') }}</p>
                                        @if($log->notes)
                                            <p class="text-sm mt-1">{{ $log->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-gray-500">No processing steps recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                    
                    <!-- Outputs if completed -->
                    @if($ticket->status === 'completed' && $ticket->outputs->count() > 0)
                        <div class="mb-6">
                            <h2 class="text-xl font-semibold mb-4">Service Output</h2>
                            <div class="border rounded-lg p-4">
                                @foreach($ticket->outputs as $output)
                                    <div class="mb-3 last:mb-0">
                                        <p class="font-medium">Output Type: {{ ucfirst($output->output_type) }}</p>
                                        <p>{{ $output->output_description }}</p>
                                        @if($output->output_type === 'digital' && $output->file_path)
                                            <a href="{{ asset('storage/' . $output->file_path) }}" 
                                               target="_blank" 
                                               class="text-blue-600 hover:underline">
                                               Download File
                                            </a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="flex justify-between">
                        <a href="{{ route('onlineportal.track.ticket.form') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Track Another Ticket
                        </a>
                        @auth
                            <a href="{{ route('onlineportal.my.services') }}" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded">
                                My Services
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection