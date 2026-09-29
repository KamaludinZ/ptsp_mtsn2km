# Peran & Hak Akses

Peran dikelola dengan `spatie/laravel-permission` dan dibuat oleh `database/seeders/UserSeeder.php`.
Nama peran di bawah ini adalah satu-satunya yang dicek oleh aplikasi. Nama lama (`tu`, `petugas_tu`,
`petugas_loket`, `kepala-sekolah`, `kepala-tu`) dipindahkan otomatis oleh migration
`2026_09_25_090000_merge_legacy_staff_roles`.

## Peran staf

Didefinisikan di `App\Models\User::STAFF_ROLES`.

Semua peran staf bekerja di panel Filament **`/cp`**. Dashboard `/cp` menampilkan ringkasan
milik peran masing-masing, dan menu samping hanya berisi halaman yang boleh dibuka peran itu.

| Peran | Untuk | Menu di `/cp` |
| --- | --- | --- |
| `admin` | Administrator sistem | Semua menu, termasuk master data, pengguna, survei, keamanan |
| `kepala_sekolah` | Dashboard pimpinan (Modul 13), persetujuan (Modul 8), laporan | Persetujuan, Kinerja Pelayanan, Tiket Layanan, Pengaduan & WBS, Laporan SKM & SPAK |
| `kepala_tu` | Seperti kepala sekolah, ditambah kontrol loket & back office | Menu pimpinan + Registrasi Layanan, Buku Tamu |
| `back_office` | Petugas TU: verifikasi, disposisi, status tiket, unggah hasil | Tiket Layanan, Kinerja Pelayanan |
| `front_desk` | Petugas loket: buku tamu, registrasi layanan offline, serah produk | Registrasi Layanan, Buku Tamu, Tiket Layanan |
| `supervisor` | Pengawasan: kinerja, pengaduan, laporan survei | Kinerja Pelayanan, Tiket Layanan, Pengaduan & WBS, Laporan SKM & SPAK |

Pemohon memakai panel terpisah **`/portal`**: beranda, Permohonan Saya (detail, unduh hasil,
tanda terima), Ajukan Layanan (`/portal/ajukan?layanan=<slug>`, dibuka dari katalog), dan profil.

`App\Models\User::LEADERSHIP_ROLES` = `kepala_sekolah`, `kepala_tu`, `supervisor`.

Pengalihan setelah login ada di satu tempat, yaitu `get_dashboard_route_for_user()`
(`app/Helpers/helpers.php`): staf ke `/cp`, pemohon ke `/portal`.

## Peran pemohon

`guru`, `pegawai`, `siswa`, `walimurid`, `alumni`, `instansi`, `umum`. Peran ini sama dengan
`user_type` dan menentukan layanan yang tampil di katalog (`Service::availableFor()`).

- Pendaftaran umum otomatis mendapat peran `umum`.
- Civitas (siswa, guru, pegawai, dan lain-lain) mendaftar dengan kode registrasi 10 digit yang dibuat admin.

## Permission area staf

Permission area dimiliki **peran**, bukan pengguna. Pemetaannya ada di satu tempat,
`App\Support\RoleAccess::ROLE_PERMISSIONS`, dan diterapkan oleh seeder serta migration
`2026_09_25_100000_grant_area_permissions_to_roles`.

| Permission | Membuka | Peran |
| --- | --- | --- |
| `frontdesk.access` | Registrasi Layanan, Buku Tamu (daftar tamu, check-out, cetak kartu), serah produk | admin, kepala_tu, front_desk |
| `backoffice.access` | Aksi proses tiket (tugaskan, status, catatan, berkas, hasil, workflow), Kinerja Pelayanan | admin, kepala_sekolah, kepala_tu, back_office |
| `supervision.access` | Kinerja Pelayanan | admin, kepala_sekolah, kepala_tu, supervisor |

Hak lain:

| Halaman | Peran |
| --- | --- |
| Persetujuan (`/cp/pimpinan/persetujuan`) | admin, kepala_sekolah, kepala_tu (`RoleAccess::LEADERSHIP`) |
| Pengaduan & WBS, Laporan SKM & SPAK | admin, supervisor, kepala_sekolah, kepala_tu (`RoleAccess::COMPLAINT_HANDLERS`) |
| Tiket Layanan (daftar & detail) | semua staf (`TicketPolicy::viewAny`) |
| Master data, pengguna, peran, survei, pengaturan, Keamanan Sistem | admin (`App\Filament\Concerns\AdminOnly`) |

Akses panel diatur oleh `User::canAccessPanel()`: staf aktif ke `/cp`, pemohon aktif ke
`/portal`; akun nonaktif ditolak. Login untuk kedua panel lewat `/login` situs (dengan kode
verifikasi). Tes: `FilamentPanelsTest` (halaman per peran), `RolesAndNavigationTest` (menu per
peran) dan `DashboardNavigationTest` (setiap tautan dashboard setiap akun seed bisa dibuka).

Alamat lama (`/pimpinan`, `/frontdesk/dashboard`, `/backoffice/dashboard`,
`/supervision/management`, `/portal/dashboard`, `/admin`) dialihkan ke panel baru.

## Alur tiket & persetujuan

1. Pemohon mengajukan online, atau petugas loket mendaftarkannya offline. Target selesai
   (`estimated_completion_date`) dihitung dari jangka waktu standar layanan (hari kerja).
2. Petugas TU memverifikasi (`verified`) lalu memproses (`in_process`).
3. Pimpinan memutuskan di menu **Persetujuan** (`/cp/pimpinan/persetujuan`) atau di halaman tiket: **setujui**, dengan memilih tanda tangan TTE
   atau TTD, atau **tolak** dengan alasan. Yang boleh memutuskan adalah peran di
   `services.approval_roles` atau pengguna di `approval_users`. Bila keduanya kosong, yang
   memutuskan adalah kepala_sekolah, kepala_tu, atau admin.
4. Setelah disetujui, petugas TU mengunggah hasil atau menandai tiket selesai. Tiket yang
   belum disetujui tidak bisa diselesaikan. Tiket offline dan produk fisik masuk daftar
   **siap diambil** di dashboard loket sampai diserahkan.

Semua aturan di atas ada di `App\Services\TicketService` (loket: `FrontDeskService`,
pengaduan: `ComplaintService`), dipakai oleh halaman Filament dan diuji di `ServiceWorkflowTest`.

## Tiket & dokumen

`TicketPolicy::view` mengizinkan pemohon pemilik tiket dan semua staf. Dokumen persyaratan dan
hasil layanan disimpan di disk privat dan hanya diunduh lewat route yang memanggil policy ini.

## Akun awal

- **Lokal:** `php artisan db:seed` membuat akun demo, misalnya `ptsp@mtsn2malang.sch.id` / `admin123`.
- **Produksi:** hanya akun admin, dengan `ADMIN_PASSWORD` dari `.env` atau password acak yang
  ditampilkan sekali. Akun staf lain dibuat dari panel admin.

Setelah mengubah peran atau permission lewat database, jalankan `php artisan permission:cache-reset`.
