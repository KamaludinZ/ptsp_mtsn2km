# PTSP MTsN 2 Kota Malang

Aplikasi **Pelayanan Terpadu Satu Pintu** MTsN 2 Kota Malang: satu pintu, banyak jalur, satu alur.
Permohonan layanan dari jalur **online** (portal) dan **offline** (loket) masuk ke satu antrian
back-office yang sama, sesuai standar pelayanan Permen PANRB 15/2014.

## Modul

| Area | Fungsi | URL |
| --- | --- | --- |
| Portal publik | Katalog layanan (14 komponen standar pelayanan), lacak tiket | `/services`, `/tracking` |
| Pengaduan | Pengaduan masyarakat (Dumas) dan Whistleblowing (anonim), lacak pengaduan | `/complaints`, `/whistleblowing` |
| Survei | SKM dan SPAK 3 langkah (identitas, SKM, SPAK) sesuai Permenpan RB 14/2017 | `/survey` |
| Buku tamu | Pendaftaran tamu dan pemohon walk-in | `/visitor-book` |
| Portal pemohon | Panel Filament: pengajuan layanan, riwayat tiket, unduh hasil, tanda terima, profil | `/portal` |
| Panel staf | Panel Filament untuk semua peran staf: dashboard per peran, loket (registrasi, buku tamu), tiket layanan, persetujuan pimpinan, kinerja, pengaduan & WBS, laporan SKM/SPAK, master data, survei, keamanan | `/cp` |

Rancangan lengkap ada di [`docs/`](docs):
[rancangan aplikasi](docs/rancangan_app.md),
[arsitektur paket](docs/arsitektur_paket.md),
[survei](docs/survei.md) dan [sistem survei](docs/SURVEY_SYSTEM_DOCUMENTATION.md),
[Dumas vs Whistleblowing](docs/perbedaan_dumas_wistle.md),
[peran & hak akses](docs/ROLES_PERMISSIONS.md),
[skema database](docs/database_schema.md),
[panduan deploy](docs/DEPLOYMENT.md).

## Teknologi

- Laravel 12, PHP 8.2+
- **PostgreSQL** (satu-satunya database yang didukung, termasuk untuk tes)
- Filament 3 (panel `/cp`), Livewire 3, Laravel Breeze
- Blade + Bootstrap 5 + Tailwind/DaisyUI, dibangun dengan Vite 7
- spatie/laravel-permission, spatie/laravel-activitylog, spatie/laravel-medialibrary

## Menjalankan secara lokal

Prasyarat: PHP 8.2+ dengan ekstensi `pdo_pgsql`, Composer, Node.js 20+, PostgreSQL 14+
(misalnya lewat Laragon).

1. Buat database dan user PostgreSQL (ganti password di file terlebih dahulu):
   ```bash
   psql -U postgres -f database/setup_postgres.sql
   ```
2. Salin `.env.example` ke `.env` dan samakan `DB_PASSWORD`.
3. Jalankan setup:
   ```bash
   scripts/dev-setup.sh      # Linux/macOS
   scripts\dev-setup.bat     # Windows / Laragon
   ```
4. `php artisan serve`, lalu buka http://127.0.0.1:8000

Seeder lokal membuat akun demo (admin: `ptsp@mtsn2malang.sch.id` / `admin123`), layanan,
pertanyaan survei, serta data contoh. **Di produksi** seeder hanya membuat peran, layanan,
pertanyaan survei, dan satu admin dengan `ADMIN_PASSWORD` atau password acak.

## Tes

Tes berjalan di PostgreSQL (database `ptsp_testing`, lihat `phpunit.xml.dist`):

```bash
createdb ptsp_testing
php artisan test
```

CI (GitHub Actions) menjalankan tes yang sama dengan PostgreSQL 16 dan `composer audit`.

## Deploy

- **Docker / Coolify (disarankan):** lihat [docs/DEPLOY_COOLIFY.md](docs/DEPLOY_COOLIFY.md).
  `docker-compose.yml` menjalankan aplikasi dan PostgreSQL 16. Migrasi, data awal, dan
  cache berjalan otomatis setiap deploy.
- **Server biasa / cPanel:** lihat [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md). Siapkan `.env` dari
  `.env.production.example`, lalu jalankan `scripts/build-production.sh` (atau `.bat`).

## Kontak

MTsN 2 Kota Malang, Jl. Raya Cemorokandang 77, Kota Malang.
Senin–Kamis 07.00–15.00 WIB, Jumat 07.00–11.00 WIB.
