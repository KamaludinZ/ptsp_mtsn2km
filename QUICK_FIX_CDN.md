# Quick Fix: Remove All CDN Links

## 🎯 Tujuan
Menghapus semua CDN external links dan menggunakan local assets.

---

## 📋 Langkah Cepat

### Step 1: Install Dependencies Baru

```bash
npm install
```

Ini akan install:
- ✅ `@fortawesome/fontawesome-free` (Font Awesome local)
- ✅ `aos` (Animate On Scroll local)
- ✅ Bootstrap (sudah ada)
- ✅ Bootstrap Icons (sudah ada)

### Step 2: Build Assets

```bash
npm run build
```

### Step 3: Update View Files

Ganti semua CDN links dengan `@vite` directive.

---

## 🔧 Manual Update (Copy-Paste)

### File: `resources/views/welcome.blade.php`

**HAPUS semua baris ini:**
```blade
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link rel="preload" href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" as="style">
<link href="https://fonts.bunny.net/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="..." crossorigin="anonymous">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="..." crossorigin="anonymous" referrerpolicy="no-referrer">

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
```

**GANTI dengan:**
```blade
<!-- Vite Assets -->
@vite([
    'resources/css/bootstrap-custom.css',
    'resources/js/bootstrap-bundle.js',
    'resources/css/app.css',
    'resources/js/app.js'
])
```

**DAN HAPUS di bagian bawah (sebelum `</body>`):**
```blade
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="..." crossorigin="anonymous"></script>

<!-- AOS Animation -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
```

---

### File: `resources/views/layouts/public.blade.php`

**HAPUS:**
```blade
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/..." rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@..." rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@..."></script>
```

**GANTI dengan:**
```blade
@vite([
    'resources/css/bootstrap-custom.css',
    'resources/js/bootstrap-bundle.js',
    'resources/css/app.css',
    'resources/js/app.js'
])
```

---

### File: `resources/views/layouts/app.blade.php`

**HAPUS:**
```blade
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/..." rel="stylesheet">
```

**GANTI dengan:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

### File: `resources/views/layouts/guest.blade.php`

**HAPUS:**
```blade
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/..." rel="stylesheet">
```

**GANTI dengan:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

### File: `resources/views/layouts/auth.blade.php`

**HAPUS:**
```blade
<link href="https://fonts.bunny.net/..." rel="stylesheet">
```

**GANTI dengan:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

### File: `resources/views/errors/layout.blade.php`

**HAPUS:**
```blade
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/..." rel="stylesheet">
```

**GANTI dengan:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

---

### File: `resources/views/backoffice/layout.blade.php`

**HAPUS semua CDN links**

**GANTI dengan:**
```blade
@vite([
    'resources/css/bootstrap-custom.css',
    'resources/js/bootstrap-bundle.js',
    'resources/css/app.css',
    'resources/js/app.js'
])
```

---

### File: `resources/views/frontdesk/layout.blade.php`

**HAPUS semua CDN links**

**GANTI dengan:**
```blade
@vite([
    'resources/css/bootstrap-custom.css',
    'resources/js/bootstrap-bundle.js',
    'resources/css/app.css',
    'resources/js/app.js'
])
```

---

### File: `resources/views/supervision/layout.blade.php`

**HAPUS semua CDN links**

**GANTI dengan:**
```blade
@vite([
    'resources/css/bootstrap-custom.css',
    'resources/js/bootstrap-bundle.js',
    'resources/css/app.css',
    'resources/js/app.js'
])
```

---

## 🎨 Template Layout Lengkap

### Untuk halaman dengan Bootstrap + Tailwind:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    {{-- Vite Assets: Bootstrap, Font Awesome, AOS, Tailwind --}}
    @vite([
        'resources/css/bootstrap-custom.css',
        'resources/js/bootstrap-bundle.js',
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
```

### Untuk halaman dengan Tailwind saja:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    {{-- Vite Assets: Tailwind only --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
```

---

## ✅ Verifikasi

Setelah update, jalankan:

```bash
# 1. Clear caches
php artisan view:clear
php artisan config:clear

# 2. Rebuild (jika belum)
npm run build

# 3. Test
php artisan serve
```

### Check di Browser:

1. Buka http://localhost:8000
2. Tekan F12 (Developer Tools)
3. Tab "Network"
4. Reload halaman
5. Pastikan **TIDAK ADA** request ke:
   - ❌ fonts.bunny.net
   - ❌ cdn.jsdelivr.net
   - ❌ cdnjs.cloudflare.com
   - ❌ unpkg.com

6. Semua assets harus load dari:
   - ✅ `/build/assets/app-*.css`
   - ✅ `/build/assets/bootstrap-*.css`
   - ✅ `/build/assets/bootstrap-*.js`
   - ✅ `/build/assets/app-*.js`

7. Check Console (F12):
   - ✅ Tidak ada CSP violations
   - ✅ Tidak ada "ReferenceError: AOS is not defined"
   - ✅ Tidak ada "ReferenceError: bootstrap is not defined"

---

## 🔍 Troubleshooting

### AOS tidak work?
```bash
# AOS sekarang auto-init di bootstrap-bundle.js
# Pastikan @vite include bootstrap-bundle.js
```

### Font Awesome tidak muncul?
```bash
# Font Awesome ada di bootstrap-bundle.js
# Pastikan sudah npm install dan npm run build
```

### Bootstrap tidak work?
```bash
# Pastikan urutan import benar:
@vite([
    'resources/css/bootstrap-custom.css',  // CSS dulu
    'resources/js/bootstrap-bundle.js',    // JS kedua
    'resources/css/app.css',
    'resources/js/app.js'
])
```

---

## 🎯 One-Liner Fix

Cari pattern CDN dan ganti manual:

```regex
Cari:    https://(fonts\.bunny\.net|cdn\.jsdelivr\.net|cdnjs\.cloudflare\.com|unpkg\.com).*
Ganti:   {{-- Removed CDN - using local assets --}}
```

Lalu tambahkan `@vite` di `<head>`.

---

## 📦 What's Included in bootstrap-bundle.js?

File `resources/js/bootstrap-bundle.js` sekarang includes:
- ✅ Bootstrap CSS
- ✅ Bootstrap JavaScript
- ✅ Popper.js (dependency Bootstrap)
- ✅ Bootstrap Icons
- ✅ Font Awesome (all icons)
- ✅ AOS (Animate On Scroll - auto-initialized)

Jadi **TIDAK PERLU** tambah CDN lagi!

---

## 🚀 Deployment

Setelah semua CDN di-remove:

```bash
# Build untuk production
npm run build

# Upload public/build/ ke server
# NO NEED to upload node_modules!
```

---

**Selesai!** Semua CDN sudah di-remove dan diganti dengan local assets. ✅
