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
| `kepala_sekolah` | Monitoring, persetujuan layanan, laporan SKM/SPAK | `/backoffice/dashboard` | Ya |
| `kepala_tu` | Kontrol pelayanan, koordinasi petugas, persetujuan administratif | `/backoffice/dashboard` | Ya |
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

| Permission | Melindungi |
| --- | --- |
| `frontdesk.access` | `FrontDeskController` (`/frontdesk/*`) |
| `backoffice.access` | `BackOfficeController` (`/backoffice/*`) |
| `supervision.access` | `/supervision/management`, hasil survei, kinerja |

Area `/admin/*` mewajibkan peran `admin`. Panel Filament `/cp` diatur oleh
`User::canAccessPanel()`.

## Tiket & dokumen

`TicketPolicy::view` mengizinkan pemohon pemilik tiket dan semua staf. Dokumen persyaratan dan
hasil layanan disimpan di disk privat dan hanya diunduh lewat route yang memanggil policy ini.

## Akun awal

- **Lokal:** `php artisan db:seed` membuat akun demo, misalnya `ptsp@mtsn2malang.sch.id` / `admin123`.
- **Produksi:** hanya akun admin, dengan `ADMIN_PASSWORD` dari `.env` atau password acak yang
  ditampilkan sekali. Akun staf lain dibuat dari panel admin.

Setelah mengubah peran atau permission lewat database, jalankan `php artisan permission:cache-reset`.
