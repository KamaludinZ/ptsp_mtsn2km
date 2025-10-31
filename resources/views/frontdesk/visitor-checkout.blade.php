@extends('frontdesk.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Visitor Check-out</h1>
                    
                    <form method="POST" action="{{ route('frontdesk.visitor.checkout') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="visitor_card_number" class="block text-sm font-medium text-gray-700">Visitor Card Number</label>
                            <input type="text" name="visitor_card_number" id="visitor_card_number" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   placeholder="Enter visitor card number">
                            @error('visitor_card_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            
                            @if(session('error'))
                                <p class="text-red-500 text-xs mt-1">{{ session('error') }}</p>
                            @endif
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Check-out Visitor
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-8">
                        <h2 class="text-lg font-semibold mb-4">Active Visitors</h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Institution</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($activeVisitors ?? [] as $visitor)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $visitor->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $visitor->institution }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $visitor->visitor_card_number }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $visitor->check_in_time->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">No active visitors</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection