@extends('backoffice.layout')

@section('content')
    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-2xl font-bold">{{ $ticket->ticket_number }}</h1>
                            <p class="text-gray-500">{{ $ticket->service->name ?? '-' }} &middot; {{ $ticket->user->name ?? '-' }}</p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>
                    <p class="text-gray-700 mb-2"><strong>Petugas:</strong> {{ $ticket->assignedTo->name ?? 'Belum ditugaskan' }}</p>
                    <p class="text-gray-700"><strong>Keterangan:</strong> {{ $ticket->notes }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Tugaskan Tiket</h2>
                        <form method="POST" action="{{ route('backoffice.tickets.assign', $ticket) }}">
                            @csrf
                            <select name="assigned_to" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                                <option value="">-- Pilih Petugas --</option>
                                @foreach($availableStaff as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="notes" placeholder="Catatan (opsional)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Tugaskan</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Ubah Status</h2>
                        <form method="POST" action="{{ route('backoffice.tickets.update-status', $ticket) }}">
                            @csrf
                            <select name="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                                @foreach(['submitted' => 'Diajukan', 'verified' => 'Diverifikasi', 'in_process' => 'Diproses', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $key => $label)
                                    <option value="{{ $key }}" {{ $ticket->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <textarea name="notes" required placeholder="Keterangan perubahan status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3"></textarea>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Perbarui Status</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Tambah Catatan</h2>
                        <form method="POST" action="{{ route('backoffice.tickets.add-note', $ticket) }}">
                            @csrf
                            <textarea name="note" required placeholder="Catatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3"></textarea>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Simpan Catatan</button>
                        </form>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Unggah Berkas</h2>
                        <form method="POST" action="{{ route('backoffice.tickets.upload-file', $ticket) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" required class="mt-1 block w-full text-sm mb-3">
                            <input type="text" name="description" placeholder="Keterangan (opsional)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm mb-3">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">Unggah</button>
                        </form>
                    </div>
                </div>
            </div>

            @if($ticket->files->isNotEmpty())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-3">Berkas</h2>
                        <ul class="space-y-1 text-sm">
                            @foreach($ticket->files as $file)
                                <li>
                                    <a href="{{ route('backoffice.tickets.download-file', [$ticket, $file]) }}" class="text-blue-600 hover:underline">{{ $file->file_name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-lg font-semibold mb-3">Riwayat</h2>
                    @if($ticket->logs->isEmpty())
                        <p class="text-gray-500 text-sm">Belum ada riwayat.</p>
                    @else
                        <ul class="space-y-2 text-sm">
                            @foreach($ticket->logs as $log)
                                <li class="border-b pb-2">
                                    <span class="text-gray-800">{{ $log->notes ?? ucfirst(str_replace('_', ' ', $log->action)) }}</span>
                                    <span class="block text-xs text-gray-400">{{ $log->created_at->format('d M Y H:i') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <a href="{{ route('backoffice.tickets.queue') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Antrian</a>
        </div>
    </div>
@endsection
