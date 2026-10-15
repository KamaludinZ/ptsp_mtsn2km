<?php

/*
 * Isi Panduan Menu portal pemohon (/portal), dibaca App\Support\PanelGuide.
 * Kunci: kelas halaman/resource Filament, atau "nav:<label>" untuk menu
 * tambahan panel (PortalPanelProvider).
 */

return [

    \App\Filament\Portal\Pages\Dashboard::class => [
        'ringkas' => 'Halaman pertama portal pemohon (guru, pegawai, siswa, wali murid, alumni, instansi, dan umum). Di sini Anda memantau permohonan, mengunduh hasil layanan, dan mengisi survei.',
        'langkah' => [
            'Lihat kartu "Total permohonan", "Sedang diproses", dan "Selesai".',
            'Bila muncul kotak "Bantu kami meningkatkan layanan", klik "Isi survei kepuasan" untuk menilai layanan yang sudah selesai.',
            'Di tabel "Hasil layanan", klik "Unduh" untuk mengambil hasil layanan yang sudah jadi.',
            'Tabel "Permohonan terbaru" menampilkan status permohonan Anda; klik "Ajukan layanan" untuk membuat permohonan baru.',
        ],
    ],

    'nav:Katalog Layanan' => [
        'ringkas' => 'Daftar semua layanan PTSP beserta syarat, waktu proses, dan biayanya. Dari sini Anda memilih layanan lalu mengajukan permohonan.',
        'langkah' => [
            'Ketik nama layanan di kotak "Cari layanan" atau pilih kategori untuk mempersempit daftar.',
            'Buka "Detail Layanan" atau klik "Lihat 14 komponen standar pelayanan" untuk membaca syarat dan alurnya.',
            'Klik "Ajukan Permohonan" pada layanan yang dipilih.',
            'Di formulir pengajuan, isi "Keperluan / keterangan", pilih "Tingkat kepentingan", dan unggah "Berkas persyaratan" (unduh dulu template berkas bila tersedia).',
            'Klik "Kirim permohonan", lalu klik "Cetak tanda terima" pada pemberitahuan bila perlu bukti.',
        ],
        'tips' => 'Siapkan berkas persyaratan dalam bentuk pindaian atau foto yang jelas sebelum mengajukan.',
    ],

    \App\Filament\Portal\Resources\TicketResource::class => [
        'ringkas' => 'Semua permohonan yang pernah Anda ajukan, baik online maupun lewat loket, beserta status dan perkembangannya.',
        'langkah' => [
            'Lihat kolom No. Tiket, Layanan, Status, Diajukan, dan Perkiraan selesai; saring dengan "Status" bila perlu.',
            'Klik "Detail" untuk melihat ringkasan, berkas yang dikirim, dan riwayat "Perkembangan" permohonan.',
            'Bila petugas meminta berkas tambahan, buka permohonan lalu klik "Tambah berkas" dan unggah berkasnya.',
            'Setelah selesai, klik "Unduh hasil layanan" (atau "Unduh hasil" di daftar), lalu "Beri penilaian layanan" untuk mengisi survei.',
            'Klik "Tanda terima" untuk mencetak bukti permohonan, dan "Ajukan layanan" untuk membuat permohonan baru.',
        ],
    ],

    \App\Filament\Portal\Pages\Notifications::class => [
        'ringkas' => 'Pemberitahuan tentang permohonan Anda: diterima, diproses, ditolak, atau selesai dan siap diambil.',
        'langkah' => [
            'Pilih tab "Semua" atau "Belum dibaca".',
            'Klik baris notifikasi atau tombol "Buka" untuk langsung ke permohonan terkait.',
            'Klik "Tandai semua dibaca" untuk membersihkan notifikasi yang sudah dilihat.',
            'Hapus notifikasi lama dengan tombol "Hapus".',
        ],
        'tips' => 'Notifikasi juga bisa dibuka lewat menu akun (kanan atas) > Notifikasi.',
    ],

];
