@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h1 class="text-2xl font-bold">{{ $ticket->ticket_number }}</h1>
                            <p class="text-gray-500">{{ $ticket->service->name ?? '-' }}</p>
                        </div>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                        </span>
                    </div>

                    <div class="mb-6">
                        <h2 class="text-sm font-semibold text-gray-600 mb-1">Keterangan</h2>
                        <p class="text-gray-800">{{ $ticket->notes }}</p>
                    </div>

                    @if($canDownload)
                        <div class="mb-6">
                            <a href="{{ route('onlineportal.ticket.download', $ticket->ticket_number) }}" class="inline-block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded">
                                Unduh Hasil Layanan
                            </a>
                        </div>
                    @endif

                    @if($ticket->files->isNotEmpty())
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold mb-2">Berkas Terlampir</h2>
                            <ul class="list-disc list-inside text-sm text-gray-700">
                                @foreach($ticket->files as $file)
                                    <li>{{ $file->file_name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($ticket->workflowSteps->isNotEmpty())
                        <div class="mb-6">
                            <h2 class="text-lg font-semibold mb-2">Progres Layanan</h2>
                            <ul class="space-y-1 text-sm">
                                @foreach($ticket->workflowSteps as $step)
                                    <li class="flex justify-between border-b py-1">
                                        <span>{{ $step->workflowStep->name ?? '-' }}</span>
                                        <span class="text-gray-500">{{ ucfirst($step->status) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div>
                        <h2 class="text-lg font-semibold mb-2">Riwayat</h2>
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

                    <div class="mt-6">
                        <a href="{{ route('onlineportal.my-tickets') }}" class="text-blue-600 hover:underline">&larr; Kembali ke Tiket Saya</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
