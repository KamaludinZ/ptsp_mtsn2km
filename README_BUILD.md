# Build System Tanpa Vite

Proyek ini awalnya menggunakan Vite untuk manajemen asset, namun sekarang telah dimodifikasi untuk berjalan tanpa Vite, menggunakan sistem asset tradisional.

## Keunggulan Sistem Baru

- **Tidak bergantung pada Vite dev server** - Aplikasi langsung bisa diakses tanpa harus menjalankan `npm run dev`
- **Waktu startup lebih cepat** - Tidak menunggu Vite dev server
- **Lebih stabil di production** - Tidak ada masalah dengan koneksi ke Vite dev server
- **Lebih mudah untuk deployment** - Tidak perlu mengelola proses Vite terpisah

## Perubahan yang Dilakukan

### 1. Helper Function
Ditambahkan `AssetHelper` di `app/Helpers/AssetHelper.php` untuk menggantikan fungsi `@vite`:
- `asset_css()` menggantikan `@vite` untuk CSS
- `asset_js()` menggantikan `@vite` untuk JavaScript

### 2. Update Layout Files
Semua file layout berikut telah diperbarui:
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/auth.blade.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/supervision/layout.blade.php`
- `resources/views/onlineportal/layout.blade.php`
- `resources/views/frontdesk/layout.blade.php`
- `resources/views/backoffice/layout.blade.php`
- `resources/views/errors/layout.blade.php`
- `resources/views/welcome.blade.php`

### 3. Sistem Pembacaan Asset
AssetHelper membaca file dari `public/build/manifest.json` untuk mencocokkan file asli dengan file hasil kompilasi, dan mencari file secara otomatis jika tidak ditemukan di manifest.

## Cara Menjalankan Aplikasi

### Development
```bash
# Install dependencies
composer install
npm install

# Build assets (if needed)
npm run build

# Serve application
php artisan serve
```

### Production
```bash
# Jalankan deployment script
composer install --optimize-autoloader --no-dev
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan serve
```

## Penanganan Build Asset

Jika perlu membuat build baru:
```bash
npm run build
```

Atau gunakan skrip yang disediakan:
```bash
# Windows
build-assets.bat

# Production deployment
production-deploy.bat
```

## Troubleshooting

Jika asset tidak muncul:
1. Pastikan `public/build/manifest.json` ada dan terbaru
2. Pastikan file-file asset berada di `public/build/assets/`
3. Jalankan `php artisan view:clear` dan `php artisan cache:clear`
4. Jalankan ulang `npm run build` jika perlu

## File-file Penting
- `app/Helpers/AssetHelper.php` - Helper utama untuk manajemen asset
- `public/build/manifest.json` - File mapping antara file asli dan file hasil kompilasi
- `public/build/assets/` - Folder tempat file-file asset hasil kompilasi disimpan
- `BUILD_SYSTEM_DOCS.md` - Dokumentasi teknis sistem build