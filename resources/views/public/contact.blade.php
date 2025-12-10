@extends('layouts.public')

@section('title', 'Hubungi Kami - ' . config('app.name', 'PTSP MTsN 2 Kota Malang'))

@push('styles')
    @if(config('app.env') === 'local' && config('assets.mode', 'vite') === 'vite' && App\Helpers\AssetHelper::isViteRunning())
        @vite(['resources/css/contact.css'])
    @else
        {!! App\Helpers\AssetHelper::css('resources/css/contact.css') !!}
    @endif
@endpush

@section('content')
<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5" data-aos="fade-up">
        <h1 class="text-4xl font-bold mb-3">
            Hubungi <span class="text-primary">Kami</span>
        </h1>
        <p class="text-lg text-base-content/70">
            Kami siap membantu Anda. Silakan hubungi kami melalui form di bawah ini atau datang langsung ke lokasi kami.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Form -->
        <div class="card bg-base-100 shadow-xl" data-aos="fade-right">
            <div class="card-body">
                <h2 class="card-title">Kirim Pesan</h2>
                <form action="{{ route('public.contact.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">
                                <span class="label-text">Nama Lengkap *</span>
                            </label>
                            <input type="text" name="name" class="input input-bordered w-full" required>
                        </div>
                        <div>
                            <label class="label">
                                <span class="label-text">Email *</span>
                            </label>
                            <input type="email" name="email" class="input input-bordered w-full" required>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="label">
                                <span class="label-text">Subjek *</span>
                            </label>
                            <input type="text" name="subject" class="input input-bordered w-full" required>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <label class="label">
                                <span class="label-text">Pesan *</span>
                            </label>
                            <textarea name="message" class="textarea textarea-bordered w-full" rows="5" required></textarea>
                        </div>
                        <div class="col-span-1 md:col-span-2">
                            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contact Info & Map -->
        <div data-aos="fade-left">
            <div class="card bg-base-100 shadow-xl mb-8">
                <div class="card-body">
                    <h2 class="card-title">Informasi Kontak</h2>
                    <div class="space-y-4">
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </div>
                            <div>
                                <h5 class="font-bold">Alamat</h5>
                                <p class="text-base-content/70">Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-phone text-primary"></i>
                            </div>
                            <div>
                                <h5 class="font-bold">Telepon</h5>
                                <p class="text-base-content/70">(0341) 711500</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                                <i class="fas fa-envelope text-primary"></i>
                            </div>
                            <div>
                                <h5 class="font-bold">Email</h5>
                                <p class="text-base-content/70">mtsnmalang2adm@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Lokasi Kami</h2>
                    <div class="w-full h-64 rounded-2xl overflow-hidden">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3951.273632249995!2d112.6649996147799!3d-7.97093999425958!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd6285c24a02c3b%3A0x4f8a682f0f2b2f3!2sMTsN%202%20Kota%20Malang!5e0!3m2!1sen!2sid!4v1633582834795!5m2!1sen!2sid" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
