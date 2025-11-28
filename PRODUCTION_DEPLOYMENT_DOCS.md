# Deployment Production (Tanpa Vite Server)

## Ringkasan
Aplikasi ini menggunakan Vite untuk development dan built assets untuk production. Dokumen ini menjelaskan cara melakukan deployment ke lingkungan production.

## Proses Deployment

### 1. Build Assets untuk Production
Sebelum deployment, pastikan untuk menjalankan:

```bash
npm run build
```

Perintah ini akan:
- Mengkompilasi semua asset CSS/JS
- Menghasilkan file-file versi production di `public/build/assets/`
- Membuat `public/build/manifest.json` yang dibutuhkan Laravel

### 2. Update Environment
Pastikan di file `.env` production:

```
APP_ENV=production
APP_DEBUG=false
ASSET_MODE=compiled
```

### 3. Jalankan Artisan Commands
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4. Jalankan Migrasi (jika perlu)
```bash
php artisan migrate --force
```

## Cara Kerja Sistem Asset

### Development
- Mode: `local` environment dengan Vite server berjalan
- Menggunakan: `@vite` directive yang terhubung ke Vite dev server (localhost:5173)
- Fitur hot-reload dan build cepat

### Production
- Mode: `production` environment atau Vite server tidak berjalan
- Menggunakan: File-file yang telah dibangun melalui `vite build`
- Membaca dari `public/build/manifest.json` untuk URL asset yang benar

## Troubleshooting

### Error saat loading asset di production
- Pastikan `npm run build` telah dijalankan
- Pastikan file `public/build/manifest.json` ada
- Pastikan file-file asset ada di `public/build/assets/`
- Jalankan `php artisan view:clear` untuk membersihkan cache view

### Perubahan tidak muncul di development
- Pastikan Vite dev server berjalan (`npm run dev`)
- Cek konsol browser untuk error asset loading

## File-file Penting
- `vite.config.js` - Konfigurasi Vite
- `public/build/` - Direktori hasil build production
- `public/build/manifest.json` - File mapping asset untuk production
- `config/assets.php` - Konfigurasi mode asset loading