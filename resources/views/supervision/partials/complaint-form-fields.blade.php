<div class="mb-4">
    <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
    <input type="text" name="title" id="title" required 
           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
           placeholder="Masukkan judul singkat" 
           value="{{ old('title') }}">
    @error('title')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
    <textarea name="description" id="description" required 
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              rows="5" placeholder="Berikan deskripsi detail...">{{ old('description') }}</textarea>
    @error('description')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label class="flex items-center">
        <input type="checkbox" name="anonymous" id="anonymous-{{ $is_whistleblowing ?? false ? 'whistleblowing' : 'dumas' }}" value="1" 
               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 anonymous-checkbox"
               {{ old('anonymous') ? 'checked' : '' }}>
        <span class="ml-2 text-sm text-gray-700">Kirim secara anonim</span>
    </label>
</div>

<div id="identity-section-{{ $is_whistleblowing ?? false ? 'whistleblowing' : 'dumas' }}" class="{{ old('anonymous') ? 'hidden' : '' }}">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label for="complainant_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input type="text" name="complainant_name" id="complainant_name" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                   placeholder="Nama lengkap Anda" 
                   value="{{ old('complainant_name') ?? (Auth::user() ? Auth::user()->name : '') }}">
            @error('complainant_name')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <label for="complainant_email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="complainant_email" id="complainant_email" 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
                   placeholder="Alamat email Anda" 
                   value="{{ old('complainant_email') ?? (Auth::user() ? Auth::user()->email : '') }}">
            @error('complainant_email')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
    
    <div class="mb-4">
        <label for="complainant_contact" class="block text-sm font-medium text-gray-700">Nomor Kontak (Opsional)</label>
        <input type="text" name="complainant_contact" id="complainant_contact" 
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" 
               placeholder="Nomor telepon atau kontak lainnya" 
               value="{{ old('complainant_contact') }}">
        @error('complainant_contact')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>