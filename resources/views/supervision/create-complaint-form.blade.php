@extends('supervision.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Submit a Complaint/Suggestion</h1>
                    
                    <form method="POST" action="{{ route('supervision.complaint.submit') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="complaint_type" class="block text-sm font-medium text-gray-700">Type</label>
                            <select name="complaint_type" id="complaint_type" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select Type</option>
                                <option value="complaint" {{ old('complaint_type') === 'complaint' ? 'selected' : '' }}>Complaint</option>
                                <option value="suggestion" {{ old('complaint_type') === 'suggestion' ? 'selected' : '' }}>Suggestion</option>
                                <option value="whistleblowing" {{ old('complaint_type') === 'whistleblowing' ? 'selected' : '' }}>Whistleblowing</option>
                            </select>
                            @error('complaint_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" id="title" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   placeholder="Enter a brief title" 
                                   value="{{ old('title') }}">
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="description" id="description" required 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                      rows="5" placeholder="Provide detailed description...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="anonymous" id="anonymous" value="1" 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                       {{ old('anonymous') ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Submit anonymously</span>
                            </label>
                        </div>
                        
                        <div id="identity-section" class="{{ old('anonymous') ? 'hidden' : '' }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="complainant_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" name="complainant_name" id="complainant_name" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                           placeholder="Your full name" 
                                           value="{{ old('complainant_name') ?? (Auth::user() ? Auth::user()->name : '') }}">
                                    @error('complainant_name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="complainant_email" class="block text-sm font-medium text-gray-700">Email</label>
                                    <input type="email" name="complainant_email" id="complainant_email" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                           placeholder="Your email address" 
                                           value="{{ old('complainant_email') ?? (Auth::user() ? Auth::user()->email : '') }}">
                                    @error('complainant_email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="complainant_contact" class="block text-sm font-medium text-gray-700">Contact Number (Optional)</label>
                                <input type="text" name="complainant_contact" id="complainant_contact" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       placeholder="Phone number or other contact" 
                                       value="{{ old('complainant_contact') }}">
                                @error('complainant_contact')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('anonymous').addEventListener('change', function() {
            const identitySection = document.getElementById('identity-section');
            if (this.checked) {
                identitySection.classList.add('hidden');
            } else {
                identitySection.classList.remove('hidden');
            }
        });
    </script>
@endsection