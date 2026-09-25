# Peran & Hak Akses

Peran dikelola dengan `spatie/laravel-permission` dan dibuat oleh `database/seeders/UserSeeder.php`.
Nama peran di bawah ini adalah satu-satunya yang dicek oleh aplikasi. Nama lama (`tu`, `petugas_tu`,
`petugas_loket`, `kepala-sekolah`, `kepala-tu`) dipindahkan otomatis oleh migration
`2026_09_25_090000_merge_legacy_staff_roles`.

## Peran staf

Didefinisikan di `App\Models\User::STAFF_ROLES`.

| Peran | Untuk | Halaman utama | Panel `/cp` |
| --- | --- | --- | --- |
| `admin` | Administrator sistem: master layanan, pengguna, peran, pengaturan, keamanan | `/admin` | Ya |
| `kepala_sekolah` | Dashboard eksekutif (Modul 13), persetujuan layanan (Modul 8), laporan SKM/SPAK | `/pimpinan` | Ya |
| `kepala_tu` | Dashboard eksekutif, persetujuan, kontrol pelayanan loket & back office | `/pimpinan` | Ya |
| `back_office` | Petugas TU: verifikasi, disposisi, status tiket, unggah hasil layanan | `/backoffice/dashboard` | Ya |
| `front_desk` | Petugas loket: triage, buku tamu, registrasi layanan offline | `/frontdesk/dashboard` | Ya |
| `supervisor` | Pengawasan: manajemen survei, kinerja, pengaduan | `/supervision/management` | Tidak |

`App\Models\User::LEADERSHIP_ROLES` = `kepala_sekolah`, `kepala_tu`, `supervisor`.

Pengalihan setelah login ada di satu tempat, yaitu `get_dashboard_route_for_user()`
(`app/Helpers/helpers.php`), yang juga dipakai oleh `DashboardController`.

## Peran pemohon

`guru`, `pegawai`, `siswa`, `walimurid`, `alumni`, `instansi`, `umum`. Peran ini sama dengan
`user_type` dan menentukan layanan yang tampil di katalog (`Service::availableFor()`).

- Pendaftaran umum otomatis mendapat peran `umum`.
- Civitas (siswa, guru, pegawai, dan lain-lain) mendaftar dengan kode registrasi 10 digit yang dibuat admin.

## Permission area staf

Permission area dimiliki **peran**, bukan pengguna. Pemetaannya ada di satu tempat,
`App\Support\RoleAccess::ROLE_PERMISSIONS`, dan diterapkan oleh seeder serta migration
`2026_09_25_100000_grant_area_permissions_to_roles`.

| Permission | Melindungi | Peran |
| --- | --- | --- |
| `frontdesk.access` | `/frontdesk/*` (loket, buku tamu, registrasi offline, serah produk) | admin, kepala_tu, front_desk |
| `backoffice.access` | `/backoffice/*` (antrian tugas, proses tiket, unggah hasil) | admin, kepala_sekolah, kepala_tu, back_office |
| `supervision.access` | `/supervision/*` (dashboard pengawasan, kinerja pelayanan) | admin, kepala_sekolah, kepala_tu, supervisor |

Area lain:

| Area | Peran |
| --- | --- |
| `/pimpinan` (dashboard eksekutif) dan `/pimpinan/persetujuan` | admin, kepala_sekolah, kepala_tu |
| Tindak lanjut pengaduan & whistleblowing, laporan SKM/SPAK/kinerja (`/admin/complaints`, `/admin/whistleblowing`, `/admin/*-report`) | admin, supervisor, kepala_sekolah, kepala_tu |
| Sisa `/admin/*` (master data, pengguna, keamanan, pengaturan) | admin |

Panel Filament `/cp` diatur oleh `User::canAccessPanel()`. Menu samping hanya menampilkan
menu yang boleh dibuka peran tersebut; `tests/Feature/DashboardNavigationTest` memastikan
setiap tautan di dashboard setiap peran bisa dibuka.

## Alur tiket & persetujuan

1. Pemohon mengajukan online, atau petugas loket mendaftarkannya offline. Target selesai
   (`estimated_completion_date`) dihitung dari jangka waktu standar layanan (hari kerja).
2. Petugas TU memverifikasi (`verified`) lalu memproses (`in_process`).
3. Pimpinan memutuskan di `/pimpinan/persetujuan`: **setujui**, dengan memilih tanda tangan TTE
   atau TTD, atau **tolak** dengan alasan. Yang boleh memutuskan adalah peran di
   `services.approval_roles` atau pengguna di `approval_users`. Bila keduanya kosong, yang
   memutuskan adalah kepala_sekolah, kepala_tu, atau admin.
4. Setelah disetujui, petugas TU mengunggah hasil atau menandai tiket selesai. Tiket yang
   belum disetujui tidak bisa diselesaikan. Tiket offline dan produk fisik masuk daftar
   **siap diambil** di dashboard loket sampai diserahkan.

## Tiket & dokumen

`TicketPolicy::view` mengizinkan pemohon pemilik tiket dan semua staf. Dokumen persyaratan dan
hasil layanan disimpan di disk privat dan hanya diunduh lewat route yang memanggil policy ini.

## Akun awal

- **Lokal:** `php artisan db:seed` membuat akun demo, misalnya `ptsp@mtsn2malang.sch.id` / `admin123`.
- **Produksi:** hanya akun admin, dengan `ADMIN_PASSWORD` dari `.env` atau password acak yang
  ditampilkan sekali. Akun staf lain dibuat dari panel admin.

Setelah mengubah peran atau permission lewat database, jalankan `php artisan permission:cache-reset`.
