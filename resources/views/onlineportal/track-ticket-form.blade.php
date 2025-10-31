@extends('onlineportal.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Track Ticket Status</h1>
                    
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('onlineportal.track.ticket') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="ticket_number" class="block text-sm font-medium text-gray-700">Ticket Number</label>
                            <input type="text" name="ticket_number" id="ticket_number" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   placeholder="Enter ticket number" 
                                   value="{{ request('ticket_number') ?? old('ticket_number') }}">
                            @error('ticket_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Track Ticket
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection