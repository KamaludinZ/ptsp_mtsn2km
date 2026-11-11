<?php

// Masuk lewat Super App (SSO tiket), dari docs/sso Super App Madrasah.
// Nilai rahasia diambil dari .env: jangan tulis kunci langsung di file ini.

return [
    // Alamat API SSO Super App, mis. https://superapp.madrasah.sch.id/api/sso (Admin > Aplikasi Terhubung).
    'url' => env('SUPERAPP_SSO_URL'),

    // Kunci rahasia aplikasi PTSP (diawali "sso_"), tampil sekali saat aplikasi didaftarkan di Super App.
    'kunci' => env('SUPERAPP_SSO_KUNCI'),

    // Halaman Super App yang dibuka tombol "Masuk lewat Super App" di halaman login (dashboard Super App).
    // Kosong: diambil dari SUPERAPP_SSO_URL tanpa akhiran /api/sso.
    'portal' => env('SUPERAPP_SSO_PORTAL_URL'),

    // Urutan pencocokan akun: data dari Super App (kiri) => kolom tabel users PTSP (kanan).
    // PTSP masuk dengan e-mail, jadi akun dicocokkan lewat e-mail.
    'cocokkan' => [
        'email' => 'email',
    ],

    // Pemeriksaan ulang di sisi PTSP: jenis pengguna Super App yang boleh masuk.
    // Hak akses utama tetap diatur admin Super App; ini pengaman kedua.
    'jenis_diizinkan' => ['siswa', 'guru', 'pegawai'],

    // Batas waktu menghubungi Super App (detik).
    'timeout' => 10,
];
