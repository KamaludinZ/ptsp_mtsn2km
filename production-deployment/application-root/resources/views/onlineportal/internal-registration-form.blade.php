@extends('onlineportal.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Internal User Registration</h1>
                    
                    <p class="mb-6">For internal users (Guru, Pegawai, Siswa, Wali Murid, Alumni) with a valid registration code.</p>
                    
                    <form method="POST" action="{{ route('onlineportal.internal.registration') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="registration_code" class="block text-sm font-medium text-gray-700">Registration Code</label>
                            <input type="text" name="registration_code" id="registration_code" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   placeholder="Enter your 6-digit registration code" 
                                   value="{{ old('registration_code') }}">
                            @error('registration_code')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="user_type" class="block text-sm font-medium text-gray-700">User Type</label>
                            <select name="user_type" id="user_type" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Select User Type</option>
                                <option value="guru" {{ old('user_type') === 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="pegawai" {{ old('user_type') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                                <option value="siswa" {{ old('user_type') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                                <option value="walimurid" {{ old('user_type') === 'walimurid' ? 'selected' : '' }}>Wali Murid</option>
                                <option value="alumni" {{ old('user_type') === 'alumni' ? 'selected' : '' }}>Alumni</option>
                            </select>
                            @error('user_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="name" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   placeholder="Enter your full name" 
                                   value="{{ old('name') }}">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                   placeholder="Enter your email address" 
                                   value="{{ old('email') }}">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <input type="password" name="password" id="password" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       placeholder="Create a password">
                                @error('password')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" required 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                                       placeholder="Confirm your password">
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                                Register
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-6 text-center">
                        <p>Already have an account? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a></p>
                        <p class="mt-2">External users? <a href="{{ route('onlineportal.external.registration.form') }}" class="text-blue-600 hover:underline">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection