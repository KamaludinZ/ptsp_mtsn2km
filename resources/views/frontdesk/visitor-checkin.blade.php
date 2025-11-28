@extends('frontdesk.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Visitor Check-in</h1>
                    
                    <form method="POST" action="{{ route('frontdesk.visitor.checkin') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="name" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('name') }}">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="institution" class="block text-sm font-medium text-gray-700">Institution / Company</label>
                            <input type="text" name="institution" id="institution" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('institution') }}">
                            @error('institution')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="purpose" class="block text-sm font-medium text-gray-700">Purpose of Visit</label>
                            <textarea name="purpose" id="purpose" required 
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                      rows="3">{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="person_to_meet" class="block text-sm font-medium text-gray-700">Person to Meet</label>
                            <input type="text" name="person_to_meet" id="person_to_meet" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   value="{{ old('person_to_meet') }}">
                            @error('person_to_meet')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Take Photo</label>
                            <div id="camera-container" class="border rounded p-4 bg-gray-100">
                                <video id="video" width="320" height="240" autoplay class="mx-auto"></video>
                                <button type="button" id="capture-btn" class="mt-2 bg-gray-500 hover:bg-gray-600 text-white py-1 px-3 rounded">
                                    Capture Photo
                                </button>
                                <canvas id="canvas" width="320" height="240" class="hidden"></canvas>
                                <div id="photo-preview" class="mt-2"></div>
                                <input type="hidden" name="photo" id="photo-input">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Check-in Visitor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Get elements
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const captureBtn = document.getElementById('capture-btn');
        const photoPreview = document.getElementById('photo-preview');
        const photoInput = document.getElementById('photo-input');
        
        // Get access to the camera
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
                video.srcObject = stream;
            });
        }
        
        // Capture photo
        captureBtn.addEventListener('click', function() {
            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, 320, 240);
            
            // Preview the captured photo
            const dataUrl = canvas.toDataURL('image/png');
            photoPreview.innerHTML = '<img src="' + dataUrl + '" width="160" height="120" />';
            
            // Store the image data in the hidden input
            photoInput.value = dataUrl;
        });
    </script>
@endsection