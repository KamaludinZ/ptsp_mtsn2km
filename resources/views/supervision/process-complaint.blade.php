@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-2xl font-bold">Processing Complaint #{{ $complaint->id }}</h1>
                        <a href="{{ route('supervision.complaints.dashboard') }}" 
                           class="text-blue-600 hover:text-blue-800">
                            ← Back to Dashboard
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Complaint Info</h2>
                            <p><span class="font-medium">Type:</span> 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($complaint->complaint_type === 'complaint') bg-red-100 text-red-800
                                    @elseif($complaint->complaint_type === 'suggestion') bg-green-100 text-green-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($complaint->complaint_type) }}
                                </span>
                            </p>
                            <p><span class="font-medium">Title:</span> {{ $complaint->title }}</p>
                            <p><span class="font-medium">Status:</span> 
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($complaint->status === 'resolved' || $complaint->status === 'closed') bg-green-100 text-green-800
                                    @elseif($complaint->status === 'in_progress') bg-yellow-100 text-yellow-800
                                    @elseif($complaint->status === 'in_review') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $complaint->status)) }}
                                </span>
                            </p>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Submitter Info</h2>
                            @if($complaint->anonymous)
                                <p><span class="font-medium">Anonymous:</span> Yes</p>
                            @else
                                <p><span class="font-medium">Name:</span> {{ $complaint->complainant_name }}</p>
                                <p><span class="font-medium">Email:</span> {{ $complaint->complainant_email }}</p>
                                @if($complaint->complainant_contact)
                                    <p><span class="font-medium">Contact:</span> {{ $complaint->complainant_contact }}</p>
                                @endif
                            @endif
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <h2 class="text-lg font-semibold mb-2">Assignment</h2>
                            <p><span class="font-medium">Assigned To:</span> {{ $complaint->assignee->name ?? 'Unassigned' }}</p>
                            <p><span class="font-medium">Submitted:</span> {{ $complaint->created_at->format('d M Y H:i') }}</p>
                            @if($complaint->resolved_at)
                                <p><span class="font-medium">Resolved:</span> {{ $complaint->resolved_at->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Complaint Description -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Description</h2>
                        <div class="border rounded-lg p-4 bg-gray-50">
                            <p class="whitespace-pre-line">{{ $complaint->description }}</p>
                        </div>
                    </div>
                    
                    <!-- Resolution Notes -->
                    @if($complaint->resolution_notes)
                        <div class="mb-8">
                            <h2 class="text-xl font-semibold mb-4">Resolution Notes</h2>
                            <div class="border rounded-lg p-4 bg-yellow-50">
                                <p class="whitespace-pre-line">{{ $complaint->resolution_notes }}</p>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Processing Form -->
                    <div class="mb-8">
                        <h2 class="text-xl font-semibold mb-4">Update Complaint Status</h2>
                        <form method="POST" action="{{ route('supervision.update.complaint', $complaint->id) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select name="status" id="status" required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="submitted" {{ $complaint->status === 'submitted' ? 'selected' : '' }}>Submitted</option>
                                        <option value="in_review" {{ $complaint->status === 'in_review' ? 'selected' : '' }}>In Review</option>
                                        <option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="closed" {{ $complaint->status === 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                                    <select name="assigned_to" id="assigned_to" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">Unassign</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ $complaint->assigned_to === $user->id ? 'selected' : '' }}>
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="resolution_notes" class="block text-sm font-medium text-gray-700 mb-2">Resolution Notes</label>
                                <textarea name="resolution_notes" id="resolution_notes" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                          rows="4" placeholder="Add notes about the resolution...">{{ $complaint->resolution_notes ?? old('resolution_notes') }}</textarea>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                    Update Complaint
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection