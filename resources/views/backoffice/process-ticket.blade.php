@extends('backoffice.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold">Processing Ticket: {{ $ticket->ticket_number }}</h1>
                        <a href="{{ route('backoffice.dashboard') }}" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to Dashboard
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Applicant Info</h2>
                            <p><span class="font-medium">Name:</span> {{ $ticket->user->name }}</p>
                            <p><span class="font-medium">Email:</span> {{ $ticket->user->email }}</p>
                            <p><span class="font-medium">Type:</span> {{ ucfirst($ticket->user->user_type) }}</p>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Service Info</h2>
                            <p><span class="font-medium">Name:</span> {{ $ticket->service->name }}</p>
                            <p><span class="font-medium">Channel:</span> {{ ucfirst($ticket->channel) }}</p>
                            <p><span class="font-medium">Created:</span> {{ $ticket->created_at->format('d M Y H:i') }}</p>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Status</h2>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($ticket->status === 'completed') bg-green-100 text-green-800
                                @elseif($ticket->status === 'in_process') bg-yellow-100 text-yellow-800
                                @elseif($ticket->status === 'approved') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                            <p class="mt-2"><span class="font-medium">Current Handler:</span> {{ $ticket->currentHandler->name ?? 'Unassigned' }}</p>
                        </div>
                    </div>
                    
                    <!-- Service Details -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Service Details</h2>
                        <div class="border rounded-lg p-4">
                            <p>{{ $ticket->service->description }}</p>
                        </div>
                    </div>
                    
                    <!-- Documents -->
                    @if($ticket->files->count() > 0)
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Submitted Documents</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($ticket->files as $file)
                                    <div class="border rounded-lg p-4">
                                        <p class="font-medium">{{ $file->file_name }}</p>
                                        <a href="{{ asset('storage/' . $file->file_path) }}" 
                                           target="_blank" 
                                           class="text-blue-600 hover:underline text-sm">
                                            View Document
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Processing Form -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Update Ticket Status</h2>
                        <form method="POST" action="{{ route('backoffice.update.ticket', $ticket->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">New Status</label>
                                <select name="status" id="status" required 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="">Select Status</option>
                                    <option value="verified" {{ $ticket->status === 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="in_process" {{ $ticket->status === 'in_process' ? 'selected' : '' }}>In Process</option>
                                    <option value="approved" {{ $ticket->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ $ticket->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="completed" {{ $ticket->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                                <textarea name="notes" id="notes" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                          rows="3" placeholder="Add any notes about this ticket...">{{ old('notes') }}</textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                    Update Status
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Assign/Dispatch Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Assign Ticket -->
                        <div class="border rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4">Assign to User</h3>
                            <form method="POST" action="{{ route('backoffice.assign.ticket', $ticket->id) }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <label for="assigned_to_id" class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                                    <select name="assigned_to_id" id="assigned_to_id" required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select User</option>
                                        @foreach(\App\Models\User::where('is_active', true)->get() as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->user_type }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                                        Assign Ticket
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Dispatch Ticket -->
                        <div class="border rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4">Dispatch to Department</h3>
                            <form method="POST" action="{{ route('backoffice.dispatch.ticket', $ticket->id) }}">
                                @csrf
                                
                                <div class="mb-4">
                                    <label for="dispatch_to_id" class="block text-sm font-medium text-gray-700 mb-2">Select Department</label>
                                    <select name="dispatch_to_id" id="dispatch_to_id" required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Select Department</option>
                                        @foreach(\App\Models\User::where('is_active', true)->whereIn('user_type', ['pegawai'])->get() as $user)
                                            @if($user->roles->pluck('name')->intersect(['waka-kesiswaan', 'waka-kurikulum', 'waka-sarpras', 'waka-humas'])->isNotEmpty())
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->user_type }})</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="dispatch_notes" class="block text-sm font-medium text-gray-700 mb-2">Reason for Dispatch</label>
                                    <textarea name="notes" id="dispatch_notes" required 
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                              rows="2"></textarea>
                                </div>
                                
                                <div class="flex justify-end">
                                    <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded">
                                        Dispatch Ticket
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection