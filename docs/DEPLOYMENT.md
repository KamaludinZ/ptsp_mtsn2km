# Panduan Deploy (PostgreSQL)

## 1. Server

- PHP 8.2+ dengan ekstensi `pdo_pgsql`, `mbstring`, `intl`, `gd`, `zip`, `fileinfo`
- PostgreSQL 14+
- Composer dan Node.js 20+ (atau build aset di komputer lain lalu unggah `public/build`)

## 2. Database

```bash
# edit password di file ini terlebih dahulu
psql -U postgres -f database/setup_postgres.sql
```

## 3. Konfigurasi

Salin `.env.production.example` menjadi `.env`, lalu isi:

- `APP_URL`, `DB_*` (hanya `pgsql`)
- `APP_KEY`: buat baru dengan `php artisan key:generate`. **Jangan pakai ulang key dari
  repositori lama**, karena key tersebut pernah ter-commit.
- `ADMIN_PASSWORD` (opsional) untuk akun admin awal. Kalau dikosongkan, `db:seed` membuat
  password acak dan menampilkannya sekali.
- Pengaturan mail dan WhatsApp jika dipakai.

## 4. Build

```bash
scripts/build-production.sh     # atau scripts\build-production.bat
php artisan db:seed --force     # hanya pada instalasi pertama
```

Skrip build menjalankan `composer install --no-dev`, `npm ci && npm run build`,
`migrate --force`, `storage:link`, `filament:upgrade`, dan `optimize`.

## 5. Web root

Arahkan document root ke folder **`public/`**. Jangan arahkan ke folder utama aplikasi.

### cPanel / subdomain

Jika document root subdomain tidak bisa diarahkan ke `public/`:

1. Unggah seluruh aplikasi ke folder di luar web root, misalnya `~/ptsp-app`.
2. Salin isi `public/` ke folder web root subdomain, misalnya `~/public_html/ptsp`.
3. Ubah `index.php` di web root agar menunjuk ke aplikasi:
   ```php
   if (file_exists($maintenance = __DIR__.'/../../ptsp-app/storage/framework/maintenance.php')) {
       require $maintenance;
   }
   require __DIR__.'/../../ptsp-app/vendor/autoload.php';
   (require_once __DIR__.'/../../ptsp-app/bootstrap/app.php')
       ->handleRequest(Illuminate\Http\Request::capture());
   ```
4. Buat symlink `storage` di web root ke `~/ptsp-app/storage/app/public`.

## 6. Setelah deploy

- Pastikan `APP_DEBUG=false` dan `APP_ENV=production`.
- Ganti password semua akun yang pernah dibuat dengan password demo (misalnya `admin123`).
- Dokumen pemohon disimpan privat di `storage/app/private` dan hanya bisa diunduh lewat
  aplikasi. Ikutkan folder ini dalam backup.
