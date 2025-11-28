@extends('frontdesk.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Front Desk Dashboard</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-blue-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Active Visitors</h2>
                            <p class="text-3xl font-bold text-blue-600">{{ $activeVisitorsCount ?? 0 }}</p>
                            <a href="{{ route('frontdesk.active.visitors') }}" class="text-blue-500 hover:underline mt-2 inline-block">View Details</a>
                        </div>
                        
                        <div class="bg-green-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Today's Services</h2>
                            <p class="text-3xl font-bold text-green-600">{{ $todayServicesCount ?? 0 }}</p>
                            <a href="#" class="text-green-500 hover:underline mt-2 inline-block">View Details</a>
                        </div>
                        
                        <div class="bg-yellow-50 p-6 rounded-lg shadow">
                            <h2 class="text-lg font-semibold mb-2">Pending Tickets</h2>
                            <p class="text-3xl font-bold text-yellow-600">{{ $pendingTicketsCount ?? 0 }}</p>
                            <a href="#" class="text-yellow-500 hover:underline mt-2 inline-block">View Details</a>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white p-6 rounded-lg shadow border">
                            <h2 class="text-lg font-semibold mb-4">Quick Actions</h2>
                            <div class="space-y-3">
                                <a href="{{ route('frontdesk.triage') }}" class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                    Triage Visitor
                                </a>
                                <a href="{{ route('frontdesk.visitor.checkin.form') }}" class="block w-full text-center bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                                    Visitor Check-in
                                </a>
                                <a href="{{ route('frontdesk.register.offline.service.form') }}" class="block w-full text-center bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded">
                                    Register Offline Service
                                </a>
                            </div>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg shadow border">
                            <h2 class="text-lg font-semibold mb-4">Recent Visitors</h2>
                            <ul class="space-y-2">
                                @forelse($recentVisitors ?? [] as $visitor)
                                    <li class="border-b pb-2">
                                        <span class="font-medium">{{ $visitor->name }}</span> - {{ $visitor->institution }}
                                        <span class="text-sm text-gray-500 ml-2">{{ $visitor->check_in_time->format('H:i') }}</span>
                                    </li>
                                @empty
                                    <li class="text-gray-500">No recent visitors</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection