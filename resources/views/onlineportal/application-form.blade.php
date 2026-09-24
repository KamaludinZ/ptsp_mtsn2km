@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-2">Ajukan Layanan: {{ $service->name }}</h1>
                    <p class="text-gray-600 mb-6">{{ $service->description }}</p>

                    @if($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc list-inside text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('onlineportal.service.submit', $service->slug) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">Keterangan Pengajuan</label>
                            <textarea name="description" id="description" rows="4" required
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="urgency_level" class="block text-sm font-medium text-gray-700">Tingkat Urgensi</label>
                            <select name="urgency_level" id="urgency_level" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="normal">Normal</option>
                                <option value="high">Tinggi</option>
                                <option value="urgent">Mendesak</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label for="files" class="block text-sm font-medium text-gray-700">Berkas Pendukung (opsional)</label>
                            <input type="file" name="files[]" id="files" multiple class="mt-1 block w-full text-sm">
                        </div>

                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                            Ajukan Layanan
                        </button>
                        <a href="{{ route('onlineportal.service.detail', $service->slug) }}" class="ml-2 text-gray-600 hover:underline">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
