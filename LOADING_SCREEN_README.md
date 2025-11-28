# Loading Screen Documentation

## Gambaran Umum
Loading screen modern dan ringan yang ditambahkan ke aplikasi PTSP MTsN 2 Kota Malang untuk memberikan pengalaman pengguna yang lebih baik saat halaman sedang dimuat.

## Fitur-fitur

### 1. **Desain Modern**
- Gradient background dengan warna tema hijau aplikasi
- Spinner ring yang berputar dengan 3 lapisan
- Animasi dots bouncing
- Progress bar dengan sliding effect

### 2. **Ringan & Performa Tinggi**
- Pure CSS animations (tidak menggunakan library eksternal)
- File CSS yang sangat kecil
- Tidak membebani waktu loading halaman

### 3. **Responsif**
- Menyesuaikan ukuran di berbagai device
- Mobile-friendly dengan ukuran yang lebih kecil di layar kecil

### 4. **Accessible**
- Mendukung `prefers-reduced-motion` untuk pengguna dengan kebutuhan khusus
- Animasi akan dinonaktifkan secara otomatis jika user mengaktifkan reduced motion

### 5. **Automatic**
- Muncul otomatis saat halaman mulai dimuat
- Menghilang dengan smooth transition saat halaman selesai dimuat
- Fallback timeout 5 detik untuk mencegah stuck

## File yang Ditambahkan/Dimodifikasi

### File Baru
- `resources/css/loading.css` - CSS untuk loading screen

### File yang Dimodifikasi
- `resources/views/layouts/app.blade.php` - Menambahkan loading overlay dan script
- `resources/views/layouts/public.blade.php` - Menambahkan loading overlay dan script

## Cara Kerja

1. **Saat Halaman Mulai Dimuat:**
   - Loading overlay dengan `z-index: 99999` menutupi seluruh layar
   - Menampilkan animasi spinner, dots, dan progress bar

2. **Saat Halaman Selesai Dimuat:**
   - Event listener `window.addEventListener('load')` mendeteksi halaman selesai
   - Menambahkan class `hidden` dengan delay 300ms
   - Loading overlay fade out dengan transisi smooth

3. **Fallback:**
   - Jika halaman tidak selesai dimuat dalam 5 detik
   - Loading screen tetap akan dihilangkan untuk mencegah stuck

## Kustomisasi

### Mengubah Warna
Edit file `resources/css/loading.css`, bagian:
```css
#page-loading-overlay {
    background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
}
```

### Mengubah Teks
Edit file layout (`app.blade.php` atau `public.blade.php`):
```html
<div class="loading-text">PTSP MTsN 2 Kota Malang</div>
<div class="loading-subtext">Memuat Halaman...</div>
```

### Mengubah Durasi Animasi
Edit file `resources/css/loading.css`, cari `@keyframes` dan ubah durasinya:
```css
animation: spin 1.2s linear infinite; /* Ubah 1.2s sesuai kebutuhan */
```

### Menonaktifkan Loading Screen
Jika ingin menonaktifkan loading screen, cukup hapus atau comment out elemen:
```html
<!-- <div id="page-loading-overlay">...</div> -->
```

## Browser Support
- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- Mobile browsers: ✅ Full support

## Testing

### Testing Manual
1. Buka halaman aplikasi
2. Loading screen harus muncul saat halaman dimuat
3. Loading screen harus hilang dengan smooth setelah halaman selesai dimuat

### Testing dengan Slow Connection
1. Buka Chrome DevTools (F12)
2. Pilih tab Network
3. Set throttling ke "Slow 3G"
4. Refresh halaman
5. Loading screen harus terlihat lebih lama

## Troubleshooting

### Loading Screen Tidak Muncul
- Pastikan sudah menjalankan `npm run build` atau `npm run dev`
- Clear browser cache
- Periksa console untuk error JavaScript

### Loading Screen Tidak Hilang
- Periksa console browser untuk error
- Pastikan tidak ada JavaScript error yang memblokir event listener
- Fallback timeout akan menghilangkannya setelah 5 detik

### Animasi Tidak Smooth
- Periksa apakah browser mendukung CSS animations
- Disable hardware acceleration jika ada glitch
- Periksa setting "Reduce Motion" di sistem operasi

## Performance Notes
- Loading screen menambahkan sekitar 3-4KB ke ukuran CSS total
- Tidak ada impact pada waktu loading karena menggunakan pure CSS
- Semua animasi menggunakan GPU acceleration untuk performa optimal

## Maintenance
File ini tidak memerlukan maintenance khusus. Namun jika ada update Vite atau Laravel, pastikan:
- `loading.css` tetap termasuk dalam array `@vite()`
- Script loading tetap berada setelah semua konten

## Changelog
- **2025-11-28**: Initial implementation
  - Menambahkan modern loading screen
  - Support untuk reduced motion
  - Responsive design
  - Dark mode support
