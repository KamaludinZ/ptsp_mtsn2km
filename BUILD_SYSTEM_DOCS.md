# Build System Dokumentasi - Tanpa Vite

## Ringkasan
Sistem ini kini berjalan tanpa Vite, menggunakan helper asset tradisional untuk memuat file CSS dan JS yang telah dikompilasi sebelumnya.

## Perubahan Utama
1. Semua file layout mengganti `@vite` dengan helper function `asset_css()` dan `asset_js()`
2. Helper AssetHelper diubah untuk sepenuhnya mengabaikan Vite, menggunakan file build statis
3. Sistem build sekarang menghasilkan file manifest.json yang membantu mencocokkan file asli dengan file hasil kompilasi

## Struktur Build
File-file yang telah dikompilasi disimpan di `public/build/assets/` dan termasuk:
- CSS files: `app-DfRos30h.css`, `bootstrap-custom-Bi4iqWMz.css`, dll.
- JS files: `app-C_oYXcqv.js`, `bootstrap-bundle-DyPRAKW-.js`, dll.

## Helper Functions
- `asset_css($path)` - Menghasilkan tag `<link>` untuk file CSS
- `asset_js($path, $defer = true)` - Menghasilkan tag `<script>` untuk file JS
- `compiled_asset($path)` - Menghasilkan URL lengkap ke file asset

## Penanganan Asset
AssetHelper mencari file melalui:
1. Membaca `public/build/manifest.json` untuk mencocokkan file asli dengan file hasil kompilasi
2. Jika file tidak ditemukan di manifest, mencari file dengan nama yang cocok di `public/build/assets/`
3. Jika tetap tidak ditemukan, kembali ke fungsi `asset()` biasa

## Mode Kerja
- **Development**: Menggunakan file build statis yang sama seperti production
- **Production**: Menggunakan file build statis yang sama seperti development
- Tidak ada perbedaan perilaku berdasarkan environment

## Keuntungan
- Tidak ada ketergantungan pada Vite dev server
- Waktu startup aplikasi lebih cepat
- Lebih stabil dalam lingkungan production
- Lebih mudah untuk deployment ke lingkungan terbatas