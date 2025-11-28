@extends('frontdesk.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Register Offline Service</h1>
                    
                    <form method="POST" action="{{ route('frontdesk.register.offline.service') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="user_type" class="block text-sm font-medium text-gray-700">User Type</label>
                            <select name="user_type" id="user_type" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select User Type</option>
                                @foreach($userTypes as $key => $value)
                                    <option value="{{ $key }}" {{ old('user_type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('user_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="full_name" id="full_name" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('full_name') }}">
                            @error('full_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="service_id" class="block text-sm font-medium text-gray-700">Service</label>
                            <select name="service_id" id="service_id" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select Service</option>
                                @foreach($services as $service)
                                    <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6" id="service-details">
                            <!-- Dynamic service details fields will be added here based on selected service -->
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Register Service
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Add dynamic form fields based on selected service
        document.getElementById('service_id').addEventListener('change', function() {
            const serviceId = this.value;
            const serviceDetailsDiv = document.getElementById('service-details');
            
            // This is a simplified example - in a real app, you would fetch service-specific fields
            if (serviceId) {
                serviceDetailsDiv.innerHTML = `
                    <label class="block text-sm font-medium text-gray-700 mb-2">Service Details</label>
                    <textarea name="service_details[]" rows="3" 
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                              placeholder="Enter service details..."></textarea>
                `;
            } else {
                serviceDetailsDiv.innerHTML = '';
            }
        });
        
        // Trigger change event if service is preselected
        document.getElementById('service_id').dispatchEvent(new Event('change'));
    </script>
@endsection